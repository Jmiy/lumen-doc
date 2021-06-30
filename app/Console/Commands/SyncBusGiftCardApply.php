<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/11/26 14:37
 */

namespace App\Console\Commands;

use App\Models\Erp\Amazon\ErpBusGiftCardApply;
use App\Services\BusGiftCardApplyService;
use App\Services\GiftCardApplyService;
use App\Util\Constant;

class SyncBusGiftCardApply extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_bus_gift_card_apply  {--l|limit= : limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: sync_bus_gift_card_apply';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {
        $storeId = 1;

        $limit = 100;
        $lastId = 0;

        while (true) {
            $where = [
                [
                    [Constant::DB_TABLE_PRIMARY, '>', $lastId]
                ]
            ];

            $lists = ErpBusGiftCardApply::buildWhere($where)
                ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC)
                ->limit($limit)
                ->get();

            if ($lists->isEmpty()) {
                break;
            }

            $lists = $lists->toArray();
            foreach ($lists as $item) {
                $id = data_get($item, Constant::DB_TABLE_PRIMARY);
                $status = data_get($item, Constant::DB_TABLE_STATUS);
                $applySn = data_get($item, 'apply_sn');

                $itemData = [
                    //'id' => data_get($item, 'id', Constant::PARAMETER_INT_DEFAULT),
                    'apply_sn' => data_get($item, 'apply_sn', Constant::PARAMETER_STRING_DEFAULT),
                    'code_id' => data_get($item, 'code_id', Constant::PARAMETER_INT_DEFAULT),
                    'code' => data_get($item, 'code', Constant::PARAMETER_STRING_DEFAULT),
                    'country' => data_get($item, 'country', Constant::PARAMETER_STRING_DEFAULT),
                    'dept_id' => data_get($item, 'dept_id', Constant::PARAMETER_INT_DEFAULT),
                    'dept_name' => data_get($item, 'dept_name', Constant::PARAMETER_STRING_DEFAULT),
                    'channel' => data_get($item, 'channel', Constant::PARAMETER_INT_DEFAULT),
                    'local_amount' => data_get($item, 'local_amount', Constant::PARAMETER_INT_DEFAULT),
                    'usd_amount' => data_get($item, 'usd_amount', Constant::PARAMETER_INT_DEFAULT),
                    'exchange_rate' => data_get($item, 'exchange_rate', Constant::PARAMETER_INT_DEFAULT),
                    'buyer_name' => data_get($item, 'buyer_name', Constant::PARAMETER_STRING_DEFAULT),
                    'buyer_email' => data_get($item, 'buyer_email', Constant::PARAMETER_STRING_DEFAULT),
                    'cashback_email' => data_get($item, 'cashback_email', Constant::PARAMETER_STRING_DEFAULT),
                    'email_receive_date' => data_get($item, 'email_receive_date', Constant::PARAMETER_STRING_DEFAULT),
                    'rv_link' => data_get($item, 'rv_link', Constant::PARAMETER_STRING_DEFAULT),
                    'rv_image' => data_get($item, 'rv_image', Constant::PARAMETER_STRING_DEFAULT),
                    'rv_sn' => data_get($item, 'rv_sn', Constant::PARAMETER_STRING_DEFAULT),
                    'order_number' => data_get($item, 'order_number', Constant::PARAMETER_STRING_DEFAULT),
                    'shop_code' => data_get($item, 'shop_code', Constant::PARAMETER_STRING_DEFAULT),
                    'product_sku' => data_get($item, 'product_sku', Constant::PARAMETER_STRING_DEFAULT),
                    'seller_sku' => data_get($item, 'seller_sku', Constant::PARAMETER_STRING_DEFAULT),
                    'asin' => data_get($item, 'asin', Constant::PARAMETER_STRING_DEFAULT),
                    'three_category_code' => data_get($item, 'three_category_code', Constant::PARAMETER_STRING_DEFAULT),
                    'three_category_name' => data_get($item, 'three_category_name', Constant::PARAMETER_STRING_DEFAULT),
                    'unit_price' => data_get($item, 'unit_price', Constant::PARAMETER_INT_DEFAULT),
                    'is_refund' => data_get($item, 'is_refund', Constant::PARAMETER_INT_DEFAULT),
                    'apply_user' => data_get($item, 'apply_user', Constant::PARAMETER_STRING_DEFAULT),
                    'review_user' => data_get($item, 'review_user', Constant::PARAMETER_STRING_DEFAULT),
                    'distribute_user' => data_get($item, 'distribute_user', Constant::PARAMETER_STRING_DEFAULT),
                    'apply_time' => data_get($item, 'apply_time', Constant::PARAMETER_STRING_DEFAULT),
                    'review_time' => data_get($item, 'review_time', Constant::PARAMETER_STRING_DEFAULT),
                    'distribute_time' => data_get($item, 'distribute_time', Constant::PARAMETER_STRING_DEFAULT),
                    'remark' => data_get($item, 'remark', Constant::PARAMETER_STRING_DEFAULT),
                    'status' => data_get($item, 'status', Constant::PARAMETER_INT_DEFAULT),
                    'batch_number' => data_get($item, 'batch_number', Constant::PARAMETER_STRING_DEFAULT),
                    'created_time' => data_get($item, 'created_time', Constant::PARAMETER_STRING_DEFAULT),
                    'updated_time' => data_get($item, 'updated_time', Constant::PARAMETER_STRING_DEFAULT),
                ];


                $exists = GiftCardApplyService::getModel($storeId)->withTrashed()->where(['apply_sn' => $applySn])->count();
                if (!$exists && in_array($status, [400, 600])) {
                    //礼品返现数据同步至ptxcrm.crm_gift_card_applies表，防止[400,600]状态变更
                    GiftCardApplyService::getModel($storeId)->insert($itemData);
                }

                //(202101191350 Jmiy 注释，礼品返现数据直接使用美国硅谷的数据库数据即可) 停止将 礼品返现数据同步至ptx_sync.crm_bus_gift_card_applies表
//                $exists = BusGiftCardApplyService::getModel($storeId)->withTrashed()->where(['apply_sn' => $applySn])->count();
//                if ($exists) {
//                    BusGiftCardApplyService::getModel($storeId)->withTrashed()->where(['apply_sn' => $applySn])->update($itemData);
//                } else {
//                    BusGiftCardApplyService::getModel($storeId)->insert($itemData);
//                }

                $lastId = $id;
            }
        }

        return true;
    }

}
