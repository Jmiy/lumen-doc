<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2021/1/9 14:10
 */

namespace App\Http\Controllers\Admin;

use App\Services\ProductFileService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class ProductFileController extends Controller {

    /**
     * 添加产品文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileService::addFile($storeId, $requestData);
        return Response::json(data_get($data, Constant::RESPONSE_DATA_KEY), data_get($data, Constant::RESPONSE_CODE_KEY), data_get($data, Constant::RESPONSE_MSG_KEY));
    }

    /**
     * 编辑产品文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileService::edit($storeId, $requestData);
        return Response::json(data_get($data, Constant::RESPONSE_DATA_KEY), data_get($data, Constant::RESPONSE_CODE_KEY), data_get($data, Constant::RESPONSE_MSG_KEY));
    }

    /**
     * 删除产品文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileService::deleteFile($storeId, $requestData);
        return Response::json(data_get($data, Constant::RESPONSE_DATA_KEY), data_get($data, Constant::RESPONSE_CODE_KEY), data_get($data, Constant::RESPONSE_MSG_KEY));
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
     * 导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'is_export', 1);
        $data = ProductFileService::export($requestData);
        return Response::json($data);
    }

    /**
     * 导入
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request) {
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);

        $data = ProductFileService::importProductFiles($storeId, $requestData);
        return Response::json(data_get($data, Constant::RESPONSE_DATA_KEY), data_get($data, Constant::RESPONSE_CODE_KEY), data_get($data, Constant::RESPONSE_MSG_KEY));
    }
}
