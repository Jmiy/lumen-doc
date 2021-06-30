<?php

namespace App\Http\Controllers\Api;

use App\Services\AwardUserService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Response;
use App\Services\RankService;
use App\Services\ActivityService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Services\Activity\Factory;

class ActivityController extends Controller {

    /**
     * 获取走马灯数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLanternData(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, ActivityService::getValidActIds($storeId)); //获取有效的活动id
        $type = 1; //榜单类型 1:分享 2:邀请
        $data = RankService::getLanternData($storeId, $actId, $type);

        return Response::json($data);
    }

    /**
     * 获取倒计时
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountdownTime(Request $request) {

        $ttl = 2 * 60 * 60; //缓存24小时 单位秒
        $tag = 'countdown';
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $key = 'act:countdowntime:' . $storeId . ':' . $actId;

        $ret = [
            Constant::COUNTDOWN => -1,
            Constant::START_DATE => Carbon::tomorrow()->toDateTimeString(),
            Constant::END_DATE => Carbon::yesterday()->toDateTimeString(),
            Constant::IS_START => 0,//活动是否开始 1：是  0：否
            Constant::IS_END => 1,//活动是否结束 1：是  0：否
        ];
        if (empty($actId)) {
            $ret[Constant::COUNTDOWN] = 0;
            return Response::json($ret);
        }

//        $parameters = [
//            $key, $ttl, function () use ($storeId, $actId, &$ret) {
//                $actData = ActivityService::existsOrFirst($storeId, '', [Constant::DB_TABLE_PRIMARY => $actId], true, [Constant::DB_TABLE_END_AT, Constant::DB_TABLE_START_AT]);
//                if ($actData === null) {
//                    return $ret;
//                }
//
//                $endAt = data_get($actData, Constant::DB_TABLE_END_AT, null);
//                $startAt = data_get($actData, Constant::DB_TABLE_START_AT, null);
//
//                $ret[Constant::COUNTDOWN] = $endAt === null ? 365 * 24 * 60 * 60 : Carbon::parse($endAt)->timestamp;
//                $ret[Constant::END_DATE] = $endAt;
//                $ret[Constant::START_DATE] = $startAt;
//                return $ret;
//            }
//        ];
//
//        $_ret = ActivityService::handleCache($tag, FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'remember', $parameters, []));

        $actData = ActivityService::existsOrFirst($storeId, '', [Constant::DB_TABLE_PRIMARY => $actId], true, [Constant::DB_TABLE_END_AT, Constant::DB_TABLE_START_AT]);
        if ($actData !== null) {
            $endAt = data_get($actData, Constant::DB_TABLE_END_AT, null);
            $startAt = data_get($actData, Constant::DB_TABLE_START_AT, null);

            $ret[Constant::COUNTDOWN] = $endAt === null ? 365 * 24 * 60 * 60 : Carbon::parse($endAt)->timestamp;
            $ret[Constant::END_DATE] = $endAt;
            $ret[Constant::START_DATE] = $startAt;
        }

        $_ret = $ret;
        if($_ret[Constant::COUNTDOWN] == -1){//如果活动不存在，就直接返回
            $ret[Constant::COUNTDOWN] = 0;
            return Response::json($ret);
        }

        $countdown = ($_ret[Constant::COUNTDOWN] - (Carbon::now()->timestamp)) + 0;
        $_ret[Constant::COUNTDOWN] = $countdown > 0 ? $countdown : 0;

        $isStart = 1;//活动是否开始 1：是  0：否
        $isEnd = 0;//活动是否结束 1：是  0：否
        $nowTime = Carbon::now()->toDateTimeString();
        $startAt = data_get($_ret, Constant::START_DATE);
        $endAt = data_get($_ret, Constant::END_DATE);

        if ($startAt !== null && $startAt > $nowTime) {//如果活动未开始，就设置活动未开始
            $isStart = 0;
        }

        if ($endAt !== null && $nowTime > $endAt) {//如果活动已经结束，就设置活动已经结束
            $isEnd = 1;
        }

        $_ret[Constant::IS_START] = $isStart;
        $_ret[Constant::IS_END] = $isEnd;

        return Response::json($_ret);
    }

    /**
     * 获取活动数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityData(Request $request) {

        $ttl = 24 * 60 * 60; //缓存24小时 单位秒
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $actUnique = $request->input(Constant::DB_TABLE_ACT_UNIQUE, Constant::PARAMETER_STRING_DEFAULT); //活动唯一标识
        $key = 'act:data:' . $storeId . ':' . $actUnique;

        $parameters = [$key, $ttl, function () use($storeId, $actUnique) {
            $nowTime = Carbon::now()->toDateTimeString();
            $where = [
                Constant::DB_TABLE_ACT_UNIQUE => FunctionHelper::getUniqueId($actUnique),
                '{customizeWhere}' => [
                    [
                        Constant::METHOD_KEY => Constant::DB_EXECUTION_PLAN_WHERE,
                        Constant::PARAMETERS_KEY => function ($query) use($nowTime) {
                            $query->whereNull(Constant::DB_TABLE_START_AT)->orWhere(Constant::DB_TABLE_START_AT, '<=', $nowTime);
                        },
                    ],
                    [
                        Constant::METHOD_KEY => Constant::DB_EXECUTION_PLAN_WHERE,
                        Constant::PARAMETERS_KEY => function ($query) use($nowTime) {
                            $query->whereNull(Constant::DB_TABLE_END_AT)->orWhere(Constant::DB_TABLE_END_AT, '>=', $nowTime);
                        },
                    ],
                ]
            ];
            $order = [[Constant::DB_TABLE_UPDATED_AT, 'DESC']];
            $limit = 1;
            $actData = ActivityService::getActivityData($storeId, 0, [], [], false, false, $where, $order, $limit);

            if (empty($actData)) {
                data_set($where, '{customizeWhere}', [
                    [
                        Constant::METHOD_KEY => Constant::DB_EXECUTION_PLAN_WHERE,
                        Constant::PARAMETERS_KEY => function ($query) use($nowTime) {
                            $query->whereNull(Constant::DB_TABLE_START_AT)->orWhere(Constant::DB_TABLE_START_AT, '<=', $nowTime);
                        },
                    ]
                ]);
                $actData = ActivityService::getActivityData($storeId, 0, [], [], false, false, $where, $order, $limit);
            }

            return $actData;
        }];

        $actData = ActivityService::handleCache('activity', FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'remember', $parameters, []));

        return Response::json($actData, $actData ? 1 : 0);
    }

    /**
     * 中奖名单列表接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function awardList(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        if (empty($storeId)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $data = AwardUserService::awardList($storeId);

        return Response::json($data);
    }

    /**
     * 获取活动统计次数接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNums(Request $request) {

        $requestData = $request->all();

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $lotteryData = Factory::handle($storeId, $actId, 'getNums', [$requestData]);

        return Response::json($lotteryData);
    }

    /**
     * 社媒关注接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = Factory::handle($storeId, $actId, 'handleFollow', [$request->all()]);

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 参与活动
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $customerId  = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT);
        $data = Factory::handle($storeId, $actId, 'handle', [$storeId, $actId, $customerId, $request->all()]);

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 社媒关注接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function share(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = Factory::handle($storeId, $actId, 'handleShare', [$request->all()]);

        return Response::json(...Response::getResponseData($data));
    }

}
