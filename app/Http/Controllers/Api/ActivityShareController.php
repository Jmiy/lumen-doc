<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActivityShareService;

class ActivityShareController extends Controller {

    /**
     * 活动分享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request) {

        $socialMedia = $request->input('social_media', ''); //社媒平台 FB TW
        $fromUrl = $request->input('url', $request->headers->get('Referer') ?? 'no');

        ActivityShareService::handle($this->storeId, $this->actId, $this->customerId, $this->account, $socialMedia, $fromUrl, $request->all());

        return Response::json([], 1, '');
    }

    /**
     * 分享得积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareHandle(Request $request) {

        $socialMedia = $request->input('social_media', ''); //社媒平台 FB TW
        $fromUrl = $request->input('url', $request->headers->get('Referer') ?? 'no');

        ActivityShareService::share($this->storeId, $this->actId, $this->customerId, $this->account, $socialMedia, $fromUrl, $request->all());

        return Response::json([], 1, '');
    }
}
