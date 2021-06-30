<?php

namespace App\Http\Controllers\Platform\Notice;

use App\Http\Controllers\Api\Controller;
use App\Services\Platform\FulfillmentService;
use App\Util\Response;
use Illuminate\Http\Request;

class FulfillmentController extends Controller {

    /**
     * 物流创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeCreate(Request $request) {

        $data = $request->all(); //请求参数
        //通知买家已经发货
        $ret = FulfillmentService::handle($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

    /**
     * 物流更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeUpdate(Request $request) {

        $data = $request->all(); //请求参数
        //通知买家物流更新
        $ret = FulfillmentService::handle($this->storeId, $this->platform, $data);

        return Response::json($ret);
    }

}
