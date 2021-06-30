<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActivityApplyInfoService;

class ActivityApplyInfoController extends Controller {

    /**
     * 活动申请资料详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $request->input('store_id', 0); //商城id
        $actId = $request->input('act_id', 0); //活动id
        $customerId = $request->input('customer_id', ''); //会员id

        $data = ActivityApplyInfoService::info($storeId, $actId, $customerId, true);

        return Response::json($data);
    }

}
