<?php

namespace App\Console\Commands;

use App\Util\Constant;
use Carbon\Carbon;
use App\Util\FunctionHelper;
use App\Services\ProductService;

class SyncProduct extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_product {storeId : The ID of the store} {--l|limit= : limit} {--createdAtMin= : createdAtMin} {--createdAtMax= : createdAtMax} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: sync_product';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {
        $storeId = $this->argument('storeId'); //商城id
        if (empty($storeId)) {
            return true;
        }

        $this->handleRequest($storeId);

        FunctionHelper::setTimezone($storeId); //设置时区

        $createdAtMin = $this->option('createdAtMin') ? $this->option('createdAtMin') : '';
        if ($createdAtMin != 'all') {
            $createdAtMin = $createdAtMin ? Carbon::parse($createdAtMin)->toDateTimeString() : Carbon::now()->rawFormat('Y-m-d 00:00:00');
        }
        $createdAtMax = $this->option('createdAtMax') ? $this->option('createdAtMax') : '';
        $createdAtMax = $createdAtMax ? Carbon::parse($createdAtMax)->toDateTimeString() : Carbon::now()->toDateTimeString();
        $limit = $this->option(Constant::ACT_LIMIT_KEY) ? $this->option(Constant::ACT_LIMIT_KEY) : 1000;
        $ids = [];
        $sinceId = '';
        $source = 6;
        $operator = 'console';

        if ($createdAtMin == 'all') {
            $parameters = [
                'updated_at_min' => '',
                'updated_at_max' => $createdAtMax,
                'ids' => $ids,
                'sinceId' => $sinceId,
                'limit' => $limit,
                'source' => $source,
                'operator' => $operator,
            ];
            ProductService::sync($storeId, $parameters);
        } else {
            $dateTime = 24 * 60 * 60;
            while ($createdAtMin < $createdAtMax) {
                $_createdAtMax = Carbon::createFromTimestamp(((Carbon::parse($createdAtMin)->timestamp) + $dateTime))->rawFormat('Y-m-d 00:00:00');
                $parameters = [
                    'updated_at_min' => $createdAtMin,
                    'updated_at_max' => $_createdAtMax,
                    'ids' => $ids,
                    'sinceId' => $sinceId,
                    'limit' => $limit,
                    'source' => $source,
                    'operator' => $operator,
                ];
                ProductService::sync($storeId, $parameters);
                $createdAtMin = $_createdAtMax;
            }
        }

        $this->handleResponse(); //处理响应


        return true;
    }

}
