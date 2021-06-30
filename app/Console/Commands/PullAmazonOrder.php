<?php

namespace App\Console\Commands;

use App\Util\FunctionHelper;
use App\Services\OrdersService;

class PullAmazonOrder extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull_amazon_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_amazon_order';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = 2;
        $this->handleRequest($storeId);

        //订单国家
        $orderCountryData = OrdersService::$orderCountryData;
        $service = OrdersService::getNamespaceClass();
        $method = 'handleAmazonOrder';
        $tag = OrdersService::getCacheTags();
        $cacheKey = $tag . ':pullAmazonOrder:';

        $orderPullTimePeriod = OrdersService::getOrderPullTimePeriod(); //获取订单拉取时间段

        $dictData = OrdersService::getPullOrderConfig();

        $limit = data_get($dictData, 'limit', 50); //获取每次拉取订单数量
        $eachPullTime = data_get($dictData, 'each_pull_time', 1); //每个订单拉取需要的时间 单位秒
        $ttl = data_get($dictData, 'ttl', 600); //批次已经在消息队列里面的缓存时间 单位秒
        $eachBatchExeTime = $limit * $eachPullTime; //每个批次执行时间

        $laterTime = OrdersService::getLaterTime();

        foreach ($orderPullTimePeriod as $item) {
            foreach ($orderCountryData as $country) {

                $startAt = data_get($item, 'startAt', null);
                $endAt = data_get($item, 'endAt', null);
                $parameters = [$country, $startAt, $endAt];
                //OrdersService::forceReleaseOrdersLock(...$parameters); //释放订单拉取分布式锁

                $_cacheKey = $cacheKey . implode(':', $parameters); //拉取时段cacheKey

                $has = OrdersService::handleCache($tag, FunctionHelper::getJobData($service, 'has', [$_cacheKey])); //获取 $_cacheKey 对应的批次是否存在
                if (!empty($has)) {//如果 $_cacheKey 对应的批次已经在消息队列里面，就不需要加入消息队列
                    continue;
                }

                FunctionHelper::laterQueue($laterTime, FunctionHelper::getJobData($service, $method, $parameters), null, '{amazon-order-pull}'); //延时 $laterTime 秒再弹出任务

                OrdersService::handleCache($tag, FunctionHelper::getJobData($service, 'put', [$_cacheKey, 1, $ttl])); //记录 $_cacheKey 对应的批次已经在消息队列里面

                $laterTime = $laterTime + $eachBatchExeTime; //获取 消息队列延时执行的时间 单位秒
            }
        }

//        $method = 'repair';
//        $parameters = [];
//        $_cacheKey = implode(':', [$tag, $method, md5(json_encode($parameters))]); //拉取时段cacheKey
//        $has = OrdersService::handleCache($tag, FunctionHelper::getJobData($service, 'has', [$_cacheKey])); //获取 $_cacheKey 对应的批次是否存在
//        if (empty($has)) {//如果 $_cacheKey 对应的任务不在消息队列中，就把 $_cacheKey 对应的任务加入消息队列
//            FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{amazon-order-pull}'); //把任务加入消息队列
//        }

        $this->handleResponse(); //处理响应

        return true;
    }

}
