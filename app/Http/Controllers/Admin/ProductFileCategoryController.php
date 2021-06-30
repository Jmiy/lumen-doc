<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2021/1/9 15:57
 */

namespace App\Http\Controllers\Admin;

use App\Services\ProductFileCategoryService;
use App\Services\ProductFileService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class ProductFileCategoryController extends Controller {

    /**
     * 添加类目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $oneCategoryName = data_get($requestData, 'one_category_name', Constant::PARAMETER_STRING_DEFAULT);
        $twoCategoryName = data_get($requestData, 'two_category_name', Constant::PARAMETER_STRING_DEFAULT);
        $threeCategoryName = data_get($requestData, 'three_category_name', Constant::PARAMETER_STRING_DEFAULT);

        $data = ProductFileCategoryService::addCategory($storeId, $oneCategoryName, $twoCategoryName, $threeCategoryName);
        return Response::json($data);
    }

    /**
     * 获取列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $oneCategoryName = data_get($requestData, 'one_category_name', Constant::PARAMETER_STRING_DEFAULT);
        $twoCategoryName = data_get($requestData, 'two_category_name', Constant::PARAMETER_STRING_DEFAULT);
        $threeCategoryName = data_get($requestData, 'three_category_name', Constant::PARAMETER_STRING_DEFAULT);

        $data = ProductFileCategoryService::getCategoriesAdmin($storeId, $oneCategoryName, $twoCategoryName, $threeCategoryName);

        return Response::json($data);
    }

}
