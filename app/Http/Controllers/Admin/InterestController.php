<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExcelService;
use App\Services\InviteService;

class InterestController extends Controller {

    /**
     * 列表
     * */
    public function index(Request $request) {

        $requestData = $request->all();
        $data = InviteService::getListData($requestData);

        return Response::json($data);
    }

    /**
     * 列表导出
     * @return string
     */
    public function export(Request $request) {

        $header = [
            '邮箱' => 'account',
            '国家' => 'country',
            '邮箱ip' => 'ip',
            '邀请码' => 'invite_code',
            '被邀请者邮箱' => 'invite_account',
            '邀请时间' => 'created_at',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['ih.id']
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = InviteService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getListData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

}
