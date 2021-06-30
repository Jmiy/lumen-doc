<?php

namespace App\Console;

use Guzzle\Http\QueryAggregator\CommaAggregator;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\SyncCustomer::class,
        Commands\SyncProduct::class,
        Commands\Rank::class,
        Commands\Coupon::class,
        Commands\SendCouponEmail::class,
        Commands\Order::class,
        Commands\DingSummaryAlarm::class,
        Commands\DingCustomerIp::class,
        Commands\DingCustomerAccount::class,
        Commands\SyncErpFinanceRate::class,
        Commands\SyncProductPrice::class,
        Commands\PointCleared::class,
        Commands\ActivityOrder::class,
        Commands\FixPurchaseTime::class,
        Commands\PullAmazonOrder::class,
        Commands\PullShopifyOrder::class,
        Commands\PullPlatformOrder::class,
        Commands\DeductActWarrantyPoint::class,
        Commands\FixWarrantyExchange::class,
        Commands\PullCategory::class,
        Commands\PullProduct::class,
        Commands\SyncBusGiftCardApply::class,
        Commands\OrderReviewCashBackStatus::class,
        Commands\UrgeUserSubmitRv::class,
        Commands\PullAmazonOrderMetafields::class,
        Commands\GenerateActivityUsers::class,
        \App\Services\Activity\LuckyNumber\Console\Commands\Draw::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        //
        // runInBackground()方法会新启子进程执行任务，这是异步的，不会影响其他任务的执行时机
        //$schedule->command(TestCommand::class)->runInBackground()->everyMinute();
    }

}
