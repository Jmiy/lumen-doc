<?php

namespace App\Http\Controllers\Api;

use App\Util\FunctionHelper;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Util\Constant;
use App\Services\RewardService;

class RewardController extends Controller {

    /**
     * 获取订单索评奖励
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderReviewReward(Request $request) {

        $orderno = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);

        $data = RewardService::getOrderReviewReward($this->storeId, $orderno);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 获取订单索评奖励
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderReviewRewardV2(Request $request) {

        $orderno = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);

        $data = RewardService::getOrderReviewRewardV2($this->storeId, $this->customerId, $orderno, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }
}
