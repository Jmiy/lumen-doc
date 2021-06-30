<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\EmailService;
use App\Services\ExcelService;

class EmailController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        $data = EmailService::getListData($request->all());
        return Response::json($data);
    }

    /**
     * 列表导出
     * @return string
     */
    public function export(Request $request) {

        $header = [
            '邮箱' => 'to_email',
            '国家' => 'country',
            '邮件类型' => 'type',
            '邮件内容' => 'extinfo',
            '邮件状态' => 'status',
            '邮件标识' => 'remark',
            '邮件发送时间' => 'ctime',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = EmailService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getListData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

}
