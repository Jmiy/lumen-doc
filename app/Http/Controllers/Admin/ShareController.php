<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExcelService;
use App\Services\ShareService;

class ShareController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();
        $data = ShareService::getListData($requestData);

        return Response::json($data);
    }

    /**
     * 列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $header = [
            '邮箱' => 'account',
            '国家' => 'country',
            '分享链接' => 'content',
            '审核状态' => 'audit_status',
            '分享时间' => 'created_at',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['shares.id']
            ],
        ];

        $request->offsetSet('source', 'admin');
        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = ShareService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getListData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request) {
        $requestData = $request->all();
        $id = $requestData['id'] ?? 0;
        if (empty($id)) {
            return Response::json([], 10015, '数据不存在');
        }

        $auditStatus = $request->input('audit_status', 0);
        $remarks = $request->input('remarks', '');
        $storeId = $request->input('store_id', 0);
        $value = $request->input('value', 0);
        $addType = $request->input('add_type', 0);
        $action = $request->input('action', '');

        $ret = ShareService::audit($id, $auditStatus, $remarks, $storeId, $value, $addType, $action);

        if ($ret['code'] != 1) {//如果添加失败，就提示用户
            return Response::json([], 10015, $ret['msg']);
        }

        return Response::json();
    }

}
