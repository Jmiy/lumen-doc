<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\CreditService;
use App\Models\CustomerInfo;

class CreditController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $request->offsetSet('source', 'api');
        $data = CreditService::getListData($request->all());

        return Response::json($data);
    }

    /**
     * 会员积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $customer = $request->user();
        $customerId = 0;
        if ($customer) {
            $customerId = $customer->customer_id;
        }

        $credit = CustomerInfo::where('customer_id', $customerId)->value('credit');

        return Response::json(['credit' => $credit ? $credit : 0]);
    }

}
