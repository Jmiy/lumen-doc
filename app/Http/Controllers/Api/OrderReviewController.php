<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Util\Constant;
use App\Services\OrderReviewService;

class OrderReviewController extends Controller {

    /**
     * 提交订单索评
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function input(Request $request) {

        $orderno = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);

        $data = OrderReviewService::input($this->storeId, $orderno, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 获取订单review列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReviewList(Request $request) {

        $data = OrderReviewService::getReviewList($request->all());

        return Response::json($data);
    }

    /**
     * 提交订单评星
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function playStar(Request $request) {

        $orderno = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);

        $data = OrderReviewService::playStar($this->storeId, $orderno, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 提交订单索评
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderReview(Request $request) {

        $orderno = $request->input(Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);

        $data = OrderReviewService::orderReview($this->storeId, $orderno, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 获取订单review列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReviewListV2(Request $request) {

        $data = OrderReviewService::getReviewListV2($request->all());

        return Response::json($data);
    }
}
