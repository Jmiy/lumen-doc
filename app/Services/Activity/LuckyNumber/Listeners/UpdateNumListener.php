<?php

namespace App\Services\Activity\LuckyNumber\Listeners;

use App\Services\Activity\Listeners\BaseListener;
use App\Services\Activity\LuckyNumber\Events\UpdateNumEvent;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Services\ActivityGuessNumberService;
use App\Services\ActivityService;

class UpdateNumListener extends BaseListener
{

    /**
     * Handle the event.
     *
     * @param UpdateNumEvent $event
     * @return void
     */
    public function runHandle(UpdateNumEvent $event)
    {

        $eventData = data_get($event, 'data');

        $storeId = data_get($eventData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($eventData, Constant::DB_TABLE_ACT_ID);
        $customerId = data_get($eventData, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0);

        //获取活动剩余次数
        $actionData = FunctionHelper::getJobData(ActivityService::getNamespaceClass(), 'get', [], $eventData);
        $lotteryData = ActivityService::handleLimit($storeId, $actId, $customerId, $actionData);
        $lotteryNum = data_get($lotteryData, Constant::LOTTERY_NUM, 0);

        //发送次数变更邮件
        ActivityGuessNumberService::sendNotificationEmail($storeId, $actId, $customerId, ($lotteryNum > 0 ? $lotteryNum : 0));

    }

}
