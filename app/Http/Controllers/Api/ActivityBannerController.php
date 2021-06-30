<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActivityBannerService;

class ActivityBannerController extends Controller {

    /**
     * 活动banner列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $storeId = $request->input('store_id', 0);
        $actId = $request->input('act_id', 0); //活动id 
        $name = $request->input('name', '');

        $data = ActivityBannerService::getItemData($name, $storeId, $actId);
        unset($data['pagination']);
        return Response::json($data, 1, 'ok', false);
    }

}
