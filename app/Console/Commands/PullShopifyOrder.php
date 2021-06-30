<?php
/**
 * 拉取Shopify平台订单
 * User: Roy_qiu
 * Date: 2020-06-23
 * Time: 09:51
 */
namespace App\Console\Commands;

class PullShopifyOrder extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull_shopify_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: pull_shopify_order';

    public function runHandle() {

        $extData = [
            'updated_at_min' => '2020-05-29 00:00:00',
            'updated_at_max' => '2020-05-31 00:00:00',
        ];

        \App\Services\Store\Shopify\Orders\Order::getOrder(2, $extData);

        return true;
    }

}
