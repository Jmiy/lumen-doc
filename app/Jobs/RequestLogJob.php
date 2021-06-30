<?php

namespace App\Jobs;

use App\Services\CustomerInfoService;
use App\Util\Constant;
use App\Services\LogService;
use App\Util\FunctionHelper;

class RequestLogJob extends Job
{

    public $logData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($logData)
    {
        $this->logData = $logData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $handleData = data_get($this->logData, 'handleData', []);
            $storeId = data_get($this->logData, 'storeId', 0); //商城id

            //设置时区
            FunctionHelper::setTimezone($storeId);

            foreach ($handleData as $item) {
                $service = data_get($item, Constant::SERVICE_KEY);
                $method = data_get($item, Constant::METHOD_KEY);
                $parameters = data_get($item, Constant::PARAMETERS_KEY, []);

                if ($service && $method && method_exists($service, $method)) {
                    $service::{$method}(...$parameters);
                }
            }

            /*             * ***************更新会员的lastlogin********************** */
            CustomerInfoService::updateLastlogin($this->logData);
        } catch (\Exception $exc) {
            LogService::addSystemLog('error', 'log_job', 'addApiLog', '添加请求日志出错', ['exc' => $exc->getTraceAsString()]); //添加系统日志
        }
    }

}
