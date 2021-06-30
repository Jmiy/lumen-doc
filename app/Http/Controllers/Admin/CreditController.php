<?php

namespace App\Http\Controllers\Admin;

use App\Util\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\CreditService;
use App\Services\ExcelService;
use App\Util\FunctionHelper;
use App\Services\ExpService;

class CreditController extends Controller {

    public function index(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'source', 'admin');
        $data = CreditService::getListData($requestData);
        return Response::json($data);
    }

    /**
     * 积分编辑
     * @author harry
     * @return string
     */
    public function edit(Request $request) {


        $customerId = $request->input('customer_id', 0);

        $storeId = $request->input($this->storeIdKey, 0);
        $addType = $request->input(Constant::DB_TABLE_ADD_TYPE, 0);
        $expansionData = [
            $this->storeIdKey => $storeId,
            $this->remarkKey => $request->input($this->remarkKey, 0),
        ];
        $data = FunctionHelper::getHistoryData([
                    'customer_id' => $customerId,
                    'value' => $request->input('value', 0),
                    Constant::DB_TABLE_ADD_TYPE => $request->input(Constant::DB_TABLE_ADD_TYPE, 0),
                    $this->actionKey => $request->input($this->actionKey, 0),
                    'ext_id' => $customerId,
                    'ext_type' => 'customer',
                        ], $expansionData);
        $ret = CreditService::handle($data); //记录积分流水

        if ($ret['code'] != 1) {//如果添加失败，就提示用户
            return Response::json([], 10015, $ret['msg']);
        }

        if ($storeId == 8 && $addType == 1) {//如果是ilitom，并且是添加积分，就同步添加经验，触发等级更新
            ExpService::handle($data); //记录经验流水
        }

        return Response::json();
    }

    /**
     * 列表导出
     * @return string
     */
    public function export(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'source', 'admin');

        $header = [
            '邮箱' => Constant::DB_TABLE_ACCOUNT,
            '用户名' => Constant::DB_TABLE_NAME,
            '用户注册ip' => Constant::DB_TABLE_IP,
            '用户国家' => Constant::DB_TABLE_COUNTRY,
            '积分明细' => Constant::DB_TABLE_VALUE,
            '积分明细方式' => $this->actionKey,
            'distinctField' => [
                'primaryKey' => Constant::DB_TABLE_PRIMARY,
                'primaryValueKey' => Constant::DB_TABLE_PRIMARY,
                'select' => ['cl.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID
        if ($storeId == 1) {
            $header = Arr::collapse([$header, [
                            "Email(收件)" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "email",
                            "UserName(收件)" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "name",
                            "Country" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "country",
                            "City" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "city",
                            "State/Province" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "province",
                            "Street Address" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "address1",
                            "Zip Code" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "zip",
                            "Apartment" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "address2",
//            "phone" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "phone",
//            "company" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "company",
//            "latitude" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "latitude",
//            "longitude" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "longitude",
//            "country_code" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "country_code",
//            "province_code" => Constant::DB_TABLE_CONTENT . Constant::LINKER . "province_code",
            ]]);
        }

        $header = Arr::collapse([$header, [
                        '备注' => $this->remarkKey,
                        '积分变动时间' => 'ctime',
        ]]);

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = CreditService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        $isFromEmailExport = data_get($requestData, 'is_from_email_export');
        if ($isFromEmailExport) {
            return Response::json(...Response::getResponseData($file));
        }

        return Response::json([Constant::FILE_URL => $file]);
    }

    /**
     * 积分批量导入
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request) {
        $data = CreditService::importData($request);

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

}
