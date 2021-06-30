<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\LeaveMessageService;
use App\Util\Response;

class LeaveMessageController extends Controller {

    /**
     * 添加留言
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {

        $rs = LeaveMessageService::add($this->storeId, $request->all());

        $parameters = Response::getResponseData($rs);

        return Response::json(...$parameters);
    }

    /**
     * 留言列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $rs = LeaveMessageService::getListData($request->all());

        return Response::json($rs);
    }

}
