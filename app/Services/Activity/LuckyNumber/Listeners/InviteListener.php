<?php

namespace App\Services\Activity\LuckyNumber\Listeners;

use App\Services\Activity\Listeners\BaseListener;
use App\Services\Activity\LuckyNumber\Events\InviteEvent;
use App\Services\Activity\Factory;
use App\Util\Constant;

class InviteListener extends BaseListener
{

    /**
     * Handle the event.
     *
     * @param InviteEvent $event
     * @return void
     */
    public function runHandle(InviteEvent $event)
    {
        //__METHOD__, $event, $event->data
        //dd(__METHOD__, $event, $event->data,data_get($event,'data'));

        $eventData = data_get($event, 'data');

        $storeId = data_get($eventData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($eventData, Constant::DB_TABLE_ACT_ID);

        Factory::handle($storeId, $actId, 'handleInvite', [$eventData]);

    }

}
