<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Util\Constant;
use App\Util\Cdn\CdnManager;
use App\Services\ActivityPrizeCustomerService;
use App\Util\Response;

class ActivityPrizeCustomerController extends Controller {

    public $showKey = '_show';
    public $memoryLimit = 'memory_limit';

    /**
     * 导入
     * @return string
     */
    public function import(Request $request) {

        ini_set($this->memoryLimit, '1024M');

        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT); //活动形式 1:抽奖 2:邀请助力 3:众测 4:投票 5:排名 6:其他
        $storeId = $request->input($this->storeIdKey, 0);
        $user = $request->input(Constant::DB_TABLE_OPERATOR, Constant::PARAMETER_STRING_DEFAULT);

        if (empty($actId) || empty($storeId)) {
            return Response::json([], 0, '非法参数');
        }

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {

            $parameters = Response::getResponseData($fileData);

            return Response::json(...$parameters);
        }

        $fileFullPath = data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, '');
        $data = ActivityPrizeCustomerService::import($storeId, $actId, $fileFullPath, $user, $request->all());

        return Response::json(...Response::getResponseData($data));
    }

}
