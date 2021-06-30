<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/9/3 13:52
 */

namespace App\Console\Commands;

use App\Services\ActivityService;
use App\Services\CreditService;
use App\Services\DictService;
use App\Services\OrderWarrantyService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Models\Store;

class DeductActWarrantyPoint extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deduct_act_warranty_point {--storeId= : storeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: deduct_act_warranty_point';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;

        $storeIds = $storeId ? [$storeId] : Store::pluck('id');
        foreach ($storeIds as $storeId) {
            $this->handleRequest($storeId);

            //设置时区
            FunctionHelper::setTimezone($storeId);

            //获取活动数据
            $actUnique = '/pages/primeday';
            $where = [Constant::DB_TABLE_ACT_UNIQUE => FunctionHelper::getUniqueId($actUnique)];
            $order = [[Constant::DB_TABLE_UPDATED_AT, 'DESC']];
            $limit = 1;
            $actData = ActivityService::getActivityData($storeId, 0, [], [], false, false, $where, $order, $limit);
            $actData['start_at'] = '2020-09-22 00:00:00';
            if (empty($actData)) {
                continue;
            }

            //获取汇率配置
            $exchangeData = DictService::getListByType('exchange', Constant::DB_TABLE_DICT_KEY, Constant::DB_TABLE_DICT_VALUE);

            $startAt = data_get($actData, Constant::DB_TABLE_START_AT);
            $endAt = data_get($actData, Constant::DB_TABLE_END_AT);

            $minId = PHP_INT_MIN;
            $customerCredits = [];
            while (true) {
                $where = [
                    ['ctime', '>=', $startAt],
                    ['ctime', '<=', $endAt],
                    ['id', '>', $minId],
                ];
                $limit = 50;
                $credits = CreditService::getModel($storeId)->where($where)->whereIn(Constant::DB_TABLE_ACTION, ['order_bind', 'creditExchange'])->orderBy('id', 'asc')->limit($limit)->get();
                if ($credits->isEmpty()) {
                    break;
                }

                $credits = $credits->toArray();
                foreach ($credits as $credit) {
                    if ($credit['id'] > $minId) {
                        $minId = $credit['id'];
                    }

                    //判断是否给了双倍积分
                    if ($credit[Constant::DB_TABLE_ACTION] == 'order_bind') {
                        $order = OrderWarrantyService::existsOrFirst($storeId,'', [Constant::DB_TABLE_PRIMARY => $credit['ext_id']], true);
                        if (empty($order)) {
                            continue;
                        }

                        $exchange = data_get($exchangeData, $order[Constant::DB_TABLE_CURRENCY_CODE], 0);
                        $value = abs(ceil($order[Constant::DB_TABLE_AMOUNT] * $exchange));

                        if ($value == $credit[Constant::DB_TABLE_VALUE]) {
                            continue;
                        }
                    }

                    $customerId = $credit['customer_id'];
                    if (isset($customerCredits[$customerId][$credit[Constant::DB_TABLE_ACTION]])) {
                        $customerCredits[$customerId][$credit[Constant::DB_TABLE_ACTION]] += $credit['value'];
                    } else {
                        $customerCredits[$customerId][$credit[Constant::DB_TABLE_ACTION]] = $credit['value'];
                    }
                }
            }

            foreach ($customerCredits as $customerId => $item) {
                $orderBind = data_get($item, 'order_bind', Constant::PARAMETER_INT_DEFAULT);
                $creditExchange = data_get($item, 'creditExchange', Constant::PARAMETER_INT_DEFAULT);

                $where = [
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                    Constant::DB_TABLE_EXT_ID => $customerId,
                    Constant::DB_TABLE_EXT_TYPE => 'customer',
                    Constant::DB_TABLE_ACTION => 'deductActWarrantyPoint',
                ];
                $exists = CreditService::existsOrFirst($storeId, '', $where);
                if ($exists) {
                    continue;
                }

                if ($orderBind > $creditExchange) {
                    //未消耗完的活动延保积分扣除
                    $value = ($orderBind - $creditExchange) * 0.5;
                    $params = [];
                    $params[Constant::DB_TABLE_STORE_ID] = $storeId;
                    $params[Constant::DB_TABLE_CUSTOMER_PRIMARY] = $customerId;
                    $params[Constant::DB_TABLE_ADD_TYPE] = 2;
                    $params[Constant::DB_TABLE_ACTION] = 'deductActWarrantyPoint';
                    $params[Constant::DB_TABLE_VALUE] = $value;
                    $params[Constant::DB_TABLE_EXT_ID] = $customerId;
                    $params[Constant::DB_TABLE_EXT_TYPE] = 'customer';
                    $params[Constant::DB_TABLE_CONTENT] = json_encode([$customerId, $item]);
                    $params[Constant::DB_TABLE_REMARK] = 'primeday活动结束延保积分恢复';
                    CreditService::handle($params);
                    //echo "$customerId-----$value\n";
                }
            }

            $this->handleResponse(); //处理响应
        }

        return true;
    }

}
