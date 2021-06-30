<?php

namespace App\Http\Controllers\Platform\Client;

use App\Http\Controllers\Api\Controller;
use App\Services\Platform\OrderService;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Services\DictService;
use Illuminate\Support\Arr;
use App\Services\PointStoreService;
use App\Util\Cache\CacheManager as Cache;

class OrderController extends Controller {

    /**
     * 订单创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();

        data_set($requestData, Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY, false); //订单平台 Amazon：亚马逊  Shopify：Shopify
        data_set($requestData, Constant::DB_TABLE_ORDER_TYPE, 1, false); //订单类型,1:正常购买订单,2积分兑换订单,3秒杀订单,其他值待定义

        $data = OrderService::getOrderList($requestData);
        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request) {

        $data = $request->all();

        $storeId = $this->storeId; //商城id
        $orderId = $request->input(Constant::DB_TABLE_PRIMARY, 0);

        $select = [
            Constant::DB_TABLE_PRIMARY, //订单id
            Constant::DB_TABLE_UNIQUE_ID, //平台订单唯一id
            Constant::DB_TABLE_ORDER_NO,
            Constant::DB_TABLE_ORDER_AT,
            Constant::DB_TABLE_ORDER_STATUS,
            Constant::DB_TABLE_AMOUNT,
            Constant::WARRANTY_AT,
            Constant::DB_TABLE_CURRENCY,
            Constant::DB_TABLE_EMAIL,
            Constant::DB_TABLE_PHONE,
        ];

        $where = [
            Constant::DB_TABLE_PRIMARY => $orderId,
        ];

        $orderStatusData = DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE); //订单状态 -1:匹配中 0:未支付 1:已经支付 2:取消 默认:-1
        $currencyData = DictService::getListByType(Constant::DB_TABLE_CURRENCY, Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE); //订单状态 -1:匹配中 0:未支付 1:已经支付 2:取消 默认:-1

        $field = Constant::DB_TABLE_ORDER_STATUS;
        $data = $orderStatusData;
        $dataType = Constant::PARAMETER_STRING_DEFAULT;
        $dateFormat = Constant::PARAMETER_STRING_DEFAULT;
        $time = Constant::PARAMETER_STRING_DEFAULT;
        $glue = Constant::PARAMETER_STRING_DEFAULT;
        $isAllowEmpty = true;
        $default = Constant::PARAMETER_STRING_DEFAULT;
        $callback = Constant::PARAMETER_ARRAY_DEFAULT;
        $only = Constant::PARAMETER_ARRAY_DEFAULT;
        $parameters = [$field, $default, $data, $dataType, $dateFormat, $time, $glue, $isAllowEmpty, $callback, $only];

        $handleData = [
            Constant::DB_TABLE_ORDER_AT => FunctionHelper::getExePlanHandleData(Constant::DB_TABLE_ORDER_AT, $default, Constant::PARAMETER_ARRAY_DEFAULT, Constant::DB_EXECUTION_PLAN_DATATYPE_DATETIME, 'Y-m-d', $time, $glue, $isAllowEmpty, $callback, $only), //订单时间
            Constant::WARRANTY_AT => FunctionHelper::getExePlanHandleData(Constant::WARRANTY_AT, $default, Constant::PARAMETER_ARRAY_DEFAULT, Constant::DB_EXECUTION_PLAN_DATATYPE_DATETIME, 'Y-m-d', $time, $glue, $isAllowEmpty, $callback, $only), //延保时间
            Constant::CURRENCY_SYMBOL => FunctionHelper::getExePlanHandleData(Constant::DB_TABLE_CURRENCY, data_get($currencyData, 'USD', '$'), $currencyData), //货币符号
        ];

        $joinData = Constant::PARAMETER_ARRAY_DEFAULT;

        $callback = [];
        $itemHandleData = [
            Constant::DB_TABLE_IMG => FunctionHelper::getExePlanHandleData('variant' . Constant::LINKER . 'image' . Constant::LINKER . 'src{or}product' . Constant::LINKER . 'image_src', '', Constant::PARAMETER_ARRAY_DEFAULT, $dataType, $dateFormat, $time, $glue, $isAllowEmpty, $callback, $only),
        ];
        $itemSelect = [
            Constant::DB_TABLE_UNIQUE_ID, //平台订单item唯一id
            Constant::DB_TABLE_ORDER_UNIQUE_ID, //订单 唯一id
            Constant::DB_TABLE_PRODUCT_VARIANT_UNIQUE_ID, //产品变种 唯一id
            Constant::DB_TABLE_PRODUCT_UNIQUE_ID, //产品 唯一id
            Constant::FILE_TITLE,
            'total_discount', //促销所产生的折扣金额
            Constant::DB_TABLE_QUANTITY, //订单中的sku件数
            Constant::DB_TABLE_AMOUNT, //订单产品金额
        ];
        $itemOrders = Constant::PARAMETER_ARRAY_DEFAULT; //[[Constant::DB_TABLE_AMOUNT, Constant::DB_EXECUTION_PLAN_ORDER_DESC]];

        $defaultHandleData = [];

        $imageSelect = [
            Constant::DB_TABLE_UNIQUE_ID, //唯一id
            'src',
        ];
        $imageWith = [
            'image' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $imageSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, [], $defaultHandleData, Constant::PARAMETER_ARRAY_DEFAULT, 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联产品图片
        ];

        $variantSelect = [
            Constant::DB_TABLE_UNIQUE_ID, //唯一id
            Constant::DB_TABLE_PRODUCT_IMAGE_UNIQUE_ID,
        ];

        $productSelect = [
            Constant::DB_TABLE_UNIQUE_ID, //唯一id
            'image_src',
        ];

        $itemFulfillmentSelect = [
            Constant::DB_TABLE_ORDER_ITEM_UNIQUE_ID,
            Constant::DB_TABLE_FULFILLMENT_UNIQUE_ID
        ];

        $fulfillmentSelect = [
            Constant::DB_TABLE_UNIQUE_ID, //唯一id
            'tracking_number',
            'tracking_company',
            'tracking_url',
        ];
        $fulfillmentWith = [
            'fulfillment' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $fulfillmentSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, [], $defaultHandleData, Constant::PARAMETER_ARRAY_DEFAULT, 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联物流数据
        ];

        $variantWith = [
            'variant' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $variantSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, $imageWith, $defaultHandleData, Constant::PARAMETER_ARRAY_DEFAULT, 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联产品变种
            'product' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $productSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, [], $defaultHandleData, Constant::PARAMETER_ARRAY_DEFAULT, 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联产品变种
            'item_fulfillment' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $itemFulfillmentSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, $fulfillmentWith, $defaultHandleData, Constant::PARAMETER_ARRAY_DEFAULT, 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联产品变种
        ];

        $addressSelect = [
            Constant::DB_TABLE_ORDER_UNIQUE_ID,
            Constant::DB_TABLE_NAME, //名字
            Constant::DB_TABLE_ZIP, //邮编
            Constant::DB_TABLE_ADDRESS1, //地址
            Constant::DB_TABLE_ADDRESS2, //可选地址
            Constant::DB_TABLE_CITY, //城市
            Constant::DB_TABLE_PROVINCE, //省份
            Constant::DB_TABLE_COUNTRY, //国家
            Constant::DB_TABLE_PHONE, //电话
            Constant::DB_TABLE_FIRST_NAME, //first name
            Constant::DB_TABLE_LAST_NAME, //last name
        ];
        $with = [
            'items' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $itemSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, $variantWith, $itemHandleData, [], 'hasMany', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联订单item
            'shipping_address' => FunctionHelper::getExePlan(
                    $storeId, null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, $addressSelect, [], $itemOrders, null, null, false, [], false, Constant::PARAMETER_ARRAY_DEFAULT, [], [], [], 'hasOne', false, Constant::PARAMETER_ARRAY_DEFAULT), //关联订单收件地址
        ];
        $unset = [
            Constant::DB_TABLE_UNIQUE_ID, //平台订单唯一id
            Constant::DB_TABLE_ORDER_STATUS,
            Constant::WARRANTY_AT,
            Constant::DB_TABLE_CURRENCY,
            'shipping_address' . Constant::LINKER . Constant::DB_TABLE_ORDER_UNIQUE_ID
        ];
        $exePlan = FunctionHelper::getExePlan($storeId, null, OrderService::getNamespaceClass(), Constant::PARAMETER_STRING_DEFAULT, $select, $where, [], null, null, false, [], false, $joinData, Constant::PARAMETER_ARRAY_DEFAULT, $handleData, $unset);

        $itemHandleDataCallback = [
            'order_status_show' => function($item) use($storeId) {
                $clientWarrantData = OrderService::getClientWarrantyData($storeId, $item);
                return data_get($clientWarrantData, 'order_status_show', '');
            },
            Constant::RESPONSE_WARRANTY => function($item) use($storeId) {

                $clientWarrantData = OrderService::getClientWarrantyData($storeId, $item);
                $isShowWarrantyAt = data_get($clientWarrantData, 'isShowWarrantyAt', 0);
                if (!$isShowWarrantyAt) {//如果不显示延保时间，就直接显示延保状态即可
                    return data_get($item, 'order_status_show', '');
                }

                $warranty = implode('-', [data_get($item, Constant::DB_TABLE_ORDER_AT, ''), data_get($item, Constant::WARRANTY_AT, '')]);
                if ($storeId == 5) {
                    $warranty = Constant::IKICH_WARRANTY_DATE;
                }
                return $warranty;
            },
            '{nokey}' => function (&$itme) use($unset) {//表 model
                foreach ($itme['items'] as $key => $value) {
                    data_set($itme, 'items.' . $key . '.fulfillment', data_get($itme, 'items.' . $key . '.item_fulfillment.fulfillment', null) ?? []);
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . Constant::DB_TABLE_UNIQUE_ID;
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . Constant::DB_TABLE_ORDER_UNIQUE_ID; //订单 唯一id
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . Constant::DB_TABLE_PRODUCT_VARIANT_UNIQUE_ID; //产品变种 唯一id
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . Constant::DB_TABLE_PRODUCT_UNIQUE_ID; //产品 唯一id
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . 'variant';
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . 'product';
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . 'item_fulfillment';
                    $unset[] = 'items' . Constant::LINKER . $key . Constant::LINKER . 'fulfillment' . Constant::LINKER . Constant::DB_TABLE_UNIQUE_ID; //物流唯一id
                }

                Arr::forget($itme, $unset);

                return '';
            },
        ];

        $dbExecutionPlan = [
            Constant::DB_EXECUTION_PLAN_PARENT => $exePlan,
            Constant::DB_EXECUTION_PLAN_WITH => $with,
            Constant::DB_EXECUTION_PLAN_ITEM_HANDLE_DATA => FunctionHelper::getExePlanHandleData(null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_ARRAY_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, true, $itemHandleDataCallback, $only),
        ];

        $dataStructure = 'one';
        $flatten = false;

        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(1, null, FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, false, $dataStructure))));
    }

    /**
     * 订单创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $storeId = $this->storeId;
        $account = $this->account;
        $customerId = $this->customerId;
        $variantItems = $request->input('variant_items', Constant::PARAMETER_ARRAY_DEFAULT);
        $platform = $request->input(Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY);
        $orderType = $request->input(Constant::DB_TABLE_ORDER_TYPE, 1);
        $requestData = $request->all();

        $rs = Cache::lock('create_order:' . $storeId . ':' . $customerId)->get(function () use($storeId, $platform, $account, $customerId, $orderType, $variantItems, $requestData) {
            return PointStoreService::handleOrder($storeId, $platform, $account, $customerId, $orderType, $variantItems, $requestData);
        });

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 订单创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function address(Request $request) {

        $storeId = $this->storeId;
        $rs = PointStoreService::address($storeId, $request->all());

        return Response::json(...Response::getResponseData(Response::getDefaultResponseData($rs ? 1 : 0, '', $rs)));
    }

}
