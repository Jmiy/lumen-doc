<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/12/5 9:42
 */

namespace App\Console\Commands;

use App\Services\BusGiftCardApplyService;
use App\Services\GiftCardApplyService;
use App\Services\OrderReviewService;
use App\Util\Constant;

class OrderReviewCashBackStatus extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash_back_status {--storeId= : storeId}';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {
        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;
        if (empty($storeId)) {
            return true;
        }

        $this->busGiftCardHandle($storeId);
        $this->giftCardHandle($storeId);
        return true;
    }

    public function busGiftCardHandle($storeId) {
        $lastId = 0;
        $limit = 10;
        while (true) {
            $lists = BusGiftCardApplyService::getModel($storeId)->withTrashed()
                ->select(Constant::DB_TABLE_PRIMARY, 'order_number')
                ->where(Constant::DB_TABLE_PRIMARY, '>', $lastId)
                ->whereIn(Constant::DB_TABLE_STATUS, [400, 600])
                ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC)
                ->limit($limit)
                ->get();

            if ($lists->isEmpty()) {
                break;
            }

            $orderNos = [];
            foreach ($lists as $item) {
                $lastId = data_get($item, Constant::DB_TABLE_PRIMARY);
                $orderNumber = data_get($item, 'order_number', Constant::PARAMETER_INT_DEFAULT);
                !empty($orderNumber) && $orderNos[$orderNumber] = $orderNumber;
            }

            if (empty($orderNos)) {
                continue;
            }

            $this->updateReviewStatus($storeId, $orderNos);
        }
    }

    public function giftCardHandle($storeId) {
        $lastId = 0;
        $limit = 10;
        while (true) {
            $lists = GiftCardApplyService::getModel($storeId)->withTrashed()
                ->select(Constant::DB_TABLE_PRIMARY, 'order_number')
                ->where(Constant::DB_TABLE_PRIMARY, '>', $lastId)
                ->whereIn(Constant::DB_TABLE_STATUS, [400, 600])
                ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_ASC)
                ->limit($limit)
                ->get();

            if ($lists->isEmpty()) {
                break;
            }

            $orderNos = [];
            foreach ($lists as $item) {
                $lastId = data_get($item, Constant::DB_TABLE_PRIMARY);
                $orderNumber = data_get($item, 'order_number', Constant::PARAMETER_INT_DEFAULT);
                !empty($orderNumber) && $orderNos[$orderNumber] = $orderNumber;
            }

            if (empty($orderNos)) {
                continue;
            }

            $this->updateReviewStatus($storeId, $orderNos);
        }
    }

    /**
     * 更新索评数据状态为已经返现
     * @param $storeId
     * @param $orderNos
     * @return bool
     */
    public function updateReviewStatus($storeId, $orderNos) {
        $orderReviews = OrderReviewService::getModel($storeId)
            ->select(Constant::DB_TABLE_PRIMARY)
            ->where(Constant::AUDIT_STATUS, '!=', 5)
            ->whereIn(Constant::DB_TABLE_ORDER_NO, array_values($orderNos))
            ->get();
        if ($orderReviews->isNotEmpty()) {
            $ids = data_get($orderReviews, "*.id");
            if (empty($ids)) {
                return true;
            }

            $where = [Constant::DB_TABLE_PRIMARY => $ids];
            $update = [Constant::AUDIT_STATUS => 5]; //状态：5已返现
            OrderReviewService::update($storeId, $where, $update);
        }
        return true;
    }
}
