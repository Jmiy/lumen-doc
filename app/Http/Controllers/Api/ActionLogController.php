<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActionLogService;
use App\Util\Constant;

class ActionLogController extends BaseActController {

    /**
     * 排行榜
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request) {

        $requestData = $request->all();
        $data = ActionLogService::handle($this->storeId, $this->customerId, $this->actId, data_get($requestData, Constant::DB_TABLE_TYPE, Constant::PARAMETER_INT_DEFAULT), $requestData);
        $parameters = Response::getResponseData($data);
        return Response::json(...$parameters);
    }

    /**
     * 关注
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request) {
        $data = ActionLogService::follow($this->storeId, $this->customerId, $this->actId, 5, $request->all());
        return Response::json(...Response::getResponseData($data));
    }

}
