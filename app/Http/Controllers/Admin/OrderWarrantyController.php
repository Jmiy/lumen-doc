<?php

namespace App\Http\Controllers\Admin;

use App\Util\Constant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\OrderWarrantyService;
use App\Services\ExcelService;
use App\Services\DictService;
use App\Services\DictStoreService;

class OrderWarrantyController extends Controller {

    public $orderCountryKey = 'order_country';
    public $ordernoKey = 'orderno';

    public function getWarratySelect(Request $request = null) {
        return [
            'co.' . Constant::DB_TABLE_PRIMARY,
            'co.' . Constant::DB_TABLE_ORDER_UNIQUE_ID,
            'co.' . Constant::DB_TABLE_CUSTOMER_PRIMARY,
            'co.' . Constant::DB_TABLE_ACCOUNT,
            //'po.' . Constant::DB_TABLE_COUNTRY . ' as ' . Constant::DB_TABLE_COUNTRY,
            'co.' . Constant::DB_TABLE_ORDER_NO,
            'co.' . Constant::DB_TABLE_AMOUNT,
            'co.' . Constant::DB_TABLE_ORDER_TIME,
            'co.' . Constant::DB_TABLE_OLD_CREATED_AT,
            //'co.content',
            'co.' . Constant::DB_TABLE_ORDER_STATUS,
            'co.' . Constant::DB_TABLE_REVIEW_LINK,
            'co.' . Constant::DB_TABLE_REVIEW_TIME,
            'co.' . Constant::REVIEW_STATUS,
//            'ci.' . Constant::DB_TABLE_FIRST_NAME,
//            'ci.' . Constant::DB_TABLE_LAST_NAME,
            'co.' . Constant::DB_TABLE_BRAND,
            'co.' . Constant::DB_TABLE_PLATFORM,
            'co.' . Constant::WARRANTY_DATE,
            'co.' . Constant::WARRANTY_AT,
        ];
    }

    /**
     * 订单列表
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {

        $requestData = $request->all();
        if (Arr::exists($requestData, $this->countryKey)) {
            $requestData[$this->orderCountryKey] = $requestData[$this->countryKey];
        }

        $data = OrderWarrantyService::getListData($requestData, true, true, $this->getWarratySelect());
        return Response::json($data);
    }

    /**
     * 列表导出
     * @return string
     */
    public function export(Request $request) {

        $header = [
            '订单号' => Constant::DB_TABLE_ORDER_NO,
            '金额' => Constant::DB_TABLE_AMOUNT,
            '国家' => Constant::DB_TABLE_COUNTRY,
            '邮箱' => Constant::DB_TABLE_ACCOUNT,
            '会员名(First Name)' => Constant::DB_TABLE_FIRST_NAME,
            '会员名(Last Name)' => Constant::DB_TABLE_LAST_NAME,
            '状态' => Constant::DB_TABLE_ORDER_STATUS,
            '产品名称(参考)' => 'product_title',
            '产品SKU(参考)' => Constant::DB_TABLE_SKU,
            '平台来源' => Constant::DB_TABLE_PLATFORM,
            '订单填写时间' => Constant::DB_TABLE_OLD_CREATED_AT,
            '订单时间' => Constant::DB_TABLE_ORDER_TIME,
            '产品asin' => Constant::DB_TABLE_ASIN,
            '延保时间' => Constant::RESPONSE_WARRANTY,
            'distinctField' => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => 'co' . Constant::LINKER . Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['co' . Constant::LINKER . Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;
        $requestData['is_export_data'] = true; //是否是数据导出
        if (Arr::exists($requestData, $this->countryKey)) {
            $requestData[$this->orderCountryKey] = $requestData[$this->countryKey];
        }

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, 0); //商城id
        $isFromEmailExport = data_get($requestData, 'is_from_email_export');
        if (!$isFromEmailExport) {
            $requestData['auditStatusData'] = DictService::getListByType('audit_status', Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE); //审核状态 -1:未提交审核 0:未审核 1:已通过 2:未通过 3:其他
            $requestData['orderStatusData'] = DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE); //订单状态 -1:匹配中 0:未支付 1:已经支付 2:取消 默认:-1
            $requestData['orderConfig'] = DictStoreService::getByTypeAndKey($storeId, Constant::ORDER, [Constant::CONFIG_KEY_WARRANTY_DATE_FORMAT, Constant::WARRANTY_DATE]);
        }

        $service = OrderWarrantyService::getNamespaceClass();
        $method = $this->listMethod;
        $parameters = [$requestData, true, true, $this->getWarratySelect(), false, false]; //

        $countMethod = $this->listMethod;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000


        if ($isFromEmailExport) {
            return Response::json(...Response::getResponseData($file));
        }

        return Response::json(['url' => $file]);
    }

    /**
     * 订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0);
        $orderno = $request->input($this->ordernoKey, 0);

        $data = OrderWarrantyService::getDetails($storeId, $orderno);
        if (empty($data)) {
            return Response::json([], 10024, 'order not exists');
        }

        return Response::json($data);
    }

    /**
     * 订单绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bind(Request $request) {

        $requestData = $request->all();
        $storeId = $requestData[$this->storeIdKey] ?? 0;
        $account = $requestData[$this->accoutKey] ?? '';
        $orderno = $requestData[$this->ordernoKey] ?? '';
        $country = $requestData[$this->orderCountryKey] ?? ($requestData[$this->countryKey] ?? '');
        $type = $requestData[Constant::DB_TABLE_TYPE] ?? 'platform';

        $ret = OrderWarrantyService::bind($storeId, $account, $orderno, $country, $type, $requestData);
        if ($ret[Constant::RESPONSE_CODE_KEY] != 1) {
            if (Constant::RESPONSE_CODE_KEY == 3000) {
                $ret[Constant::RESPONSE_MSG_KEY] = '订单重复绑定';
            }
            if (strpos(Constant::RESPONSE_MSG_KEY, 'not exists') !== false) {
                $ret[Constant::RESPONSE_MSG_KEY] = '邮箱账号不存在需先注册账号后延保';
            }
            return Response::json([], $ret[Constant::RESPONSE_CODE_KEY], $ret[Constant::RESPONSE_MSG_KEY]);
        }

        return Response::json([], 1, $ret[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * 评论链接订单列表
     * @param Request $request
     * @return type
     */
    public function reviewList(Request $request) {
        $requestData = $request->all();
        if (Arr::exists($requestData, $this->countryKey)) {
            $requestData[$this->orderCountryKey] = $requestData[$this->countryKey];
        }
        $requestData['addWhere'] = [['co.' . Constant::REVIEW_STATUS, '>', -1]];
        $data = OrderWarrantyService::getListData($requestData, true, true, $this->getWarratySelect());
        return Response::json($data);
    }

    /**
     * 订单评论链接审核接口
     * @param Request $request
     * @return type
     */
    public function reviewCheck(Request $request) {
        $storeId = Arr::get($request, $this->storeIdKey, 0);
        $orderId = Arr::get($request, 'id', 0);
        if (empty($orderId)) {
            return Response::json([], '10019', 'id不存在');
        }

        $reviewStatus = Arr::get($request, 'review_status', 0);
        $reviewCredit = Arr::get($request, 'review_credit', 0);
        $addType = Arr::get($request, 'add_type', 1);
        $action = Arr::get($request, 'action', 'order_review');
        $reviewRemark = Arr::get($request, 'review_remark', '');
        $ret = OrderWarrantyService::addReviewcheck($storeId, $orderId, $reviewStatus, $reviewCredit, $addType, $action, $reviewRemark);
        if ($ret['code'] != 1) {//如果添加失败，就提示用户
            return Response::json([], 10015, $ret['msg']);
        }
        return Response::json();
    }

    /**
     * 列表导出
     * @return string
     */
    public function reviewExport(Request $request) {

        $requestData = $request->all();

        $header = [
            '邮箱' => $this->accoutKey,
            '国家' => $this->countryKey,
            '订单号' => $this->ordernoKey,
            '订单金额' => 'amount',
            '产品SKU' => 'sku',
            '订单延保时间' => 'warranty',
            '订单时间' => 'order_time',
            '订单状态' => 'order_status',
            '订单评论链接' => 'review_link',
            '评论提交时间' => 'review_time',
            '评论审核状态' => 'review_status',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['co.id']
            ],
        ];
        $requestData['addWhere'] = [['co.' . Constant::REVIEW_STATUS, '>', -1]]; //筛选审核状态大于-1的才导出
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;
        if (Arr::exists($requestData, $this->countryKey)) {
            $requestData[$this->orderCountryKey] = $requestData[$this->countryKey];
        }

        $service = OrderWarrantyService::getNamespaceClass();
        $method = $this->listMethod;
        $parameters = [$requestData, true, true, $this->getWarratySelect(), false, false];

        $countMethod = $this->listMethod;
        $countParameters = Arr::collapse([$parameters, [true]]);

        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 订单接触绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unBind(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT);
        if (empty($id) || empty($storeId)) {
            return Response::json(Constant::PARAMETER_ARRAY_DEFAULT, -1, Constant::PARAMETER_STRING_DEFAULT);
        }

        $data = OrderWarrantyService::unBind($storeId, $id);
        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

}
