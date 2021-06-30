<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActivityAddressService;
use App\Services\ActivityWinningService;
use App\Util\Constant;

class ActivityAddressController extends Controller {

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {

        $storeId = $request->input('store_id', 0); //商城id
        $actId = $request->input('act_id', 0); //活动id
        $account = $request->input('account', ''); //会员账号
        $customerId = $request->input('customer_id', 0); //会员id
        $activityWinningId = $request->input(Constant::ACTIVITY_WINNING_ID, 0); //申请id 或者 中奖id

        ActivityAddressService::add($storeId, $actId, $customerId, $account, $activityWinningId, $request->all());

        return Response::json([], 1, '');
    }

    /**
     * 获取收件地址详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $request->input('store_id', 0); //商城id
        $activityWinningId = $request->input(Constant::ACTIVITY_WINNING_ID, 0); //会员id

        $where = [
            Constant::ACTIVITY_WINNING_ID => $activityWinningId,
        ];

        $data = ActivityAddressService::exists($storeId, '', $where, true);

        return Response::json($data);
    }

}
