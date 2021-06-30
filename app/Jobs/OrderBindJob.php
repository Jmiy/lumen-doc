<?php

namespace App\Jobs;

use App\Services\OrderWarrantyService;
use App\Services\LogService;

class OrderBindJob extends Job {

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try {
            //处理订单绑定业务
            $parameters = $this->data;
            OrderWarrantyService::handleBind(...$parameters);
        } catch (\Exception $exc) {
            LogService::addSystemLog('error', 'order_bind_job', 'handleBind', '订单绑定出错', ['exc' => $exc->getTraceAsString()]); //添加系统日志
        }
    }

}
