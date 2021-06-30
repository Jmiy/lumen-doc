<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ShareService;
use App\Services\DictStoreService;

class ShareController extends Controller {

    /**
     * 添加分享
     * @param Request $request
     * @return type
     */
    public function add(Request $request) {//ServerRequestInterface $request
        $requestData = $request->all();
        $customer = $request->user();
        $requestData['customer_id'] = $customer->customer_id;

        $id = ShareService::insert($requestData);
        if ($id !== true) {
            return Response::json([], $id, 'Submit sharing successfully.  Please do not  repeat submit');
        }

        return Response::json();
    }

    /**
     * 分享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        $storeId = $request->input('store_id', 0);
        $customer = $request->user();
        $customerId = $customer->customer_id;

        /*         * *********更新邀请汇总数据************ */
        $score = DictStoreService::getByTypeAndKey($storeId, 'share', 'rank_score', true);
        $rs = ShareService::handle($storeId, $customerId, 0, 1, $score); // 榜单类型 1:分享 2:邀请

        return Response::json($rs['data'], $rs['code'], $rs['msg']);
    }

}
