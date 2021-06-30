<?php

/**
 * 活动服务
 * User: Jmiy
 * Date: 2019-06-14
 * Time: 16:50
 */

namespace App\Services;

use Illuminate\Support\Arr;
use App\Util\Cache\CacheManager as Cache;
use Carbon\Carbon;
use App\Models\Activity;
use App\Util\FunctionHelper;
use App\Util\Constant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Util\Response;

class ActivityService extends BaseService {

    /**
     * 检查记录是否存在
     * @param int $storeId
     * @param string $account
     * @param boolean $getData true:获取数据  false:获取是否存在标识
     * @return bool|object|null $rs
     */
    public static function exists($storeId = 0, $name = '', $getData = false) {

        $where = [];

        if ($name) {
            $where[Constant::DB_TABLE_NAME] = $name;
        }

        return static::existsOrFirst($storeId, '', $where, $getData);
    }

    /**
     * 添加积分记录
     * @param $storeId
     * @param $data
     * @return bool
     */
    public static function insert($storeId, $data) {
        return static::getModel($storeId)->insertGetId($data);
    }

    /**
     * 获取db query
     * @param array $where
     * @return \Illuminate\Database\Query\Builder|static $query
     */
    public static function getQuery($storeId, $where = []) {
        return static::getModel($storeId)->buildWhere($where);
    }

    /**
     * 获取公共参数
     * @param array $params 请求参数
     * @return array
     */
    public static function getPublicData($params, $order = []) {

        $where = [];

        $type = Arr::get($params, 'type', 0); //活动类型 1:日常活动 2:注册拉新活动 3:大型活动
        if ($type) {
            $where[] = ['type', '=', $type];
        }

        $name = Arr::get($params, Constant::DB_TABLE_NAME, '');
        if ($name) {//活动名称
            $where[] = [Constant::DB_TABLE_NAME, '=', $name];
        }

        $actType = Arr::get($params, Constant::DB_TABLE_ACT_TYPE, '');
        if ($actType) {//活动类型
            $where[] = [Constant::DB_TABLE_ACT_TYPE, '=', $actType];
        }

        $start_time = Arr::get($params, 'start_time', '');
        if ($start_time) {//开始时间
            $where[] = [Constant::DB_TABLE_START_AT, '<=', $start_time];
        }

        $end_time = Arr::get($params, 'end_time', '');
        if ($end_time) {//结束时间
            $where[] = [Constant::DB_TABLE_END_AT, '>=', $end_time];
        }

        $start_at = Arr::get($params, 'start_at', '');
        if ($start_at) {//开始时间
            $where[] = [Constant::DB_TABLE_START_AT, '>=', $start_at];
        }

        $end_at = Arr::get($params, 'end_at', '');
        if ($end_at) {//结束时间
            $where[] = [Constant::DB_TABLE_END_AT, '<=', $end_at];
        }

        $_where = [];
        if (data_get($params, Constant::DB_TABLE_PRIMARY, 0)) {
            $_where[Constant::DB_TABLE_PRIMARY] = $params[Constant::DB_TABLE_PRIMARY];
        }

        if ($where) {
            $_where[] = $where;
        }


        $order = $order ? $order : [Constant::DB_TABLE_PRIMARY, 'desc'];
        return Arr::collapse([parent::getPublicData($params, $order), [
                        Constant::DB_EXECUTION_PLAN_WHERE => $_where,
        ]]);
    }

    /**
     * 获取有效的活动数据
     * @param int $storeId  商城id
     * @param int $type 活动类型 1:日常活动 2:注册拉新活动 3:大型活动
     * @param boolean $isPage 是否分页 true:是  false:否
     * @param int $page 分页页码
     * @param int $pageSize 每个分页记录条数
     * @param array $select 要查询的字段
     * @param boolean $isRaw 是否原始查询语句 true:是 false:否 默认:false
     * @return array|null $data
     */
    public static function getValidData($storeId = 0, $type = 3, $isPage = false, $page = 1, $pageSize = 1, $select = [], $isRaw = false, $isGetQuery = false) {

        if (empty($storeId)) {
            return [];
        }

        $ttl = 2 * 60 * 60; //缓存2小时 单位秒
        $tags = config('cache.tags.activity');
        $key = md5(json_encode(func_get_args()));
        return Cache::tags($tags)->remember($key, $ttl, function () use($storeId, $type, $isPage, $page, $pageSize, $select, $isRaw, $isGetQuery) {
                    $params = [
                        'type' => $type,
                        'page' => $page,
                        'page_size' => $pageSize,
                    ];

                    $_data = static::getPublicData($params);

                    $where = $_data['where'];
                    $order = $_data['order'];
                    $pagination = $_data[Constant::DB_EXECUTION_PLAN_PAGINATION];
                    $limit = $pagination['page_size'];

                    $query = static::getQuery($storeId, $where);
                    $nowTime = Carbon::now()->toDateTimeString();
                    $query = $query->where(function ($query) use($nowTime) {
                                $query->whereNull(Constant::DB_TABLE_START_AT)->orWhere(Constant::DB_TABLE_START_AT, '<=', $nowTime);
                            })
                            ->where(function ($query) use($nowTime) {
                        $query->whereNull(Constant::DB_TABLE_END_AT)->orWhere(Constant::DB_TABLE_END_AT, '>=', $nowTime);
                    });

                    $customerCount = true;
                    if ($isPage) {
                        $customerCount = $query->count();
                        $pagination['total'] = $customerCount;
                        $pagination['total_page'] = ceil($customerCount / $limit);
                    }

                    if (empty($customerCount)) {
                        $query = null;
                        return [
                            'data' => [],
                            Constant::DB_EXECUTION_PLAN_PAGINATION => $pagination,
                        ];
                    }

                    $query = $query->orderBy($order[0], $order[1]);
                    $data = [
                        'query' => $query,
                        Constant::DB_EXECUTION_PLAN_PAGINATION => $pagination,
                    ];

                    $select = $select ? $select : ['*'];

                    return static::getList($data, true, $isPage, $select, $isRaw, $isGetQuery);
                });
    }

    /**
     * 获取有效的活动id
     * @param int $storeId  商城id
     * @param int $type 活动类型 1:日常活动 2:注册拉新活动 3:大型活动
     * @param boolean $isPage 是否分页 true:是  false:否
     * @param int $page 分页页码
     * @param int $pageSize 每个分页记录条数
     * @param array $select 要查询的字段
     * @param boolean $isRaw 是否原始查询语句 true:是 false:否 默认:false
     * @return array|null $data
     */
    public static function getValidActIds($storeId = 0, $type = 3, $isPage = false, $page = 1, $pageSize = 1, $select = [], $isRaw = false, $isGetQuery = false) {

        $activityData = static::getValidData($storeId, $type, $isPage, $page, $pageSize, $select, $isRaw, $isGetQuery);

        return $page == 1 && $pageSize == 1 ? Arr::get($activityData, 'data.0.id', 0) : Arr::pluck(Arr::get($activityData, 'data', []), Constant::DB_TABLE_PRIMARY);
    }

    /**
     * 活动类型 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 6:上传图片投票
     * @return array
     */
    public static function getActType($key = null, $default = null) {
        $data = [
            '九宫格' => 1,
            '转盘' => 2,
            '砸金蛋' => 3,
            '翻牌' => 4,
            '邀请好友注册' => 5,
            '上传图片投票' => 6,
            1 => '九宫格',
            2 => '转盘',
            3 => '砸金蛋',
            4 => '翻牌',
            5 => '邀请好友注册',
            6 => '上传图片投票',
        ];
        return data_get($data, $key, $default);
    }

    /**
     * 活动列表
     * @param array $params 请求参数
     * @param boolean $toArray 是否转化为数组 true:是 false:否 默认:true
     * @param boolean $isPage  是否分页 true:是 false:否 默认:true
     * @param array $select  查询字段
     * @param boolean $isRaw 是否原始 select true:是 false:否 默认:false
     * @param boolean $isGetQuery 是否获取 query
     * @param boolean $isOnlyGetCount 是否仅仅获取总记录数
     * @return array|\Illuminate\Database\Eloquent\Builder 列表数据|Builder
     */
    public static function getListData($params, $toArray = true, $isPage = true, $select = [], $isRaw = false, $isGetQuery = false, $isOnlyGetCount = false) {

        $_data = static::getPublicData($params);

        $where = data_get($_data, Constant::DB_EXECUTION_PLAN_WHERE, null);

        if (empty(data_get($params, Constant::DB_TABLE_PRIMARY, 0))) {
            $where[Constant::DB_TABLE_ACT_TYPE] = array_keys(static::getActType());
        }

        $order = data_get($params, 'orderBy', data_get($_data, 'order', []));
        $pagination = data_get($_data, Constant::DB_EXECUTION_PLAN_PAGINATION, []);
        $limit = data_get($params, 'limit', data_get($pagination, Constant::REQUEST_PAGE_SIZE, 10));
        $offset = data_get($params, Constant::DB_EXECUTION_PLAN_OFFSET, data_get($pagination, Constant::DB_EXECUTION_PLAN_OFFSET, 0));
        $storeId = Arr::get($params, Constant::DB_TABLE_STORE_ID, 0);

        $select = $select ? $select : [
            Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_NAME,
            Constant::DB_TABLE_START_AT,
            Constant::DB_TABLE_END_AT,
            Constant::DB_TABLE_CREATED_AT,
            Constant::DB_TABLE_UPDATED_AT,
            Constant::DB_TABLE_ACT_TYPE,
            Constant::DB_TABLE_MARK,
            Constant::FILE_URL,
        ];

        $actTypeData = static::getActType();
        $dbExecutionPlan = [
            Constant::DB_EXECUTION_PLAN_PARENT => [
                Constant::DB_EXECUTION_PLAN_SETCONNECTION => true,
                Constant::DB_EXECUTION_PLAN_STOREID => $storeId,
                Constant::DB_EXECUTION_PLAN_BUILDER => null,
                Constant::DB_EXECUTION_PLAN_MAKE => static::getModelAlias(),
                Constant::DB_EXECUTION_PLAN_FROM => Constant::PARAMETER_STRING_DEFAULT,
                Constant::DB_EXECUTION_PLAN_SELECT => $select,
                Constant::DB_EXECUTION_PLAN_WHERE => $where,
                Constant::DB_EXECUTION_PLAN_ORDERS => [$order],
                Constant::DB_EXECUTION_PLAN_LIMIT => $limit,
                Constant::DB_EXECUTION_PLAN_OFFSET => $offset,
                Constant::DB_EXECUTION_PLAN_IS_PAGE => $isPage,
                Constant::DB_EXECUTION_PLAN_PAGINATION => $pagination,
                Constant::DB_EXECUTION_PLAN_IS_ONLY_GET_COUNT => $isOnlyGetCount,
                Constant::DB_EXECUTION_PLAN_HANDLE_DATA => [
                    'act_type_show' => [
                        Constant::DB_EXECUTION_PLAN_FIELD => Constant::DB_TABLE_ACT_TYPE,
                        Constant::RESPONSE_DATA_KEY => $actTypeData,
                        Constant::DB_EXECUTION_PLAN_DATATYPE => Constant::DB_EXECUTION_PLAN_DATATYPE_STRING,
                        Constant::DB_EXECUTION_PLAN_DATA_FORMAT => Constant::PARAMETER_STRING_DEFAULT,
                        Constant::DB_EXECUTION_PLAN_GLUE => Constant::PARAMETER_STRING_DEFAULT,
                        Constant::DB_EXECUTION_PLAN_DEFAULT => Constant::PARAMETER_STRING_DEFAULT,
                    ],
                ],
                Constant::DB_EXECUTION_PLAN_UNSET => [],
            ],
            Constant::DB_EXECUTION_PLAN_WITH => [
            ],
            Constant::DB_EXECUTION_PLAN_ITEM_HANDLE_DATA => [
                Constant::DB_EXECUTION_PLAN_FIELD => null, //数据字段
                Constant::RESPONSE_DATA_KEY => [], //数据映射map
                Constant::DB_EXECUTION_PLAN_DATATYPE => '', //数据类型
                Constant::DB_EXECUTION_PLAN_DATA_FORMAT => '', //数据格式
                Constant::DB_EXECUTION_PLAN_TIME => '', //时间处理句柄
                Constant::DB_EXECUTION_PLAN_GLUE => '', //分隔符或者连接符
                Constant::DB_EXECUTION_PLAN_IS_ALLOW_EMPTY => true, //是否允许为空 true：是  false：否
                Constant::DB_EXECUTION_PLAN_DEFAULT => '', //默认值$default
                Constant::DB_EXECUTION_PLAN_CALLBACK => [
                    Constant::DB_TABLE_START_AT => function ($item) {
                        return FunctionHelper::getShowTime(data_get($item, Constant::DB_TABLE_START_AT, 'null'));
                    },
                    Constant::DB_TABLE_END_AT => function ($item) {
                        return FunctionHelper::getShowTime(data_get($item, Constant::DB_TABLE_END_AT, 'null'));
                    },
                    'act_time' => function ($item) {
                        $field = [
                            Constant::DB_EXECUTION_PLAN_FIELD => Constant::DB_TABLE_START_AT . Constant::DB_EXECUTION_PLAN_CONNECTION . Constant::DB_TABLE_END_AT,
                            Constant::RESPONSE_DATA_KEY => Constant::PARAMETER_ARRAY_DEFAULT,
                            Constant::DB_EXECUTION_PLAN_DATATYPE => Constant::DB_EXECUTION_PLAN_DATATYPE_STRING,
                            Constant::DB_EXECUTION_PLAN_DATA_FORMAT => Constant::PARAMETER_STRING_DEFAULT,
                            Constant::DB_EXECUTION_PLAN_GLUE => '-',
                            Constant::DB_EXECUTION_PLAN_DEFAULT => Constant::PARAMETER_STRING_DEFAULT,
                        ];
                        return FunctionHelper::handleData($item, $field);
                    },
                ],
                'only' => [
                ],
            ],
                //Constant::DB_EXECUTION_PLAN_DEBUG => true,
        ];

        if (data_get($params, 'isOnlyGetPrimary', false)) {//如果仅仅获取主键id，就不需要处理数据，不关联
            data_set($dbExecutionPlan, 'parent.handleData', []);
            data_set($dbExecutionPlan, 'with', []);
        }

        $dataStructure = 'list';
        $flatten = false;
        $data = FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, $isGetQuery, $dataStructure);

        if ($isGetQuery) {
            return $data;
        }

        return $data;
    }

    /**
     * 获取活动数据
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param array $select 查询的字段
     * @param array $dbExecutionPlan sql执行计划
     * @param boolean $flatten 是否合并
     * @param boolean $isGetQuery 是否获取查询句柄
     * @param array $where where条件
     * @return array 活动数据
     */
    public static function getActivityData($storeId = 0, $actId = 0, $select = [], $dbExecutionPlan = [], $flatten = false, $isGetQuery = false, $where = null, $order = [], $limit = null, $offset = null) {

        $select = $select ? $select : Activity::getColumns();
        if (empty(Arr::exists($dbExecutionPlan, Constant::DB_EXECUTION_PLAN_PARENT))) {
            $where = $where === null ? [Constant::DB_TABLE_PRIMARY => $actId] : $where;
            $parent = FunctionHelper::getExePlan($storeId, null, static::getModelAlias(), '', $select, $where, $order, $limit, $offset);
            data_set($dbExecutionPlan, Constant::DB_EXECUTION_PLAN_PARENT, $parent);
        }

        $dataStructure = 'one';
        return FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, $isGetQuery, $dataStructure);
    }

    /**
     * 获取活动配置数据
     * @param int $storeId 商城id
     * @param int $actId  活动id
     * @param string|array $type 配置类型
     * @param string $key  配置项key
     * @return array $data ['registered_is_need_activate' => [
      'type'=>'registered',
      'key'=>'is_need_activate',
      'value'=>1,
      Constant::RESPONSE_MSG_KEY=>'A verification email is sent to your inbox, please verify account before applying Free Product Testi',
      'landing_url'=>'https://www.xmpow.com/pages/product-activity',
      ]]
     */
    public static function getActivityConfigData($storeId = 0, $actId = 0, $type = '', $key = '', $orderBy = []) {

        if (empty($actId)) {
            return [];
        }

        $tag = 'activity';
        $ttl = config('cache.ttl', 86400); //认证缓存时间 单位秒
        $cacheKey = 'configs:' . md5(json_encode(func_get_args()));
        $parameters = [
            $cacheKey, $ttl, function () use($storeId, $actId, $type, $key, $orderBy) {

                $where = ['activity_id' => $actId];
                if ($type) {
                    data_set($where, 'type', $type);
                }

                if ($key) {
                    data_set($where, 'key', $key);
                }

                //获取活动配置数据
                $dbExecutionPlan = [
                    Constant::DB_EXECUTION_PLAN_PARENT => [
                        'setConnection' => true,
                        'storeId' => $storeId,
                        'builder' => null,
                        'relation' => 'hasMany',
                        'make' => 'ActivityConfig',
                        'from' => '',
                        'select' => [
                            'activity_id',
                            'type',
                            'key',
                            'value',
                            Constant::RESPONSE_MSG_KEY,
                            'landing_url',
                        ],
                        'where' => $where,
                        Constant::DB_EXECUTION_PLAN_ORDERS => $orderBy,
                        'handleData' => [
                            'key' => [
                                'field' => 'type{connection}key',
                                'data' => [],
                                'dataType' => Constant::DB_EXECUTION_PLAN_DATATYPE_STRING,
                                'dateFormat' => '',
                                'glue' => '_',
                                'default' => '',
                            ],
                        ],
                        //'unset' => ['configs'],
                    ],
                    //'sqlDebug' => true,
                ];

                $dataStructure = 'list';
                $flatten = false;
                $data = FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, false, $dataStructure);
                return Arr::pluck($data, null, 'key');
            }
        ];

        return static::handleCache($tag, FunctionHelper::getJobData(static::getNamespaceClass(), 'remember', $parameters, []));

    }

    /**
     * 获取要清空的tags
     * @return array
     */
    public static function getClearTags() {
        return ['activity'];
    }

    /**
     * 获取限制的缓存keys Jmiy 2021-05-20 11:08 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $actionData 缓存操作数据
     * @param array $actData 活动数据
     * @return array 限制的缓存keys
     */
    public static function getLimitKeyData($storeId = 0, $actId = 0, $customerId = 0, $actionData = [], $actData = [])
    {
        $tag = 'lotteryLimit';
        $totalKey = Constant::LOTTERY_TOTAL . ':' . $storeId . ':' . $actId . ':' . $customerId; //累计总次数
        $addTotalKey = Constant::ADD_TOTAL_CACHE . ':' . $storeId . ':' . $actId . ':' . $customerId; //累计增加总次数
        $remainingTotalKey = 'lotteryLimit' . ':' . $storeId . ':' . $actId . ':' . $customerId; //剩余的次数
        $initDbKey = Constant::INIT_DB . ':' . $storeId . ':' . $actId . ':' . $customerId; //初始化活动统计表相关数据

        $requestData = data_get($actionData, Constant::REQUEST_DATA_KEY);
        $actForm = data_get($requestData, Constant::ACT_FORM);//, 'lottery'

        if ($actForm === null) {//如果请求参数没有传 Constant::ACT_FORM，就通过活动配置获取
            $actConfig = static::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
            $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
        }

        if ($actForm === null) {//如果请求参数没有传 Constant::ACT_FORM，并且活动没有配置，就使用 lottery 作为活动形式
            $actForm = 'lottery';
        }

        $limitKey = Constant::ACT_DAY_LIMIT_KEY;//限制级别：'limit':整个活动期间 'month_limit':按月 'week_limit':按周 'day_limit':按天 'hour_limit':按小时 默认：按天
        $timestamp = Carbon::now()->timestamp;
        $ttl = (Carbon::parse(Carbon::now()->rawFormat('Y-m-d 23:59:59'))->timestamp) - $timestamp; //缓存时间 单位秒

        if ($actForm && $actForm != 'lottery') {
            $totalKey .= ':' . $actForm;
            $addTotalKey .= ':' . $actForm;
            $remainingTotalKey .= ':' . $actForm;
            $initDbKey .= ':' . $actForm;
        }

        $activityConfigData = static::getActivityConfigData($storeId, $actId, $actForm, [
            Constant::ACT_LIMIT_KEY,
            Constant::ACT_MONTH_LIMIT_KEY,
            Constant::ACT_WEEK_LIMIT_KEY,
            Constant::ACT_DAY_LIMIT_KEY,
            Constant::ACT_HOUR_LIMIT_KEY,
            'is_credit_count',
            'deduct_credit',
            //'day_max_play_nums'
        ]);

        if (empty($actData)) {
            $actData = static::existsOrFirst($storeId, '', [Constant::DB_TABLE_PRIMARY => $actId], true, [Constant::DB_TABLE_END_AT]);
        }

        $actEndAt = null;
        $endAt = data_get($actData, Constant::DB_TABLE_END_AT, null);
        if ($endAt !== null) {
            $actEndAt = Carbon::parse($endAt)->timestamp;
        }

        $type = 4;//类型 1:整个活动参与次数 2:month活动参与次数 3:week活动参与次数 4:day活动参与次数 5:小时活动参与次数 6:关注 7:分享 8:邀请
        switch (true) {

            case (data_get($activityConfigData, $actForm . '_' . Constant::ACT_HOUR_LIMIT_KEY . Constant::LINKER . Constant::DB_TABLE_VALUE) !== null): //按小时限制
                $limitKey = Constant::ACT_HOUR_LIMIT_KEY;
                $time = strtotime('+1hour');
                $ttl = $actEndAt === null ? ($time - $timestamp) : ($time > $actEndAt ? ($actEndAt - $timestamp) : ($time - $timestamp)); //缓存时间 单位秒
                $type = 5;
                break;

            case (data_get($activityConfigData, $actForm . '_' . Constant::ACT_DAY_LIMIT_KEY . Constant::LINKER . Constant::DB_TABLE_VALUE) !== null): //按天限制
                $type = 4;
                break;

            case (data_get($activityConfigData, $actForm . '_' . Constant::ACT_WEEK_LIMIT_KEY . Constant::LINKER . Constant::DB_TABLE_VALUE) !== null): //自然周限制
                $limitKey = Constant::ACT_WEEK_LIMIT_KEY;
                $time = strtotime('+1week');
                $ttl = $actEndAt === null ? ($time - $timestamp) : ($time > $actEndAt ? ($actEndAt - $timestamp) : ($time - $timestamp)); //缓存时间 单位秒
                $type = 3;
                break;

            case (data_get($activityConfigData, $actForm . '_' . Constant::ACT_MONTH_LIMIT_KEY . Constant::LINKER . Constant::DB_TABLE_VALUE) !== null): //自然月限制
                $limitKey = Constant::ACT_MONTH_LIMIT_KEY;
                $time = strtotime('+1month');
                $ttl = $actEndAt === null ? ($time - $timestamp) : ($time > $actEndAt ? ($actEndAt - $timestamp) : ($time - $timestamp)); //缓存时间 单位秒
                $type = 2;
                break;

            case (data_get($activityConfigData, $actForm . '_' . Constant::ACT_LIMIT_KEY . Constant::LINKER . Constant::DB_TABLE_VALUE) !== null): //整个活动期间的限制
                $limitKey = Constant::ACT_LIMIT_KEY;
                $ttl = $actEndAt === null ? (30 * 24 * 60 * 60) : ($actEndAt - $timestamp); //缓存时间 单位秒
                $type = 1;
                break;

            default :

        }

        if ($limitKey && $limitKey != Constant::ACT_DAY_LIMIT_KEY) {
            $totalKey .= ':' . $limitKey;
            $addTotalKey .= ':' . $limitKey;
            $remainingTotalKey .= ':' . $limitKey;
            $initDbKey .= ':' . $limitKey;
        }

        return [
            Constant::TOTAL => $totalKey,//累计总次数
            Constant::ADD_TOTAL => $addTotalKey,//累计增加总次数
            Constant::REMAINING_TOTAL => $remainingTotalKey,//剩余次数
            Constant::INIT_DB => $initDbKey,//初始化次数
            Constant::DB_TABLE_TYPE => $type,//类型 1:整个活动参与次数 2:month活动参与次数 3:week活动参与次数 4:day活动参与次数 5:小时活动参与次数 6:关注 7:分享 8:邀请
            Constant::TTL => $ttl,//缓存到期时间key
            Constant::TAG => $tag,//缓存tag key
            Constant::ACT_FORM => $actForm,//活动形式
            Constant::LIMIT_KEY => $limitKey,//限制级别：'limit':整个活动期间 'month_limit':按月 'week_limit':按周 'day_limit':按天 'hour_limit':按小时 默认：按天
            Constant::ACT_CONFIG => $activityConfigData,//活动配置
            Constant::TIMESTAMP => $timestamp,//当前时间戳
        ];
    }

    /**
     * 获取活动初始化次数 Jmiy_cen 2021-05-20 11:08 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $actionData 缓存操作数据
     * @param null|int $init 初始化次数
     * @return array|int|mixed
     */
    public static function getInitNnm($storeId = 0, $actId = 0, $customerId = 0, $actionData = [], $init = null)
    {
        if ($init !== null) {
            return $init;
        }

        $init = data_get($actionData, Constant::REQUEST_DATA_KEY . '.act_init');
        if ($init !== null) {
            return $init;
        }

        $activityConfigData = static::getActivityConfigData($storeId, $actId, Constant::ADD_NUMS, [
            Constant::INIT,
            'init_vote',
            'init_share',
            'init_invite',
        ]);

        $init = data_get($activityConfigData, Constant::ADD_NUMS . '_' . Constant::INIT . Constant::LINKER . Constant::DB_TABLE_VALUE, 1); //活动默认参与次数

        $initVote = data_get($activityConfigData, Constant::ADD_NUMS . '_' . 'init_vote' . Constant::LINKER . Constant::DB_TABLE_VALUE); //投票添加初始化次数
        if ($initVote !== null) {//如果是mpow，就根据参加投票情况，添加抽奖次数
            //默认一个自然日内登录账号可以玩1次，分享活动到社媒平台可以再额外获得1次抽奖的机会，分享即可不限制必邀请注册，总共2次机会；
            //参与过投票环节的用户再次参与抽奖活动，除默认的一个自然日内可以玩1次以后，自动获得3次抽奖机会（鼓励参与投票竞猜），
            //另外分享社媒也可以再获得一次抽奖（分享即可不限制必邀请注册），总共5次机会
            $isVote = VoteLogService::exists($storeId, '', $actId, $customerId);
            if ($isVote) {//如果已经参加了投票，就多添加 $initVote 次机会
                $init += $initVote;
            }
        }

        $initShare = data_get($activityConfigData, Constant::ADD_NUMS . '_' . 'init_share' . Constant::LINKER . Constant::DB_TABLE_VALUE); //投票添加初始化次数
        if ($initShare !== null) {//根据参加分享情况，添加次数
            $where = [
                Constant::DB_TABLE_ACT_ID => $actId,
                Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
            ];
            $isShare = ActivityShareService::existsOrFirst($storeId, '', $where);
            if ($isShare) {//如果已经分享，就多添加 $initShare 次机会
                $init += $initShare;
            }
        }

        $initInvite = data_get($activityConfigData, Constant::ADD_NUMS . '_' . 'init_invite' . Constant::LINKER . Constant::DB_TABLE_VALUE); //投票添加初始化次数
        if ($initInvite !== null) {//根据参加分享情况，添加次数
            $where = [
                Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                Constant::DB_TABLE_STORE_ID => $storeId,
                Constant::DB_TABLE_ACT_ID => $actId,
            ];
            $is = InviteService::existsOrFirst($storeId, '', $where);
            if ($is) {//如果已经分享，就多添加 $initShare 次机会
                $init += $initInvite;
            }
        }

        return $init;

    }

    /**
     * 获取限制的缓存数据 Jmiy_cen 2021-05-20 11:08 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $actionData 缓存操作数据
     * @param array $actData 活动数据
     * @param array $limitKeyData 限制的缓存keys
     * @return array|string[] 限制的缓存数据
     */
    public static function getLimitData($storeId = 0, $actId = 0, $customerId = 0, $actionData = [], $actData = [], $limitKeyData = [])
    {
        if (empty($limitKeyData)) {
            $limitKeyData = static::getLimitKeyData($storeId, $actId, $customerId, $actionData, $actData);
        }

        $tag = data_get($limitKeyData, Constant::TAG);
        $totalKey = data_get($limitKeyData, Constant::TOTAL);//累计总次数
        $addTotalKey = data_get($limitKeyData, Constant::ADD_TOTAL); //累计增加总次数
        $remainingTotalKey = data_get($limitKeyData, Constant::REMAINING_TOTAL); //剩余的次数
        $initDbKey = data_get($limitKeyData, Constant::INIT_DB); //初始化活动统计表相关数据
        $actForm = data_get($limitKeyData, Constant::ACT_FORM);//活动形式
        $limitKey = data_get($limitKeyData, Constant::LIMIT_KEY);//限制类型key
        $activityConfigData = data_get($limitKeyData, Constant::ACT_CONFIG);//活动配置

        $isCreditCount = data_get($activityConfigData, $actForm . '_' . 'is_credit_count' . Constant::LINKER . Constant::DB_TABLE_VALUE); //活动是否使用积分计算参与机会
        $deductCredit = data_get($activityConfigData, $actForm . '_' . 'deduct_credit' . Constant::LINKER . Constant::DB_TABLE_VALUE); //每次参与活动扣除积分
        //$dayMaxPlayNums = data_get($activityConfigData, $actForm . '_' . 'day_max_play_nums' . Constant::LINKER . Constant::DB_TABLE_VALUE); //每天最多可以参与活动的次数

        if ($isCreditCount && $deductCredit) {
            $customerInfo = CustomerInfoService::existsOrFirst($storeId, '', [Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId], true, [Constant::DB_TABLE_CREDIT]);
            $initPlayNums = floor(data_get($customerInfo, Constant::DB_TABLE_CREDIT, 0) / $deductCredit) . '';

            return [
                Constant::LOTTERY_TOTAL => $initPlayNums, //累计总次数
                Constant::ADD_TOTAL => '0',//累计增加总次数
                Constant::LOTTERY_NUM => $initPlayNums, //剩余次数
                Constant::ACT_TOTAL => '0', //可使用总次数=可以添加的次数+初始化次数
                Constant::USED_TOTAL => '0', //已使用总次数
                Constant::INIT => '0',//初始化次数
            ];
        }

        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hgetall', [$initDbKey]);
        $data = static::handleCache('', $handleCacheData);
        if ($data) {//如果新的缓存数据存在，就直接使用新的缓存数据
            $total = data_get($data, Constant::TOTAL, '0');//累计总次数
            $addTotal = data_get($data, Constant::DB_TABLE_ADD_TOTAL, '0'); //累计增加总次数
            $remainingTotal = data_get($data, Constant::REMAINING_TOTAL, '0');//剩余的次数
            $init = data_get($data, Constant::INIT);//初始化次数

        } else {//如果新的缓存数据不存在，优先使用旧的缓存数据

            //获取旧的缓存数据
            $keys = [$totalKey, $addTotalKey, $remainingTotalKey];
            $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'many', [$keys]);
            $data = static::handleCache($tag, $handleCacheData);

            $total = data_get($data, $totalKey, '0');//累计总次数
            $addTotal = data_get($data, $addTotalKey, '0'); //累计增加总次数
            $remainingTotal = data_get($data, $remainingTotalKey, '0');//剩余的次数
            $init = static::getInitNnm($storeId, $actId, $customerId, $actionData);//初始化次数

            if (data_get($data, $remainingTotalKey) === null) {//如果旧的缓存没有，就直接返回初始化的数据
                $total = $init;//累计总次数
                $addTotal = '0'; //累计增加总次数
                $remainingTotal = $init;//剩余的次数
            } else {
                foreach ($keys as $key) {
                    $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'forget', [$key]);
                    static::handleCache($tag, $handleCacheData);
                }
            }

        }

        $actTotal = data_get($activityConfigData, $actForm . '_' . $limitKey . Constant::LINKER . Constant::DB_TABLE_VALUE, 1); //获取可以添加的次数

        return [
            Constant::LOTTERY_TOTAL => $total, //累计总次数
            Constant::ADD_TOTAL => $addTotal,//累计增加总次数
            Constant::LOTTERY_NUM => $remainingTotal, //剩余次数
            Constant::ACT_TOTAL => $actTotal + $init, //可使用总次数=可以添加的次数+初始化次数
            Constant::USED_TOTAL => $total - ($remainingTotal > 0 ? $remainingTotal : 0), //已使用总次数
            Constant::INIT => $init,//初始化次数
        ];

    }

    /**
     * 同步活动统计数据到数据库 Jmiy_cen 2021-05-20 11:36 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $actionData 缓存操作数据
     * @param array $actData 活动数据
     * @param array $limitKeyData 限制的缓存keys
     * @return array|object 统计数据
     */
    public static function handleStat($storeId = 0, $actId = 0, $customerId = 0, $actionData = [], $actData = [], $limitKeyData = [])
    {

        if (empty($limitKeyData)) {
            $limitKeyData = static::getLimitKeyData($storeId, $actId, $customerId, $actionData, $actData);
        }

        $initDbKey = data_get($limitKeyData, Constant::INIT_DB); //初始化活动统计表相关数据
        $cacheKeyData = [md5(__METHOD__), $initDbKey];
        $parameters = [
            function () use ($storeId, $actId, $customerId, $actionData, $actData, $limitKeyData) {

                $initDbKey = data_get($limitKeyData, Constant::INIT_DB); //初始化活动统计表相关数据
                $ttl = data_get($limitKeyData, Constant::TTL);//缓存到期时间
                $type = data_get($limitKeyData, Constant::DB_TABLE_TYPE);//类型 1:整个活动参与次数 2:month活动参与次数 3:week活动参与次数 4:day活动参与次数 5:小时活动参与次数 6:关注 7:分享 8:邀请
                $timestamp = data_get($limitKeyData, Constant::TIMESTAMP);//当前时间戳
                $actForm = data_get($limitKeyData, Constant::ACT_FORM);//活动形式

                //初始化活动统计表相关数据
                $handleCacheData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'hgetall', [$initDbKey]);
                $statData = static::handleCache('', $handleCacheData);
                if (!$statData) {//如果缓存没有记录或者初始化时，就更新数据库数据

                    $where = [
                        Constant::DB_TABLE_ACT_ID => $actId,
                        '{customizeWhere}' => [
                            [
                                Constant::METHOD_KEY => Constant::DB_EXECUTION_PLAN_WHERE,
                                Constant::PARAMETERS_KEY => function ($query) use ($customerId) {
                                    $query->where(Constant::DB_TABLE_CUSTOMER_PRIMARY, $customerId)
                                        ->orWhere(Constant::DB_TABLE_ACCOUNT, '=', $customerId);
                                },
                            ]
                        ],

                        Constant::DB_TABLE_TYPE => $type,
                    ];

                    if ($type != 1) {//如果不是整个活动期间的限制，就加上 截止时间作为查询条件
                        $where[] = [[Constant::DB_TABLE_END_AT, '>=', $timestamp]];
                    }

                    $select = [Constant::DB_TABLE_PRIMARY, Constant::TOTAL, Constant::DB_TABLE_ADD_TOTAL, Constant::REMAINING_TOTAL, Constant::INIT];
                    $statData = ActivityStatService::existsOrFirst($storeId, '', $where, true, $select);
                    if (empty($statData)) {//如果数据库统计数据为空，就将统计数据添加到数据库中

                        $cacheData = static::getLimitData($storeId, $actId, $customerId, $actionData, $actData, $limitKeyData);
                        $remainingTotal = data_get($cacheData, Constant::LOTTERY_NUM, 0);//剩余的次数
                        $statData = [
                            Constant::DB_TABLE_ACT_ID => $actId,//活动id
                            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,//会员id
                            Constant::DB_TABLE_ACCOUNT => $customerId,//账号
                            Constant::DB_TABLE_TYPE => $type,//类型 1:整个活动参与次数 2:month活动参与次数 3:week活动参与次数 4:day活动参与次数 5:小时活动参与次数 6:关注 7:分享 8:邀请
                            Constant::TOTAL => data_get($cacheData, Constant::LOTTERY_TOTAL),//累计总次数
                            Constant::DB_TABLE_ADD_TOTAL => data_get($cacheData, Constant::ADD_TOTAL),//累计增加总次数
                            Constant::REMAINING_TOTAL => ($remainingTotal < 0 ? 0 : $remainingTotal),//剩余次数
                            Constant::INIT => data_get($cacheData, Constant::INIT),//初始化次数
                            Constant::DB_TABLE_START_AT => $timestamp,//开始时间
                            Constant::DB_TABLE_END_AT => $timestamp + $ttl,//到期时间
                        ];
                        $id = ActivityStatService::getModel($storeId)->insertGetId($statData);
                        data_set($statData, Constant::DB_TABLE_PRIMARY, $id);
                    } else {
                        $statData = $statData->toArray();
                    }

                    if ($statData) {
                        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hmset', [$initDbKey, $statData, $ttl]);
                        ActivityService::handleCache('', $handleCacheData);
                    }

                    if ($actForm == Constant::ACT_FORM_SLOT_MACHINE) {//如果是老虎机，就控制后续参与游戏赠送次数，后续参与，一天送一次
                        //dateKey控制后续参与游戏赠送次数，后续参与，一天送一次
                        $dateKey = "every_{$storeId}_{$actId}_{$customerId}_" . date("Ymd");
                        //过期时间至当天末尾
                        $expireTime = strtotime(date("Y-m-d 23:59:59")) - time();
                        Redis::setex($dateKey, $expireTime, 1);
                    }
                }

                return $statData;
            }
        ];

        $rs = static::handleLock($cacheKeyData, $parameters);

        return $rs === false ? Response::getDefaultResponseData(110001) : $rs;
    }

    /**
     * 处理活动各种限制 Jmiy_cen 2021-05-20 04:05 update
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $actionData 缓存操作数据
     * @return mix
     */
    public static function handleLimit($storeId = 0, $actId = 0, $customerId = 0, $actionData = [])
    {

        //获取缓存操作相关数据
        $method = data_get($actionData, Constant::METHOD_KEY, '');//缓存操作方法

        $actData = static::getModel($storeId)->where([Constant::DB_TABLE_PRIMARY => $actId])->select([Constant::DB_TABLE_END_AT])->first();
        if ($actData === null) {//如果活动不存在,并且是活动不存在就不可以执行的请求就直接提示
            if ($method == 'get') {
                return [
                    Constant::LOTTERY_TOTAL => 0, //累计总次数
                    Constant::LOTTERY_NUM => 0, //剩余次数
                    Constant::ACT_TOTAL => 0, //可使用总次数=可以添加的次数+初始化次数
                    Constant::USED_TOTAL => 0, //已使用总次数
                    Constant::ADD_TOTAL => 0,//累计增加总次数
                    Constant::INIT => 0,//初始化次数
                ];
            }

            return Response::getDefaultResponseData(69998);//活动不存在
        }

        $nowTime = Carbon::now()->toDateTimeString();
        $endAt = data_get($actData, Constant::DB_TABLE_END_AT, null);
        if ($endAt !== null && $nowTime > $endAt) {//活动已经结束，就直接返回
            if ($method == 'get') {
                return [
                    Constant::LOTTERY_TOTAL => 0, //累计总次数
                    Constant::LOTTERY_NUM => 0, //剩余次数
                    Constant::ACT_TOTAL => 0, //可使用总次数=可以添加的次数+初始化次数
                    Constant::USED_TOTAL => 0, //已使用总次数
                    Constant::ADD_TOTAL => 0,//累计增加总次数
                    Constant::INIT => 0,//初始化次数
                ];
            }
            return Response::getDefaultResponseData(69999);//活动过期
        }

        $limitKeyData = static::getLimitKeyData($storeId, $actId, $customerId, $actionData, $actData);
        $tag = data_get($limitKeyData, Constant::TAG);
        $remainingTotalKey = data_get($limitKeyData, Constant::REMAINING_TOTAL); //剩余的次数
        $initDbKey = data_get($limitKeyData, Constant::INIT_DB); //初始化活动统计表相关数据
        $actForm = data_get($limitKeyData, Constant::ACT_FORM);//活动形式
        $limitKey = data_get($limitKeyData, Constant::LIMIT_KEY);//限制级别：'limit':整个活动期间 'month_limit':按月 'week_limit':按周 'day_limit':按天 'hour_limit':按小时 默认：按天
        $activityConfigData = data_get($limitKeyData, 'actConfig');//活动配置

        $parameters = data_get($actionData, Constant::PARAMETERS_KEY, []);//缓存操作方法 对应的参数
        array_unshift($parameters, $remainingTotalKey);//将key插入到 函数参数的第一个元素

        //同步活动统计数据到数据库
        $statData = static::handleStat($storeId, $actId, $customerId, $actionData, $actData, $limitKeyData);
        if(data_get($statData,Constant::RESPONSE_CODE_KEY) == 110001){//如果获取分布式锁失败，就直接返回
            if ($method == 'get') {
                return [
                    Constant::LOTTERY_TOTAL => 0, //累计总次数
                    Constant::LOTTERY_NUM => 0, //剩余次数
                    Constant::ACT_TOTAL => 0, //可使用总次数=可以添加的次数+初始化次数
                    Constant::USED_TOTAL => 0, //已使用总次数
                    Constant::ADD_TOTAL => 0,//累计增加总次数
                    Constant::INIT => 0,//初始化次数
                ];
            }
            return $statData;
        }

        if ($actForm == Constant::ACT_FORM_SLOT_MACHINE) {//如果是老虎机，就控制后续参与游戏赠送次数，后续参与，一天送一次
            //老虎机游戏非首次参与次数增加
            GameService::updatePlayNums($storeId, $actId, $customerId, 'add_nums', 'every');
        }

        $data = Response::getDefaultResponseData(1);
        $isUpdate = false;//同步更新数据库表
        if (in_array(strtolower($method), ['decrement', 'increment'])) {
            $isUpdate = true;
        }

        $_num = data_get($parameters, 1, 1);
        switch ($method) {
            case 'increment':
                if ($_num < 0) {

                    $method = 'decrement';

                    //更新剩余次数
                    $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hincrby', [$initDbKey, Constant::REMAINING_TOTAL, $_num]);
                    static::handleCache('', $handleCacheData);

                    break;
                }

                //获取累计增加总次数
                $statisticsNum = data_get($statData,Constant::DB_TABLE_ADD_TOTAL,0);
                $remainingTotalOld = data_get($statData,Constant::REMAINING_TOTAL,0);

                $actTotal = data_get($activityConfigData, $actForm . '_' . $limitKey . Constant::LINKER . Constant::DB_TABLE_VALUE, 1); //获取可以添加的次数
                if ($statisticsNum >= $actTotal) {//如果累计增加总次数大于可以添加的次数，就不更新缓存
                    $isUpdate = false;
                    $data = Response::getDefaultResponseData(-3);//累计增加总次数大于可以添加的次数
                    break;
                }

                //更新统计数据
                $values = [
                    Constant::DB_TABLE_ADD_TOTAL => $_num,//更新累计增加总次数
                    Constant::TOTAL => $_num,//更新累计总次数
                ];
                if($remainingTotalOld < 0){//如果缓存的剩余次数小于0, 直接设置剩余次数为$_num
                    $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hset', [$initDbKey, Constant::REMAINING_TOTAL, $_num]);
                    static::handleCache('', $handleCacheData);
                }else{
                    $values[Constant::REMAINING_TOTAL] = $_num;//更新剩余次数
                }
                $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hmincrby', [$initDbKey, $values]);
                static::handleCache('', $handleCacheData);

                break;

            case 'get':

                $data = static::getLimitData($storeId, $actId, $customerId, $actionData, $actData, $limitKeyData);
                break;

            case 'decrement':
                //更新剩余次数
                $_num = -(data_get($parameters, 1, 1));
                $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'hincrby', [$initDbKey, Constant::REMAINING_TOTAL, $_num]);
                static::handleCache('', $handleCacheData);
                break;

            default:
                data_set($handleCacheData, Constant::METHOD_KEY, $method);
                data_set($handleCacheData, Constant::PARAMETERS_KEY, $parameters);
                $data = Response::getDefaultResponseData(static::handleCache($tag, $handleCacheData));

                break;
        }

        if (!$isUpdate) {//如果没有更新数据，就不同步更新数据库表
            return $data;
        }

        $id = data_get($statData, Constant::DB_TABLE_PRIMARY, -1);
        $remainingTotalSql = Constant::REMAINING_TOTAL . '+' . $_num;
        $updateData = [
            Constant::REMAINING_TOTAL => DB::raw("IF(" . $remainingTotalSql . ">=0," . $remainingTotalSql . ",0)"),
        ];

        if ($method == 'increment') {
            $updateData[Constant::TOTAL] = DB::raw(Constant::TOTAL . '+' . $_num);
            $updateData[Constant::DB_TABLE_ADD_TOTAL] = DB::raw(Constant::DB_TABLE_ADD_TOTAL . '+' . $_num);
        }

        ActivityStatService::update($storeId, [Constant::DB_TABLE_PRIMARY => $id], $updateData);

        return $data;
    }

    /**
     * 生成活动
     * @param int $storeId
     * @param string $activityName 活动名称
     * @return bool|object|null $rs
     */
    public static function addActivity($storeId, $activityName, $data = []) {

        if (empty($activityName)) {
            return Constant::PARAMETER_ARRAY_DEFAULT;
        }

        $where = [];
        if ($activityName) {
            $where[Constant::DB_TABLE_NAME] = $activityName;
        }

        data_set($data, Constant::DB_TABLE_ACT_UNIQUE, static::getActUnique($storeId, 'g/' . $activityName), false);
        data_set($data, Constant::FILE_URL, static::getActUrl($storeId, 'g/' . $activityName), false);

        $actData = static::updateOrCreate($storeId, $where, $data);

        static::clear(); //清空活动缓存

        return data_get($actData, Constant::RESPONSE_DATA_KEY, Constant::PARAMETER_ARRAY_DEFAULT);
    }

    /**
     * 返回活动id列表
     * @param int $storeId
     * @return bool|object|null $rs
     */
    public static function getActivityList($storeId) {
        return static::getModel($storeId)->select(Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_NAME, Constant::DB_TABLE_ACT_TYPE)->orderBy(Constant::DB_TABLE_PRIMARY, 'desc')->get();
    }

    /**
     * 判断是否可以继续活动
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @return mix
     */
    public static function isHandle($storeId, $actId, $customerId, $requestData) {

        $actionData = FunctionHelper::getJobData(static::getNamespaceClass(), 'decrement', [], $requestData);
        static::handleLimit($storeId, $actId, $customerId, $actionData);

        data_set($actionData, Constant::METHOD_KEY, 'get');
        $lotteryData = static::handleLimit($storeId, $actId, $customerId, $actionData);
        $lotteryNum = data_get($lotteryData, Constant::LOTTERY_NUM, 0);
        if ($lotteryNum < 0) {
            return Response::getDefaultResponseData(62000);
        }

        return Response::getDefaultResponseData(1);
    }

    /**
     * 获取活动hash标识
     * @param int $storeId 活动id
     * @param string $actUnique 活动标识
     * @param int $length 标识长度
     * @return string 唯一活动标识
     */
    public static function getActUnique($storeId, $actUnique = null, $length = 2) {

        if ($actUnique !== null) {
            return FunctionHelper::getUniqueId(FunctionHelper::getShopifyUri($actUnique));
        }

        $isDo = true;
        $actUnique = '';
        while ($isDo) {
            $actUnique = FunctionHelper::randomStr($length);
            $where = [
                Constant::DB_TABLE_ACT_UNIQUE => $actUnique,
            ];
            $isDo = static::existsOrFirst($storeId, '', $where);
        }

        return FunctionHelper::getUniqueId(FunctionHelper::getShopifyUri($actUnique));
    }

    /**
     * 获取活动链接
     * @param int $storeId 商城id
     * @param string $mark 活动标识
     * @param string $host host
     * @param string $actUnique 活动hash
     * @param int $length  字符串长度
     * @return string 活动链接
     */
    public static function getActUrl($storeId, $mark, $host = null, $actUnique = null, $length = 2) {
        return implode('/', ['https:/', FunctionHelper::getShopifyHost($storeId, $host),]) . FunctionHelper::getShopifyUri($mark);
    }

    /**
     * 是否能够切换 活动类型 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 6:上传图片投票 7:免费评测活动 8:会员deal 9:通用deal
     * @param int $srcActType 源活动类型
     * @param int $distActType 目标活动类型
     * @return boolean true:是 false:否
     */
    public static function isCanSwitchActType($srcActType, $distActType) {
        $data = [
            1 => [1, 2, 3, 4],
            2 => [1, 2, 3, 4],
            3 => [1, 2, 3, 4],
            4 => [1, 2, 3, 4],
        ];
        return in_array($distActType, data_get($data, $srcActType, [$srcActType]));
    }

    /**
     * 判断活动是否存在 规则：活动名称+活动类型确认活动唯一性
     * @param int $storeId 商城id
     * @param string $name 活动名称
     * @param int $actType 活动类型
     * @param int $id 活动id
     * @return array $rs 活动是否存在结果
     */
    public static function isExists($storeId, $name, $actType, $id = 0) {

        $rs = [
            Constant::RESPONSE_CODE_KEY => 1,
            Constant::RESPONSE_MSG_KEY => 'ok',
            Constant::RESPONSE_DATA_KEY => []
        ];

        $where = [
            Constant::DB_TABLE_NAME => $name, //活动名字
            Constant::DB_TABLE_ACT_TYPE => $actType, //活动类型
        ];

        if ($id) {
            $where[] = [[Constant::DB_TABLE_PRIMARY, '!=', $id]];
        }

        $actData = static::existsOrFirst($storeId, '', $where);
        if ($actData) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, Constant::PARAMETER_INT_DEFAULT);
            data_set($rs, Constant::RESPONSE_MSG_KEY, '活动名称重复');
            return $rs;
        }

        return $rs;
    }

    /**
     * 添加活动
     * @param int $storeId 商城id
     * @param array $data 活动数据
     */
    public static function input($storeId, $data) {

        $mark = data_get($data, Constant::DB_TABLE_MARK, '');
        $id = data_get($data, Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT);

        $actType = data_get($data, Constant::DB_TABLE_ACT_TYPE, -1); //活动类型 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 6:上传图片投票 7:免费评测活动 8:会员deal 9:通用deal
        $name = data_get($data, Constant::DB_TABLE_NAME, ''); //活动名字
        //活动名称+活动类型确认活动唯一性
        $rs = static::isExists($storeId, $name, $actType, $id);
        if (data_get($rs, Constant::RESPONSE_CODE_KEY, Constant::PARAMETER_INT_DEFAULT) != 1) {
            return $rs;
        }

        $actData = [
            Constant::DB_TABLE_NAME => $name, //活动名字
            Constant::DB_TABLE_START_AT => data_get($data, Constant::DB_TABLE_START_AT, null), //活动开始时间
            Constant::DB_TABLE_END_AT => data_get($data, Constant::DB_TABLE_END_AT, null), //活动结束时间
            Constant::DB_TABLE_ACT_TYPE => $actType, //活动类型
            Constant::DB_TABLE_MARK => $mark, //活动标识
            Constant::DB_TABLE_ACT_UNIQUE => static::getActUnique($storeId, $mark), //活动hash标识
            Constant::FILE_URL => static::getActUrl($storeId, $mark), //活动链接
        ];

        if (empty($id)) {
            $id = static::insert($storeId, $actData);
            data_set($rs, Constant::RESPONSE_DATA_KEY, [Constant::DB_TABLE_PRIMARY => $id]);
            return $rs;
        }

        $where = [
            Constant::DB_TABLE_PRIMARY => $id,
        ];
        $_actData = static::existsOrFirst($storeId, '', $where, true, [Constant::DB_TABLE_ACT_UNIQUE, Constant::DB_TABLE_ACT_TYPE]);
        $isCanSwitchActType = static::isCanSwitchActType(data_get($_actData, Constant::DB_TABLE_ACT_TYPE, -1), $actType);
        if (!$isCanSwitchActType) {
            data_set($rs, Constant::RESPONSE_CODE_KEY, 0);
            data_set($rs, Constant::RESPONSE_MSG_KEY, '活动类型不可以更改');
            return $rs;
        }

        $offset = static::update($storeId, $where, $actData);
        data_set($rs, Constant::RESPONSE_DATA_KEY, [Constant::DB_EXECUTION_PLAN_OFFSET => $offset]);

        static::clear(); //清空活动缓存

        return $rs;
    }

    /**
     * 删除活动
     * @param int $storeId 商城id
     * @param array $ids 活动id
     * @return array 处理结果
     */
    public static function delAct($storeId, $ids) {

        $rs = [
            Constant::RESPONSE_CODE_KEY => 1,
            Constant::RESPONSE_MSG_KEY => 'ok',
            Constant::RESPONSE_DATA_KEY => []
        ];

        if (empty($ids)) {
            return $rs;
        }

        $where = [
            Constant::DB_TABLE_PRIMARY => $ids,
        ];
        $offset = static::delete($storeId, $where);
        data_set($rs, Constant::RESPONSE_DATA_KEY, [Constant::DB_EXECUTION_PLAN_OFFSET => $offset]);

        static::clear(); //清空活动缓存

        return $rs;
    }

    /**
     * 获取活动数据
     * @param int $storeId 品牌商店id
     * @param int $actId 活动id
     * @return boolean
     */
    public static function getActData($storeId, $actId) {

        $actData = static::existsOrFirst($storeId, '', ['id' => $actId], true, ['start_at', 'end_at']);

        $startAt = data_get($actData, 'start_at');
        $endAt = data_get($actData, 'end_at');
        $rs = [
            'actData' => $actData,
            'startAt' => $startAt,
            'endAt' => $endAt,
            'isStart' => null,
            'isEnd' => null,
            'isValid' => false,
        ];

        if ($actData === null) {
            return $rs;
        }

        $nowTime = Carbon::now()->toDateTimeString();

        $isStart = $startAt === null ? true : ($startAt <= $nowTime ? true : false);
        $isEnd = $endAt === null ? false : ($endAt >= $nowTime ? false : true);

        data_set($rs, 'isStart', $isStart);
        data_set($rs, 'isEnd', $isEnd);
        data_set($rs, 'isValid', ($isStart && !$isEnd));

        return $rs;
    }

    /**
     * 编辑活动结束时间
     * @param int $storeId 商城id
     * @param array $where 编辑条件
     * @param array $data  编辑数据
     * @return boolean
     */
    public static function updateExpireTime($storeId, $where, $data) {

        if (empty($storeId) || empty($where) || empty($data)) {
            return false;
        }
        //更新活动截止时间
        return static::update($storeId, $where, $data);
    }
}
