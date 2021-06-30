<?php

namespace App\Http\Controllers\Redman\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Redman\InfluencerService;

class InfluencerController extends Controller {

    /**
     * 红人系统Influencer表单提交接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function formAdd(Request $request) {
        $requestData = $request->all();
        $platform = data_get($requestData, 'platform', 0);
        $username = data_get($requestData, 'username', '');
        $email = data_get($requestData, 'email', '');
        $country = data_get($requestData, 'country', '');
        $blogLink = data_get($requestData, 'social_link', '');
        $blogDescription = data_get($requestData, 'social_description', '');
        $otherSocial = data_get($requestData, 'other_social', '');
        InfluencerService::add($platform, $username, $email, $country, $blogLink, $blogDescription, $otherSocial);
        return Response::json([]);
    }

}
