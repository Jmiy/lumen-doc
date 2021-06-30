<?php

namespace App\Console\Commands;

use App\Util\Constant;
use App\Services\Platform\CategoryService;

class PullCategory extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull_category {storeId : The ID of the store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_category';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = $this->argument('storeId'); //商城id
        $this->handleRequest($storeId);

        CategoryService::handlePull($storeId, Constant::PLATFORM_SERVICE_AMAZON, []);

        $this->handleResponse(); //处理响应

        return true;
    }

}
