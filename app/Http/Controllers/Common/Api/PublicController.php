<?php

namespace App\Http\Controllers\Common\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Util\Cdn\CdnManager;
use App\Util\Response;
use App\Util\Constant;

class PublicController extends Controller {

    /**
     * 上传文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request) {

        $data = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '', 'AwsS3Cdn', false, false, '', 1, $request->all()); ///upload/img/  Constant::UPLOAD_FILE_KEY
        data_set($data, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, '');
        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

}
