<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\ActivityService;

class BaseActController extends Controller {

    public $activityData;

    /**
     * Create a new controller instance.
     * @param Request $request
     * @return void
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        $storeId = $request->input('store_id', 0);
        $data = ActivityService::getValidData($storeId, false, 1, 1);

        $this->activityData = $data['data'] ? current($data['data']) : [];
    }

}
