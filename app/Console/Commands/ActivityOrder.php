<?php

namespace App\Console\Commands;

use App\Services\ActivityApplyInfoService;
use App\Services\DictService;
use App\Util\Constant;
use App\Models\Store;
use App\Util\FunctionHelper;
use App\Services\OrderWarrantyService;
use App\Services\Platform\OrderService;

class ActivityOrder extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity_order {--orderIds= : orderIds} {--storeId= : storeId} {--pullDay= : pullDay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: activity_order';
    protected $created_at = 'created_at';
    protected $updated_at = 'updated_at';
    protected $order_status = Constant::DB_TABLE_ORDER_STATUS;
    protected $order_no = 'orderno';
    protected $whereKey = 'where';

    public function handleStoreOrder($storeIds) {

        //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
        $orderStatusData = DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, 'dict_value', 'dict_key');

        foreach ($storeIds as $storeId) {

            $this->handleRequest($storeId);

            //设置时区
            FunctionHelper::setTimezone($storeId);

            $activityApplyInfoModel = ActivityApplyInfoService::getModel($storeId, '');

            $handleData = [
                [//订单未匹配的
                    $this->whereKey => [
                        [
                            [$this->order_status, '=', OrderWarrantyService::$initOrderStatus],
                            [$this->order_no, '!=', ''],
                        ]
                    ],
                ],
                [//订单Pending
                    $this->whereKey => [
                        [
                            [$this->order_status, '=', 0],
                            [$this->order_no, '!=', ''],
                        ]
                    ],
                ],
            ];

            foreach ($handleData as $item) {
                $where = data_get($item, $this->whereKey, null);
                $activityApplyInfoModel->buildWhere($where)->select([Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_ORDER_NO, Constant::DB_TABLE_CREATED_AT])
                        ->chunk(100, function ($data) use($storeId, $orderStatusData) {
                            $this->updateOrders($data, $storeId, $orderStatusData);
                        });
            }

            $this->handleResponse(); //处理响应
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        $orderIds = $this->option('orderIds') ? $this->option('orderIds') : '';
        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;

        //处理所有官网的所有活动订单
        if ($orderIds == 'all' && $storeId == 'all') {
            $storeIds = Store::pluck('id');
            $this->handleStoreOrder($storeIds);
            return true;
        }

        //处理单个官网的所有活动订单
        if ($orderIds == 'all' && $storeId != 'all') {
            $storeIds = collect([$storeId]);
            $this->handleStoreOrder($storeIds);
            return true;
        }

        //处理单个官网的一批活动订单
        if (!empty($orderIds) && $storeId != 'all') {
            $this->handleBatchOrderIds($storeId, $orderIds);
            return true;
        }

        return true;
    }

    /**
     * 处理指定官网下的一批订单
     * @param int $storeId 官网id
     * @param string $orderIds 订单号
     * @return bool
     */
    public function handleBatchOrderIds($storeId, $orderIds) {
        $orderIds = array_unique(array_filter(explode(',', $orderIds)));
        if (empty($orderIds)) {
            return true;
        }

        //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
        $orderStatusData = DictService::getListByType(Constant::DB_TABLE_ORDER_STATUS, 'dict_value', 'dict_key');

        $this->handleRequest($storeId);

        //设置时区
        FunctionHelper::setTimezone($storeId);
        $activityApplyInfo = ActivityApplyInfoService::getModel($storeId, '');
        $activityApplyInfo->whereIn('orderno', $orderIds)->select([Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_ORDER_NO, Constant::DB_TABLE_CREATED_AT])
                ->chunk(100, function ($data) use($storeId, $orderStatusData) {
                    $this->updateOrders($data, $storeId, $orderStatusData);
                });

        $this->handleResponse(); //处理响应

        return true;
    }

    /**
     * 更新活动订单状态
     * @param array $orders 订单
     * @param int $storeId  官网id
     * @param mixed $orderStatusData 订单状态配置
     * @return bool
     */
    public function updateOrders($orders, $storeId, $orderStatusData) {

        foreach ($orders as $item) {
            if (empty($item->orderno)) {
                continue;
            }
            $orderRet = OrderService::getOrderData($item->orderno, '', Constant::PLATFORM_SERVICE_AMAZON, $storeId);
            if ($orderRet[Constant::RESPONSE_CODE_KEY] == 1) {
                $orderStatus = data_get($orderRet, 'data.order_status', 'Pending'); //订单状态 Pending Shipped Canceled
                $update = [
                    Constant::DB_TABLE_ORDER_STATUS => data_get($orderStatusData, $orderStatus, OrderWarrantyService::$initOrderStatus) //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
                ];
                $where = [
                    'id' => $item->id
                ];
                ActivityApplyInfoService::getModel($storeId)->buildWhere($where)->update($update);
            } else {
                $update = [
                    Constant::DB_TABLE_ORDER_STATUS => 3 //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
                ];
                $where = [
                    'id' => $item->id
                ];
                ActivityApplyInfoService::getModel($storeId)->buildWhere($where)->update($update);
            }
        }

        return true;
    }

}
