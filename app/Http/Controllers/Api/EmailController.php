<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityService;
use App\Services\GameService;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Util\Cache\CacheManager as Cache;
use App\Services\CustomerService;
use App\Services\EmailService;
use App\Util\Constant;
use Illuminate\Support\Facades\Redis;

class EmailController extends Controller {

    /**
     * 发送激活邮件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(Request $request) {
        $storeId = $this->storeId; //商城id
        $account = $this->account; //会员账号
        $customerId = $this->customerId; //会员id
        $actId = $this->actId; //活动id

        //获取会员激活状态数据
        $customer = CustomerService::getCustomerActivateData($storeId, $customerId);
        $isactivate = data_get($customer, 'info.isactivate', 0);
        $requestLimit = data_get($request->all(), 'request_limit', 'times_by_seconds');

        if ($isactivate) {
            //已激活用户增加游戏次数
            GameService::updatePlayNums($storeId, $actId, $customerId, 'add_nums', 'activate');

            return Response::json([], 10000, 'Account has been activated');
        }

        //hommak官网一个账号一个自然日限制只能收5封激活邮件
        $activateSum = EmailService::getActivateSum($storeId, $account, 'activate');
        if (!$activateSum){
            return Response::json([], 10012, 'The activation email has been sent to the mailbox, please do not click repeatedly.');
        }

        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, ['request_limit', 'activate'], [$requestLimit ,'is_duplicate_send']);
        $timesBySeconds = data_get($activityConfigData, 'request_limit_times_by_seconds.value', Constant::PARAMETER_INT_DEFAULT);
        if (empty($timesBySeconds)) {

            //限制重发，一分钟内限制点击5次防止被刷，超过次数在按钮下方给出提示文案：Messages are limited,please wait for about 10 minutes before you try again。
            $key = 'activate:' . $customerId;
            $tags = config('cache.tags.email');
            $limit = Cache::tags($tags)->get($key);
            if ($limit > 5) {
                return Response::json([], 10001, 'Messages are limited,please wait for about 10 minutes before you try again.');
            }

            if (Cache::tags($tags)->has($key)) {
                Cache::tags($tags)->increment($key);
            } else {
                $ttl = 60; //缓存时间 单位秒
                Cache::tags($tags)->put($key, 2, $ttl);
            }

        } else {
            if (!static::requestLimit($storeId, $actId, $customerId, $timesBySeconds)) {
                return Response::json([], 10001, '');
            }
        }

//        //发送激活邮件
        $code = data_get($customer, 'info.code', '');
        $inviteCode = '';
        $country = data_get($customer, 'info.country', ''); //会员国家
        $orderno = ''; //订单
        $ip = $this->ip; //会员ip
        $createdAt = '';
        $extId = $customerId;
        $handleActivate = 1;
        $extData = [
            'handleActivate' => $handleActivate,
            'act_id' => $actId,
            'actId' => $actId,
            'activityConfigType' => 'email_activate',
            'extType' => 'Customer',
        ];

        $requestData = $request->all();
        if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
            data_set($extData, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
            $createdAt = $requestData[Constant::DB_TABLE_CREATED_AT];
        }

        if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
            data_set($extData, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
        }

        if (isset($requestData['bk'])) {
            data_set($extData, 'rowStatus', 1);
            data_set($extData, 'isSendEmail', false);
            data_set($extData, 'status', 1);
        }

        $ret = EmailService::sendActivateEmail($storeId, $customerId, $account, $code, $inviteCode, $country, $orderno, $ip, '会员激活', $createdAt, $extId, $handleActivate, $extData);
        if ($ret[Constant::RESPONSE_CODE_KEY] == 1 && static::isDuplicateSend($storeId, $actId, $customerId, $activityConfigData)) {
            return Response::json([], 200002, '');
        }

        return Response::json([], 1, 'The activation email has been sent, please check it in time.');
    }

    /**
     * 请求限制
     * @param int $storeId 官网id
     * @param int $actId 活动id
     * @param int $customerId 会员id
     * @param string $timesBySeconds N秒限制M次，传过来的配置N_M
     * @return bool
     */
    public static function requestLimit($storeId, $actId, $customerId, $timesBySeconds) {
        $key = "activate_limit_{$storeId}_{$actId}_{$customerId}";
        $explode = explode("_", $timesBySeconds);
        $seconds = data_get($explode, '0', 3); //默认3秒
        $times = data_get($explode, '1', 1);   //默认1次

        if (Redis::exists($key)) {
            if (Redis::incr($key) > $times) {
                return false;
            }
            return true;
        }

        Redis::setex($key, $seconds, 1);
        return true;
    }

    /**
     * 判断是否重复发送激活邮件
     * @param int $storeId 官网id
     * @param int $actId 活动id
     * @param int $customerId 会员id
     * @param array $activityConfigData 活动配置
     * @return bool
     */
    public static function isDuplicateSend($storeId, $actId, $customerId, $activityConfigData) {
        $isDuplicateSend = data_get($activityConfigData, 'activate_is_duplicate_send.value', Constant::PARAMETER_INT_DEFAULT);
        if ($isDuplicateSend) {
            //获取活动数据
            $activityData = ActivityService::getActivityData($storeId, $actId);

            //是否重复发送激活邮件标识key
            $key = "activate_duplicate_send_{$storeId}_{$actId}_{$customerId}";

            //计算过期时间,缓存至活动结束时间点
            $currentTime = time();
            $endAt = !empty($activityData[Constant::DB_TABLE_END_AT]) ? strtotime($activityData[Constant::DB_TABLE_END_AT]) : $currentTime;
            $expireTime = $endAt - $currentTime;

            //存在，表示之前已经发过
            if (Redis::exists($key)) {
                Redis::setex($key, $expireTime, 1);
                return true;
            }

            Redis::setex($key, $expireTime, 1);
            return false;
        }

        return false;
    }
}
