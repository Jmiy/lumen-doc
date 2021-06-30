<?php

namespace App\Http\Controllers\Admin;

use App\Services\RewardService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class RewardController extends Controller {

    /**
     * 礼品添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $businessType = $request->input(Constant::BUSINESS_TYPE, Constant::PARAMETER_STRING_DEFAULT);
        $rewardName = $request->input(Constant::DB_TABLE_NAME, Constant::PARAMETER_STRING_DEFAULT);
        if (empty($storeId) || empty($businessType) || empty($rewardName)) {
            return Response::json(Constant::PARAMETER_ARRAY_DEFAULT, -1, Constant::PARAMETER_STRING_DEFAULT);
        }

        $data = RewardService::add($storeId, $request->all());

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * 礼品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $data = RewardService::getList($request->all(), true, true, ['*']);
        return Response::json($data);
    }

    /**
     * 礼品编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);

        $data = RewardService::edit($storeId, $request->all());

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * 礼品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);

        $data = RewardService::info($storeId, $request->all());

        return Response::json($data);
    }

    /**
     * 礼品导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {
        $data = RewardService::export($request->all());
        return Response::json($data);
    }

    /**
     * 礼品状态编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRewardStatus(Request $request) {

        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商城id
        $rewardId = data_get($requestData, Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //礼品id
        $rewardStatus = data_get($requestData, Constant::REWARD_STATUS, Constant::WHETHER_YES_VALUE); //礼品状态

        $asins = data_get($requestData, Constant::DB_TABLE_ASIN, null); //删除的asin
        $data = RewardService::updateRewardStatus($storeId, $rewardId, $rewardStatus, $asins);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 礼品删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $ids = $request->input('ids', []);
        $data = RewardService::deleteRewards($storeId, $ids);
        return Response::json($data);
    }

}
