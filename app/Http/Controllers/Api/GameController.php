<?php

namespace App\Http\Controllers\Api;

use App\Services\GameService;
use App\Services\KeyWordLogService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class GameController extends Controller {

    /**
     * 预生成图片结果数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preGenerationGameResult(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $requestData = $request->all();

        $data = GameService::preGeneration($storeId, $actId, $customerId, $requestData);
        return Response::json($data);
    }

    /**
     * 游戏接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function playGame(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $columnIdx = $request->input('column_idx', Constant::PARAMETER_INT_DEFAULT);
        $columnRet = $request->input('column_ret', []);
        $requestData = $request->all();

        $data = GameService::playGame($storeId, $actId, $customerId, $columnIdx, $columnRet, $requestData);
        return Response::json($data);
    }

    /**
     * 口令提交
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function keyword(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $key = $request->input('key', Constant::PARAMETER_INT_DEFAULT);
        $word = $request->input('word', []);

        $data = KeyWordLogService::input($storeId, $actId, $customerId, $key, $word);
        $parameters = Response::getResponseData($data);
        return Response::json(...$parameters);
    }

    /**
     * 获取图片组配置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);

        $data = GameService::getImages($storeId, $actId);
        return Response::json($data);
    }

    /**
     * 获取口令,激活邮件状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function taskFlag(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);

        $data = GameService::taskFlag($storeId, $actId, $customerId);
        return Response::json($data);
    }
}
