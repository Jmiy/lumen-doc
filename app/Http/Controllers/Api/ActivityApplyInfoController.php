<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityApplyService;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\ActivityApplyInfoService;
use App\Services\ActivityService;
use App\Services\CustomerService;
use App\Util\Constant;
use App\Services\ActivityProductService;
use App\Services\ActivityTaskService;
use App\Services\Erp\ErpAmazonService;
use App\Util\Cache\CacheManager as Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Services\Platform\OrderService;

class ActivityApplyInfoController extends Controller {

    /**
     * 活动申请资料填写
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request) {

        $storeId = $this->storeId; //商城id
        $account = $this->account; //会员账号
        $actId = $request->input($this->actIdKey, 0); //获取有效的活动id ActivityService::getValidActIds($storeId)
        $customerId = $this->customerId; //会员id
        $isPurchased = $request->input('is_purchased', 0); //是否购买了商品 0:未购买 1:已购买
        $orderno = $request->input('orderno', ''); //订单id
        $orderCountry = $request->input('order_country', ''); //订单国家
        $id = $request->input(Constant::DB_TABLE_PRIMARY, 0); //产品id

        $requestData = $request->all();
        $tag = ActivityApplyInfoService::getCacheTags();
        $cacheKey = $tag . ':' . $storeId . ':' . $actId . ':' . $customerId;
        $handleCacheData = [
            'service' => ActivityApplyInfoService::getNamespaceClass(),
            'method' => 'lock',
            'parameters' => [
                $cacheKey,
            ],
            'serialHandle' => [
                [
                    'service' => ActivityApplyInfoService::getNamespaceClass(),
                    'method' => 'get',
                    'parameters' => [
                        function () use($storeId, $actId, $customerId, $account, $isPurchased, $orderno, $orderCountry, $id, $requestData, $request) {

                            $products = array_filter(array_unique(data_get($requestData, 'products', [])));

                            $data = [
                                Constant::DB_TABLE_ACT_ID => $actId, //活动id
                                Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //会员id
                                'social_media' => data_get($requestData, 'social_media', Constant::PARAMETER_STRING_DEFAULT), //社交媒体
                                'youtube_channel' => data_get($requestData, 'youtube_channel', Constant::PARAMETER_STRING_DEFAULT), //youtube频道
                                'blogs_tech_websites' => data_get($requestData, 'blogs_tech_websites', Constant::PARAMETER_STRING_DEFAULT), //博客或者技术站
                                'deal_forums' => data_get($requestData, 'deal_forums', Constant::PARAMETER_STRING_DEFAULT), //论坛
                                'others' => data_get($requestData, 'others', Constant::PARAMETER_STRING_DEFAULT), //其他
                                'products' => json_encode($products, JSON_UNESCAPED_UNICODE), //用户感兴趣的产品
                                'is_purchased' => $isPurchased, //是否购买了商品 0:未购买 1:已购买
                                Constant::DB_TABLE_ORDER_NO => $orderno, //订单编号
                                'order_country' => $orderCountry, //订单国家
                                Constant::DB_TABLE_REMARKS => data_get($requestData, Constant::DB_TABLE_REMARKS, Constant::PARAMETER_STRING_DEFAULT), //备注
                                'phone_model' => data_get($requestData, 'phone_model', Constant::PARAMETER_STRING_DEFAULT), //手机型号
                                'product_video' => data_get($requestData, 'product_video', Constant::PARAMETER_STRING_DEFAULT), //产品视频地址
                            ];

                            if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
                                data_set($data, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
                            }

                            if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
                                data_set($data, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
                            }

                            //www.homasy.com||www.iseneo.com||holife.com等官网众测需求不用绑定订单
                            if (!in_array($storeId, [2, 3, 5, 6, 7, 8, 9, 10])) {
                                //编辑会员基本资料 订单绑定
                                $customerHandle = new CustomerController($request);
                                $customerHandle->edit($request);
                            } else {
                                //编辑会员基本资料
                                CustomerService::apiEdit($customerId, $requestData);

                                $activityData = ActivityService::getActivityData($storeId, $actId);
                                $currentTime = time();
                                $endAt = !empty($activityData[Constant::DB_TABLE_END_AT]) ? strtotime($activityData[Constant::DB_TABLE_END_AT]) : $currentTime;
                                $expireTime = $endAt - $currentTime;
                                $setnxKey = "{$storeId}_{$actId}_{$customerId}";
                                if (Redis::setnx($setnxKey, "1")) {
                                    Redis::expire($setnxKey, $expireTime);
                                    //用户申请产品+1
                                    $apply = ActivityApplyService::existsOrFirst($storeId, '', [Constant::DB_TABLE_ACT_ID => $actId, Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId], true);
                                    $where = [Constant::DB_TABLE_PRIMARY => data_get($apply, Constant::DB_TABLE_EXT_ID, Constant::PARAMETER_INT_DEFAULT)];
                                    $upData = [Constant::DB_TABLE_QTY_APPLY => DB::raw(Constant::DB_TABLE_QTY_APPLY . '+1')];
                                    ActivityProductService::insert($storeId, $where, $upData);
                                }
                            }

                            $isExists = ActivityApplyInfoService::exists($storeId, $actId, $customerId);
                            if ($isExists) {
                                if (isset($requestData['bk'])) {
                                    $where = [
                                        Constant::DB_TABLE_ACT_ID => $actId, //活动id
                                        Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //会员id
                                    ];
                                    ActivityApplyInfoService::insert($storeId, $where, $data);
                                }
                            } else {
                                $where = [];
                                ActivityApplyInfoService::insert($storeId, $where, $data);
                            }

                            $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, 'apply_info', 'create_product'); //提交申请资料是否创建活动产品数据 1：是 0：否
                            $isCreateProduct = data_get($activityConfigData, 'apply_info_create_product.value', Constant::PARAMETER_INT_DEFAULT);
                            if ($isCreateProduct && empty($id) && $products) {
                                $country = data_get($requestData, 'country', ''); //国家
                                $name = implode(',', $products);
                                $activityProductWhere = [
                                    Constant::DB_TABLE_ACT_ID => $actId, //活动id
                                    Constant::DB_TABLE_COUNTRY => $country,
                                    'name' => $name,
                                ];
                                $activityProductData = [
                                    'qty' => 99999999,
                                ];
                                $activityProductData = ActivityProductService::updateOrCreate($storeId, $activityProductWhere, $activityProductData, $country);
                                $id = data_get($activityProductData, (Constant::RESPONSE_DATA_KEY . '.' . Constant::DB_TABLE_PRIMARY), Constant::PARAMETER_INT_DEFAULT);
                                $request->offsetSet('id', $id);
                            }

                            if ($id) {
                                $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, 'registered', 'is_need_activate');
                                $isCanApply = true; //是否可以申请产品 true:可以  false:不可以
                                if (data_get($activityConfigData, 'registered_is_need_activate.value', 0)) {//如果当期活动需要激活，就判断用户是否激活
                                    //获取会员激活状态数据
                                    $customer = CustomerService::getCustomerActivateData($storeId, $customerId);
                                    if (empty(data_get($customer, 'info.isactivate', 0))) {//如果用户未激活，就不可以申请产品
                                        $isCanApply = false;
                                    }
                                }

                                if ($isCanApply) {//如果可以申请产品，就执行申请产品
                                    //申请产品体验
                                    $request->offsetSet('act_id', $actId);
                                    $applyHandle = new ActivityApplyController($request);
                                    $applyHandle->insert($request);
                                }
                            }

                            if ($isExists) {
                                return Response::getDefaultResponseData(60002);
                            }

                            return Response::getDefaultResponseData(1);
                        }
                    ],
                ]
            ]
        ];

        $rs = ActivityApplyInfoService::handleCache($tag, $handleCacheData);

        $defaultRs = Response::getDefaultResponseData(60008);

        $rs = $rs === false ? $defaultRs : $rs;

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 活动申请资料详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $this->storeId; //商城id
        $actId = $request->input($this->actIdKey, ActivityService::getValidActIds($storeId)); //获取有效的活动id
        $customerId = $this->customerId; //会员id

        $data = ActivityApplyInfoService::info($storeId, $actId, $customerId);

        return Response::json($data);
    }

    /**
     * 活动申请资料提交
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function applyInfoSubmit(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城id
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
        $orderId = $request->input('order_no', 0); //订单号
        $account = $request->input(Constant::DB_TABLE_ACCOUNT, ''); //会员账号
        $activityWinningId = $request->input('apply_id', 0); //申请id
        $requestData = $request->all();

        if (empty($storeId) || empty($actId) || empty($customerId) || empty($orderId) || empty($activityWinningId)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        //判断当前用户能否申请
        $_applyInfoData = ActivityApplyInfoService::getApplyInfo($storeId, $actId, $customerId);
        if ($_applyInfoData->count() >= 2) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(60100)));
        }

        $where = [
            Constant::DB_TABLE_EXT_TYPE => ActivityProductService::getModelAlias(),
            Constant::DB_TABLE_EXT_ID => $activityWinningId,
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId
        ];
        $exists = ActivityApplyInfoService::existsOrFirst($storeId, '', $where);
        if ($exists) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(60101)));
        }

        //判断当前IP能否申请
        $ip = ActivityTaskService::getRegIp($storeId, $customerId, $requestData);

        if (!empty($ip)) {
            //判断当前IP下用户能否申请
            $where = [
                Constant::DB_TABLE_EXT_TYPE => ActivityProductService::getModelAlias(),
                Constant::DB_TABLE_ACT_ID => $actId,
                Constant::PRODUCT_TYPE => 3,
                Constant::DB_TABLE_IP => $ip,
            ];
            $_applyData = ActivityApplyService::getModel($storeId)->select([Constant::DB_TABLE_CUSTOMER_PRIMARY])->buildWhere($where)->get();
            if (!$_applyData->isEmpty()) {
                $customerIds = array_column($_applyData->toArray(), Constant::DB_TABLE_CUSTOMER_PRIMARY);
                $_applyInfoData = ActivityApplyInfoService::getApplyInfo($storeId, $actId, $customerIds);
                if ($_applyInfoData->count() >= 2) {
                    return Response::json(...Response::getResponseData(Response::getDefaultResponseData(60003)));
                }
            }
        }

        if (!ActivityTaskService::taskIsFinish($storeId, $actId, $customerId)) {
            //任务没完成
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(61114)));
        }

        if (empty(OrderService::isExists($storeId, $orderId))) {
            //订单不存在
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(39001)));
        }

        $data = Cache::lock('free:' . $storeId . ':' . $actId . ':' . $customerId . ':')->get(function () use($storeId, $actId, $customerId, $account, $activityWinningId, $requestData) {
            // 获取无限期锁并自动释放...
            return ActivityApplyInfoService::applyInfoSubmit($storeId, $actId, $customerId, $account, $activityWinningId, $requestData);
        });

        return Response::json(...Response::getResponseData($data === false ? Response::getDefaultResponseData(110001) : $data));
    }

}
