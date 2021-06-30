<?php

namespace App\Http\Controllers\Admin;

use App\Services\CustomerInfoService;
use App\Util\Cdn\CdnManager;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ExcelService;
use App\Services\CustomerService;

class CustomerController extends Controller {

    /**
     * 会员列表查询
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();
        $data = CustomerService::getShowList($requestData);

        return Response::json($data);
    }

    /**
     * 列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {
        $requestData = $request->all();
        $header = [
            '会员名' => 'name',
            '邮箱' => $this->accoutKey,
            '国家' => 'country',
            '会员当前积分' => 'credit',
            '会员总积分' => 'total_credit',
            '会员经验值' => 'exp',
            '会员等级' => 'vip',
            '注册时间' => 'ctime',
            '最后活动时间' => 'lastlogin',
            '注册ip' => 'ip',
            '来源' => 'source_value',
            '是否激活' => 'isactivate',
            'distinctField' => [
                'primaryKey' => $this->customerPrimaryKey,
                'primaryValueKey' => 'a.' . $this->customerPrimaryKey,
                'select' => ['a.' . $this->customerPrimaryKey]
            ],
        ];

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = CustomerService::getNamespaceClass();
        $method = 'getShowList';
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
     * 会员详情列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailsList(Request $request) {

        $requestData = $request->all();
        $data = CustomerInfoService::getDetailsListNew($requestData);

        return Response::json($data);
    }

    /**
     * 会员详情列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportDetailsList(Request $request) {
        $requestData = $request->all();
        $header = [
            '会员名' => 'name',
            '性别' => 'gender',
            '出生日期' => 'brithday',
            '邮箱' => $this->accoutKey,
            '国家' => 'country',
            '州省' => 'region',
            '填写时间' => 'mtime',
            '注册ip' => 'ip',
            'distinctField' => [
                'primaryKey' => Constant::DB_TABLE_CUSTOMER_PRIMARY,
                'primaryValueKey' => 'ci.' . Constant::DB_TABLE_CUSTOMER_PRIMARY,
                'select' => ['ci.' . Constant::DB_TABLE_CUSTOMER_PRIMARY]
            ],
        ];
        $storeId = Arr::get($requestData, $this->storeIdKey, 0);
        switch ($storeId) {
            case 1:
                $header['profile'] = 'profile_url';

                break;

            default:
                $header['兴趣'] = 'interest';
                break;
        }

        $requestData['page_size'] = 20000;
        $requestData['page'] = 1;

        $service = CustomerInfoService::getNamespaceClass();
        $method = 'getDetailsListNew';
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
     * 会员信息详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $requestData = $request->all();
        $customerId = $requestData[$this->customerPrimaryKey] ?? 0;
        $storeId = $requestData[$this->storeIdKey] ?? 0;
        $account = $requestData[$this->accoutKey] ?? '';
        $storeCustomerId = $requestData['store_customer_id'] ?? 0;
        $data = CustomerService::getCustomer($storeId, $customerId, $account, $storeCustomerId);
        if (empty($data)) {
            return Response::json([], 10004, 'customer not exists');
        }

        return Response::json($data);
    }

    /**
     * 编辑会员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $requestData = $request->all();
        $customerId = $requestData[$this->customerPrimaryKey] ?? 0;
        $customer = CustomerService::customerExists(0, $customerId, '', 0, false);
        if (empty($customer)) {//不存在，就提示用户
            return Response::json([], 10014, '会员不存在');
        }

        CustomerService::adminEdit($customerId, $requestData); //修改基本资料

        return Response::json();
    }

    /**
     * 会员同步
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request) {

        $requestData = $request->all();

        $storeId = $requestData[$this->storeIdKey] ?? 0;
        $operator = $requestData['operator'] ?? '';
        $checkFreq = CustomerService::checkSyncFrequent($storeId, $operator);
        if ($checkFreq) {
            return Response::json([], 10025, '5分钟内只能拉取一次');
        }

        $createdAtMin = $requestData['start_time'] ?? '';
        $createdAtMax = $requestData['end_time'] ?? '';
        $limit = isset($requestData[Constant::DB_EXECUTION_PLAN_LIMIT]) && $requestData[Constant::DB_EXECUTION_PLAN_LIMIT] ? $requestData[Constant::DB_EXECUTION_PLAN_LIMIT] : 1000;
        $ids = [];
        $sinceId = '';
        $source = 5;
        $retData = CustomerService::sync($storeId, $createdAtMin, $createdAtMax, $ids, $sinceId, $limit, $source, $operator);

        if ($retData['code'] != 1) {
            return Response::json($retData['data'], 10024, $retData['msg']);
        }

        return Response::json([], 'ok', $retData['msg']);
    }

    /**
     * 删除会员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete(Request $request) {

        $accountData = $request->input($this->accoutKey, ''); //会员账号
        $storeId = $request->input($this->storeIdKey, 0); //商城id
        if (empty($accountData) || empty($storeId)) {
            return Response::json([]);
        }

        $data = CustomerService::deleteCustomerData($storeId, $accountData);

        return Response::json(data_get($data, 'data', []), data_get($data, 'code', 1), data_get($data, 'msg', ''));
    }

    /**
     * 删除会员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importShopfiyAppAccount(Request $request) {


        ini_set('memory_limit', '1024M');

        $storeId = $request->input($this->storeIdKey, 0);

        if (empty($storeId)) {
            return Response::json([], 0, '非法参数');
        }

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');

        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {

            $parameters = Response::getResponseData($fileData);

            return Response::json(...$parameters);
        }

        $fileFullPath = data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, '');

        //使用消息队列解析上传的文件
        $service = CustomerService::getNamespaceClass();
        $method = 'readShopfiyAppAccount';
        $parameters = [$storeId, $fileFullPath];

        FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{data-import}'); //把任务加入消息队列

        return Response::json([],2,'数据导入中，大概10分钟后导入完成');
    }

}
