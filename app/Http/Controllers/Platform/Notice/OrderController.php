<?php

namespace App\Http\Controllers\Platform\Notice;

use App\Http\Controllers\Api\Controller;
use App\Services\Platform\OrderService;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;

class OrderController extends Controller {

    /**
     * 订单创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeCreate(Request $request) {
        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30015, false);
        $ret = OrderService::handle($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 订单发货
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeDelivery(Request $request) {

        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30018, false);
        $ret = OrderService::noticeDelivery($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 订单付款
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticePayment(Request $request) {
        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30019, false);
        $ret = OrderService::noticePayment($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 订单删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeDelete(Request $request) {
        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30020, false);
        $ret = OrderService::noticeDelete($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 订单取消
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeCancel(Request $request) {

        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30021, false);
        $ret = OrderService::noticeCancel($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 订单更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeUpdate(Request $request) {

        $data = $request->all(); //请求参数
        data_set($data, Constant::CUSTOMER_SOUTCE, 30016, false);
        $ret = OrderService::noticeUpdate($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

}
