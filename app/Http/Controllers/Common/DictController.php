<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\DictService;
use App\Services\DictStoreService;

class DictController extends Controller {

    /**
     * 获取指定下拉数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request) {
        $type = $request->input('type', '');
        $data = DictService::getListByType($type);
        return Response::json($data);
    }

    /**
     * 获取商城指定下拉数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDictSelect(Request $request) {

        $storeId = $request->input('store_id', 0);
        $type = $request->input('type', ''); //template_type

        $keyField = 'conf_key';
        $valueField = 'conf_value';
        $distKeyField = 'dict_key';
        $distValueField = 'dict_value';
        $data = DictService::getDistData($storeId, $type, $keyField, $valueField, $distKeyField, $distValueField);

        return Response::json($data);
    }

}
