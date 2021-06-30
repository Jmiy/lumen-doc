<?php

namespace App\Console\Commands;

use App\Util\Constant;
use App\Services\OrderWarrantyService;
use Illuminate\Support\Facades\DB;

class FixPurchaseTime extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix_purchase_time {--storeId= : storeId}';

    public static $storeIds = [
        1,2,3,5,6,7,8,9,10
    ];

    public static $countryTimes = [
        'US' => '-8',
        'UK' => '0',
        'IT' => '+1',
        'FR' => '+1',
        'MX' => '-5',
        'CA' => '-8',
        'IN' => '+6',
        'DE' => '+1',
        'JP' => '+9',
        'ES' => '+2',
        'AU' => '+11',
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $this->fixTime(static::$storeIds);

        return true;
    }

    public function fixTime($storeIds) {
        foreach ($storeIds as $storeId) {
            $lastId = 0;
            $limit = 100;
            while (true) {
                $orders = OrderWarrantyService::getModel($storeId)->where('id', '>', $lastId)->orderBy('id', 'asc')->limit($limit)->get();
                if ($orders->isEmpty()) {
                    break;
                }

                $countryOrders = [];
                foreach ($orders as $order) {
                    $country = data_get($order, Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT);
                    if (!empty($country)) {
                        $orderNo = data_get($order, Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_INT_DEFAULT);
                        $countryOrders[$country][] = $orderNo;
                    }
                    $lastId = data_get($order, Constant::DB_TABLE_PRIMARY);
                }

                foreach ($countryOrders as $country => $orders) {
                    if (isset(static::$countryTimes[$country])) {
                        $time = static::$countryTimes[$country];
                        $update = [
                            'order_time' => DB::raw("date_add(order_time, interval $time hour)"),
                        ];

                        OrderWarrantyService::getModel($storeId)->buildWhere(['orderno' => $orders])->update($update);
                    }
                }
            }
        }

        return true;
    }
}
