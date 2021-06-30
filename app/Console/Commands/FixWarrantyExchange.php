<?php

namespace App\Console\Commands;

use App\Models\CustomerInfo;
use App\Services\CreditService;
use App\Services\DictService;
use App\Services\OrderWarrantyService;
use App\Services\Platform\OrderService;
use App\Util\Constant;
use Illuminate\Support\Facades\DB;

class FixWarrantyExchange extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix_warranty_exchange {--storeId= : storeId}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $this->fix(1, 'INR');

        return true;
    }

    public function fix($storeId, $currencyCode) {
        //获取延保积分流水
        $result = CreditService::getModel($storeId)
            ->whereRaw("customer_id IN (SELECT customer_id FROM `crm_customer_order` WHERE currency_code = '$currencyCode' AND `status` = 1)")
            ->where('action', 'order_bind')
            ->where('status', 1)
            ->get();

        if ($result->isEmpty()) {
            return true;
        }

        //获取汇率配置
        $exchangeData = DictService::getListByType('exchange', Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE);
        $exchange = data_get($exchangeData, $currencyCode, 0);

        $credits = $result->toArray();
        foreach ($credits as $credit) {
            $orderWarrantyId = data_get($credit, Constant::DB_TABLE_EXT_ID);
            $creditId = data_get($credit, Constant::DB_TABLE_PRIMARY);

            //获取延保订单
            $orderWarrantyWhere = [
                Constant::DB_TABLE_PRIMARY => $orderWarrantyId,
            ];
            $orderWarrantySelect = [
                Constant::DB_TABLE_PRIMARY,
                Constant::DB_TABLE_CUSTOMER_PRIMARY,
                Constant::DB_TABLE_ORDER_STATUS,
                Constant::DB_TABLE_AMOUNT,
                Constant::DB_TABLE_CURRENCY_CODE
            ];
            $orderWarrantyData = OrderWarrantyService::existsOrFirst($storeId, '', $orderWarrantyWhere, true, $orderWarrantySelect);

            $customerId = data_get($orderWarrantyData, Constant::DB_TABLE_CUSTOMER_PRIMARY) ?? Constant::DEFAULT_CUSTOMER_PRIMARY_VALUE; //账号id
            $amount = data_get($orderWarrantyData, Constant::DB_TABLE_AMOUNT) ?? 0; //订单金额
            $creditValue = data_get($credit, Constant::DB_TABLE_VALUE); //已加的错误积分

            //应该获得积分
            $value = $exchange * $amount;
            $value = abs(ceil($value));

            $updateValue = $creditValue - $value;
            if ($updateValue == 0) {
                continue;
            }

            //更新账户积分
            $where = [Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId];
            if ($updateValue > 0) {
                $data = [
                    Constant::DB_TABLE_CREDIT => DB::raw('credit-' . $updateValue),
                    'total_credit' => DB::raw('total_credit-' . $updateValue),
                ];
            } else {
                $data = [
                    Constant::DB_TABLE_CREDIT => DB::raw('credit+' . abs($updateValue)),
                    'total_credit' => DB::raw('total_credit+' . abs($updateValue)),
                ];
            }

            CustomerInfo::where($where)->update($data);

            //更新积分流水的积分值
            CreditService::update($storeId, [Constant::DB_TABLE_PRIMARY => $creditId], [Constant::DB_TABLE_VALUE => $value, 'point_cleared_remark' => "延保积分变动:{$creditValue}=>{$value}"]);

        }

        //获取PhysicalProductExchange积分流水
        $result = CreditService::getModel($storeId)
            ->whereRaw("customer_id IN (SELECT customer_id FROM `crm_customer_order` WHERE currency_code = '$currencyCode' AND `status` = 1)")
            ->where('action', 'PhysicalProductExchange')
            ->where('status', 1)
            ->get();

        //已经兑换的积分返还
        if (!$result->isEmpty()) {
            $credits = $result->toArray();
            foreach ($credits as $credit) {
                $exchangeValue = data_get($credit, Constant::DB_TABLE_VALUE);
                $customerId = data_get($credit, Constant::DB_TABLE_CUSTOMER_PRIMARY);
                $creditId = data_get($credit, Constant::DB_TABLE_PRIMARY);

                $content = json_decode($credit['content'], true);
                $orderUniqueId = data_get($content, 'order_unique_id', 0);
                if (empty($orderUniqueId)) {
                    continue;
                }
                $exists = OrderService::existsOrFirst($storeId, '', [Constant::DB_TABLE_UNIQUE_ID => $orderUniqueId]);
                if (empty($exists)) {
                    continue;
                }

                //更新账户积分
                $where = [Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId];
                $data = [
                    Constant::DB_TABLE_CREDIT => DB::raw('credit+' . $exchangeValue),
                ];
                CustomerInfo::where($where)->update($data);

                //积分流水设置为无效
                CreditService::update($storeId, [Constant::DB_TABLE_PRIMARY => $creditId], ['status' => 0, 'point_cleared_remark' => "{$currencyCode}延保订单积分有误,兑换无效"]);

                //已经兑换的订单设置为无效
                $where = [Constant::DB_TABLE_UNIQUE_ID => $orderUniqueId];
                OrderService::delete($storeId, $where);
            }
        }

        //获取NonPhysicalExchange积分流水
        $result = CreditService::getModel($storeId)
            ->whereRaw("customer_id IN (SELECT customer_id FROM `crm_customer_order` WHERE currency_code = '$currencyCode' AND `status` = 1)")
            ->where('action', 'NonPhysicalExchange')
            ->where('status', 1)
            ->get();

        //已经兑换的积分返还
        if (!$result->isEmpty()) {
            $credits = $result->toArray();
            foreach ($credits as $credit) {
                $exchangeValue = data_get($credit, Constant::DB_TABLE_VALUE);
                $customerId = data_get($credit, Constant::DB_TABLE_CUSTOMER_PRIMARY);
                $creditId = data_get($credit, Constant::DB_TABLE_PRIMARY);

                $content = json_decode($credit['content'], true);
                $orderUniqueId = data_get($content, 'order_unique_id', 0);
                if (empty($orderUniqueId)) {
                    continue;
                }
                $exists = OrderService::existsOrFirst($storeId, '', [Constant::DB_TABLE_UNIQUE_ID => $orderUniqueId]);
                if (empty($exists)) {
                    continue;
                }

                //更新账户积分
                $where = [Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId];
                $data = [
                    Constant::DB_TABLE_CREDIT => DB::raw('credit+' . $exchangeValue),
                ];
                CustomerInfo::where($where)->update($data);

                //积分流水设置为无效
                CreditService::update($storeId, [Constant::DB_TABLE_PRIMARY => $creditId], ['status' => 0, 'point_cleared_remark' => "{$currencyCode}延保订单积分有误,兑换无效"]);

                //已经兑换的订单设置为无效
                $where = [Constant::DB_TABLE_UNIQUE_ID => $orderUniqueId];
                OrderService::delete($storeId, $where);
            }
        }

        return true;
    }
}
