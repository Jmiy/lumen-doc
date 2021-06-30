<?php

namespace App\Http\Controllers\Api;

use App\Services\GameService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Response;
use App\Util\FunctionHelper;
use App\Models\Customer;
use App\Models\CustomerInfo;
use App\Models\InviteCode;
use App\Services\CustomerService;
use App\Services\OrderWarrantyService;
use App\Services\InviteService;
use App\Services\SubcribeService;
use App\Services\CreditService;
use App\Services\ExpService;
use App\Services\ActivityService;
use App\Services\DictStoreService;
use App\Services\DictService;
use App\Services\Store\ShopifyService;
use App\Services\LogService;
use App\Util\Constant;
use App\Services\CustomerInfoService;
use App\Services\ActivityCustomerService;
use App\Services\CustomerAddressService;
use App\Services\InviteCodeService;
use App\Services\SocialMediaLoginService;
use App\Services\Store\PlatformServiceManager;
use App\Services\Auth\AuthService;
use Illuminate\Support\Arr;
use App\Services\Activity\Factory;

class CustomerController extends Controller {

    /**
     * 检查是否注册
     * 说明：只用于检查会员是否已经注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checking(Request $request) {

        $platform = $request->input(Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY); //平台

        $isExists = PlatformServiceManager::handle($platform, 'Customer', 'customerQuery', [$this->storeId, '', $this->account]);

        $parameters = Response::getResponseData(Response::getDefaultResponseData(($isExists ? 10029 : 1), ($isExists ? 'customer  exists' : '')));

        return Response::json(...$parameters);
    }

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

        $orderController = new OrderWarrantyController($request);

        return $orderController->bindOrder($request, $storeId, $account, $orderno, $orderCountry);
    }

    /**
     * 会员注册、资料完善
     * 规则
     * 1：账号基本信息入库
     * 2：根据配置发放新人礼包 目前是10积分
     * 3：发送新人优惠券
     * 4：有邀请码，就添加邀请流水,更新邀请汇总数据
     * 5: 记录活动拉新的会员
     * 6: 如果是vt就发送激活邮件，激活成功以后奖励激活积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registered(Request $request) {

        $registerResponse = $request->input(Constant::REGISTER_RESPONSE, []);
        if ($registerResponse) {
            return $registerResponse;
        }

        $requestData = $request->all();
        $account = $this->account; //会员账号
        $storeId = $this->storeId; //商城id

        $storeCustomerId = data_get($requestData, Constant::DB_TABLE_STORE_CUSTOMER_ID, ''); //平台用户ID
        $createdAt = data_get($requestData, Constant::DB_TABLE_CREATED_AT, ''); //会员账号创建时间
        $source = data_get($requestData, Constant::DB_TABLE_SOURCE, 1); //会员来源

        $ip = $this->ip; //ip
        $country = data_get($requestData, Constant::DB_TABLE_COUNTRY, ''); //会员国家
        $firstName = data_get($requestData, Constant::DB_TABLE_FIRST_NAME, ''); //会员 first name
        $lastName = data_get($requestData, Constant::DB_TABLE_LAST_NAME, ''); //会员 last name
        $gender = data_get($requestData, 'gender', 0); //会员性别 0:未知  1:男 2:女 3:Private
        $brithday = data_get($requestData, 'brithday', ''); //会员生日
        $lastlogin = data_get($requestData, 'lastlogin', ''); //会员最近活跃时间
        $orderno = data_get($requestData, 'orderno', ''); //订单
        //根据配置 限制同一个ip注册，同一个ip一个自然日只能注册 15(mpow的翻一倍30)个账号 防止被刷，
        //超过次数在按钮下方给出提示文案：Messages are limited,please wait for about 1 day before you try again。
        //大型活动期间，其他官网是40，mpow官网80  （暂定）
        $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID, 0); //活动id
        $registeredIpLimit = 0; //注册时同一个ip注册账号限制 mpow:30 其他：15
        $inviteCode = data_get($requestData, Constant::DB_TABLE_INVITE_CODE, '');

        $ttl = 0;
        $keyData = [$ip];
        if ($actId) {
            $actData = ActivityService::getActData($storeId, $actId);
            if (data_get($actData, 'isValid') === true) {
                //获取活动配置数据
                $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, Constant::REGISTERED, [Constant::IP_LIMIT_KEY, 'invite_ip_limit', 'invite_ip_limit_ttl']);

                if (!empty($activityConfigData)) {
                    if (!empty($inviteCode)) {//如果是邀请注册
                        if (strlen($inviteCode) == 10 && in_array($storeId, [3])){//被邀请者来源写死
                            $source = 3;
                        }
                        $registeredIpLimit = data_get($activityConfigData, Constant::REGISTERED . '_invite_ip_limit.value', Constant::PARAMETER_STRING_DEFAULT); //整个活动期间，通过邀请码注册的限制
                        if ($registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT) {
                            $keyData[] = $actId;
                            $ttl = data_get($activityConfigData, Constant::REGISTERED . '_invite_ip_limit_ttl.value', $ttl); //整个活动期间，通过邀请码注册的限制
                        }
                    }

                    if (empty($registeredIpLimit)) {
                        $registeredIpLimit = data_get($activityConfigData, Constant::REGISTERED . '_ip_limit.value', Constant::PARAMETER_STRING_DEFAULT); //活动期间，同一个ip自然日的注册限制
                    }
                }
            }
        }

        $registeredConfig = [];//注册配置
        if (empty($registeredIpLimit)) {
            $distKey = [Constant::IP_LIMIT_KEY, 'invite_ip_limit', 'invite_ip_limit_ttl'];
            $extWhere = [
                Constant::DICT => [
                    Constant::DB_TABLE_DICT_KEY => $distKey,
                ],
                Constant::DICT_STORE => [
                    Constant::DB_TABLE_STORE_DICT_KEY => $distKey,
                ],
            ];
            $registeredConfig = CustomerService::getMergeConfig($storeId, Constant::SIGNUP_KEY, $extWhere);

            if (!empty($inviteCode)) {//如果是邀请注册
                if (strlen($inviteCode) == 10 && in_array($storeId, [3])){//被邀请者来源写死
                    $source = 3;
                }
                $registeredIpLimit = data_get($registeredConfig, 'invite_ip_limit', Constant::PARAMETER_STRING_DEFAULT); //通过邀请码注册的限制

                if ($registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT) {
                    $keyData[] = Constant::ACTION_INVITE;
                    if (in_array($storeId, [3])) {
                        $keyData[] = $inviteCode;
                    }
                    $ttl = data_get($registeredConfig, 'invite_ip_limit_ttl', $ttl); //通过邀请码注册的限制时间
                }
            }
        }

        if (empty($registeredIpLimit)) {//如果 $storeId 没有大型活动，就获取日常限制
            $registeredIpLimit = data_get($registeredConfig,Constant::IP_LIMIT_KEY,Constant::PARAMETER_STRING_DEFAULT);//同一个ip注册限制
        }

        $service = CustomerService::getNamespaceClass();
        $tag = CustomerService::getCacheTags();
        $key = implode(':', $keyData);
        if ($registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT) {//如果注册有限制，就判断是否超过限制
            $limit = CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'get', [$key]));
            if (!isset($requestData['bk']) && $limit > $registeredIpLimit) {//如果不是数据修复就判断
                return Response::getDefaultResponseData(10028);
            }
        }

        //1：账号基本信息入库
        $customerData = CustomerService::reg($storeId, $account, $storeCustomerId, $createdAt, $source, $country, $firstName, $lastName, $gender, $brithday, $orderno, $lastlogin, $ip, $requestData);
        $customerId = data_get($customerData, Constant::CUSTOMER_ID, 0);
        $responseData = data_get($customerData, Constant::RESPONSE_DATA, []);
        if (data_get($responseData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            return $responseData;
        }

        //2：根据配置发放新人礼包 目前是10积分
        CustomerService::regInit($storeId, $customerId, $requestData); //注册初始化 如：送积分和经验

        $isIpLimitWhitelist = false; //是否为白名单 true:是  false:否 默认:false
        if ($registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT) {//如果注册有限制，就更新限制缓存
            /*             * *****************根据注册ip白名单处理ip限制******************** */
            $ipLimitWhitelist = DictService::getByTypeAndKey(Constant::SIGNUP_KEY, 'ip_limit_whitelist', true); //获取注册ip白名单
            if (!empty($ipLimitWhitelist)) {
                $ipLimitWhitelist = explode(',', $ipLimitWhitelist);
                foreach ($ipLimitWhitelist as $value) {
                    if (false !== strpos($ip, $value)) {
                        $isIpLimitWhitelist = true;
                        break;
                    }
                }
            }

            if (!$isIpLimitWhitelist) {//如果不是白名单，就根据ip限制注册的会员数
                //更新同一个ip的自然日注册的会员数
                $isHas = CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'has', [$key]));
                if ($isHas) {
                    CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'increment', [$key]));
                } else {
                    $ttl = $ttl ? $ttl : (Carbon::parse(Carbon::now()->rawFormat('Y-m-d 23:59:59'))->timestamp) - (Carbon::now()->timestamp); //缓存时间 单位秒
                    CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'put', [$key, 2, $ttl]));
                }
            }
        }

        data_set($responseData, Constant::REGISTERED . '_' . Constant::IP_LIMIT_KEY, $key);
        data_set($responseData, Constant::IS_IP_LIMIT_WHITE_LIST, $isIpLimitWhitelist);
        data_set($responseData, Constant::REGISTERED_IP_LIMIT, $registeredIpLimit);

        return $responseData;
    }

    /**
     * 修改密码
     * @param Request $request
     * @return boolean
     */
    private function updatePassword(Request $request) {
        return AuthService::updatePassword($request->all());
    }

    /**
     * 处理注册
     * 1：更新 first_name last_name 防止  first_name last_name 丢失
     * 2：新人订阅 发送新人优惠券
     * 3：如果是被邀请者注册，就根据 被邀请者注册是否发送激活邮件配置 发送激活邮件，激活成功以后奖励激活积分; 并且添加邀请流水,更新邀请汇总数据
     * 4：根据配置发送激活邮件
     * 5：如果有订单，就绑定订单
     * @param Request $request
     * @return array 订单绑定结果
     */
    private function handleSignup(Request $request) {

        $requestData = $request->all();

        $storeId = $this->storeId; //商城id
        $account = $this->account; //会员账号
        $customerId = $this->customerId; //会员id
        $createdAt = data_get($requestData, Constant::DB_TABLE_CREATED_AT, ''); //会员账号创建时间
        $updatedAt = data_get($requestData, Constant::DB_TABLE_UPDATED_AT, ''); //会员账号更新时间
        $ip = $this->ip; //ip
        $firstName = $request->input(Constant::DB_TABLE_FIRST_NAME, ''); //会员 first name
        $lastName = $request->input(Constant::DB_TABLE_LAST_NAME, ''); //会员 last name
        //更新会员密码 防止  password 丢失
        $this->updatePassword($request);

        /*         * ***************** 1：更新 first_name last_name 防止  first_name last_name 丢失 *********************************** */
        $updateData = [];
        if (!empty($firstName)) {
            $updateData[Constant::DB_TABLE_FIRST_NAME] = $firstName;
        }
        if (!empty($lastName)) {
            $updateData[Constant::DB_TABLE_LAST_NAME] = $lastName;
        }

        if ($updateData) {
            if ($updatedAt) {
                data_set($updateData, Constant::DB_TABLE_UPDATED_AT, $updatedAt);
            }
            CustomerService::edit($storeId, $customerId, $updateData, $requestData);
        }

        /*         * ***************** 2：新人订阅 发送Coupon *********************************** */
        $country = data_get($requestData, Constant::DB_TABLE_COUNTRY, ''); //会员国家
        $group = 'customer';
        $remark = '注册且默认订阅';
        $extData = [
            'actId' => data_get($requestData, Constant::DB_TABLE_ACT_ID, 1), //活动id
            Constant::DB_TABLE_SOURCE => data_get($requestData, Constant::DB_TABLE_SOURCE, 1), //会员来源,
            Constant::DB_TABLE_ACTION => data_get($requestData, 'bk', Constant::SIGNUP_KEY), //会员行为
            Constant::DB_TABLE_ACCEPTS_MARKETING => data_get($requestData, Constant::DB_TABLE_ACCEPTS_MARKETING, 0), //是否订阅 1：是 0：否
        ];
        if ($createdAt) {
            data_set($extData, Constant::DB_TABLE_CREATED_AT, $createdAt);
        }

        if ($updatedAt) {
            data_set($extData, Constant::DB_TABLE_UPDATED_AT, $updatedAt);
        }

        if (isset($requestData['bk'])) {
            data_set($extData, 'bk', $requestData['bk']);
        }
        SubcribeService::handle($storeId, $account, $country, $firstName, $lastName, $group, $ip, $remark, $createdAt, $extData);

        /*         * ***************** 3：如果是被邀请者注册，就根据 被邀请者注册是否发送激活邮件配置 发送激活邮件，激活成功以后奖励激活积分; 并且添加邀请流水,更新邀请汇总数据 *********************************** */
        if (!isset($requestData['bk'])) {//如果不是会恢复数据，就执行发送激活邮件
            $inviteCode = data_get($requestData, Constant::DB_TABLE_INVITE_CODE, '');
            $inviteActivateEmail = 0; //被邀请者注册是否发送激活邮件 1:是  0:否 默认:0
            if ($inviteCode) {//如果是被邀请者注册，就添加邀请流水,更新邀请汇总数据
                $inviteData = InviteService::handle($inviteCode, $customerId, $storeId, $createdAt, $updatedAt, $this->actId, $requestData);
                if (!empty($inviteData) && is_array($inviteData)) {
                    $ext_id = data_get($inviteData, Constant::INVITE_DATA . Constant::LINKER . Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::DB_TABLE_PRIMARY, 0);
                    if ($ext_id) {//如果添加邀请流水成功，就根据配置 给邀请者添加相应的积分和经验，更新用户等级
                        $action = Constant::ACTION_INVITE;
                        $type = Constant::SIGNUP_KEY;
                        $confKey = 'invite_credit';
                        $expType = Constant::SIGNUP_KEY;
                        $expConfKey = 'invite_exp';
                        $inviterId = data_get($inviteData, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //邀请者id

                        $actId = data_get($requestData, 'related_act_id', data_get($requestData, Constant::DB_TABLE_ACT_ID, 0)); //优先取活动关联id，如果活动关联id不存在就获取 活动id

                        $actCreditLogParameters = CreditService::getHandleLogParameters($storeId, $actId, null, $inviterId, Constant::REGISTERED, $action, 'credit');
                        $type = data_get($actCreditLogParameters, Constant::DB_TABLE_TYPE);
                        $confKey = data_get($actCreditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分

                        $actExpLogParameters = ExpService::getHandleLogParameters($storeId, $actId, $expConfKey, $inviterId, Constant::SIGNUP_KEY, $action, 'exp');
                        $expType = data_get($actExpLogParameters, Constant::DB_TABLE_TYPE);;
                        $expConfKey = data_get($actExpLogParameters, Constant::DB_TABLE_VALUE);//邀请功能经验

                        if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分if (null !== $type) {//如果 $actId 对应的活动没有限制邀请积分，就根据常规配置限制邀请积分
                            $creditLogParameters = CreditService::getHandleLogParameters($storeId, 0, $confKey, $inviterId, Constant::SIGNUP_KEY, $action, 'credit');
                            $type = data_get($creditLogParameters, Constant::DB_TABLE_TYPE);
                            $confKey = data_get($creditLogParameters, Constant::DB_TABLE_VALUE);//邀请功能积分
                        }

                        if (null !== $expType) {//如果 $actId 对应的活动没有限制邀请经验，就根据常规配置限制邀请经验
                            $expLogParameters = ExpService::getHandleLogParameters($storeId, 0, $expConfKey, $inviterId, Constant::SIGNUP_KEY, $action, 'exp');
                            $expType = data_get($expLogParameters, Constant::DB_TABLE_TYPE);;
                            $expConfKey = data_get($expLogParameters, Constant::DB_TABLE_VALUE);//邀请功能经验
                        }

                        CreditService::handleVip($storeId, $inviterId, $action, $type, $confKey, $inviteData, $expType, $expConfKey);

                        //触发邀请事件
                        data_set($requestData, Constant::EVENT_DATA, $inviteData);
                        $parameters = [$storeId, $actId, 'event', [$storeId, $actId, $requestData, Constant::ACTION_INVITE, [Constant::EVENTS, Constant::LISTENERS]]];
                        FunctionHelper::pushQueue(FunctionHelper::getJobData(Factory::getNamespaceClass(), 'handle', $parameters));

                    }
                }

                $inviteActivateEmail = DictStoreService::getByTypeAndKey($storeId, Constant::SIGNUP_KEY, 'invite_activate_email', true); //被邀请者注册是否发送激活邮件 1:是  0:否 默认:0
            }

            //4：根据配置发送激活邮件
            $activateEmailHandle = $request->input(Constant::ACTIVATE_EMAIL_HANDLE, []);
            $activateEmailHandleCode = data_get($activateEmailHandle, Constant::RESPONSE_CODE_KEY, 0);
            if ($activateEmailHandleCode != 1) {//如果激活邮件没有发送过，就根据配置发送激活邮件
                $activateEmail = DictStoreService::getByTypeAndKey($storeId, Constant::SIGNUP_KEY, 'activate_email', true); //注册是否发送激活邮件 1:是  0:否 默认:0
                if ($activateEmail || $inviteActivateEmail) {
                    $emailHandle = new EmailController($request);
                    $emailHandle->activate($request);
                }
            }
        }

        /*         * ***************** 5: 如果有订单，就绑定订单 *********************************** */
        $orderBind = $this->bindOrder($request);

        //修复订单延保的绑定关系
        $orderWhere = [
            Constant::DB_TABLE_STORE_ID => $storeId,
            Constant::DB_TABLE_ACCOUNT => $account,
            Constant::DB_TABLE_CUSTOMER_PRIMARY => Constant::ORDER_STATUS_DEFAULT,
        ];
        $orderData = [
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
            Constant::DB_TABLE_ORDER_STATUS => Constant::ORDER_STATUS_DEFAULT,
        ];
        OrderWarrantyService::update($storeId, $orderWhere, $orderData);

        return $orderBind;
    }

    /**
     * 会员注册、资料完善
     * 规则
     * 1：账号基本信息入库
     * 2：新人礼包 根据配置发放 目前是10积分
     * 3：发送新人优惠券 如果有订单号  就绑定订单
     * 4：有邀请码，就添加邀请流水,更新邀请汇总数据
     * 5: 如果是vt就发送激活邮件，激活成功以后奖励激活积分
     * 6: 如果有订单，就绑定订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signup(Request $request) {//ServerRequestInterface $request
        $data = $this->registered($request);

        if ($data[Constant::RESPONSE_CODE_KEY] == 1) {//如果注册成功就绑定订单
            $data[Constant::RESPONSE_DATA_KEY]['orderBind'] = $this->handleSignup($request);
        }

        return Response::json($data[Constant::RESPONSE_DATA_KEY], $data[Constant::RESPONSE_CODE_KEY], $data[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * 活动注册
     * 说明：独立一个活动注册接口方便，方便以后调整注册逻辑
     * 规则
     * 1：账号基本信息入库
     * 2：新人礼包 根据配置发放 目前是10积分
     * 3：发送新人优惠券 如果有订单号  就绑定订单
     * 4：有邀请码，就添加邀请流水,更新邀请汇总数据
     * 5: 如果是vt就发送激活邮件，激活成功以后奖励激活积分
     * 6: 如果有订单，就绑定订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function actReg(Request $request) {
        return $this->signup($request);
    }

    /**
     * 编辑会员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $requestData = $request->all();

        $customer = CustomerService::customerExists($this->storeId, 0, $this->account, 0, true);
        if (empty($customer)) {//不存在，就去注册
            return $this->signup($request);
        }

        $customerId = $customer->customer_id;
        data_set($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY, $customerId);
        if ($request->filled(Constant::DB_TABLE_STORE_CUSTOMER_ID)) {//绑定平台用户ID
            Customer::where(Constant::DB_TABLE_CUSTOMER_PRIMARY, $customerId)->update([Constant::DB_TABLE_STORE_CUSTOMER_ID => $request->input(Constant::DB_TABLE_STORE_CUSTOMER_ID, '')]);
        }

        data_set($requestData, 'edit_at', Carbon::now()->toDateTimeString());
        if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
            data_set($requestData, 'edit_at', $requestData[Constant::DB_TABLE_CREATED_AT]);
        }

        CustomerService::apiEdit($customerId, $requestData); //修改基本资料
        //账号编辑
        $editAccountRet = $this->editAccount($request);
        $_result = $editAccountRet->getData(true);
        if (data_get($_result, Constant::RESPONSE_CODE_KEY) != 1) {
            return Response::json([], data_get($_result, Constant::RESPONSE_CODE_KEY), data_get($_result, Constant::RESPONSE_MSG_KEY));
        }

        //订单绑定
        $orderRet = $this->bindOrder($request);
        if ($orderRet[Constant::RESPONSE_CODE_KEY] != 1) {
            return Response::json([], '10020', $orderRet[Constant::RESPONSE_MSG_KEY]);
        }

        return Response::json();
    }

    /**
     * 获取会员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {
        $storeId = $this->storeId;
        $customerId = $this->customerId;
        $data = CustomerService::getCustomer($storeId, $customerId);
        if (!$data) {
            return Response::json([], 10019, 'customer not exists');
        }

        return Response::json($data);
    }

    /**
     * 会员行为记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function record(Request $request) {
        return Response::json();
    }

    /**
     * 会员激活
     * 规则：
     * 1：记录激活标识
     * 2：有邀请码并且是vt，就给邀请人 5积分 和 5经验值
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(Request $request) {

        $requestData = $request->all();
        $storeId = $this->storeId;

        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0); //会员id

        /*         * *************清空会员激活缓存************ */
        $tags = config('cache.tags.customer', ['{customer}']);
        $cacheKey = 'activate:' . $customerId;
        Cache::tags($tags)->forget($cacheKey);

        $customerInfo = CustomerInfo::select(['isactivate', Constant::RESPONSE_CODE_KEY, Constant::DB_TABLE_COUNTRY, Constant::DB_TABLE_FIRST_NAME, Constant::DB_TABLE_LAST_NAME])->where([Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId])->first();

        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0);
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, 'registered', 'is_need_activate');
        $url = data_get($activityConfigData, 'registered_is_need_activate.landing_url', DictStoreService::getByTypeAndKey($storeId, Constant::ACTION_ACTIVATE, 'back_url', true));

        if (empty($customerInfo)) {
            return $request->expectsJson() ? Response::json([], 10019, 'customer not exists') : redirect($url);
        }

        if ($customerInfo->isactivate == 1) {
            return $request->expectsJson() ? Response::json([], 10025, 'code is activated do not repetat activate') : redirect($url);
        }

        if (data_get($requestData, Constant::RESPONSE_CODE_KEY, '') != $customerInfo->code) {
            return $request->expectsJson() ? Response::json([], 10026, 'code incorrect ' . '==>' . data_get($requestData, Constant::RESPONSE_CODE_KEY, '') . '===>' . $customerInfo->code) : redirect($url);
        }

        $ret = CustomerService::edit($storeId, $customerId, ['isactivate' => 1], $requestData);
        if (!$ret) {
            return $request->expectsJson() ? Response::json([], 10016, 'modify false') : redirect($url);
        }

        //根据配置 给当前激活的会员添加  激活积分和激活经验
        $data = FunctionHelper::getHistoryData([
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                    Constant::DB_TABLE_VALUE => 0,
                    'add_type' => 1,
                    Constant::DB_TABLE_ACTION => Constant::ACTION_ACTIVATE, //积分来源
                    Constant::DB_TABLE_EXT_ID => $customerId, //关联ID
                    'ext_type' => 'Customer', //关联模型
                        ], [Constant::DB_TABLE_STORE_ID => $storeId]);

        $credit = DictStoreService::getByTypeAndKey($storeId, Constant::ACTION_ACTIVATE, 'credit', true); //激活积分
        if ($credit) {
            $data[Constant::DB_TABLE_VALUE] = $credit;
            CreditService::handle($data); //记录积分流水
        }

        $exp = DictStoreService::getByTypeAndKey($storeId, Constant::ACTION_ACTIVATE, 'exp', true); //激活经验
        if ($exp) {
            $data[Constant::DB_TABLE_VALUE] = $exp;
            ExpService::handle($data); //记录经验流水
        }

        $inviteCode = data_get($requestData, Constant::DB_TABLE_INVITE_CODE, '');
        if ($inviteCode) {//如果有邀请码并且是vt，就给邀请人 5积分 和 5经验值
            $_customer_id = InviteCode::where([Constant::DB_TABLE_INVITE_CODE => $inviteCode])->limit(1)->value(Constant::DB_TABLE_CUSTOMER_PRIMARY); //获取拥有 $invite_code 的客户id
            if (empty($_customer_id)) {
                return $request->expectsJson() ? Response::json([], 10016, 'Inviter does not exist') : redirect($url);
            }

            //如果被邀请人注册成功，就给邀请人 5积分 和 5经验值
            $data = FunctionHelper::getHistoryData([
                        Constant::DB_TABLE_CUSTOMER_PRIMARY => $_customer_id,
                        Constant::DB_TABLE_VALUE => 0,
                        'add_type' => 1,
                        Constant::DB_TABLE_ACTION => Constant::ACTION_INVITE,
                        'ext_type' => 'invite_historys',
                        Constant::DB_TABLE_EXT_ID => data_get($requestData, Constant::DB_TABLE_EXT_ID, 0),
                            ], [Constant::DB_TABLE_STORE_ID => $storeId]);

            $credit = DictStoreService::getByTypeAndKey($storeId, Constant::ACTION_INVITE, 'credit', true);
            if ($credit) {
                $data[Constant::DB_TABLE_VALUE] = $credit;
                CreditService::handle($data); //记录积分流水
            }

            $exp = DictStoreService::getByTypeAndKey($storeId, Constant::ACTION_INVITE, 'exp', true);
            if ($exp) {
                $data[Constant::DB_TABLE_VALUE] = $exp;
                ExpService::handle($data); //记录经验流水
            }
        }

        //激活加游戏次数
        GameService::updatePlayNums($storeId, $actId, $customerId, 'add_nums', 'activate');

        return $request->expectsJson() ? Response::json() : redirect($url); //view($siteData->theme . $view); //返回上一页 back()->withInput()->with([])
    }

    /**
     * 创建会员
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCustomer(Request $request) {

        $storeId = $this->storeId;
        $account = $this->account;
        $platform = $request->input(Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY); //平台
        $password = $request->input(Constant::DB_TABLE_PASSWORD, ''); //密码
        $action = $request->input(Constant::DB_TABLE_ACTION, 'login'); //行为：register：注册  order_bind：订单绑定  login：登录
        $orderno = $request->input('orderno', ''); //订单号

        $cacheKeyData = [__FUNCTION__, $action, $storeId, $account];
        $tag = CustomerService::getCacheTags();
        $service = CustomerService::getNamespaceClass();

        $parameters = [
            function () use($request, $storeId, $account, $platform, $password, $action, $orderno) {
                // 获取无限期锁并自动释放...
                //检查该订单是否绑定过
                if ($orderno) {
                    $isExists = OrderWarrantyService::checkExists($storeId, 0, Constant::DB_TABLE_PLATFORM, $orderno);
                    if ($isExists) {
                        return Response::getDefaultResponseData(30000); //, 'The order number you submitted is duplicate,please try another one.'
                    }
                }

                $requestData = $request->all();

                $acceptsMarketing = data_get($requestData, Constant::DB_TABLE_ACCEPTS_MARKETING, 0) ? true : false;
                $firstName = $request->input(Constant::DB_TABLE_FIRST_NAME, ''); //会员 first name
                $lastName = $request->input(Constant::DB_TABLE_LAST_NAME, ''); //会员 last name
                $phone = $request->input('phone', ''); //会员 phone
                $request->offsetSet(Constant::ORDER_PLATFORM, Constant::PLATFORM_AMAZON);

                $createCustomerRs = [];
                if (!isset($requestData['bk'])) {//如果不是恢复数据，就执行创建账号
                    $createCustomerRs = CustomerService::createCustomer($platform, $storeId, $account, $password, $action, $acceptsMarketing, $firstName, $lastName, $phone, $requestData);
                    if (data_get($createCustomerRs, Constant::RESPONSE_CODE_KEY, 0) != 1) {
                        return $createCustomerRs;
                    }
                }

                $orderBind = [];
                if ($action == 'register') {//如果注册成功，就执行以下注册处理
                    $orderBind = $this->handleSignup($request);
                }

                if (empty($orderBind) && $orderno) {
                    //4: 如果有订单，就绑定订单
                    $orderBind = $this->bindOrder($request);
                }

                if ($orderBind && $orderBind[Constant::RESPONSE_CODE_KEY] != 1) {
                    return Response::getDefaultResponseData(data_get($orderBind, Constant::RESPONSE_CODE_KEY, 1), data_get($orderBind, Constant::RESPONSE_MSG_KEY, ''));
                }

                data_set($createCustomerRs, Constant::RESPONSE_DATA_KEY.Constant::LINKER.'order_bind', data_get($orderBind, Constant::RESPONSE_DATA_KEY, Constant::PARAMETER_ARRAY_DEFAULT));

                return $createCustomerRs;
            }
        ];
        $rs = CustomerService::handleLock($cacheKeyData, $parameters);

        $rs = $rs === false ? Response::getDefaultResponseData(10003) : $rs;

        $code = data_get($rs, Constant::RESPONSE_CODE_KEY, 1);

        $requestMark = $request->input('request_mark', Constant::PARAMETER_STRING_DEFAULT); //请求标识
        $customer = $request->user();
        $customerRequestMark = data_get($customer, Constant::DB_TABLE_CREATED_MARK, Constant::PARAMETER_STRING_DEFAULT);
        if ($code != 1 && $requestMark && $customerRequestMark && $requestMark == $customerRequestMark) {//如果整个注册过程有失败的环节，就设置账号无效，保证数据的一致性
            $_whereCustomerId = [
                Constant::DB_TABLE_CUSTOMER_PRIMARY => data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1),
            ];

            CustomerService::delete($storeId, $_whereCustomerId); //用户基本信息
            CustomerInfoService::delete($storeId, $_whereCustomerId); //用户详情
            ActivityCustomerService::delete($storeId, $_whereCustomerId); //活动用户流水
            CustomerAddressService::delete($storeId, $_whereCustomerId); //用户地址
            InviteCodeService::delete($storeId, $_whereCustomerId); //邀请码
            CreditService::delete($storeId, $_whereCustomerId); //积分流水
            ExpService::delete($storeId, $_whereCustomerId); //经验流水
            SocialMediaLoginService::delete($storeId, $_whereCustomerId); //社媒信息
            //清空缓存
            $headers = [
                'Content-Type: application/json; charset=utf-8', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
                'X-Requested-With: XMLHttpRequest', //告诉服务器，当前请求是 ajax 请求
            ];

            $curlOptions = [
                CURLOPT_CONNECTTIMEOUT_MS => 1000 * 100,
                CURLOPT_TIMEOUT_MS => 1000 * 100,
            ];
            $url = ('production' == config('app.env', 'production')) ? 'https://brand-api.patozon.net/api/shop/clear' : 'http://127.0.0.1:8006/api/shop/clear';
            \App\Util\Curl::request($url, $headers, $curlOptions, [], 'GET');

            $registerResponse = $request->input(Constant::REGISTER_RESPONSE, Constant::PARAMETER_ARRAY_DEFAULT); //注册响应数据
            $isIpLimitWhitelist = data_get($registerResponse, Constant::IS_IP_LIMIT_WHITE_LIST, false); //是否白名单 true：是 false：否
            $key = data_get($registerResponse, Constant::REGISTERED . '_' . Constant::IP_LIMIT_KEY);
            $registeredIpLimit = data_get($registerResponse, Constant::REGISTERED_IP_LIMIT, Constant::PARAMETER_STRING_DEFAULT);
            if ($registerResponse && $registeredIpLimit !== Constant::PARAMETER_STRING_DEFAULT && !$isIpLimitWhitelist && !empty($key)) {////如果注册有限制，并且不是白名单，就还原限制缓存
                //更新同一个ip的自然日注册的会员数
                $isHas = CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'has', [$key]));
                if ($isHas) {
                    CustomerService::handleCache($tag, FunctionHelper::getJobData($service, 'decrement', [$key]));
                }
            }
        }

        return Response::json(...Response::getResponseData($rs));
    }

    /**
     * 获取邀请者的会员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInviteCustomer(Request $request) {

        $inviteCode = $request->input(Constant::DB_TABLE_INVITE_CODE, '');

        $data = InviteService::getCustomerData($inviteCode);

        return Response::json(['account' => FunctionHelper::handleAccount(data_get($data, 'customer.account', ''))]);
    }

    /**
     * 测试创建账户回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountCallback(Request $request) {

        $post = $request->all();

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 8);
        $account = data_get($post, 'customer.email', data_get($post, 'email', ''));

        LogService::addSystemLog('info', 'shopify', 'account_callback', $account, ['post' => $post, 'header' => $request->headers->all()], 'account_callback_data');

        $key = "X-Shopify-Hmac-Sha256";
        $hmac = $request->headers->get($key, '');
        $data = file_get_contents('php://input');
        $verify = ShopifyService::verifyWebhook($storeId, $data, $hmac);
        $post['X-Shopify-Hmac-Sha256'] = $hmac;
        if (!$verify) {
            LogService::addSystemLog('info', 'shopify', 'account_verify', $account, ['post' => $data, 'header' => $request->headers->all()], 'verify false');
            return Response::json([], '10025', 'verify false');
        }
    }

    /**
     * 获取邀请者的会员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvatar(Request $request) {

        $customerSelect = [
            Constant::AVATAR
        ];
        $data = CustomerInfoService::getData($this->storeId, $this->customerId, $customerSelect);

        return Response::json(['url' => data_get($data, Constant::AVATAR, '')]);
    }

    /**
     * 编辑账号
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editAccount(Request $request) {
        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT);
        $account = $request->input($this->accoutKey, Constant::PARAMETER_STRING_DEFAULT);
        $newAccount = $request->input('new_account', Constant::PARAMETER_STRING_DEFAULT);
        $platform = $request->input(Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_SERVICE_SHOPIFY);
        if (empty($storeId) || empty($account) || empty($platform)) {
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(9999999999)));
        }

        $data = CustomerService::editAccount($storeId, $account, $newAccount, $platform);

        return Response::json(...Response::getResponseData($data));
    }

}
