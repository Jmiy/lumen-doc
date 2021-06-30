<?php

namespace App\Console\Commands;

use App\Services\PointClearedLogService;

class PointCleared extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'point_cleared {storeId : The ID of the store} {--r|restore= : restore} {--rid= : rid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: point_cleared';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = $this->argument('storeId') + 0; //商城id
        if (empty($storeId)) {
            return true;
        }

        $restore = $this->option('restore') ? $this->option('restore') : 0;
        $rid = $this->option('rid') ? $this->option('rid') : 0;

        $this->handleRequest($storeId);

        PointClearedLogService::handle($storeId, $restore, $rid);

        $this->handleResponse(); //处理响应

        return true;
    }

}
