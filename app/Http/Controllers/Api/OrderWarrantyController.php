<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Util\FunctionHelper;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\OrderWarrantyService;
use App\Services\ProductService;
use App\Services\CustomerService;
use App\Services\Store\ShopifyService;
use App\Services\LogService;
use App\Util\Constant;
use App\Services\Platform\OrderService;
use App\Services\BusGiftCardApplyService;

class OrderWarrantyController extends Controller {

    /**
     * 绑定订单
     * @param Request $request
     * @param int $storeId 商城id
     * @param string $account 会员账号
     * @param string $orderno  订单号
     * @param string $orderCountry 订单国家
     * @return $this
     */
    public function bindOrder(Request $request, $storeId = null, $account = '', $orderno = '', $orderCountry = '') {

        //判断当前请求是否已经执行过绑定订单
        $bindOrder = $request->input('bindOrder', []);
        if ($bindOrder) {//如果当前请求是否已经执行过绑定订单，就直接返回绑定结果
            return $bindOrder;
        }

        $requestData = $request->all();
        $storeId = $storeId !== null ? $storeId : $this->storeId;
        $account = $account ? $account : $this->account;
        $orderno = $orderno ? $orderno : data_get($requestData, Constant::DB_TABLE_ORDER_NO, '');
        $orderCountry = $orderCountry ? $orderCountry : data_get($requestData, Constant::DB_TABLE_ORDER_COUNTRY, data_get($requestData, Constant::DB_TABLE_COUNTRY, ''));
        $type = data_get($requestData, Constant::DB_TABLE_TYPE, Constant::DB_TABLE_PLATFORM);

        if (empty($orderno)) {
            return Response::getDefaultResponseData(Constant::RESPONSE_SUCCESS_CODE);
        }

        if (!FunctionHelper::checkOrderNo($orderno)) {
            return Response::getDefaultResponseData(39006);
        }

        $isCanWarrantyRs = BusGiftCardApplyService::rewardWarrantyHandle($storeId, $orderno);
        //已返现,不能延保
        if (data_get($isCanWarrantyRs, Constant::RESPONSE_CODE_KEY) == 3) {
            return Response::getDefaultResponseData(50004);
        }

        $extData = [
            Constant::DB_TABLE_BRAND => data_get($requestData, Constant::DB_TABLE_BRAND, ''), //品牌
            Constant::DB_TABLE_PLATFORM => data_get($requestData, Constant::ORDER_PLATFORM, Constant::PLATFORM_AMAZON), //平台
            Constant::DB_TABLE_ACT_ID => $this->actId, //活动id
            Constant::DB_TABLE_REMARK => data_get($requestData, Constant::DB_TABLE_REMARK, Constant::PARAMETER_STRING_DEFAULT),
        ];

        if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
            data_set($extData, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
        }

        if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
            data_set($extData, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
        }

        if (isset($requestData['bk'])) {
            data_set($extData, 'bk', $requestData['bk']);
        }

        $bindOrder = OrderWarrantyService::bind($storeId, $account, $orderno, $orderCountry, $type, $extData);
        $request->offsetSet('bindOrder', $bindOrder);

        //已返现,能延保,不能索评
        if (data_get($bindOrder, Constant::RESPONSE_CODE_KEY) == 1) {
            data_set($bindOrder, 'data.isCanReview', true);
            if (data_get($isCanWarrantyRs, Constant::RESPONSE_CODE_KEY) == 2) {
                data_set($bindOrder, 'data.isCanReview', false);
            }
        }

        return $bindOrder;
    }

    /**
     * 订单绑定
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bind(Request $request) {

        $orderBind = $this->bindOrder($request);

        return Response::json(...Response::getResponseData($orderBind));
    }

    /**
     * 订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $requestData = $request->all();

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $customer = $request->user();

        $data = OrderWarrantyService::getModel($storeId, '')->select([Constant::DB_TABLE_ORDER_NO, Constant::DB_TABLE_COUNTRY])
                ->where([Constant::DB_TABLE_STORE_ID => $storeId, Constant::DB_TABLE_CUSTOMER_PRIMARY => data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT), 'type' => $requestData['type']])
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->first();
        if (!$data) {
            return Response::json([], 10024, 'order not exists');
        }

        return Response::json($data);
    }

    /**
     * 订单延保列表查询
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();

        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        $data = OrderWarrantyService::getShowList($requestData);

        return Response::json($data);
    }

    /**
     * 积分兑换校验
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function creditExchange(Request $request) {

        $requestData = $request->all();
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城id
        $products = $request->input('products', []); //兑换产品数据
        $customer = $request->user();
        $customerId = $customer->customer_id;

        $ids = array_column($products, 'id');
        $productData = ProductService::getProducts($storeId, $ids, true);
        list($checkProductRet, $isValid) = OrderWarrantyService::checkExchangeProduct($productData, $products, $customerId);

        //判断兑换还是校验
        $isexchange = isset($requestData['exchange']) && $requestData['exchange'] == 'buy' ? true : false;
        if (!$isexchange) {
            return Response::json($checkProductRet);
        }

        if (!$isValid) {
            return Response::json([], '10018', 'Exchange failure');
        }

        //兑换
        $requestData['type'] = $requestData['type'] ?? 'credit';
        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        $requestData[Constant::DB_TABLE_ADD_TYPE] = $requestData[Constant::DB_TABLE_ADD_TYPE] ?? 2;
        $productIdQty = ProductService::productIdQty($products);
        $ret = OrderWarrantyService::batchExchange($productData, $requestData, $productIdQty);
        if (!$ret) {
            return Response::json([], '10017', '兑换异常');
        }

        return Response::json();
    }

    public function creatNotice(Request $request) {
        $post = $request->all();

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 2);
        $customerId = 0;
        $account = data_get($post, 'customer.email', data_get($post, Constant::DB_TABLE_EMAIL, ''));
        $storeCustomerId = data_get($post, 'customer.id', 0);
        $getData = true;

        $routeData = $request->route();
        $appEnv = $request->input(Constant::APP_ENV, data_get($routeData, '2.' . Constant::APP_ENV)); //开发环境 $request->route(Constant::APP_ENV, null)

        $apiUrl = $request->getRequestUri();

        LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_callback', $account, ['apiUrl' => $apiUrl, 'routeData' => $routeData, 'storeId' => $storeId, 'appEnv' => $appEnv, 'post' => $post, 'header' => $request->headers->all()], 'order_callback_data');

        $key = "X-Shopify-Hmac-Sha256";
        $hmac = $request->headers->get($key, '');
        $data = $request->getContent();//file_get_contents('php://input');
        $verify = ShopifyService::verifyWebhook($storeId, $data, $hmac);
        $post['X-Shopify-Hmac-Sha256'] = $hmac;
        $post[Constant::DB_TABLE_STORE_ID] = $storeId;
        $post[Constant::APP_ENV] = $appEnv;
        if (!$verify) {
            LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_verify', $account, ['apiUrl' => $apiUrl, 'routeData' => $routeData, 'storeId' => $storeId, 'appEnv' => $appEnv, 'post' => $data, 'header' => $request->headers->all()], 'verify false');
            return Response::json([], '10025', 'verify false');
        }

        if (empty($post[Constant::LINE_ITEMS])) {
            LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_items', $account, $post, 'this product empty');
            return Response::json([], '10021', 'this product empty');
        }

        //校验用户
        $customer = CustomerService::customerExists($storeId, $customerId, $account, 0, $getData); //$storeCustomerId
        if (empty($customer)) {
            LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_customer', $account, $post, 'this account not exists');
            return Response::json([], '10020', 'this account not exists');
        }

        $storeId = data_get($customer, Constant::DB_TABLE_STORE_ID, 0);
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);
        $post["store_id"] = $storeId;

        //校验产品
        $params['ids'] = array_column($post[Constant::LINE_ITEMS], 'product_id');
        $params[Constant::DB_TABLE_STORE_ID] = $storeId;
        $products = ProductService::getProducts($storeId, $params['ids'], true); //获取产品数据
        $exchangeProducts = []; //兑换的产品数据
        foreach ($post[Constant::LINE_ITEMS] as $key => $item) {
            $exchangeProducts[$key]['id'] = $item['product_id'];
            $exchangeProducts[$key]['qty'] = $item['quantity'] ?? 0;
        }

        list($checkProductRet, $isValid) = OrderWarrantyService::checkExchangeProduct($products, $exchangeProducts, data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0));

        if (empty($isValid)) {
            LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_isValid', $post[Constant::DB_TABLE_EMAIL], $post, json_encode($checkProductRet));
            return Response::json($checkProductRet, '10018', 'Exchange failure');
        }

        //积分兑换产品
        $params = $post;
        $params['type'] = $params['type'] ?? 'credit';
        $params[Constant::DB_TABLE_ADD_TYPE] = $params[Constant::DB_TABLE_ADD_TYPE] ?? 2;
        $params[Constant::DB_TABLE_ACCOUNT] = $post[Constant::DB_TABLE_EMAIL];
        $params[Constant::DB_TABLE_ORDER_NO] = $post["id"];
        $params[Constant::DB_TABLE_PLATFORM] = Constant::PLATFORM_SHOPIFY;
        $params[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        unset($params['id']);
        $productIdQty = ProductService::productIdQty($exchangeProducts);
        $ret = OrderWarrantyService::batchExchange($products, $params, $productIdQty);
        if (!$ret) {
            LogService::addSystemLog('info', Constant::PLATFORM_SHOPIFY, 'order_exchange', $post[Constant::DB_TABLE_EMAIL], $post, 'exchange exception');
            return Response::json([], '10017', 'exchange exception');
        }

        $note = $checkProductRet['total_credit'] . " redeem for Customer " . $post[Constant::DB_TABLE_EMAIL];
        ShopifyService::paidOrder($storeId, $post["id"], $post["total_price"], $note); //付款回单

        return Response::json();
    }

    /**
     * shopify下单回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shopify(Request $request) {
        return $this->creatNotice($request);
    }

    /**
     * 订单评论列表查询 备注：目前只有holife在使用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function warrantyList(Request $request) {

        $requestData = $request->all();
        $requestData[Constant::DB_TABLE_COUNTRY] = '';

        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        unset($requestData[Constant::DB_TABLE_ACCOUNT]);
        $data = OrderWarrantyService::getReviewlist($requestData);

        return Response::json($data);
    }

    /**
     * 添加订单评论链接 备注：目前只有holife在使用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addReview(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城id
        $orderId = $request->input('order_id', 0); //订单ID
        if (empty($orderId)) {
            return Response::json([], '10019', 'order ID empty');
        }
        $reviewLink = $request->input('review_link', ''); //评论链接
        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);
        OrderWarrantyService::addReviewLink($storeId, $orderId, $customerId, $reviewLink, $request->all());
        return Response::json();
    }

    /**
     * 订单评论列表查询（新的）
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newWarrantyList(Request $request) {

        $requestData = $request->all();
        $requestData[Constant::DB_TABLE_COUNTRY] = '';

        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);

        $requestData[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
        unset($requestData[Constant::DB_TABLE_ACCOUNT]);
        $data = OrderWarrantyService::newReviewlist($requestData);

        return Response::json($data);
    }

    /**
     * 订单是否存在
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderExists(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城id
        $orderId = $request->input('order_no', '');
        if (empty($orderId)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        if (in_array($storeId, OrderWarrantyService::getWarranyStore($storeId))) {//如果 $storeId对应的品牌, 使用的v2的延保规则就返回订单详情数据以便前端根据订单状态判断，是否可以延保
            $orderData = OrderService::getOrderDataNew($orderId, '', Constant::PLATFORM_SERVICE_AMAZON, $storeId);
            return Response::json(...Response::getResponseData($orderData));
        }

        if (empty(OrderService::isExists($storeId, $orderId))) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(39001)));
        }

        return Response::json(...Response::getResponseData(Response::getDefaultResponseData(1, 'ok')));
    }

}
