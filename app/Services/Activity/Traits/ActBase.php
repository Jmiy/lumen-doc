<?php

/**
 * Base trait
 * User: Jmiy
 * Date: 2020-09-03
 * Time: 09:27
 */

namespace App\Services\Activity\Traits;

use App\Services\ActivityService;
use App\Services\ChanceLogService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\Response;
use Carbon\Carbon;
use Illuminate\Support\Arr;

trait ActBase
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    public static $listen = [];

    public static function getCacheTags()
    {
        return '';
    }

    /**
     * 判断活动是否有效
     * @param $storeId 商城id
     * @param $actId 活动id
     * @return array
     */
    public static function isValidAct($storeId, $actId)
    {
        if (empty($storeId) || empty($actId)) {
            return Response::getDefaultResponseData(69998, null, []);//活动不存在
        }

        $actData = ActivityService::existsOrFirst($storeId, '', [Constant::DB_TABLE_PRIMARY => $actId], true, [Constant::DB_TABLE_END_AT]);
        if ($actData === null) {//如果活动不存在,并且是活动不存在就不可以执行的请求就直接提示
            return Response::getDefaultResponseData(69998, null, $actData);//活动不存在
        }

        $nowTime = Carbon::now()->toDateTimeString();
        $endAt = data_get($actData, Constant::DB_TABLE_END_AT, null);
        if ($endAt !== null && $nowTime > $endAt) {//活动已经结束，就直接返回
            return Response::getDefaultResponseData(69999, null, $actData);//活动过期
        }

        return Response::getDefaultResponseData(1, null, $actData);//活动有效
    }

    /**
     * 获取活动统计数据（包括 累计总次数 剩余次数  累计添加次数  已使用次数 等）
     * @param array $requestData
     * @return array $lotteryData
     */
    public static function getNums($requestData){

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID);
        $customerId = data_get($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY);//邀请者id

        $lotteryData = ActivityService::handleLimit($storeId, $actId, $customerId, FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'get', [], $requestData));
        $lotteryNum = data_get($lotteryData, 'lotteryNum', 0);
        $lotteryNum = $lotteryNum > 0 ? $lotteryNum : 0;
        data_set($lotteryData, 'lotteryNum', $lotteryNum);

        return $lotteryData;
    }

    /**
     * 更新活动次数 Jmiy_cen 2021-05-24 09:17 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $requestData 请求数据
     * @param string $type 类型
     * @param string $key key
     * @param int $num 更新数量
     * @return mix
     */
    public static function baseUpdateNum($storeId = 0, $actId = 0, $customerId = 0, $requestData = [], $type = 'add_nums', $key = Constant::ACTION_INVITE, $num = 1)
    {

        //次数更新
        $actionData = FunctionHelper::getJobData(static::getNamespaceClass(), 'increment', [$num], $requestData);
        $rs = ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);

        if (data_get($rs, Constant::RESPONSE_CODE_KEY) != 1) {
            return $rs;
        }

        //记录次数变更日志
        $chanceLog = [
            Constant::DB_TABLE_ACT_ID => $actId,
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
            Constant::DB_TABLE_TYPE => $type,
            Constant::DB_TABLE_KEY => $key,
            'num' => $num,
        ];
        ChanceLogService::getModel($storeId)->insert($chanceLog);

        return $rs;
    }

    /**
     * 获取完整类名
     * @return string
     */
    public static function getCompleteClassName($serviceProvider=[])
    {
        $class = explode('\\', get_called_class());
        unset($class[count($class)-1]);

        $serviceData = Arr::collapse([$class, (is_array($serviceProvider) ? $serviceProvider : [$serviceProvider])]);

        return implode('\\', array_filter($serviceData));
    }

    /**
     * 获取活动事件和监听器
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param string $type 事件名称
     * @param array $key 事件 监听器
     * @return array
     */
    public static function getEventListeners($storeId, $actId, $type = Constant::ACTION_INVITE, $key = [Constant::EVENTS, Constant::LISTENERS])
    {

        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, $type, $key);

        $listenData = [];
        if (empty($actConfig)) {
            return [];
        }

        foreach ($actConfig as $key => $item) {
            $event = data_get($item, Constant::DB_TABLE_TYPE);
            $values = explode(',', data_get($item, Constant::DB_TABLE_VALUE));

            $_event = $event . '_' . Constant::EVENTS;
            if ($key == $_event) {
                foreach ($values as $__event) {
                    $listenData[$event][Constant::EVENTS][] = static::getCompleteClassName(['Events', $__event]);//__NAMESPACE__ . '\\Events\\' . $__event;
                }
            }

            $listener = $event . '_' . Constant::LISTENERS;
            if ($key == $listener) {
                foreach ($values as $__listener) {
                    $listenData[$event][Constant::LISTENERS][] = static::getCompleteClassName(['Listeners', $__listener]);//__NAMESPACE__ . '\\Listeners\\' . $__listener;
                }
            }

        }

        $eventListeners = [];
        foreach ($listenData as $event => $eventsListeners) {
            $events = data_get($eventsListeners, Constant::EVENTS);
            $listeners = data_get($eventsListeners, Constant::LISTENERS);

            if (empty($events) || empty($listeners)) {
                continue;
            }


            foreach ($events as $event) {
                $eventListeners[$event] = $listeners;
            }
        }

        return $eventListeners;
    }

    /**
     * 设置活动 事件和监听器 此方法在 活动中间件 App\Http\Middleware\ActivityMiddleware::handle  调用
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @return array|mixed|null
     */
    public static function setEventListeners($storeId, $actId)
    {
        $isValidAct = static::isValidAct($storeId, $actId);
        if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
            return $isValidAct;
        }

        $listenData = static::getEventListeners($storeId, $actId, []);//,[Constant::EVENTS,Constant::listeners],[]
        if (empty($listenData)) {
            return Response::getDefaultResponseData(0);//活动不需要触发的事件
        }

        $events = app('events');

        foreach ($listenData as $event => $listeners) {

            if (!isset(static::$listen[$event])) {
                static::$listen[$event] = [];
            }

            foreach ($listeners as $listener) {
                if (!in_array($listener, static::$listen[$event])) {
                    static::$listen[$event][] = $listener;
                    $events->listen($event, $listener);
                }

            }
        }

        return Response::getDefaultResponseData(1);//活动 事件和监听器  设置成功
    }

    /**
     * 触发活动事件
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $requestData 请求参数
     * @param string $type 事件名称
     * @param array $key 事件 监听器
     * @return array|bool
     */
    public static function event($storeId, $actId, $requestData, $type = Constant::ACTION_INVITE, $key = [Constant::EVENTS, Constant::LISTENERS])
    {

        $isValidAct = static::isValidAct($storeId, $actId);
        if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
            return $isValidAct;
        }

        $listenData = static::getEventListeners($storeId, $actId, $type, $key);

        if (empty($listenData)) {
            return Response::getDefaultResponseData(0);//活动不需要触发的事件
        }

        //设置活动 事件和监听器 Jmiy_cen add 2021-06-01 09:53 (lucky numbers 活动  使用事件驱动 减少耦合)
        static::setEventListeners($storeId, $actId);

        $events = array_keys($listenData);
        foreach ($events as $event) {
            $event = '\\' . $event;
            event(new $event($requestData));
        }

        return true;

    }

    /**
     * 处理邀请
     * @param $requestData
     * @return mixed
     */
    public static function handleInvite($requestData){

    }

    /**
     * 处理关注
     * @param $requestData
     * @return mixed
     */
    public static function handleFollow($requestData){

    }

    public static function handle($storeId, $actId, $customerId, $extData = [])
    {
    }

}
