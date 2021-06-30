<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityTaskService;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;

class ActivityTaskController extends Controller {

    /**
     * 活动分享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function share(Request $request) {

        $socialMedia = $request->input(Constant::SOCIAL_MEDIA, '');
        $url = $request->input('share_url', $request->headers->get('Referer') ?? 'no');

        $data = ActivityTaskService::share($this->storeId, $this->actId, $this->customerId, $this->account, $socialMedia, $url, $request->all());

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * url填写
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fillInUrl(Request $request) {

        //$socialMedia = $request->input(Constant::SOCIAL_MEDIA, '');
        //$url = $request->input('url', '');

        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $actId = $request->input($this->actIdKey, Constant::PARAMETER_INT_DEFAULT);
        $customerId = $request->input($this->customerPrimaryKey, Constant::PARAMETER_INT_DEFAULT);
        $key = $request->input('key', Constant::PARAMETER_INT_DEFAULT);
        $word = $request->input('word', []);

        //$data = ActivityTaskService::profileUrl($this->storeId, $this->actId, $this->customerId, $this->account, $socialMedia, $url, $request->all());

        $data = ActivityTaskService::input($storeId, $actId, $customerId, $key, $word);

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * vipClub 点击
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vipClub(Request $request) {

        $socialMedia = $request->input(Constant::SOCIAL_MEDIA, '');
        $url = $request->input('url', '');

        $data = ActivityTaskService::vipClub($this->storeId, $this->actId, $this->customerId, $this->account, $socialMedia, $url, $request->all());

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * 任务完成情况及任务数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function taskStatusAndInfos(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0);
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, '');
        $requestData = $request->all();

        $data = ActivityTaskService::taskStatusAndInfos($storeId, $actId, $customerId, 'ActivityProduct', $requestData);

        return Response::json($data);
    }
}
