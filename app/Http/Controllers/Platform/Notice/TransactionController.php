<?php

namespace App\Http\Controllers\Platform\Notice;

use App\Http\Controllers\Api\Controller;
use App\Services\Platform\TransactionService;
use App\Util\Response;
use Illuminate\Http\Request;

class TransactionController extends Controller {

    /**
     * 交易创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeCreate(Request $request) {

        $data = $request->all(); //请求参数
        $ret = TransactionService::noticeCreate($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

}
