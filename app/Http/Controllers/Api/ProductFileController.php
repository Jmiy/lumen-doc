<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2021/1/9 14:10
 */

namespace App\Http\Controllers\Api;

use App\Services\ProductFileCategoryService;
use App\Services\ProductFileService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class ProductFileController extends Controller {

    /**
     * 类目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileCategoryService::getCategories($storeId);
        return Response::json($data);
    }

    /**
     * 获取列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $data = ProductFileService::getListData($request->all());

        return Response::json($data);
    }

    /**
     * 获取下载记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileDownload(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileService::fileDownload($storeId, $request->all());

        return Response::json($data);
    }
}
