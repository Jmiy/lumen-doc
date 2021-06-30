<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Store\StoreService;
use App\Models\Store;

class StoreController extends Controller {

    public function actionList(Request $request) {
        $list = StoreService::getActionList();
        return Response::json($list);
    }

    /**
     * 商城列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStore(Request $request) {
        $data = Store::select(['name', 'id'])->get();
        return Response::json($data);
    }

}
