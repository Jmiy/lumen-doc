<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use Carbon\Carbon;
use App\Models\CustomerInfo;
use App\Services\ActivityProductService;
use App\Services\InviteService;
use App\Services\CustomerService;
use App\Services\ActivityService;
use App\Services\CouponService;
use App\Services\ActivityApplyService;
use App\Util\Constant;

class ActivityProductController extends Controller {

    /**
     * 活动商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $country = $request->input('product_country', $request->input(Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT));

        $customer = $request->user();
        $customerId = Arr::get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);
        $page = $request->input(Constant::REQUEST_PAGE, 1);
        $pageSize = $request->input(Constant::REQUEST_PAGE_SIZE, 10);

        $data = ActivityProductService::getItemData($this->storeId, $this->actId, $country, $customerId, $page, $pageSize, $request->all());
        unset($data['pagination']);
        return Response::json($data, 1, 'ok', false);
    }

    /**
     * 商品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request) {

        $storeId = $this->storeId; //商城id
        $actId = $this->actId; //活动id
        $customerId = 0; //邀请者会员id
        $account = $request->input(Constant::DB_TABLE_ACCOUNT, Constant::PARAMETER_STRING_DEFAULT); //邀请者会员账号
        $productId = $request->input(Constant::DB_TABLE_PRIMARY, 0); //关联id 活动产品id

        if ($account) {
            $customerData = CustomerService::customerExists($storeId, 0, $account, 0, true);
            $customerId = data_get($customerData, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
            unset($customerData);
        }

        $inviteCode = $request->input('invite_code', Constant::PARAMETER_STRING_DEFAULT); //邀请者的邀请码
        if ($inviteCode) {
            $inviteData = InviteService::getCustomerData($inviteCode);
            $customerId = data_get($inviteData, 'customer.customer_id', 0);
            unset($inviteData);
        }

        $rs = ActivityProductService::getDetails($storeId, $actId, $productId, $customerId, 'ActivityProduct');

        if (data_get($rs, Constant::RESPONSE_CODE_KEY) == 1) {
            data_set($rs, 'data.unlocking_nums', ActivityApplyService::getCount($storeId, $actId, $productId, 'ActivityProduct'));
        }

        $parameters = Response::getResponseData($rs);

        return Response::json(...$parameters);
    }

    /**
     * VT deal产品展示
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealIndex(Request $request) {
        $country = $request->input('aws_country', Constant::PARAMETER_STRING_DEFAULT); //获取传入的国家
        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1);

        if (empty($country)) {//没有传国家时，获取用户注册时的国家
            $country = CustomerInfo::where(Constant::DB_TABLE_CUSTOMER_PRIMARY, $this->customerId)->value(Constant::DB_TABLE_COUNTRY);
            if (empty($country)) {//没有获取到用户注册国家时，统一用美国
                $country = 'US';
            }
        }

        $countrylist = data_get(ActivityProductService::$country_list, $this->storeId, data_get(ActivityProductService::$country_list, 2, []));
        $coutrymap = data_get(ActivityProductService::$countryMap, $this->storeId, data_get(ActivityProductService::$countryMap, 2, []));
        if (in_array($country, $countrylist)) {//匹配获取的国家是否在数组中，true就按照数组映射到对应的国家，false就统一用美国
            $country = data_get($coutrymap, $country, data_get($coutrymap, 'Other', Constant::PARAMETER_STRING_DEFAULT));
        } else {
            $country = 'US';
        }
        $type = $request->input(Constant::DB_TABLE_MB_TYPE, 0);

        $this->actId = $request->input($this->actIdKey, 4); //活动id
        $data = ActivityProductService::getDealData($this->storeId, $this->actId, $country, $customerId, $type, $this->page, $this->pageSize);
        return Response::json($data, 1, 'ok', false);
    }

    /**
     * VT deal获取优惠劵code
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoupon(Request $request) {
        $customerId = $this->customerId; //用户ID
        $asin = $request->input(Constant::DB_TABLE_ASIN, 0); //产品asin
        $country = $request->input(Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT); //产品对应的国家
        $getTime = Carbon::now()->toDateString(); //获取当前年月日
        $couponSelect = [Constant::DB_TABLE_PRIMARY, Constant::RESPONSE_CODE_KEY, 'extinfo', Constant::DB_TABLE_ASIN, 'receive'];
        $data = CouponService::getAsinCoupon($this->storeId, $country, $asin, $getTime, $customerId, $couponSelect);

        return Response::json(data_get($data, Constant::RESPONSE_DATA_KEY, []), data_get($data, Constant::RESPONSE_CODE_KEY, 0), data_get($data, Constant::RESPONSE_MSG_KEY, Constant::PARAMETER_STRING_DEFAULT));
    }

    /**
     * VT deal 领取code和点击次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clickReceiveCode(Request $request) {
        $productId = $request->input(Constant::DB_TABLE_PRODUCT_ID, Constant::PARAMETER_STRING_DEFAULT); //产品id
        $couponId = $request->input('coupon_id', Constant::PARAMETER_STRING_DEFAULT); //优惠劵id
        $data = ActivityProductService::clickReceiveData($this->storeId, $this->customerId, $this->account, $productId, $couponId);

        $parameters = Response::getResponseData($data);
        return Response::json(...$parameters);
    }

    /**
     * VT deal通用模板产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function universalList(Request $request) {
        $all = $request ->all();
        $storeId = $this->storeId;
        $actId = $this->actId; //活动id
        $actName = $request->input(Constant::DB_TABLE_ACTIVITY_NAME, Constant::PARAMETER_STRING_DEFAULT); //活动标识
        if (empty($actId)) {//根据标识查询活动的ID
            $actId = ActivityService::getModel($storeId)->where(Constant::DB_TABLE_NAME, $actName)->value(Constant::DB_TABLE_PRIMARY);
        }

        $country = $request->input('aws_country', Constant::PARAMETER_STRING_DEFAULT); //获取传入的国家
        if (empty($country)) {//没有获取到国家时，统一用美国
            $country = 'US';
        }

        if ($storeId == 2 && empty($country)){//vt活动定制化需求，需要根据ip自动识别国家
            $arrayCountry = ['US', 'CA', 'DE', 'FR', 'UK', 'IT', 'ES', 'MX'];
            $country = in_array($all['country'],$arrayCountry) ? $all['country'] : 'US';
        }

        $type = $request->input(Constant::DB_TABLE_MB_TYPE, 0);
        $page = $request->input(Constant::REQUEST_PAGE, 1);
        $pageSize = $request->input(Constant::REQUEST_PAGE_SIZE, 9);

        $data = ActivityProductService::getUniversaData($storeId, $actId, $country, $type, $page, $pageSize, $request->all());

        return Response::json($data, 1, 'ok', false);
    }

    /**
     * VT deal 更新通用模板产品点击数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clicks(Request $request) {
        $productId = $request->input(Constant::DB_TABLE_PRODUCT_ID, 0); //产品ID
        $data = ActivityProductService::dealClicks($this->storeId, $productId);
        return Response::json($data);
    }

    /**
     * 根据申请数据的类型获取数据(目前用于评测2.0)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function productDetails(Request $request) {
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //产品的主键id
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商城id
        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT);

        $data = ActivityProductService::productDetails($storeId, $id, $customerId);

        return Response::json($data);
    }

    /**
     * 评测产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeTestingList(Request $request) {
        $requestData = $request->all();
        data_set($requestData, Constant::DB_TABLE_SOURCE, 'api');
        $customer = $request->user();
        data_set($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY, data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT));
        $select = [
            'activity_products.' . Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_NAME,
            Constant::FILE_URL,
            Constant::DB_TABLE_IMG_URL,
            Constant::DB_TABLE_MB_IMG_URL,
            Constant::DB_TABLE_ASIN,
            Constant::DB_TABLE_QTY,
            Constant::DB_TABLE_QTY_APPLY,
            'show_apply',
            Constant::DB_TABLE_REGULAR_PRICE,
            Constant::DB_TABLE_LISTING_PRICE,
            Constant::DB_TABLE_PRODUCT_STATUS,
            Constant::EXPIRE_TIME,
            'activity_products.' . Constant::DB_TABLE_COUNTRY . ' as product_country',
            Constant::DB_TABLE_DES . ' as product_des',
            'aa.id as apply_id'
        ];
        $data = ActivityProductService::getFreeTestingList($requestData, true, true, $select);
        return Response::json($data);
    }

}
