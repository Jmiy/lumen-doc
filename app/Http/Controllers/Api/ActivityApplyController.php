<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Response;
use App\Services\ActivityApplyService;
use App\Services\ActivityProductService;
use App\Util\Constant;
use Carbon\Carbon;
use App\Util\FunctionHelper;

class ActivityApplyController extends Controller {

    public $extType = 'ActivityProduct'; //关联模型 活动产品

    private function addApply(Request $request) {
        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $account = $request->input($this->accoutKey, ''); //会员账号
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
        $extId = $request->input('id', ''); //关联id 活动产品id
        //申请产品体验
        $where = [
            'ext_type' => $this->extType, //关联模型 活动产品
            'ext_id' => $extId, //关联id 活动产品id
            Constant::DB_TABLE_ACT_ID => $actId, //活动id
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //会员id
        ];

        $productData = ActivityProductService::exists($storeId, $extId, $actId, '', true); //获取产品数据
        $data = [
            'ip' => $request->input('ip', ''),
            $this->accoutKey => $account,
            'product_type' => data_get($productData, 'type', 0),
            'country' => $request->input('country', ''),
        ];

        $requestData = $request->all();
        if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
            data_set($data, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
        }

        if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
            data_set($data, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
        }

        return ActivityApplyService::updateOrCreate($storeId, $where, $data);
    }

    /**
     * 产品申请
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
        $extId = $request->input('id', ''); //关联id 活动产品id

        if (empty($storeId) || empty($actId) || empty($customerId) || empty($extId)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        $rs = Cache::lock('apply:' . $storeId . ':' . $actId . ':' . $customerId . ':' . $extId)->get(function () use($request, $storeId, $actId, $customerId, $extId) {
            // 获取无限期锁并自动释放...
            try {
                $isCanApply = ActivityApplyService::isCanApply($storeId, $actId, $customerId, $extId, $this->extType);
                if ($isCanApply['code'] != 1) {
                    return $isCanApply;
                }

                $this->addApply($request);
            } catch (\Exception $exc) {

            }

            return Response::getDefaultResponseData(1);
        });

        $defaultRs = Response::getDefaultResponseData(60008);
        $rs = $rs === false ? $defaultRs : $rs;

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 获取产品申请状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuditStatus(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id

        $data = ActivityApplyService::getAuditStatus($storeId, $actId, $customerId, $this->extType);

        return Response::json($data);
    }

    /**
     * 产品申请
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function free(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
        $extId = $request->input('id', 0); //关联id 活动产品id

        if (empty($storeId) || empty($actId) || empty($customerId) || empty($extId)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        $rs = Cache::lock('free:' . $storeId . ':' . $actId . ':' . $customerId . ':' . $extId)->get(function () use($request, $storeId, $actId, $customerId, $extId) {
            // 获取无限期锁并自动释放...
            $applyData = [];
            try {
                $account = $request->input($this->accoutKey, ''); //会员账号
                $applyCountry = $request->input(Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT);
                $isCanApply = ActivityApplyService::isCanApplyFree($storeId, $actId, $customerId, $extId, $this->extType, $account, $applyCountry);
                if (data_get($isCanApply, 'code', 0) != 1) {
                    return $isCanApply;
                }

                $applyData = $this->addApply($request);
            } catch (\Exception $exc) {

            }

            return Response::getDefaultResponseData(1, null, ['apply_id' => data_get($applyData, 'data.id', 0)]);
        });

        $defaultRs = Response::getDefaultResponseData(60008);
        $rs = $rs === false ? $defaultRs : $rs;

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 添加订单评论链接(新的)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newAddReview(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $orderId = $request->input('order_id', 0); //订单ID
        if (empty($orderId)) {
            return Response::json([], '10019', 'order ID empty');
        }
        $reviewLink = $request->input('review_link', ''); //评论链接
        $account = $request->input($this->accoutKey, ''); //email
        $customer = $request->user();
        $customerId = Arr::get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);
        ActivityApplyService::newAddReviewLink($storeId, $orderId, $customerId, $account, $reviewLink);
        return Response::json();
    }

    /**
     * 配件申请
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accessoriesApply(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $actId = $request->input($this->actIdKey, 0); //活动id
        $customerId = $request->input($this->customerPrimaryKey, 0); //会员id
        $productIds = $request->input('product_ids', []); //申请的产品id列表
        $requestData = $request->all();

        if (empty($storeId) || empty($actId) || empty($customerId) || empty($productIds)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        $rs = Cache::lock('free:' . $storeId . ':' . $actId . ':' . $customerId . ':' . json_encode($productIds))->get(function () use($request, $storeId, $actId, $customerId, $productIds, $requestData) {
            // 获取无限期锁并自动释放...
            $applyData = [];
            try {

                $ip = ActivityTaskService::getRegIp($storeId, $customerId, $requestData);

                $isCanApply = ActivityApplyService::isCanApplyAccessories($storeId, $actId, $customerId, $productIds, $this->extType, $ip);
                if ($isCanApply['code'] != 1) {
                    return $isCanApply;
                }
                $applyData = ActivityApplyService::addApply($request, data_get($isCanApply, 'data.products'), $ip);
            } catch (\Exception $exception) {

            }

            return Response::getDefaultResponseData(1, null, ['apply_id' => data_get($applyData, 'data.apply_id', 0)]);
        });

        $defaultRs = Response::getDefaultResponseData(60008);
        $rs = $rs === false ? $defaultRs : $rs;

        return Response::json(...Response::getResponseData($rs));
    }

    public function joinAct(Request $request) {
        $storeId = $request->input($this->storeIdKey, 0); //商城id
        $account = $request->input($this->accoutKey, ''); //会员账号
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //活动id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id
        $extId = $request->input(Constant::DB_TABLE_EXT_ID, ''); //关联id 活动产品id
        $extType = $request->input(Constant::DB_TABLE_EXT_TYPE, ''); //关联模型

        $rs = Cache::lock('joinAct:' . $storeId . ':' . $actId . ':' . $customerId . ':' . $extId . ':' . $extType)->get(function () use($request, $storeId, $actId, $customerId, $extId, $extType, $account) {
            // 获取无限期锁并自动释放...
            //申请产品体验
            $where = [
                Constant::DB_TABLE_ACT_ID => $actId, //活动id
                Constant::DB_TABLE_IP => $request->input(Constant::DB_TABLE_IP, ''),
            ];

            $data = Arr::collapse([$where, [
                            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, //会员id
                            Constant::DB_TABLE_EXT_TYPE => $this->extType, //关联模型 活动产品
                            Constant::DB_TABLE_EXT_ID => $extId, //关联id 活动产品id
                            $this->accoutKey => $account,
                            Constant::DB_TABLE_COUNTRY => $request->input(Constant::DB_TABLE_COUNTRY, ''),
            ]]);

            $where[] = [[Constant::DB_TABLE_CREATED_AT, '>=', Carbon::now()->rawFormat('Y-m-d 00:00:00')]];
            $activityApply = ActivityApplyService::existsOrFirst($storeId, '', $where); //获取产品数据
            if (!empty($activityApply)) {
                return Response::getDefaultResponseData(60012);
            }

            $applyId = ActivityApplyService::getModel($storeId)->insertGetId($data);

            $service = ActivityApplyService::getNamespaceClass();
            $method = 'handleJoinActEmail'; //邮件处理
            $parameters = [$storeId, $actId, $customerId, $account, $applyId, ActivityApplyService::getModelAlias(), 'join_email', $request->all()];
            FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters));

            return Response::getDefaultResponseData(1, null, ['apply_id' => $applyId]);
        });

        $defaultRs = Response::getDefaultResponseData(60008);
        $rs = $rs === false ? $defaultRs : $rs;

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 众测产品申请2.0接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function freeTestingProductApply(Request $request) {
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //申请产品的主键id
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商城id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //会员id

        $data = ActivityApplyService::freeTestingProductApply($storeId, $customerId, $id, $request->all());

        return Response::json(...Response::getResponseData($data));
    }

    /**
     * 根据申请数据的类型获取数据(目前用于评测2.0)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApply(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商城id
        $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //会员id
        $applyType = $request->input('apply_type', Constant::PARAMETER_INT_DEFAULT);
        $productId = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT);

        $productDetails = ActivityProductService::productDetails($storeId, $productId);
        $countries = data_get($productDetails, Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_ARRAY_DEFAULT);
        $data = ActivityApplyService::getApply($storeId, $customerId, $applyType);
        data_set($data, 'countries', $countries);

        return Response::json($data);
    }

    /**
     * 用户申请列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyList(Request $request) {
        $requestData = $request->all();
        unset($requestData[Constant::DB_TABLE_COUNTRY]);
        $select = [
            'aa.' . Constant::DB_TABLE_PRIMARY . ' as apply_id',
            'aa.' . Constant::DB_TABLE_CREATED_AT,
            'aa.' . Constant::AUDIT_STATUS,
            'ap.' . Constant::DB_TABLE_PRIMARY . ' as product_id',
            'ap.' . Constant::DB_TABLE_NAME,
            'ap.' . Constant::FILE_URL,
            'ap.' . Constant::DB_TABLE_IMG_URL,
            'ap.' . Constant::DB_TABLE_MB_IMG_URL,
            'ap.' . Constant::DB_TABLE_ASIN,
        ];
        $data = ActivityApplyService::applyList($requestData, true, true, $select);
        return Response::json($data);
    }
}
