<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerGuide;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\GuideService;
use App\Util\Constant;

class GuideController extends Controller {

    /**
     * 添加客户次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTimes(Request $request) {
        $requestData = $request->all();
        $customer = $request->user();
        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customer->customer_id;
        $storeId = $requestData[Constant::DB_TABLE_STORE_ID];
        $data = CustomerGuide::select(Constant::FREQUENCY)
                ->where([Constant::DB_TABLE_STORE_ID => $storeId, Constant::DB_TABLE_CUSTOMER_PRIMARY => $customer->customer_id])
                ->exists();
        if (!$data) {
            GuideService::insert($requestData); //第一次就新增
        } else {
            $this->updTimes($request); //第二次就更新
        }
        return Response::json();
    }

    /**
     * 更新客户次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updTimes(Request $request) {
        $requestData = $request->all();
        $customer = $request->user();
        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customer->customer_id;
        GuideService::update($requestData);
        return Response::json();
    }

    /**
     * 查询客户的次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTimes(Request $request) {
        $requestData = $request->all();
        $customer = $request->user();
        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customer->customer_id;
        $storeId = $requestData[Constant::DB_TABLE_STORE_ID];
        $data = CustomerGuide::select(Constant::FREQUENCY)
                ->where([Constant::DB_TABLE_STORE_ID => $storeId, Constant::DB_TABLE_CUSTOMER_PRIMARY => $customer->customer_id])
                ->first();
        if (empty($data)) {
            $data = [Constant::FREQUENCY => 0];
        }
        return Response::json($data);
    }

}
