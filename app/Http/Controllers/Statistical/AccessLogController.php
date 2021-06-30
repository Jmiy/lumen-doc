<?php

namespace App\Http\Controllers\Statistical;

use App\Util\Constant;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Auth\AuthService;

class AccessLogController extends Controller {

    public function add(Request $request) {
        return Response::json();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(Request $request) {
        $requestData = $request->all();
        $actionType = data_get($requestData, Constant::ACTION_TYPE, Constant::PARAMETER_INT_DEFAULT); //操作类型:1-4登陆相关,5-20预留,21活动相关,其他值待定义
        $subType = data_get($requestData, Constant::SUB_TYPE, Constant::PARAMETER_INT_DEFAULT);

        $data = Response::getDefaultResponseData(1);
        if ($actionType < 5) {//如果是登录，就处理登录
            $data = AuthService::login($requestData);
        }

        return Response::json(...Response::getResponseData($data));
    }

}
