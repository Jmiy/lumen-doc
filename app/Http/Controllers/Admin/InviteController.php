<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExcelService;
use App\Services\InviteService;
use App\Util\Constant;

class InviteController extends Controller {

    /**
     * 邀请会员列表查询
     * */
    public function index(Request $request) {

        $requestData = $request->all();
        $data = InviteService::getListData($requestData);

        return Response::json($data);
    }

    /**
     * 邀请会员列表导出
     * @return string
     */
    public function export(Request $request) {

        $header = [
            '邀请邮箱' => Constant::DB_TABLE_ACCOUNT,
            '邀请码' => Constant::DB_TABLE_INVITE_CODE,
            '被邀请者邮箱' => Constant::DB_TABLE_INVITE_ACCOUNT,
            '被邀请者用户名' => 'use_name',
            '被邀请者国家' => Constant::DB_TABLE_COUNTRY,
            '被邀请者邮箱IP' => Constant::DB_TABLE_IP,
            '被邀请者来源' => Constant::DB_TABLE_SOURCE . '_show',
            '被邀请注册时间' => Constant::DB_TABLE_OLD_CREATED_AT,
            '活动名称' => 'act_name',
            '备注' => Constant::DB_TABLE_REMARKS,
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['ih.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = InviteService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 邀请关系列表编辑备注
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $requestData = $request->all();
        $storeId = data_get($requestData, 'store_id', 0);
        $Id = data_get($requestData, 'id', 0);
        if (empty($Id)) {
            return Response::json([], 10005, 'id not exists');
        }

        $data = InviteService::getInviteEdit($storeId, $Id, $requestData);

        return Response::json($data);
    }
}
