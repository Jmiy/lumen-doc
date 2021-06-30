<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\SubcribeService;
use App\Services\ExcelService;

class PubController extends Controller {

    /**
     * 订阅列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();

        $data = SubcribeService::getItemData($requestData);

        return Response::json($data);
    }

    /**
     * 订阅列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $header = [
            '邮箱' => 'email',
            '国家' => 'country',
            'ip' => 'ip',
            '订阅方式' => 'remark',
            '订阅时间' => 'ctime',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = SubcribeService::getNamespaceClass();
        $method = 'getItemData';
        $select = ['id', 'email', 'country', 'ip', 'remark', 'ctime'];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getItemData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000


        return Response::json(['url' => $file]);
    }

}
