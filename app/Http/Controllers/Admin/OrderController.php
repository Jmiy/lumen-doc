<?php

/**
 * Created by Patazon.
 * @desc   : 后台订单管理
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/7/6 9:57
 */

namespace App\Http\Controllers\Admin;

use App\Services\Platform\Admin\OrderService;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;

class OrderController extends Controller {

    /**
     * 订单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $requestData = $request->all();

        data_set($requestData, Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY, false); //订单平台 Amazon：亚马逊  Shopify：Shopify
        $data = OrderService::orderList($requestData);

        return Response::json($data);
    }

    /**
     * 订单数据导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {
        $requestData = $request->all();

        data_set($requestData, Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY, false); //订单平台 Amazon：亚马逊  Shopify：Shopify
        $data = OrderService::exportOrders($requestData);

        return Response::json($data);
    }

}
