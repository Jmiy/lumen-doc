<?php

namespace App\Services;

use App\Util\Cache\CacheManager as Cache;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\obj;
use App\Util\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;

class ActivityGuessNumberService extends BaseService
{
    //type 1 公布数字 2凑够entries资格  type_sub 1 停止接收
    public static $revResult = 1;
    public static $revEntryNotify = 2;

    public static $stopRev = 1;
    public static $acceptRev = 2;

    public static $sentEmailArr = [];


    /**
     * 获取猜数字活动的获奖用户
     * @param  int  $actID
     * @param  int  $storeID
     * @param  int  $page
     * @param  int  $pageSize
     * @return mixed
     */
    public static function getWinnerList(int $actID, int $storeID, $page = 1, $pageSize = 20)
    {
        // 获取当天的猜中数字获奖的用户列表
        $where = [
            [
                ['act_id', '=', $actID],
                ['win_log_id', '!=', 0],
            ]
        ];
        $timeFormat = "m/d/Y";
        $params = [
            Constant::REQUEST_PAGE => $page,
            Constant::REQUEST_PAGE_SIZE => $pageSize,
            Constant::ORDER => [['date', 'desc']],
            Constant::DB_EXECUTION_PLAN_SELECT => ['date'],
        ];
        return self::_getList($storeID, $where, $params, $timeFormat);
    }

    /**
     * 组装条件进行查询
     * @param  int  $storeID
     * @param  array  $where
     * @param  array  $params
     * @param  string  $timeFormat
     * @return array
     */
    public static function _getList(int $storeID, array $where, array $params = [], $timeFormat = "d/m/Y H:i:s")
    {
        $_data = static::getPublicData($params);
        $_data['where'] = $where;
        $_data['order'] = $params['order'] ?? [['id', 'desc']];
        $select = ['account', 'created_at', 'show_guess_num as guess'];
        $select = !empty($params['select']) ? array_merge($select, $params['select']) : $select;
        $unset = $params['unset'] ?? ['created_at'];
        $limit = $_data[Constant::DB_EXECUTION_PLAN_PAGINATION][Constant::REQUEST_PAGE_SIZE];
        $offset = $_data[Constant::DB_EXECUTION_PLAN_PAGINATION]['offset'];

        $exePlan = FunctionHelper::getExePlan($storeID, null,
            'ActivityGuessNumber', '', $select, $_data['where'],
            $_data['order'], $limit, $offset, true, $_data[Constant::DB_EXECUTION_PLAN_PAGINATION],
            false, [], [], [], $unset);

        $itemHandleDataCallback = [
            'account' => function ($item) {
                return FunctionHelper::handleAccountEmail(data_get($item, 'account'));
            },
            'time' => function ($item) use ($timeFormat) {
                $time = data_get($item, 'date', strtotime(data_get($item, 'created_at')));
                return date($timeFormat, $time);
            }
        ];

        $exeHandlePlan = FunctionHelper::getExePlanHandleData();
        data_set($exeHandlePlan, 'callback', $itemHandleDataCallback);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            'itemHandleData' => $exeHandlePlan,
            // 'sqlDebug' => true
        ];

        return FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');
    }

    /**
     * 获取所有参与活动活动的用户列表
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $page
     * @param  int  $pageSize
     * @return mixed
     */
    public static function getUserList(int $storeID, int $actID, int $page = 1, int $pageSize = 10)
    {
        $ttl = 15; //认证缓存时间 单位秒
        $key = md5(json_encode([$storeID, $actID, $page, $pageSize]));

        $where = [
            [
                ['act_id', '=', $actID],
                [Constant::DB_TABLE_CREATED_AT, '>=', Carbon::today()->toDateTimeString()],
            ]
        ];
        $timeFormat = "H:i:s";
        $params = [Constant::REQUEST_PAGE => $page, Constant::REQUEST_PAGE_SIZE => $pageSize];
        return Cache::remember($key, $ttl, function () use ($storeID, $where, $timeFormat, $params) {
            return self::_getList($storeID, $where, $params, $timeFormat);
        });
    }

    /**
     * 获取用户的猜数字活动参与记录
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $account
     * @param  int  $page
     * @param  int  $pageSize
     * @return array
     */
    public static function getUserGuessHistory(
        int $storeID,
        int $actID,
        string $account,
        int $page = 1,
        int $pageSize = 10
    ) {
        $where = [
            [
                ['act_id', '=', $actID],
                [Constant::DB_TABLE_ACCOUNT, '=', $account],
            ]
        ];

        $params = [Constant::REQUEST_PAGE => $page, Constant::REQUEST_PAGE_SIZE => $pageSize];
        $params['select'] = ['account', 'created_at', 'show_guess_num as guess'];
        $params['unset'] = ['created_at', 'account'];

        return self::_getList($storeID, $where, $params);
    }

    /**
     * 获取每日开奖结果 随机数字
     * @param  int  $actID
     * @param  int  $storeID
     * @return array
     */
    public static function getDailyResult(int $actID, int $storeID)
    {

        $select = ['show_num as num', 'date'];
        $where = [
            [
                ['act_id', '=', $actID],
                ['date', '>=', Carbon::yesterday()->timestamp],
                ['date', '<', Carbon::today()->timestamp],
            ]
        ];
        $order = [['id', 'desc']];
        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityLuckyNumber', '', $select, $where, $order);
        $itemHandleDataCallback = [
            'time' => function ($item) {
                return date("Y-m-d", data_get($item, 'date'));
            }
        ];

        $exeHandlePlan = FunctionHelper::getExePlanHandleData();
        data_set($exeHandlePlan, 'callback', $itemHandleDataCallback);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            'itemHandleData' => $exeHandlePlan,
            // 'sqlDebug' => true
        ];

        return FunctionHelper::getResponseData(null, $dbExecutionPlan);
    }

    /**
     * 获取用户的获奖记录
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $account  ,
     * @param  int  $page
     * @param  int  $pageSize
     * @return array
     */
    public static function getMyPrize(
        int $storeID,
        int $actID,
        string $account,
        int $page = 1,
        int $pageSize = 10
    ) {
        // 包含奖品图片， 猜中的数字，时间, 活动ID, winning_log_id是否已领取
        $timeFormat = "F.jS";
        $where = [
            [
                ['account', '=', $account],
                ['act_id', '=', $actID],
                ['win_log_id', '!=', 0]
            ]
        ];
        $order = [['id', 'desc']];
        $select = ['prize_id', 'created_at', 'show_guess_num as guess', 'win_log_id as activity_winning_id'];
        $unset = ['prize_id', 'created_at'];
        $limit = $pageSize;
        $offset = $limit * ($page - 1);

        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityGuessNumber',
            '', $select, $where, $order, $limit, $offset, true, [],
            false, [], [], [], $unset);

        $itemHandleDataCallback = [
            'prize' => function ($item) use ($storeID, $actID) {
                $select = ['img_url', 'mb_img_url', 'name'];
                $where = ['id' => data_get($item, 'prize_id'), 'act_id' => $actID,];
                $cond = [
                    'parent' => FunctionHelper::getExePlan($storeID, null, 'ActivityPrize', '', $select, $where),
                    // 'sqlDebug' => true,
                ];
                return FunctionHelper::getResponseData(null, $cond);
            },
            'time' => function ($item) use ($timeFormat) {
                return date($timeFormat, strtotime(data_get($item, 'created_at')));
            }
        ];

        $exeHandlePlan = FunctionHelper::getExePlanHandleData();
        data_set($exeHandlePlan, 'callback', $itemHandleDataCallback);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            'itemHandleData' => $exeHandlePlan,
            // 'sqlDebug' => true
        ];

        return FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');
    }

    /**
     * 生成水军的具体方法
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $countNum
     * @return array
     * @throws Exception
     */
    public static function generateActivityUser(int $storeID, int $actID, int $countNum=1)
    {
        // 生成水军的具体方法
        $key = "GuessLuckyNumber:Fakers";
        $ttl = 3600 * 24 * 10; // 假数据保存10天
        static::gatherFakers($key, $ttl);
        $prizeID = data_get(static::getPrize($storeID, $actID), 'id', 0);
        // 不需要去重 20210604
        // $existedVirtualUsers = static::getJoinedFakeUser($storeID, $actID);
        // foreach ($existedVirtualUsers as $user) {
        //     if (Cache::sismember($key, $user)) {
        //         Cache::srem($key, $user);
        //     }
        // }

        $newFakerUsers = Cache::srandmember($key, $countNum);
        // 插入到参与活动流水表中
        $model = static::getModel($storeID);
        foreach ($newFakerUsers as $faker) {
            $randNum = FunctionHelper::getRandNum();
            $infoData = [
                'show_guess_num' => $randNum,
                'guess_num' => intval($randNum),
                'prize_id' => $prizeID,
                Constant::DB_TABLE_ACT_ID => $actID,
                Constant::DB_TABLE_CUSTOMER_PRIMARY => Constant::PARAMETER_INT_DEFAULT,
                Constant::DB_TABLE_ACCOUNT => $faker,
            ];

            $result = $model->insert($infoData);
            if (empty($result)) {
                LogService::addSystemLog(Constant::LEVEL_ERROR, 'guess_num_user', __METHOD__,
                    '插入猜数字活动水军出错', json_encode(array_merge(['countNum'=>$countNum],$infoData), JSON_UNESCAPED_UNICODE));
            }
        }

        return Response::getDefaultResponseData(1);
    }

    /**
     * 获取猜数字活动的奖品ID
     * @param  int  $storeID
     * @param  int  $actID
     * @return mixed
     */
    public static function getPrize(int $storeID, int $actID)
    {
        $where = ['act_id' => $actID];
        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityPrize',
            '', [], $where, []);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            // 'sqlDebug' => true
        ];
        return FunctionHelper::getResponseData(null, $dbExecutionPlan);
    }

    /**
     * 获取所有已使用的假帐号
     * @param  int  $storeID
     * @param  int  $actID
     * @return array
     */
    public static function getJoinedFakeUser(int $storeID, int $actID)
    {
        $limit = 9999;
        $where = ['act_id' => $actID, 'customer_id' => 0];
        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityGuessNumber',
            '', ['account'], $where, [], $limit);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            // 'sqlDebug' => true
        ];
        $res = FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');
        return Arr::pluck($res, 'account');
    }


    /**
     * 获取缓存的所有虚拟用户
     * @param  string  $key
     * @param  int  $ttl
     * @return array|mixed
     * @throws \RedisException
     */
    public static function gatherFakers(string $key, int $ttl = (3600 * 24 * 10))
    {
        if (empty($key)) {
            throw new \RedisException('缓存KEY为空');
        }
        if (empty(Cache::scard($key))) {
            $select = ['account'];
            $limit = 9999;
            $where = ['type' => 1, 'type_sub' => 1];
            $exePlan = FunctionHelper::getExePlan(1, null, 'ActivityVirtualAccount',
                '', $select, $where, [], $limit);
            $dbExecutionPlan = [
                'parent' => $exePlan,
                // 'sqlDebug' => true
            ];
            $res = FunctionHelper::getResponseData(
                null, $dbExecutionPlan, false, false, 'list');
            $data = Arr::pluck($res, 'account');
            array_unshift($data, $key);
            call_user_func_array([Cache::class, 'sadd'], $data);
            Cache::expire($key, $ttl);
            array_splice($data, 1);
        } else {
            $data = Cache::smembers($key);
        }

        return $data;
    }


    /**
     * 发送活动结果邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $luckyNumID  开奖批次 对应lucky_number表ID guess_number表中的lucky_num_id字段值
     * @param  array  $selectedArr  [$guessNumbID, $guessNumbID, $guessNumbID] guess_number表 主键ID
     * @return bool
     */
    public static function sendResultEmail(int $storeID, int $actID, int $luckyNumID, array $selectedArr = [])
    {
        // 查询出需要发送邮件的对象
        $winnersArr = static::getWinnerEmail($storeID, $actID, $luckyNumID, $selectedArr);
        $losersArr = static::getLoserEmail($storeID, $actID, $luckyNumID);

        // 已中奖则不再发未中奖邮件
        $emails = static::uniqueEmail(array_merge($winnersArr, $losersArr));

        static::push2EmailQueue($storeID, $actID, $emails);
        return true;
    }


    /**
     * 发送中奖邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $luckyNum
     * @param  array  $selectedArr
     * @return array
     */
    public static function getWinnerEmail(int $storeID, int $actID, int $luckyNum, array $selectedArr = [])
    {
        if (empty($luckyNum)) {
            return [];
        }
        $where = [
            [
                ['act_id', '=', $actID],
                ['customer_id', '!=', 0],
                ['lucky_num_id', '=', $luckyNum],
            ]
        ];
        if ($selectedArr) {
            $where = array_merge(['id' => $selectedArr], $where);
        }
        $winners = static::getEmailObjects($storeID, $where);
        return $winners['data'];
    }

    /**
     * 给未中奖用户发送邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $luckyNum
     * @return array
     */
    public static function getLoserEmail(int $storeID, int $actID, int $luckyNum)
    {
        // 当没有传递lucky_num时表示非法传递， 因为默认为0避免意外发送
        if (empty($luckyNum)) {
            return [];
        }
        // 设置lucky_number_id为0 是因为当开奖筛选过后，未中奖者将再次进入奖池待选，设置为0，默认为0
        $where = [
            [
                ['act_id', '=', $actID],
                ['customer_id', '!=', 0],
                ['win_log_id', '=', 0],
            ]
        ];

        $data = static::getEmailObjects($storeID, $where);
        // 对邮件去重
        $data['data'] = static::uniqueEmail($data['data']);

        $current = intval($data['pagination']['page_index']);
        $total = intval($data['pagination']['total_page']);
        if ($total > $current) {
            for($i= ($current + 1); $i<= $total; $i++){
                $res = static::getEmailObjects($storeID, $where, $i);
                $res = static::uniqueEmail($res['data']);
                $data['data'] = array_merge($data['data'], $res['data']);
            }
        }
        // 请求结束 将去重数组置空
        self::$sentEmailArr = [];
        return $data['data'];
    }

    /**
     * 对未中奖用户发件记录进行去重
     * @param  array  $emailArr
     * @return array
     */
    public static function uniqueEmail(array $emailArr)
    {
        $arr = [];
        foreach($emailArr as $key=> $value){
            if (key_exists($value['email'], self::$sentEmailArr)) {
                continue;
            } else {
                $arr[$value['email']] = $value;
                self::$sentEmailArr[$value['email']] = 1;
            }
        }
        return array_values($arr);
    }


    /**
     * 查询所有符合要求的邮件发送对象
     * @param  int  $storeID
     * @param  array  $where
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public static function getEmailObjects(
        int $storeID,
        array $where = [],
        int $page = 1,
        int $limit = 1000
    ) {
        // 查出用户名称  活动连接地址
        $select = [
            'account as email', 'show_num', 'customer_id', 'win_log_id',
            'act_id', 'date', 'first_name', 'last_name'
        ];

        $offset = $limit * ($page - 1);
        $pagination = [
            'page_index' => $page,
            'page_size' => $limit,
            'offset' => $offset,
        ];
        $order = [['id', 'desc']];
        $activitySelect = ['url', 'name', 'start_at', 'end_at', 'id'];
        $with = [
            //关联活动
            'activity' => FunctionHelper::getExePlan($storeID, null, '', '', $activitySelect),
        ];
        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityGuessNumber', '',
            $select, $where, $order, $limit, $offset, true, $pagination);

        $handleData = [
            'name' => FunctionHelper::getExePlanHandleData('activity.name', ''),
            'url' => FunctionHelper::getExePlanHandleData('activity.url', ''), //审核状态
        ];

        $exePlan[Constant::DB_EXECUTION_PLAN_HANDLE_DATA] = $handleData;

        $dbExecutionPlan = [
            'parent' => $exePlan,
            'with' => $with,
            // 'sqlDebug' => true
        ];
        return FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');

    }

    /**
     * 插入邮件发送队列
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $infoArr
     */
    public static function push2EmailQueue(int $storeID, int $actID, array $infoArr)
    {
        // 过滤不收邮件的用户
        $infoArr = static::filterStopEmail($storeID, $actID, $infoArr);
        foreach ($infoArr as $key => $value) {
            $customerInfo = [
                'customer_id' => data_get($value, 'customer_id'),
                'email' => data_get($value, 'email'),
                'first_name' => data_get($value, 'first_name', ''),
                'last_name' => data_get($value, 'last_name', ''),
            ];
            $activityInfo = [
                'show_num' => data_get($value, 'show_num'),
                'date' => data_get($value, 'date'),
                'win_log_id' => intval(data_get($value, 'win_log_id')),
                'url' => data_get($value['activity'], 'url', ''),
            ];

            static::_sendResultEmail($storeID, $actID, $customerInfo, $activityInfo);
        }
    }


    /**
     * 查询拒绝接收结果邮件的用户邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $arr
     * @return array
     */
    public static function filterStopEmail(int $storeID, int $actID, array $arr)
    {
        $model = static::createModel($storeID, 'ActivityEmailSet');
        // type 1 公布数字  type_sub 1 停止接收
        $where = ['type' => static::$revResult, 'type_sub' => static::$stopRev, 'act_id' => $actID];
        $res = $model->where($where)->pluck('customer_id');
        if ($res->isEmpty()) {
            return $arr;
        } else {
            $filterIDs = $res->toArray();
            $flip = [];

            foreach ($arr as $key => $value) {
                $flip[$value['customer_id']] = $value;
            }

            foreach ($filterIDs as $id => $value) {
                if (!empty($flip[$value])) {
                    unset($flip[$value]);
                }
            }
            return array_values($flip);
        }
    }


    /**
     * 发送满三次entry可参与活动的邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  int  $customerID
     * @param  int  $entries  助力次数
     * @return bool|mixed
     */
    public static function sendNotificationEmail(int $storeID, int $actID, int $customerID, int $entries)
    {
        // 发送满足参与活动条件的通知邮件
        // 条件：助力次数超过或等于三次
        // 用户是否拒绝接收邮件
        if ($entries === 0 || $entries % 3 !== 0) {
            // 每次助力人數变动，都会调用当前方法
            return false;
        }
        $activityEmailSetModel = static::createModel($storeID, 'ActivityEmailSet');
        // type 1 公布数字 2凑够entries资格  type_sub 1 停止接收  type_sub 2 继续接收
        $where = [
            'type' => static::$revEntryNotify,
            // 'type_sub' => static::$stopRev,
            'act_id' => $actID,
            'customer_id' => $customerID
        ];
        $res = $activityEmailSetModel->where($where)->select(['customer_id', 'type_sub'])->first();

        if ($res && data_get($res, 'type_sub') !== static::$acceptRev) {
            // 用户拒绝接收邮件
            return false;
        }

        $customerInfo = static::createModel("default_connection_".$storeID, 'CustomerInfo')
            ->where(['store_id' => $storeID, 'customer_id' => $customerID])
            ->select(['first_name', 'last_name', 'account as email', 'customer_id'])
            ->first();
        $activityInfo = static::createModel($storeID, 'Activity')
            ->where(['id' => $actID])
            ->select(['url', 'id'])
            ->first();

        if ($customerInfo && $activityInfo) {
            $customerInfo = $customerInfo->toArray();
            $activityInfo = $activityInfo->toArray();
        } else {
            return false;
        }

        $info = static::_sendNotificationEmail(intval($storeID), intval($actID), $customerInfo, $activityInfo);
        // 用户愿意接收多封邮件

        if (data_get($res, 'type_sub') !== static::$acceptRev) {
            static::_markSendEmail(intval($storeID), intval($actID), $customerInfo, $activityInfo);
        }

        return boolval($info);
    }


    /**
     * 获取邮件模板配置内容
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $emailConfigKey
     * @return mixed
     */
    public static function _getEmailContent(int $storeID, int $actID, string $emailConfigKey)
    {
        $configModel = static::createModel($storeID, 'ActivityConfig');
        $resultEmailSubjectKey = "lucky_number_{$emailConfigKey}_subject";
        $resultEmailBodyKey = "lucky_number_{$emailConfigKey}_body";
        $where = [
            'type' => 'email',
            'activity_id' => $actID,
        ];
        return $configModel->where($where)
            ->whereIn('key', [$resultEmailBodyKey, $resultEmailSubjectKey])
            ->orderBy('sort', 'asc')
            ->pluck('value')->toArray();
    }

    /**
     * 发送活动参与的结果邮件
     *  $customerInfo = [
     *      'customer_id' => $value['customer_id'],
     *      'email' => $value['account'],
     *      'first_name' => data_get($value['customer'], 'first_name'),
     *      'last_name' => data_get($value['customer'], 'last_name'),
     * ];
     * $activityInfo = [
     *      'guess_num' => $value['guess_num'],
     *      'date' => $value['date'],
     *      'win_log_id' => $value['win_log_id'],
     *      'url' => data_get($value['activity'], 'url'),
     * ];
     * @param  int  $storeID  站点ID
     * @param  int  $actID  活动ID
     * @param  array  $customerInfo  用户信息数组
     * @param  array  $activityInfo
     * @return bool
     */
    public static function _sendResultEmail(int $storeID, int $actID, array $customerInfo, array $activityInfo)
    {
        // 获取邮件内容
        $typekey = intval($activityInfo['win_log_id']) !== 0 ? "winner" : "loser";
        $typekey = $typekey.'_result';
        $emailConfigValue = static::_getEmailContent($storeID, $actID, $typekey);
        list($subject, $body) = $emailConfigValue;

        $dateTime = date('M jS', data_get($activityInfo, 'date'));
        $name = data_get($customerInfo, 'first_name', '');
        if (intval(data_get($activityInfo, 'win_log_id'))) {
            // Congrats to first_name（无名字的默认you）for Winning The Outstanding Mpow
            $subjectName = $name ? ucfirst($name) : 'You';
            $subject = str_replace('{{$name}}', $subjectName, $subject);
        } else {
            $subject = str_replace('{{$dateTime}}', $dateTime, $subject);
        }

        $replacePairs = [
            '{{$dateTime}}' => $dateTime,
            '{{$luckyNumber}}' => data_get($activityInfo, 'show_num'),
            '{{$name}}' => !empty($name) ? ucfirst($name) : 'Customer',
            '{{$url}}' => data_get($activityInfo, 'url'),
            '{{$account}}' => data_get($customerInfo, 'email'),
        ];
        $body = strtr($body, $replacePairs);
        $emailArr = [
            'email' => data_get($customerInfo, 'email'),
            'first_name' => data_get($customerInfo, 'first_name'),
            'last_name' => data_get($customerInfo, 'last_name'),
            'customer_id' => data_get($customerInfo, 'customer_id'),
            'subject' => $subject,
            'body' => $body,
        ];
        return static::_sendEmail($storeID, $actID, $emailArr, "发送活动结果: {$typekey}");
    }


    /**
     * 发送满足活动参与条件的邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $customerInfo
     * @param  array  $activityInfo
     * @return bool
     */
    public static function _sendNotificationEmail(int $storeID, int $actID, array $customerInfo, array $activityInfo)
    {
        // 获取邮件内容
        $typekey = "notification";
        $emailConfigValue = static::_getEmailContent($storeID, $actID, $typekey);
        list($subject, $body) = $emailConfigValue;
        $name = data_get($customerInfo, 'first_name', '');
        $body = str_replace('{{$name}}', !empty($name) ? ucfirst($name) : 'Customer', $body);
        $body = str_replace('{{$url}}', data_get($activityInfo, 'url'), $body);
        $body = str_replace('{{$account}}', data_get($customerInfo, 'email'), $body);
        $emailArr = [
            'email' => data_get($customerInfo, 'email'),
            'first_name' => data_get($customerInfo, 'first_name'),
            'last_name' => data_get($customerInfo, 'last_name'),
            'customer_id' => data_get($customerInfo, 'customer_id'),
            'subject' => $subject,
            'body' => $body,
        ];
        return static::_sendEmail($storeID, $actID, $emailArr, "发送助力资格已满通知");
    }

    /**
     * 发送邀请注册参与活动的邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $customerInfo
     * @param  array  $activityInfo
     * @return bool
     */
    public static function _sendInviteEmail(int $storeID, int $actID, $customerInfo, array $activityInfo)
    {
        // 获取邮件内容
        $emailConfigKey = "invite";
        $emailConfigArr = static::_getEmailContent($storeID, $actID, $emailConfigKey);
        list($subject, $body) = $emailConfigArr;
        $firstName = data_get($customerInfo, 'first_name', '');
        $replacePairs = [
            '{{$name}}' => !empty($firstName) ? ucfirst($firstName) : 'Friend',
            '{{$url}}' => data_get($activityInfo, 'url'),
            '{{$account}}' => data_get($activityInfo, 'account'),
        ];
        $body = strtr($body, $replacePairs);
        $emailArr = [
            'email' => data_get($customerInfo, 'email'),
            'first_name' => data_get($customerInfo, 'first_name'),
            'last_name' => data_get($customerInfo, 'last_name'),
            'customer_id' => data_get($customerInfo, 'customer_id'),
            'subject' => $subject,
            'body' => $body,
        ];
        return static::_sendEmail($storeID, $actID, $emailArr, '发送活动邀请');
    }

    /**
     * 发送邮件过多, 发出一封是否停止发送提醒邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $email
     * @param  string  $firstName
     * @param  string  $activityUrl
     * @return int
     */
    public static function _sendReferenceEmail(
        int $storeID,
        int $actID,
        string $email,
        string $firstName,
        string $activityUrl
    ) {
        $typekey = "stop_push";
        $emailConfigValue = static::_getEmailContent($storeID, $actID, $typekey);
        list($subject, $body) = $emailConfigValue;
        $replacePairs = [
            '{{$name}}' => $firstName ? ucfirst($firstName) : 'Customer',
            '{{$url}}' => $activityUrl,
            '{{$account}}' => $email,
        ];
        $body = strtr($body, $replacePairs);
        // 发送邮件
        $emailArr = [
            'email' => $email,
            'subject' => $subject,
            'body' => $body,
        ];
        return static::_sendEmail($storeID, $actID, $emailArr);
    }

    /**
     * 调用系统接口发送邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $emailArr
     * @param  string  $remark
     * @return string
     */
    public static function _sendEmail(int $storeID, int $actID, array $emailArr, $remark = "")
    {
        // 获取发送邮箱配置
        $service = static::getNamespaceClass();
        $method = 'getActivityEmailConfig';
        $parameters = [$storeID, $actID, $emailArr];
        $extData = [
            Constant::ACT_ID => $actID, //活动id
            'service' => $service,
            'method' => $method,
            'parameters' => $parameters,
        ];
        $group = 'activity';
        $type = 'email';

        return EmailService::handle($storeID, $emailArr['email'], $group, $type, $remark, '', '', $extData);
    }

    /**
     * 配置活动邮件内容
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $emailArr
     * @return array
     */
    public static function getActivityEmailConfig(int $storeID, int $actID, array $emailArr)
    {
        $rs = [
            Constant::RESPONSE_CODE_KEY => 1,
            Constant::DB_EXECUTION_PLAN_STOREID => $storeID, //商城id
            Constant::ACT_ID => $actID, //活动id
            Constant::DB_TABLE_CONTENT => '', //邮件内容
            Constant::SUBJECT => '',
            Constant::DB_TABLE_COUNTRY => '',
            Constant::DB_TABLE_ADDRESS => '',
            Constant::DB_TABLE_NAME => '',

        ];

        data_set($rs, Constant::SUBJECT, data_get($emailArr, 'subject'));
        data_set($rs, Constant::DB_TABLE_CONTENT, data_get($emailArr, 'body'));
        data_set($rs, Constant::DB_TABLE_ADDRESS, data_get($emailArr, 'email'));
        data_set($rs, Constant::DB_TABLE_NAME, data_get($emailArr, 'email'));

        return $rs;
    }

    /**
     * 标记活动邮件触发上线，并作相应处理
     * @param  int  $storeID
     * @param  int  $actID
     * @param  array  $emailArr
     * @param  array  $activityArr
     * @param  int  $limit
     */
    public static function _markSendEmail(
        int $storeID,
        int $actID,
        array $emailArr,
        array $activityArr,
        int $limit = 30
    ) {
        // zrank 判断是否存在成员
        // zadd 添加成员
        // incrby 提高分值
        // zrem 移除成员
        // zrangebyscore 通过分值获取分值区间内的所有成员
        $sep = ":";
        $maxCount = 1000; // 最大分值
        $activityUrl = data_get($activityArr, 'url', '');
        $email = data_get($emailArr, 'email');
        $userFirstName = data_get($emailArr, 'first_name', '');
        $customerID = data_get($emailArr, 'customer_id', 0);
        $value = $email.$sep.$userFirstName;
        // 记录缓存， 当一天内达到30封邮件记录，即触发退订邮件， 缓存有效期为一天 3600 * 24, 使用集合
        $key = "GuessLuckyNumber:sendEmail:store{$storeID}-act{$actID}";
        if (Cache::zcard($key) === 0) {
            // 当key值不存在 有序集合不存在
            Cache::zadd($key, [$value => 1]);
            Cache::expire($key, 24 * 3600);
        } else {
            if (Cache::zrank($key, $value) !== null) {
                // 存在发送记录 则进行加一
                Cache::zincrby($key, 1, $value);
            } else {
                Cache::zadd($key, [$value => 1]);
            }
            $emailList = Cache::zrangebyscore($key, $limit, $maxCount);
            if (count($emailList) > 0) {
                foreach ($emailList as $val) {
                    list($email, $firstName) = explode($sep, $val);
                    static::_sendReferenceEmail($storeID, $actID,$email, $firstName, $activityUrl);
                    // 增加停止发送邮箱配置
                    static::_handlePush($storeID, $actID, $customerID, $email, static::$revEntryNotify, static::$stopRev);
                }
                Cache::zremrangebyscore($key, $limit, $maxCount);
            }
            // 并不再推送后续邮件；若用户选择继续接受邮件，则继续推送邮件，后续不再监测
        }
    }


    /**
     *  * 处理用户提交停止接收活动邮件推送的配置
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $account
     * @param  int  $type  -2接受通知推送  -1接收开奖结果推送 1 取消开奖结果推送  2 取消通知推送   3 取消两种邮件推送
     * @return array|bool
     */
    public static function handlePush(int $storeID, int $actID, string $account, int $type)
    {
        // 停止
        $userModel = static::createModel('default_connection_'.$storeID, 'Customer');
        $customer = $userModel->select(['customer_id'])
            ->where(['account' => $account, 'store_id' => $storeID])->first();

        if (!$customer) {
            return false;
        }
        $customerID = data_get($customer, 'customer_id');

        switch (intval($type)) {
            case -2:
                // 接收消息通知邮件
                $action = static::$acceptRev;
                $group = static::$revEntryNotify;
                break;
            case -1:
                // 接收结果通知邮件
                $action = static::$acceptRev;
                $group = static::$revResult;
                break;
            case 1:
                // 拒收结果通知邮件
                $action = static::$stopRev;
                $group = static::$revResult;
                break;
            case 2:
                // 拒收消息通知邮件
                $action = static::$stopRev;
                $group = static::$revEntryNotify;
                break;
            default:
                $group = 0;
                $action = 0;
                break;
        }
        if (!$action || !$group) {
            return Constant::RESPONSE_FAILURE_CODE;
        }

        $res = static::_handlePush($storeID, $actID, $customerID, $account, $group, $action);
        return boolval($res) ? Constant::RESPONSE_SUCCESS_CODE : Constant::RESPONSE_FAILURE_CODE;
    }

    /**
     * * 处理推送活动邮件配置
     * @param $storeID
     * @param  int  $actID
     * @param  int  $customerID
     * @param  string  $account
     * @param  int  $type
     * @param  int  $action
     * @return mixed
     */
    public static function _handlePush($storeID, int $actID, int $customerID, string $account, int $type, int $action)
    {
        // -2接受通知推送  -1接收开奖结果推送 1 取消开奖结果推送  2 取消通知推送
        $configModel = static::createModel($storeID, 'ActivityEmailSet');
        $where = [
            'account' => $account,
            'act_id' => $actID,
            'customer_id' => $customerID,
            'type' => $type,
        ];
        $update = [
            'type_sub' => $action,
        ];
        $info = $configModel->where($where)->first();
        if ($info) {
            $info->type_sub = $action;
            $res = $info->save();
        } else {
            $res = $configModel->insert(array_merge($where, $update));
        }

        return $res;
    }

    /**
     * 发送邀请注册活动邮件
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $toEmail
     * @param  string  $account
     * @param  string  $url
     * @return int
     */
    public static function sendInviteEmail(int $storeID, int $actID, string $toEmail, string $account, string $url)
    {
        $customerInfo = static::createModel("default_connection_".$storeID, 'CustomerInfo')
            ->where(['store_id' => $storeID, 'account' => $account])
            ->select(['first_name', 'last_name', 'account as email', 'customer_id'])
            ->first();
        if (!$customerInfo) {
            return Constant::RESPONSE_FAILURE_CODE;
        } else {
            $customerInfo = $customerInfo->toArray();
        }
        // 使用带有邀请码的链接地址
        // 将邮件发送的邮箱地址改成被邀请方的邮箱
        data_set($customerInfo,'email',  $toEmail);
        data_set($customerInfo,'account',  $account);
        $activityInfo['url'] = $url;
        $res = static::_sendInviteEmail($storeID, $actID, $customerInfo, $activityInfo);
        return intval(boolval($res));
    }

}
