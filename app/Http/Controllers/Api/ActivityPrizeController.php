<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityPrizeService;
use App\Services\ActivityService;
use App\Util\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityPrizeController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $storeId = $request->input('store_id', 0);
        $actId = $request->input('act_id', ActivityService::getValidActIds($storeId)); //活动id
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);

        $data = ActivityPrizeService::getItemData($storeId, $actId, $page, $pageSize);

        return Response::json($data);
    }


}
