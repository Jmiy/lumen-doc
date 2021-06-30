<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Util\FunctionHelper;
use App\Services\CustomerService;
use App\Util\Constant;
use App\Services\DictStoreService;

class SyncCustomer extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_customer {storeId : The ID of the store} {--l|limit= : limit} {--createdAtMin= : createdAtMin} {--createdAtMax= : createdAtMax} {--ids= : ids} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: sync_customer';

    public function getCreatedAtMin($storeId) {
        FunctionHelper::setTimezone($storeId); //设置时区

        $createdAtMin = $this->option('createdAtMin') ? $this->option('createdAtMin') : '';

        if ($createdAtMin) {
            return Carbon::parse($createdAtMin)->toDateTimeString();
        }

        $nowTime = Carbon::now()->toDateTimeString();
        $_nowTime = Carbon::now()->rawFormat('Y-m-d 01:00:00');

        $pullStart = DictStoreService::getByTypeAndKey($storeId, 'customer', 'pull_start', true);
        if (empty($pullStart)) {
            $pullStart = 1;
        }
        $pullStart = '-' . $pullStart . ' hour';
        $time = strtotime($pullStart, strtotime($nowTime));
        $createdAtMin = Carbon::createFromTimestamp($time)->rawFormat('Y-m-d H:i:00');

        if ($nowTime < $_nowTime) {
            $pullStart = '-1 day';
            $time = strtotime($pullStart, strtotime($nowTime));
            $createdAtMin = Carbon::createFromTimestamp($time)->rawFormat('Y-m-d 00:00:00');
        }

        return $createdAtMin;
    }

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

        ini_set('memory_limit', '2048M'); // 设置PHP临时允许内存大小

        $this->handleRequest($storeId);

        FunctionHelper::setTimezone($storeId); //设置时区

        $createdAtMin = $this->getCreatedAtMin($storeId);
        $createdAtMax = $this->option('createdAtMax') ? $this->option('createdAtMax') : '';
        $createdAtMax = $createdAtMax ? Carbon::parse($createdAtMax)->toDateTimeString() : Carbon::now()->toDateTimeString();
        $limit = $this->option('limit') ? $this->option('limit') : 1000;
        $ids = $this->option('ids') ? $this->option('ids') : [];
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $sinceId = '';
        $source = 6;
        $operator = 'console';

        if ($ids) {
            CustomerService::sync($storeId, '', '', $ids, '', $limit, $source, $operator);
            return true;
        }

        //获取拉取时间区间
        $pullInterval = DictStoreService::getByTypeAndKey($storeId, 'customer', 'pull_interval', true);
        if (empty($pullInterval)) {
            $pullInterval = 30;
        }

        $dateTime = $pullInterval * 60;

        while ($createdAtMin < $createdAtMax) {

//            if ($createdAtMin >= '2019-11-01 00:00:00' && $createdAtMin <= '2019-11-30 00:00:00') {
//                $dateTime = 3 * 60;
//            } else {
//                $dateTime = $pullInterval * 60;
//            }

            FunctionHelper::setTimezone('cn'); //设置时区
            $_createdAtMax = Carbon::createFromTimestamp(((Carbon::parse($createdAtMin)->timestamp) + $dateTime))->toDateTimeString();

            FunctionHelper::setTimezone($storeId); //设置时区
            $extData = [];
            if (!in_array($storeId, Constant::RULES_NOT_APPLY_STORE)) {//如果不是  holife和ikich, 就使用更新时间获取shopify的账号数据
                $extData = [
                    'updated_at_min' => $createdAtMin,
                    'updated_at_max' => $_createdAtMax,
                ];

                $data = CustomerService::sync($storeId, '', '', $ids, $sinceId, $limit, $source, $operator, $extData);
            } else {
                $data = CustomerService::sync($storeId, $createdAtMin, $_createdAtMax, $ids, $sinceId, $limit, $source, $operator, $extData);
            }

            if (empty(data_get($data, Constant::RESPONSE_CODE_KEY, 0))) {
                dump($createdAtMin . '->' . $_createdAtMax . ': ' . data_get($data, Constant::RESPONSE_MSG_KEY, ''));
                sleep(1);
            }

            unset($data);

            $createdAtMin = $_createdAtMax;
        }

        $this->handleResponse(); //处理响应

        return true;
    }

}
