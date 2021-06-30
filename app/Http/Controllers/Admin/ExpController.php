<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExpService;
use App\Services\ExcelService;
use App\Util\FunctionHelper;
use App\Util\Constant;

class ExpController extends Controller {

    public function index(Request $request) {
        $data = ExpService::getListData($request->all());
        return Response::json($data);
    }

    /**
     * 编辑
     * @return string
     */
    public function edit(Request $request) {

        $customerId = $request->input('customer_id', 0);

        $expansionData = [
            $this->storeIdKey => $request->input($this->storeIdKey, 0),
            $this->remarkKey => $request->input($this->remarkKey, 0),
        ];
        $data = FunctionHelper::getHistoryData([
                    'customer_id' => $customerId,
                    Constant::DB_TABLE_VALUE => $request->input(Constant::DB_TABLE_VALUE, 0),
                    'add_type' => $request->input('add_type', 0),
                    $this->actionKey => $request->input($this->actionKey, 0),
                    'ext_id' => $customerId,
                    'ext_type' => 'customer',
                        ], $expansionData);
        $ret = ExpService::handle($data); //记录积分流水

        if (empty($ret)) {//如果添加失败，就提示用户
            return Response::json([], 10015, '添加经验失败');
        }

        return Response::json();
    }

    /**
     * 列表导出
     * @return string
     */
    public function export(Request $request) {
        $header = [
            '邮箱' => 'account',
            '经验明细' => Constant::DB_TABLE_VALUE,
            '经验明细方式' => $this->actionKey,
            '备注' => $this->remarkKey,
            '经验变动时间' => 'ctime',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = ExpService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getListData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

}
