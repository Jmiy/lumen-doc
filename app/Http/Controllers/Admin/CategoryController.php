<?php

/**
 * Created by Patazon.
 * @desc   : 后台订单管理
 * @author : Jmiy
 * @email  : Jmiy_cen@patazon.net
 * @date   : 2020/10/14 16:48
 */

namespace App\Http\Controllers\Admin;

use App\Services\Platform\CategoryService;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;

class CategoryController extends Controller {

    /**
     * 类目下拉列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request) {
        
        $requestData = $request->all();
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, 0);
        $platform = data_get($requestData, Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_AMAZON);
        $parameters = [];
        $data = CategoryService::getCategory($storeId, $platform, $parameters);

        return Response::json($data);
    }

}
