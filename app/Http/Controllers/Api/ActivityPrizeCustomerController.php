<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityPrizeCustomerService;
use App\Util\Response;
use Illuminate\Http\Request;

class ActivityPrizeCustomerController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $data = ActivityPrizeCustomerService::getItemData($this->storeId, $this->actId, $this->page, $this->pageSize);
        return Response::json($data);
    }

}
