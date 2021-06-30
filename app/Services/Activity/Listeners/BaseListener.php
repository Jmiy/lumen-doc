<?php

namespace App\Services\Activity\Listeners;

use App\Listeners\BaseListener as AppBaseListener;
use App\Services\Activity\Factory;
use App\Util\Constant;

class BaseListener extends AppBaseListener
{

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {

//        $eventData = data_get($event, 'data');
//
//        $storeId = data_get($eventData, Constant::DB_TABLE_STORE_ID);
//        $actId = data_get($eventData, Constant::DB_TABLE_ACT_ID);
//
//        //设置活动 事件和监听器 Jmiy_cen add 2021-06-01 09:53 (lucky numbers 活动  使用事件驱动 减少耦合)
//        Factory::handle($storeId, $actId, 'setEventListeners', [$storeId, $actId]);

        return $this->runHandle($event);

    }

}
