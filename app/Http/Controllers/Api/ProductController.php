<?php

namespace App\Http\Controllers\Api;

use App\Util\Constant;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ProductService;

class ProductController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $request->offsetSet('source', 'api');
        $select = [
            'p.' . Constant::DB_TABLE_UNIQUE_ID,
            'p.' . Constant::DB_TABLE_PRODUCT_UNIQUE_ID,
            'p.' . Constant::DB_TABLE_PRIMARY,
            'p.' . Constant::STORE_PRODUCT_ID,
            'p.' . Constant::DB_TABLE_CREDIT,
            'p.' . Constant::DB_TABLE_QTY,
            'p.' . Constant::EXCHANGED_NUMS,
            'p.' . Constant::EXPIRE_TIME,
            'p.url'
        ];
        $data = ProductService::getL($request->all(), true, true, $select);
        return Response::json($data);
    }

    /**
     * 详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request) {
        $id = $request->input('id', Constant::PARAMETER_INT_DEFAULT);
        $data = ProductService::info($this->storeId, 0, $id);
        return Response::json($data);
    }

    /**
     * 兑换列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exchangeList(Request $request) {
        $actUnique = $request->input(Constant::DB_TABLE_ACT_UNIQUE, Constant::PARAMETER_STRING_DEFAULT);
        $data = ProductService::exchangeList($this->storeId, $actUnique);
        return Response::json($data);
    }

    /**
     * 活动首页pointStore产品接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pointProducts(Request $request) {
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = ProductService::pointProducts($this->storeId, $actId);
        return Response::json($data);
    }

}
