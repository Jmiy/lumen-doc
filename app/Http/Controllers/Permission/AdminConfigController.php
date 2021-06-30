<?php

namespace App\Http\Controllers\Permission;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Util\Constant;
use App\Services\Permission\AdminConfigService;
use App\Services\Permission\AdminUserConfigService;

class AdminConfigController extends Controller {

    /**
     * 用户基本信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminConfig(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $route = $request->input('route', '');
        $user = $request->user('apiAdmin');

        if(empty($route)){
            return Response::json([]);
        }

        $config = AdminConfigService::getAdminConfig($storeId, $route,data_get($user,Constant::DB_TABLE_PRIMARY));

        return Response::json($config);
    }

    /**
     * 用户基本信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userConfig(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $route = $request->input('route', '');
        $user = $request->user('apiAdmin');

        if (empty($route)) {
            return Response::json([]);
        }

        AdminUserConfigService::userConfig($storeId, $route, data_get($user, Constant::DB_TABLE_PRIMARY), $request->all());

        return Response::json();
    }

}
