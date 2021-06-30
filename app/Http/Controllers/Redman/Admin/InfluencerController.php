<?php

namespace App\Http\Controllers\Redman\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExcelService;
use App\Services\Redman\InfluencerService;

class InfluencerController extends Controller {

    /**
     * 红人系统Influencer表单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $requestData = $request->all();
        $data = InfluencerService::getDataList($requestData);
        return Response::json($data);
    }

    /**
     * 红人系统Influencer表单列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $header = [
            '平台' => 'platform',
            '用户名' => 'username',
            '邮箱' => 'email',
            '国家' => 'country',
            '社媒链接' => 'social_link',
            '社媒描述' => 'social_description',
            '其他社媒' => 'other_social',
            'IP' => 'ip',
            '填写时间' => 'created_at',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $requestData['page_size'] = 20000;
        $requestData['page'] = 1;

        $service = InfluencerService::getNamespaceClass();
        $method = 'getDataList';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getDataList';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

}
