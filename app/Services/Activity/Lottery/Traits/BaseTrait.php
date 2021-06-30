<?php

/**
 * Base trait
 * User: Jmiy
 * Date: 2021-06-11
 * Time: 14:23
 */

namespace App\Services\Activity\Lottery\Traits;

use App\Services\ActivityPrizeItemService;
use App\Services\ActivityPrizeService;
use App\Services\ActivityService;
use App\Services\ActivityWinningService;
use App\Services\CreditService;
use App\Services\CustomerInfoService;
use App\Services\CustomerService;
use App\Services\InviteCodeService;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\Response;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

trait BaseTrait
{

    /**
     * 获取积分抽奖奖品数据
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 会员id
     * @param string $prizeCountry 奖品国家
     * @param array $extWhere 扩展where
     * @param null|array $orders 排序
     * @param array $actConfigData 活动配置
     * @return array $data
     */
    public static function getPrizeData($storeId = 0, $actId = 0, $customerId = 0, $prizeCountry = 'all', $extWhere = [], $orders = null, $actConfigData = [])
    {

        $dbExecutionPlan = ActivityPrizeService::getPrizeDbExecutionPlan($storeId, $actId, $customerId, $prizeCountry);

        $prefix = DB::getConfig(Constant::PREFIX);
        $where = data_get($dbExecutionPlan, (Constant::DB_EXECUTION_PLAN_PARENT . Constant::LINKER . Constant::DB_EXECUTION_PLAN_WHERE), []);

        $where[] = "({$prefix}p.qty_receive < {$prefix}p.qty)";
        $where[] = "({$prefix}pi.qty_receive < {$prefix}pi.qty)";
        $where = Arr::collapse([$where, $extWhere]);
        data_set($dbExecutionPlan, (Constant::DB_EXECUTION_PLAN_PARENT . Constant::LINKER . Constant::DB_EXECUTION_PLAN_WHERE), $where);

        $orders = $orders === null ? [['pi.winning_value', 'DESC']] : $orders;
        data_set($dbExecutionPlan, Constant::DB_EXECUTION_PLAN_PARENT . Constant::LINKER . 'orders', $orders);

        $dataStructure = 'list';
        $flatten = false;
        return FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, false, $dataStructure);
    }

    /**
     * 活动基础处理函数
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $customerId 用户id
     * @param array $extData 请求参数
     * @return array
     */
    public static function handleBase($storeId, $actId, $customerId, $extData = [])
    {

        $cacheKeyData = [md5(__METHOD__), $storeId, $actId, $customerId];
        $parameters = [
            function () use ($storeId, $actId, $customerId, $extData) {

                $result = Response::getDefaultResponseData(1);

                $inviteCode = data_get($extData, 'invite_code', null);
                if (!empty($inviteCode)) {//如果是被邀请者
                    $isValidInviteCode = InviteCodeService::exists(0, $inviteCode);
                    if (!$isValidInviteCode) {//如果邀请码无效，就直接提示用户
                        return Response::getDefaultResponseData(62006);
                    }
                }

                $isHandleAct = ActivityService::isHandle($storeId, $actId, $customerId, $extData);
                if (data_get($isHandleAct, Constant::RESPONSE_CODE_KEY, 0) != 1) {
                    return $isHandleAct;
                }

                $isWin = 0; //是否中 非安慰奖 1：中奖  0:不中奖
                $customer = CustomerService::getCustomerActivateData($storeId, $customerId);
                $prizeCountry = data_get($customer, 'info.country', '');
                $prizeCountry = $prizeCountry ? $prizeCountry : data_get($extData, Constant::DB_TABLE_COUNTRY, '');

                //获取参与奖
                $prizeWhere = ['p' . Constant::LINKER . Constant::DB_TABLE_IS_PARTICIPATION_AWARD => 1];
                $prizeItem = ActivityPrizeService::getData($storeId, $actId, $customerId, $prizeCountry, $prizeWhere, 1);

                data_set($prizeItem, Constant::ACTIVITY_WINNING_ID, 0); //设置中奖流水id
                data_set($prizeItem, Constant::DB_TABLE_COUNTRY, $prizeCountry); //设置中奖流水国家
                data_set($result, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::IS_WIN, $isWin); //设置是否中 非安慰奖
                data_set($result, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::PRIZE_DATA, $prizeItem); //设置中奖奖品

                //通过活动配置 获取禁止抽奖的国家
                $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_CONFIG_TYPE_WINNING);
                $banCountryData = data_get($activityConfigData, Constant::ACT_CONFIG_TYPE_WINNING . '_ban_country.value', '');
                if (!empty($banCountryData)) {
                    $banCountryData = explode(',', $banCountryData);
                    if (in_array($prizeCountry, $banCountryData)) {//如果当前用户来自 禁止抽奖的国家，就直接返回参与奖即可
                        data_set($result, Constant::RESPONSE_CODE_KEY, 62005);
                        return $result;
                    }
                }

                //获取奖品 $storeId = 0, $actId = 0, $customerId = 0, $prizeCountry = 'all', $extWhere = [], $orders = null, $actConfigData
                $extWhere = [];
                $orders = [];
                $prizeData = static::getPrizeData($storeId, $actId, $customerId, $prizeCountry, $extWhere, $orders, $activityConfigData);
                if (empty($prizeData)) {
                    data_set($result, Constant::RESPONSE_CODE_KEY, 62002);
                    return $result;
                }

                /*                             * ***************更新奖品领取数量以便 提前占有 这些奖品，防止高并发多人领取没有库存的奖品 ************************ */
                $prizeIds = array_unique(array_filter(data_get($prizeData, '*.id', []))); //奖品id
                $prizeItemIds = array_unique(array_filter(data_get($prizeData, '*.item_id', []))); //奖品 item id
                if ($prizeItemIds) {
                    $where = [
                        Constant::DB_TABLE_PRIMARY => $prizeItemIds,
                    ];
                    $data = [
                        Constant::DB_TABLE_QTY_RECEIVE => DB::raw(Constant::DB_TABLE_QTY_RECEIVE . '+1'),
                    ];
                    ActivityPrizeItemService::update($storeId, $where, $data);
                }

                if ($prizeIds) {
                    $where = [
                        Constant::DB_TABLE_PRIMARY => $prizeIds,
                    ];
                    $data = [
                        Constant::DB_TABLE_QTY_RECEIVE => DB::raw(Constant::DB_TABLE_QTY_RECEIVE . '+1'),
                    ];
                    ActivityPrizeService::update($storeId, $where, $data);
                }

                //遍历奖品，根据概率选择奖品
                foreach ($prizeData as $item) {
                    $num = mt_rand(1, data_get($item, Constant::DB_TABLE_MAX, 1));
                    if ($num <= data_get($item, Constant::DB_TABLE_WINNING_VALUE, 1)) {//如果中奖了，就获取奖品
                        $prizeItem = $item;
                        $isWin = 1;
                        break;
                    }
                }

                if (empty($prizeItem)) {//如果没有中奖，就获取参与奖
                    $prizeData = collect($prizeData);
                    $prizeItem = $prizeData->where(Constant::DB_TABLE_IS_PARTICIPATION_AWARD, 1)->first();
                }

                $prizeId = data_get($prizeItem, Constant::DB_TABLE_PRIMARY, 0); //奖品id
                $prizeItemId = data_get($prizeItem, Constant::DB_TABLE_ITEM_ID, 0); //奖品 item id

                /*                             * ***************更新奖品领取数量以便 释放 提前占有的奖品，防止高并发多人领取没有库存的奖品 ************************ */
                $prizeItemIds = array_diff($prizeItemIds, [$prizeItemId]);
                $prizeIds = array_diff($prizeIds, [$prizeId]);
                if ($prizeItemIds) {
                    $where = [
                        Constant::DB_TABLE_PRIMARY => $prizeItemIds,
                    ];
                    $data = [
                        Constant::DB_TABLE_QTY_RECEIVE => DB::raw('qty_receive-1'),
                    ];
                    ActivityPrizeItemService::update($storeId, $where, $data);
                }

                if ($prizeIds) {
                    $where = [
                        Constant::DB_TABLE_PRIMARY => $prizeIds,
                    ];
                    $data = [
                        Constant::DB_TABLE_QTY_RECEIVE => DB::raw('qty_receive-1'),
                    ];
                    ActivityPrizeService::update($storeId, $where, $data);
                }

                $isParticipationAward = data_get($prizeItem, Constant::DB_TABLE_IS_PARTICIPATION_AWARD, 0); //是否是参与奖

                data_set($result, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::IS_WIN, $isWin); //设置是否中奖

                if (empty($prizeId)) {
                    return $result;
                }

                /*                             * ***************添加中奖流水数据************************ */
                $prizeType = data_get($prizeItem, 'type', 0); //奖品类型（礼品卡/coupon/实物/活动积分/其他） 奖品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
                //获取用户基本资料
                $customerInfoData = CustomerInfoService::getData($storeId, $customerId);

                $data = [
                    Constant::DB_TABLE_ACCOUNT => data_get($extData, Constant::DB_TABLE_ACCOUNT, ''),
                    Constant::DB_TABLE_IP => data_get($extData, Constant::DB_TABLE_IP, ''),
                    Constant::DB_TABLE_QUANTITY => DB::raw(Constant::DB_TABLE_QUANTITY . '+1'),
                    Constant::DB_TABLE_IS_PARTICIPATION_AWARD => $isParticipationAward,
                    Constant::DB_TABLE_COUNTRY => $prizeCountry,
                    Constant::PRIZE_TYPE => $prizeType,
                    Constant::DB_TABLE_FIRST_NAME => data_get($customerInfoData, Constant::DB_TABLE_FIRST_NAME, ''),
                    Constant::DB_TABLE_LAST_NAME => data_get($customerInfoData, Constant::DB_TABLE_LAST_NAME, ''),
                    Constant::DB_TABLE_PRIZE_ID => $prizeId,
                    Constant::DB_TABLE_PRIZE_ITEM_ID => $prizeItemId,
                    Constant::DB_TABLE_ACT_ID => $actId,
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                ];
                $activityWinningId = ActivityWinningService::getModel($storeId)->insertGetId($data);//中奖流水 主键id

                //更新会员中奖列表缓存
                $tags = ActivityWinningService::getCustomerWinCacheTag();
                Cache::tags($tags)->flush();

                //获取中奖流水数据
                $member = ActivityWinningService::getItem($storeId, $actId, $prizeId, $customerId, $prizeItemId, $activityWinningId);
                $extType=ActivityWinningService::getModelAlias();//关联模型
                if ($prizeType == 5) {//如果是积分，就添加积分到当前用户
                    $credit = data_get($prizeItem, Constant::DB_TABLE_TYPE_VALUE, 0); //类型数据(即积分)
                    $creditData = FunctionHelper::getHistoryData([
                        Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                        Constant::DB_TABLE_VALUE => $credit,
                        Constant::DB_TABLE_ADD_TYPE => 1,
                        Constant::DB_TABLE_ACTION => 'lottery',
                        Constant::DB_TABLE_EXT_ID => $activityWinningId,
                        Constant::DB_TABLE_EXT_TYPE => $extType,
                    ], [Constant::DB_TABLE_STORE_ID => $storeId]);

                    CreditService::handle($creditData); //记录积分流水
                }

                data_set($prizeItem, Constant::DB_TABLE_EXT_ID, $activityWinningId); //关联id
                data_set($prizeItem, Constant::DB_TABLE_EXT_TYPE, $extType); //关联模型
                data_set($prizeItem, Constant::ACTIVITY_WINNING_ID, $activityWinningId); //设置中奖流水id
                data_set($prizeItem, Constant::DB_TABLE_COUNTRY, $prizeCountry); //设置中奖流水国家
                data_set($result, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::PRIZE_DATA, $prizeItem); //设置中奖奖品

                //处理中奖排行榜
                $rank = data_get($activityConfigData, Constant::ACT_CONFIG_TYPE_WINNING . '_rank.value', 0);; //是否需要中奖排行榜 1:需要  0:不需要 默认:0
                if ($rank) {
                    $isNeedParticipationAward = data_get($activityConfigData, Constant::ACT_CONFIG_TYPE_WINNING . '_rank_is_need_participation_award.value', 0);; //中奖排行榜是否需要安慰奖 1:需要  0:不需要 默认:0
                    $isInited = ActivityWinningService::initRank($storeId, $actId, $isNeedParticipationAward);
                    if ($isInited == false) {
                        return $result;
                    }

                    if ($isParticipationAward == 1 && $isNeedParticipationAward == 0) {//如果是安慰奖,并且安慰奖不放入中奖排行榜，就直接返回
                        return $result;
                    }

                    //更新中奖排行榜数据
                    $zsetKey = ActivityWinningService::getRankKey($storeId, $actId);
                    $score = Carbon::parse(data_get($member, Constant::DB_TABLE_UPDATED_AT, Carbon::now()->toDateTimeString()))->timestamp; //当前时间戳
                    unset($member[Constant::DB_TABLE_UPDATED_AT]);
                    Redis::zadd($zsetKey, $score, static::getZsetMember($member));
                    $ttl = 30 * 24 * 60 * 60;
                    Redis::expire($zsetKey, $ttl);
                }

                return $result;
            }
        ];

        $rs = static::handleLock($cacheKeyData, $parameters);

        return $rs === false ? Response::getDefaultResponseData(62007) : $rs;

    }

}
