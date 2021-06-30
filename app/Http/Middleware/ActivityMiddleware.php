<?php

namespace App\Http\Middleware;

use App\Services\Activity\Factory;
use Closure;
use Carbon\Carbon;
use App\Util\Response;
use App\Services\ActivityService;
use App\Services\CustomerService;
use App\Services\EmailService;
use App\Util\Constant;

class ActivityMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $storeId = $request->input('store_id', 0); //商城id
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, 0); //获取有效的活动id ActivityService::getValidActIds($storeId)
        $request->offsetSet(Constant::DB_TABLE_ACT_ID, $actId);
        if (empty($actId)) {
            return $next($request);
        }

        $currentRouteData = $request->route();
        $actEndCanExecuteRouteData = ['customer_signup', 'customer_actReg', 'customer_createCustomer', 'socialMedia_createCustomer', 'socialMedia_passwordModify']; //活动结束还可以执行的请求
        $currentRoute = data_get($currentRouteData, '1.as', ''); //当前路由名称
        $actData = ActivityService::getModel($storeId)->where(['id' => $actId])->select(['end_at'])->first();
        if ($actData === null) {//如果活动不存在,并且是活动不存在就不可以执行的请求就直接提示
            if (in_array($currentRoute, $actEndCanExecuteRouteData)) {//如果是活动不存在还可以执行的请求就直接执行请求
                return $next($request);
            }
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(69998)));
        }

        $nowTime = Carbon::now()->toDateTimeString();
        $endAt = data_get($actData, 'end_at', null);
        if ($endAt !== null && $nowTime > $endAt) {
            if (in_array($currentRoute, $actEndCanExecuteRouteData)) {//如果是活动结束还可以执行的请求就直接执行请求
                return $next($request);
            }
            return Response::json(...Response::getResponseData(Response::getDefaultResponseData(69999)));
        }

        $account = $request->input('account', ''); //会员账号
        $customerId = $request->input('customer_id', 0); //会员id
        //获取活动配置数据
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, ['registered', 'need_activate'], ['is_need_activate', 'customer']);
        if (empty($activityConfigData)) {
            return $next($request);
        }

        $activateEmailHandleKey = Constant::ACTIVATE_EMAIL_HANDLE;
        $activateEmailHandle = $request->input($activateEmailHandleKey, []);
        $activateEmailHandleCode = data_get($activateEmailHandle, 'code', 0);
        if (data_get($activityConfigData, 'registered_is_need_activate.value', 0) && $activateEmailHandleCode != 1) {//如果当前活动需要激活，就判断用户是否激活
            //获取会员激活状态数据
            $customer = CustomerService::getCustomerActivateData($storeId, $customerId);
            if (empty(data_get($customer, 'info.isactivate', 0))) {//如果用户未激活，就发送激活邮件
                $needActivateCustomer = data_get($activityConfigData, 'need_activate_customer.value', 0); //需要激活的用户类型 0:全部用户 1:新用户 2:老用户
                $isSendActivateEmail = true; //是否发送激活邮件 true:发送 false:不发送
                if ($needActivateCustomer > 0) {
                    $activityData = ActivityService::getActivityData($storeId, $actId);
                    $activityStartTime = data_get($activityData, 'start_at', data_get($activityData, Constant::DB_TABLE_CREATED_AT, ''));

                    $customer = $request->user();
                    $customerRegTime = data_get($customer, 'ctime', '');
                    switch ($needActivateCustomer) {
                        case 1://新用户
                            if ($activityStartTime > $customerRegTime) {//如果当前会员不是新注册用户，就可以直接参加活动
                                $isSendActivateEmail = false;
                            }

                            break;

                        case 2://老用户
                            if ($customerRegTime > $activityStartTime) {//如果当前会员是新注册用户，就可以直接参加活动
                                $isSendActivateEmail = false;
                            }

                            break;

                        default:
                            break;
                    }
                }

                if (!$isSendActivateEmail) {//如果是老用户只发激活邮件，不强制激活
                    return $next($request);
                }

                $country = $request->input('country', ''); //会员国家
                //判断是否已经发送过激活邮件，防止重复发送激活邮件
                $where = [
                    'store_id' => $storeId,
                    'group' => 'customer',
                    'type' => 'activate',
                    'country' => $country,
                    'to_email' => $account,
                ];

                $env = config('app.env', 'production');
                if ($env != 'production') {//如果不是正式环境  测试人员的账号可以无限发送激活邮件，其他只能发送一次
                    if (in_array($account, ['alexhong465@gmail.com', 'sunnyhong1993@yahoo.com'])) {//如果是测试人员账号，就不限制激活邮件的发送
                        $isExists = false;
                    } else {
                        $isExists = EmailService::exists($storeId, $country, $where);
                    }
                } else {
                    $isExists = EmailService::exists($storeId, $country, $where);
                }

                if (!$isExists) {
                    //发送激活邮件
                    $code = data_get($customer, 'info.code', '');
                    $inviteCode = '';
                    $orderno = ''; //订单
                    $ip = $request->input('ip', ''); //会员ip
                    $createdAt = '';
                    $extId = $customerId;
                    $handleActivate = 1;
                    $extData = [
                        Constant::DB_TABLE_ACT_ID => $actId,
                        'actId' => $actId,
                        'activityConfigType' => 'email_activate',
                        'extType' => 'Customer',
                    ];

                    $requestData = $request->all();
                    if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
                        data_set($extData, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
                    }

                    if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
                        data_set($extData, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
                    }

                    if (isset($requestData['bk'])) {
                        data_set($extData, 'rowStatus', 1);
                        data_set($extData, 'isSendEmail', false);
                    }
                    $rs = EmailService::sendActivateEmail($storeId, $customerId, $account, $code, $inviteCode, $country, $orderno, $ip, '会员激活', $createdAt, $extId, $handleActivate, $extData);
                    $request->offsetSet($activateEmailHandleKey, $rs);
                }

                if (!in_array($currentRoute, $actEndCanExecuteRouteData)) {//如果不是注册或者登陆，就提示用户激活
                    return Response::json([], 60001, data_get($activityConfigData, 'registered_is_need_activate.msg', ''));
                }
            }
        }

        return $next($request);
    }

}
