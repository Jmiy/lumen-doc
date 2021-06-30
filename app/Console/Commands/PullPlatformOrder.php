<?php

/**
 * 拉取平台订单
 * User: Jmiy
 * Date: 2020-06-29
 * Time: 10:00
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Util\FunctionHelper;
use App\Util\Constant;
use App\Services\DictStoreService;
use App\Services\Platform\OrderService;

class PullPlatformOrder extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull_platform_order {platform : The platform of the store} {storeId : The ID of the store} {--l|limit= : limit} {--updatedAtMin= : updatedAtMin} {--updatedAtMax= : updatedAtMax} {--ids= : ids} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_platform_order';

    public function getUpdatedAtMin($storeId) {
        FunctionHelper::setTimezone($storeId); //设置时区

        $updatedAtMin = $this->option('updatedAtMin') ? $this->option('updatedAtMin') : '';

        if ($updatedAtMin) {
            return Carbon::parse($updatedAtMin)->toDateTimeString();
        }

        $nowTime = Carbon::now()->toDateTimeString();
        $_nowTime = Carbon::now()->rawFormat('Y-m-d 01:00:00');

        $pullStart = DictStoreService::getByTypeAndKey($storeId, 'platform_order', 'pull_start', true);
        if (empty($pullStart)) {
            $pullStart = 1;
        }
        $pullStart = '-' . $pullStart . ' hour';
        $time = strtotime($pullStart, strtotime($nowTime));
        $updatedAtMin = Carbon::createFromTimestamp($time)->rawFormat('Y-m-d H:i:00');

        if ($nowTime < $_nowTime) {//凌晨 1 点前拉取前一天的数据
            $pullStart = '-1 day';
            $time = strtotime($pullStart, strtotime($nowTime));
            $updatedAtMin = Carbon::createFromTimestamp($time)->rawFormat('Y-m-d 00:00:00');
        }

        return $updatedAtMin;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $platform = $this->argument('platform'); //平台
        $storeId = $this->argument('storeId'); //商城id
        if (empty($platform)) {
            return true;
        }

        ini_set('memory_limit', '1024M'); // 设置PHP临时允许内存大小
        $this->handleRequest($storeId);
        FunctionHelper::setTimezone($storeId); //设置时区

        $updatedAtMin = $this->getUpdatedAtMin($storeId);
        $updatedAtMax = $this->option('updatedAtMax') ? $this->option('updatedAtMax') : '';
        $updatedAtMax = $updatedAtMax ? Carbon::parse($updatedAtMax)->toDateTimeString() : Carbon::now()->toDateTimeString();
        $limit = $this->option('limit') ? $this->option('limit') : 1000;
        $ids = $this->option('ids') ? $this->option('ids') : [];
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $sinceId = '';
        $customerSource = 30014;

        if ($ids) {
            $parameters = [
                $storeId,
                [
                    'ids' => $ids,
                    'sinceId' => $sinceId,
                    'limit' => $limit,
                ]
            ];
            OrderService::handlePull($storeId, $platform, $parameters);
            return true;
        }

        //获取拉取时间区间
        $pullInterval = DictStoreService::getByTypeAndKey($storeId, 'platform_order', 'pull_interval', true);
        if (empty($pullInterval)) {
            $pullInterval = 30;
        }

        $dateTime = $pullInterval * 60;

        while ($updatedAtMin < $updatedAtMax) {

            FunctionHelper::setTimezone('cn'); //设置时区
            $_updatedAtMax = Carbon::createFromTimestamp(((Carbon::parse($updatedAtMin)->timestamp) + $dateTime))->toDateTimeString();

            FunctionHelper::setTimezone($storeId); //设置时区
            $parameters = [
                $storeId,
                [
                    'ids' => $ids,
                    'sinceId' => $sinceId,
                    'limit' => $limit,
                    'updated_at_min' => $updatedAtMin,
                    'updated_at_max' => $_updatedAtMax,
                    'customer_source' => $customerSource,
                ]
            ];

            $data = OrderService::handlePull($storeId, $platform, $parameters);

            dump($updatedAtMin . '->' . $_updatedAtMax);

            if (empty(data_get($data, Constant::RESPONSE_CODE_KEY, 0))) {
                //dump($updatedAtMin . '->' . $_updatedAtMax . ': ' . data_get($data, Constant::RESPONSE_MSG_KEY, ''));
                sleep(1);
            }

            unset($data);

            $updatedAtMin = $_updatedAtMax;
        }

        $this->handleResponse(); //处理响应

        return true;
    }

}
