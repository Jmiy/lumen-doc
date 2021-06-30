<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\InviteService;
use App\Util\Constant;

class InviteController extends Controller {

    /**
     * 获取邀请码数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInviteCode(Request $request) {

        $customer = $request->user();
        $customerId = data_get($customer, 'customer_id', Constant::PARAMETER_INT_DEFAULT);
        $requestData = $request->all();

        $data = InviteService::getInviteCodeData($customerId, $requestData);

        return Response::json([Constant::DB_TABLE_INVITE_CODE => data_get($data, Constant::DB_TABLE_INVITE_CODE, Constant::PARAMETER_STRING_DEFAULT)]);
    }

    /**
     * 获取邀请码的历史邀请记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInviteHistory(Request $request) {

        $requestData = $request->all();

        $data = InviteService::getInviteHistoryData($requestData);

        return Response::json($data);
    }
}
