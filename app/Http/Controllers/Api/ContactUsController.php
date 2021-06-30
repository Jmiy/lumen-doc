<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\ContactUsService;
use App\Util\Response;
use App\Util\Constant;

class ContactUsController extends Controller {

    /**
     * 添加联系我们
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {

        $rs = ContactUsService::add($this->storeId, $request->all());

        $parameters = Response::getResponseData($rs);

        return Response::json(...$parameters);
    }

}
