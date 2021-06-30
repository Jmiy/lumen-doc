<?php

namespace App\Services\Activity\LuckyNumber;

use App\Services\Activity\Contracts\ServiceInterface;
use App\Services\Activity\Traits\ActBase;
use App\Services\ActivityGuessNumberService;
use App\Services\ActivityService;
use App\Services\BaseService;
use App\Services\InviteService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\Response;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLuckyNumberService;
use App\Services\AttributeService;
use App\Services\ActivityPrizeService;
use App\Services\ActivityPrizeItemService;
use App\Services\ActivityWinningService;


class Service extends BaseService implements ServiceInterface
{

    use ActBase;

    /**
     * 更新活动次数 Jmiy_cen 2021-05-24 09:17 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $requestData 请求数据
     * @param string $type 类型
     * @param string $key key
     * @param int $num 更新数量
     * @return mix
     */
    public static function updateNum($storeId = 0, $actId = 0, $customerId = 0, $requestData = [], $type = 'add_nums', $key = Constant::ACTION_INVITE, $num = 1)
    {

        //次数更新
        $rs = static::baseUpdateNum($storeId, $actId, $customerId, $requestData, $type, $key, $num);

        if (data_get($rs, Constant::RESPONSE_CODE_KEY) != 1) {
            return $rs;
        }

        if ($num > 0) {

            data_set($requestData, Constant::DB_TABLE_STORE_ID, $storeId);
            data_set($requestData, Constant::DB_TABLE_ACT_ID, $actId);
            data_set($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY, $customerId);
            static::event($storeId, $actId, $requestData, Constant::EVENT_UPDATE_NUM, [Constant::EVENTS, Constant::LISTENERS]);

//            //获取活动剩余次数
//            $actionData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'get', [], []);
//            $lotteryData = ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);
//            $lotteryNum = data_get($lotteryData, Constant::LOTTERY_NUM, 0);
//
//            //发送次数变更邮件
//            ActivityGuessNumberService::sendNotificationEmail($storeId, $actId, $customerId, ($lotteryNum > 0 ? $lotteryNum : 0));
        }

        return $rs;
    }


    /**
     * 邀请
     * @param $requestData
     * @return bool|mixed
     */
    public static function handleInvite($requestData)
    {

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID);
        $inviteId = data_get($requestData, Constant::EVENT_DATA . Constant::LINKER . Constant::INVITE_DATA . Constant::LINKER . Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::DB_TABLE_PRIMARY);
        $customerId = data_get($requestData, Constant::EVENT_DATA . Constant::LINKER . Constant::DB_TABLE_CUSTOMER_PRIMARY);//邀请者id

        $isValidAct = static::isValidAct($storeId, $actId);
        if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
            return $isValidAct;
        }

        //获取活动形式
        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
        $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
        if (empty($actForm)) {
            return false;
        }
        data_set($requestData, Constant::ACT_FORM, $actForm);

        //获取活动配置次数
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, $actForm, [
            Constant::ACTION_INVITE,
            Constant::DB_TABLE_COUNTRY,
        ]);

        $playNums = data_get($activityConfigData, $actForm . '_' . Constant::ACTION_INVITE . Constant::LINKER . Constant::DB_TABLE_VALUE); //次数;
        if (empty($playNums)) {//如果当前活动没有配置更新的次数，就直接返回
            return false;
        }
        $countryData = data_get($activityConfigData, $actForm . '_' . Constant::DB_TABLE_COUNTRY . Constant::LINKER . Constant::DB_TABLE_VALUE); //国家

        //获取活动数据
        $actData = data_get($isValidAct, Constant::RESPONSE_DATA_KEY);

        //计算过期时间,缓存至活动结束时间点
        $actEndAt = null;
        $endAt = data_get($actData, Constant::DB_TABLE_END_AT);
        if ($endAt !== null) {
            $actEndAt = Carbon::parse($endAt)->timestamp;
        }
        $currentTime = Carbon::now()->timestamp;
        $expireTime = $actEndAt === null ? (30 * 24 * 60 * 60) : ($actEndAt - $currentTime); //缓存时间 单位秒

        //邀请逻辑，邀请的用户，相同的注册IP只送一次邀请次数
        //获取用户注册IP
        $ip = data_get($requestData, Constant::DB_TABLE_IP, Constant::PARAMETER_INT_DEFAULT);
        $country = FunctionHelper::getCountry($ip);
        if ($countryData !== null) {
            $countryData = explode(',', $countryData);

            if (!in_array($country, $countryData)) {
                return false;
            }
        }

        $inviteKey = $actForm . '_' . Constant::ACTION_INVITE . "_{$storeId}_{$actId}_{$ip}";
        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'exists', [$inviteKey], []);
        $isExists = static::handleCache('', $handleCacheData);
        if ($isExists) {
            //重复设置更新缓存时间，为了防止活动结束时间更新
            $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setex', [$inviteKey, $expireTime, 1], []);
            static::handleCache('', $handleCacheData);

            return false;
        }

        //更新活动次数
        static::updateNum($storeId, $actId, $customerId, $requestData, 'add_nums', Constant::ACTION_INVITE, $playNums);

        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setex', [$inviteKey, $expireTime, 1], []);
        static::handleCache('', $handleCacheData);

        //更新邀请标识
        InviteService::update($storeId, [Constant::DB_TABLE_PRIMARY => $inviteId], ['is_act_show' => 1]);

        return true;

    }

    /**
     * 关注
     * @param $requestData 请求参数
     * @return mixed|void
     */
    public static function handleFollow($requestData)
    {

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID);
        $customerId = data_get($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY);//邀请者id

        $isValidAct = static::isValidAct($storeId, $actId);
        if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
            return $isValidAct;
        }

        //获取活动形式
        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
        $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
        if (empty($actForm)) {
            return Response::getDefaultResponseData(9999999999);//活动形式未配置
        }
        data_set($requestData, Constant::ACT_FORM, $actForm);

        //获取活动配置次数
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, $actForm, [
            Constant::ACTION_FOLLOW,
        ]);

        $playNums = data_get($activityConfigData, $actForm . '_' . Constant::ACTION_FOLLOW . Constant::LINKER . Constant::DB_TABLE_VALUE); //次数;
        if (empty($playNums)) {//如果当前活动没有配置更新的次数，就直接返回
            return Response::getDefaultResponseData(1);//关注不添加次数
        }

        //获取活动 标识到期时间
        $actData = data_get($isValidAct, Constant::RESPONSE_DATA_KEY);
        $actEndAt = null;
        $endAt = data_get($actData, Constant::DB_TABLE_END_AT);
        if ($endAt !== null) {
            $actEndAt = Carbon::parse($endAt)->timestamp;
        }
        $currentTime = Carbon::now()->timestamp;
        $expireTime = $actEndAt === null ? (30 * 24 * 60 * 60) : ($actEndAt - $currentTime); //缓存时间 单位秒

        $cacheKey = implode('_', [Constant::ACTION_FOLLOW, $storeId, $actId, $customerId]);
        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'exists', [$cacheKey], []);
        $isExists = static::handleCache('', $handleCacheData);
        if ($isExists) {
            //重复设置更新缓存时间，为了防止活动结束时间更新
            $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setex', [$cacheKey, $expireTime, 1], []);
            static::handleCache('', $handleCacheData);

            return Response::getDefaultResponseData(1);//关注已经添加过次数
        }

        //更新活动次数
        $rs = static::updateNum($storeId, $actId, $customerId, $requestData, 'add_nums', Constant::ACTION_FOLLOW, $playNums);

        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setex', [$cacheKey, $expireTime, 1], []);
        static::handleCache('', $handleCacheData);

        return $rs;

    }

    /**
     * 参与活动
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $customerId 用户id
     * @param array $extData 请求参数
     * @return \App\Services\Traits\mix|array
     */
    public static function handle($storeId, $actId, $customerId, $extData = [])
    {

        $cacheKeyData = [md5(__METHOD__), $storeId, $actId, $customerId];
        $parameters = [
            function () use ($storeId, $actId, $customerId, $extData) {

                if (empty($actId)) {//如果没有活动id，就直接返回
                    return Response::getDefaultResponseData(1);
                }

                //获取活动形式
                $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
                $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
                if (empty($actForm)) {
                    return Response::getDefaultResponseData(1);
                }

                //获取活动配置次数
                $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, $actForm, [
                    Constant::PLAY,
                ]);

                $playNums = data_get($activityConfigData, $actForm . '_' . Constant::PLAY . Constant::LINKER . Constant::DB_TABLE_VALUE); //次数;
                if (!empty($playNums)) {
                    $actionData = FunctionHelper::getJobData(static::getNamespaceClass(), 'get', [], $extData);
                    $lotteryData = ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);
                    $lotteryNum = data_get($lotteryData, Constant::LOTTERY_NUM, 0);
                    if ($lotteryNum < abs($playNums)) {
                        return Response::getDefaultResponseData(62000);
                    }
                }

                //更新活动次数
                $rs = static::updateNum($storeId, $actId, $customerId, $extData, 'deduct_nums', Constant::PLAY, $playNums);
                if (data_get($rs, Constant::RESPONSE_CODE_KEY) != 1) {//如果更新数据失败，就直接返回
                    return $rs;
                }

                $guessNum = data_get($extData, Constant::GUESS_NUM, '');
                $data = [
                    Constant::DB_TABLE_ACT_ID => $actId,
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                    Constant::DB_TABLE_ACCOUNT => data_get($extData, Constant::DB_TABLE_ACCOUNT, ''),
                    Constant::SHOW_GUESS_NUM => $guessNum,
                    Constant::GUESS_NUM => intval($guessNum),
                ];
                //添加猜数
                $id = ActivityGuessNumberService::getModel($storeId)->insertGetId($data);

                return Response::getDefaultResponseData(1, null, $id);
            }
        ];

        $rs = static::handleLock($cacheKeyData, $parameters);

        return $rs === false ? Response::getDefaultResponseData(110001) : $rs;

    }

    /**
     * 添加 中奖流水 触发 邮件
     * @param $storeId
     * @param $prizeType
     * @param array $winIds
     * @return false
     */
    public static function insertWinLog($storeId, $prizeType, $winIds = [])
    {

        if (empty($winIds)) {
            return false;
        }

        $winWhere = [
            Constant::DB_TABLE_PRIMARY => $winIds
        ];

        //添加中奖流水
        $select = [
            Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_ACT_ID,
            Constant::DB_TABLE_CUSTOMER_PRIMARY,
            Constant::DB_TABLE_ACCOUNT,
            Constant::DB_TABLE_COUNTRY,
            Constant::DB_TABLE_IP,
            Constant::DB_TABLE_PRIZE_ID,
            Constant::DB_TABLE_PRIZE_ITEM_ID,
            Constant::DB_TABLE_CREATED_AT,
            Constant::DB_TABLE_UPDATED_AT,
            Constant::DB_TABLE_CREATED_MARK,
            Constant::DB_TABLE_UPDATED_MARK,
        ];
        $winData = ActivityGuessNumberService::getModel($storeId)
            ->buildWhere($winWhere)
            ->select($select)
            ->with(['customer_info' => function ($relation) {
                $relation->select([Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::DB_TABLE_FIRST_NAME, Constant::DB_TABLE_LAST_NAME]);
            }])
            ->get();
        foreach ($winData as $item) {
            $item = $item->toArray();
            $guessNumberId = data_get($item, Constant::DB_TABLE_PRIMARY);
            $firstName = data_get($item, 'customer_info.' . Constant::DB_TABLE_FIRST_NAME, '');
            $lastName = data_get($item, 'customer_info.' . Constant::DB_TABLE_LAST_NAME, '');

            $firstName = $firstName ?? '';
            $lastName = $lastName ?? '';

            unset($item[Constant::DB_TABLE_PRIMARY]);
            unset($item['customer_info']);

            $winLog = Arr::collapse([$item, [
                Constant::PRIZE_TYPE => $prizeType,
                Constant::DB_TABLE_QUANTITY => 1,
                Constant::DB_TABLE_FIRST_NAME => $firstName,
                Constant::DB_TABLE_LAST_NAME => $lastName,
            ]]);

            $winLogId = ActivityWinningService::getModel($storeId)->insertGetId($winLog);

            //更新中奖流水id
            $guessNumberUpdate = [
                Constant::WIN_LOG_ID => $winLogId,
                Constant::DB_TABLE_FIRST_NAME => $firstName,
                Constant::DB_TABLE_LAST_NAME => $lastName,
            ];
            ActivityGuessNumberService::update($storeId, [Constant::DB_TABLE_PRIMARY => $guessNumberId], $guessNumberUpdate);
        }

        return true;
    }

    /**
     * 排除候选人
     * @param $storeId
     * @param $actId
     * @param int $luckyNumId
     * @param array $guessNumberData
     * @return bool
     */
    public static function removeCandidates($storeId, $actId, $luckyNumId = 0, $guessNumberData = [])
    {
        //设置中奖ip不再参与本次发奖 一个IP当天只能中一次 一个账号当天只能中一次
        //把已经中奖的 ip 或者 账号  设置为不再参与当天开奖
        $updateData = [
            Constant::LUCKY_NUM_ID => 0,
        ];

        $ipWhere = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::WIN_LOG_ID => 0,
            Constant::LUCKY_NUM_ID => $luckyNumId,
            '{customizeWhere}' => [
                FunctionHelper::getJobData('', Constant::DB_EXECUTION_PLAN_WHERE, [
                    function ($query) use ($guessNumberData) {
                        $query->whereIn(Constant::DB_TABLE_IP, data_get($guessNumberData, Constant::DB_TABLE_IP))
                            ->orWhereIn(Constant::DB_TABLE_ACCOUNT, data_get($guessNumberData, Constant::DB_TABLE_ACCOUNT));
                    }
                ], []),
            ],
        ];

        return ActivityGuessNumberService::update($storeId, $ipWhere, $updateData);
    }

    /**
     * 设置中奖记录
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $minPrizeNums 最小开奖数量
     * @param int $luckyNumId 开奖id
     * @param int $luckNum 开奖数字
     * @param string $showNum 开奖数字 用于显示
     * @param int $luckyDate 开奖时间 时间戳(精确到天 单位:秒)
     * @return bool
     */
    public static function win($storeId, $actId, $minPrizeNums = 0, $luckyNumId = 0, $luckNum = 0, $showNum = '', $luckyDate = 0)
    {

        if (empty($minPrizeNums) || $minPrizeNums < 0) {
            return true;
        }

        //排除候选人
        $select = [
            Constant::DB_TABLE_IP,
            Constant::DB_TABLE_ACCOUNT,
            Constant::DIFF_NUM,
        ];
        $winWhere = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::LUCKY_NUM_ID => $luckyNumId,
            [[Constant::WIN_LOG_ID, '>', 0]],
        ];
        $guessNumberData = ActivityGuessNumberService::getModel($storeId)->select($select)->buildWhere($winWhere)->get();
        $removeCandidates = [];
        $__count = 0;//不等于开奖数字的记录条数
        $count = 0;//已经发奖记录条数
        if ($guessNumberData->isNotEmpty()) {
            $removeCandidates = [
                Constant::DB_TABLE_IP => $guessNumberData->pluck(Constant::DB_TABLE_IP)->all(),
                Constant::DB_TABLE_ACCOUNT => $guessNumberData->pluck(Constant::DB_TABLE_ACCOUNT)->all(),
            ];

            $diffNumData = $guessNumberData->pluck(Constant::DIFF_NUM)->all();
            foreach ($diffNumData as $diffNum) {
                if ($diffNum != 0) {
                    $__count += 1;
                }
            }

            if ($__count > 0 && $guessNumberData->count() >= $minPrizeNums) {
                return true;
            }

            $count = $guessNumberData->count();
        }

        //设置差值
        $diffSql = "(" . Constant::NUM . '-' . Constant::GUESS_NUM . ")";
        $data = [
            Constant::NUM => $luckNum,
            Constant::DIFF_NUM => DB::raw("IF(" . $diffSql . ">=0," . $diffSql . "," . ("-" . $diffSql) . ")"),
            Constant::LUCKY_NUM_ID => $luckyNumId,
            Constant::SHOW_NUM => $showNum,
            Constant::DATE => $luckyDate,
        ];
        $where = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::WIN_LOG_ID => 0,
            [[Constant::DB_TABLE_CREATED_AT, '<', Carbon::createFromTimestamp($luckyDate + 24 * 60 * 60)->toDateTimeString()]]
        ];
        if ($removeCandidates) {
            $where['{customizeWhere}'] = [
                FunctionHelper::getJobData('', Constant::DB_EXECUTION_PLAN_WHERE, [
                    function ($query) use ($removeCandidates) {
                        $query->whereNotIn(Constant::DB_TABLE_IP, data_get($removeCandidates, Constant::DB_TABLE_IP))
                            ->whereNotIn(Constant::DB_TABLE_ACCOUNT, data_get($removeCandidates, Constant::DB_TABLE_ACCOUNT));
                    }
                ], []),
            ];
        }
        $isUpdate = ActivityGuessNumberService::update($storeId, $where, $data);
        if (empty($isUpdate)) {//如果没有 候选人  就直接返回
            return true;
        }

        //获取奖品数据
        $prizeData = ActivityPrizeService::existsOrFirst($storeId, '', [Constant::DB_TABLE_ACT_ID => $actId], true, [Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_TYPE]);
        $prizeId = data_get($prizeData, Constant::DB_TABLE_PRIMARY, 0);

        $prizeItemData = ActivityPrizeItemService::existsOrFirst($storeId, '', [Constant::DB_TABLE_PRIZE_ID => $prizeId], true, [Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_TYPE, Constant::DB_TABLE_TYPE_VALUE]);
        $prizeItemId = data_get($prizeItemData, Constant::DB_TABLE_PRIMARY, 0);
        $prizeType = data_get($prizeData, Constant::DB_TABLE_TYPE, 0);

        $winIds = [];
        $guessNum = null;
        while ($count < $minPrizeNums || $guessNum === null || $guessNum == $luckNum) {

            //获取中奖记录
            $_where = [
                Constant::DB_TABLE_ACT_ID => $actId,
                Constant::WIN_LOG_ID => 0,
                Constant::LUCKY_NUM_ID => $luckyNumId,
            ];
            $select = [
                Constant::DB_TABLE_PRIMARY,
                Constant::DB_TABLE_IP,
                Constant::DB_TABLE_ACCOUNT,
                Constant::GUESS_NUM,
            ];
            $orders = [
                [Constant::DIFF_NUM, Constant::DB_EXECUTION_PLAN_ORDER_ASC],
                [Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC]
            ];
            $guessNumberData = ActivityGuessNumberService::existsOrFirst($storeId, '', $_where, true, $select, $orders);

            if (empty($guessNumberData)) {
                break;
            }

            //设置中奖记录
            $guessNumberId = data_get($guessNumberData, Constant::DB_TABLE_PRIMARY, -1);
            $guessNum = data_get($guessNumberData, Constant::GUESS_NUM, -1);
            if ($guessNum != $luckNum && $count >= $minPrizeNums) {//如果不是 全等于开奖数字 并且 发奖数量到达奖品数，就停止发奖
                break;
            }

            $updateData = [
                Constant::WIN_LOG_ID => 1,
                Constant::DB_TABLE_PRIZE_ID => $prizeId,
                Constant::DB_TABLE_PRIZE_ITEM_ID => $prizeItemId,
            ];
            $winWhere = [
                Constant::DB_TABLE_PRIMARY => $guessNumberId
            ];
            ActivityGuessNumberService::update($storeId, $winWhere, $updateData);

            //排除候选人
            $removeCandidates = [
                Constant::DB_TABLE_IP => [data_get($guessNumberData, Constant::DB_TABLE_IP, '')],
                Constant::DB_TABLE_ACCOUNT => [data_get($guessNumberData, Constant::DB_TABLE_ACCOUNT, '')],
            ];
            static::removeCandidates($storeId, $actId, $luckyNumId, $removeCandidates);

            $winIds[] = $guessNumberId;

            $count += 1;
        }

        if (empty($winIds)) {
            return true;
        }

        static::insertWinLog($storeId, $prizeType, $winIds);

        //发送邮件
        ActivityGuessNumberService::sendResultEmail($storeId, $actId, $luckyNumId, $winIds);

        return true;
    }

    /**
     * 开奖
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @return array|bool
     */
    public static function lucky($storeId, $actId)
    {
//        $init = 1160;
//        for ($i = 0; $i <= 100000; $i++) {
//
//            $data[] = [
//                'guess_num' => $init,
//                'ip' => $i,
//            ];
//
//            if ($i % 200 == 199) {
//                ActivityGuessNumberService::getModel($storeId)->insert($data);
//                unset($data);
//                $init = $init + 1;
//            }
//
//        }
//        dd(1);

        //获取活动形式
        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
        $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
        if (empty($actForm)) {
            return Response::getDefaultResponseData(1);
        }

        //获取活动 开奖数量
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, $actForm, [
            Constant::PRIZE_NUM,
            Constant::MIN_PRIZE_NUM,
            Constant::RAND_NUM,
        ]);

        $prizeNums = data_get($activityConfigData, $actForm . '_' . Constant::PRIZE_NUM . Constant::LINKER . Constant::DB_TABLE_VALUE); //开奖数量
        $minPrizeNums = data_get($activityConfigData, $actForm . '_' . Constant::MIN_PRIZE_NUM . Constant::LINKER . Constant::DB_TABLE_VALUE); //最小开奖数量
        if (empty($prizeNums) || empty($minPrizeNums)) {
            return Response::getDefaultResponseData(-1);//活动 不开奖
        }

        //获取开奖数字
        $luckyDate = Carbon::yesterday()->timestamp;//开奖时间 时间戳(精确到天 单位:秒)
        $luckyNumberWhere = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::DATE => $luckyDate,
        ];
        $luckyNumberData = ActivityLuckyNumberService::existsOrFirst($storeId, '', $luckyNumberWhere, true, [Constant::DB_TABLE_PRIMARY, Constant::NUM, Constant::SHOW_NUM, Constant::DATE]);
        if (!empty($luckyNumberData)) {

            $luckyNumId = data_get($luckyNumberData, Constant::DB_TABLE_PRIMARY);
            $luckNum = data_get($luckyNumberData, Constant::NUM);
            $showNum = data_get($luckyNumberData, Constant::SHOW_NUM, Constant::PARAMETER_STRING_DEFAULT);
            $luckyDate = data_get($luckyNumberData, Constant::DATE, Constant::PARAMETER_INT_DEFAULT);
            return static::win($storeId, $actId, $minPrizeNums, $luckyNumId, $luckNum, $showNum, $luckyDate);
        }

        $randNum = data_get($activityConfigData, $actForm . '_' . Constant::RAND_NUM . Constant::LINKER . Constant::DB_TABLE_VALUE); //开奖数量
        $initLuckNum = intval(FunctionHelper::getRandNum($randNum));

        $max = pow(10, $randNum) - 1;
        $min = 0;

        /**
         * 生成中奖数字规则
         * 4、活动期间每天随机生成000-999（包含）的随机数，在第二天的0点0分01秒公布前一天随机生成的随机数。
         * 5、将我们随机生成的数字和用户所有成功填写的数字进行相减并取绝对值，
         * 随机数生成规则：后台随机生成幸运数字时，判断绝对值为0数字是否<=15个，若是则公布该随机数；若否，则重新生成随机数；（当000-999中的生成的所有随机数，绝对值为0数字都大于15个，则取绝对值为0数字最少的随机数）；
         * 中奖规则：绝对值从小到大进行排列，绝对值相同则按照填写时间先后进行排序。
         * a、当绝对值为0数字>=10个，绝对值为0的所有数字的获奖；当绝对值为0数字<10个，则增加绝对值>0的数字的前X个数字获奖；X=[10-绝对值为0数字个数]；
         * b、同一天所有中奖用户中，同一ip用户多数字中奖做去重处理，取绝对值顺序最前的数字获奖；一个IP当天只能中一次；去重后用户中奖的数字不再参与本次发奖；
         */
        $winIds = [];//保存中奖的记录id
        $_luckNum = $luckNum = $initLuckNum;//中奖数字

        $_nData = [-1, 1];
        $_n = $_nData[array_rand($_nData)];
        $equalSum = 0;//等值中奖总数

        $traverseMin = false;//是否遍历了 比 $luckNum 小的所有数字  true:是 false:否 默认:false
        $traverseMax = false;//是否遍历了 比 $luckNum 大的所有数字  true:是 false:否 默认:false

        $toDayDateTimeString = Carbon::today()->toDateTimeString();//当天精确到天的时间戳 单位：秒

        beginning:

        //获取 Constant::GUESS_NUM==$_luckNum 并且是昨天以前(包含昨天) 的未中奖的数据
        $_where = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::WIN_LOG_ID => 0,
            Constant::GUESS_NUM => $_luckNum,
            [[Constant::DB_TABLE_CREATED_AT, '<', $toDayDateTimeString]]
        ];

        //获取 中奖名单(Constant::GUESS_NUM == $luckNum)
        $select = [
            DB::raw('min(' . Constant::DB_TABLE_PRIMARY . ') as ' . Constant::DB_TABLE_PRIMARY),
            Constant::DB_TABLE_ACCOUNT,
        ];
        $_equalData = ActivityGuessNumberService::getModel($storeId)->select($select)->buildWhere($_where)->groupBy(Constant::DB_TABLE_IP)->get();
        $equalData = [];
        foreach ($_equalData as $item) {
            $account = data_get($item, Constant::DB_TABLE_ACCOUNT, Constant::PARAMETER_STRING_DEFAULT);
            if (!isset($equalData[$account])) {
                $equalData[$account] = data_get($item, Constant::DB_TABLE_PRIMARY);
            }
        }
        $equalData = array_values($equalData);
        $_equalSum = count($equalData);//ip去重 账号去重 后 等值中奖总数

        if ($equalSum == 0) {
            $equalSum = $_equalSum;
            $winIds = $equalData;
            $luckNum = $_luckNum;
        }

        if ($_equalSum <= $prizeNums) {
            $equalSum = $_equalSum;
            $winIds = $equalData;
            $luckNum = $_luckNum;
        } else {//如果等值中奖ip去重后总数大于，活动的中奖总数，就继续遍历下一个可能的中奖数字

            if ($_equalSum < $equalSum) {
                $equalSum = $_equalSum;
                $winIds = $equalData;
                $luckNum = $_luckNum;
            }

            $_luckNum = $_luckNum + $_n;
            if ($_n < 0) {

                if (!$traverseMin) {

                    if ($_luckNum >= $min) {
                        goto beginning;
                    } else {

                        $traverseMin = true;

                        if (!$traverseMax) {
                            $_n = -$_n;
                            $_luckNum = $initLuckNum + $_n;
                            goto beginning;
                        }

                    }
                }
            } else {

                if (!$traverseMax) {

                    if ($_luckNum <= $max) {
                        goto beginning;
                    } else {
                        $traverseMax = true;
                        if (!$traverseMin) {
                            $_n = -$_n;
                            $_luckNum = $initLuckNum + $_n;
                            goto beginning;
                        }
                    }
                }

            }
        }

        $showNum = FunctionHelper::getRandNum($randNum, $luckNum);

        //添加开奖数据
        $luckyNumberData = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::NUM => $luckNum,
            Constant::SHOW_NUM => $showNum,
            Constant::DATE => $luckyDate,
        ];
        $id = ActivityLuckyNumberService::getModel($storeId)->insertGetId($luckyNumberData);

        //记录开奖时 幸运数字全等于开奖数字 的流水id
//        $attributeData = [
//            Constant::OWNER_RESOURCE => ActivityLuckyNumberService::getModelAlias(),
//            Constant::OWNER_ID => $id,
//            Constant::NAME_SPACE => $actForm,
//            Constant::DB_TABLE_KEY => 'equal_win_ids',
//            Constant::DB_TABLE_VALUE => json_encode($winIds, JSON_UNESCAPED_UNICODE),
//        ];
//        AttributeService::getModel($storeId)->insertGetId($attributeData);

        static::win($storeId, $actId, $minPrizeNums, $id, $luckNum, $showNum, $luckyDate);//, [$attributeData]

        return $winIds;
    }
}
