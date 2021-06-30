<?php

namespace App\Console\Commands;

use App\Services\Platform\RateService;
use App\Util\Constant;
use App\Services\Platform\CountryService;
use App\Services\Platform\CategoryService;

class SyncErpFinanceRate extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_erp_finance_rate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = 'cn';
        $this->handleRequest($storeId);

        //同步汇率
        RateService::handlePull($storeId, Constant::PLATFORM_SERVICE_AMAZON, []);

        //同步国家
        CountryService::handlePull($storeId, Constant::PLATFORM_SERVICE_AMAZON, []);

        $this->handleResponse(); //处理响应

        return true;
    }

}
