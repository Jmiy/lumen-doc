<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Store;
use App\Util\FunctionHelper;
use App\Services\OrderWarrantyService;
use App\Util\Constant;
use App\Services\DictService;
use App\Services\DictStoreService;
use App\Services\Platform\OrderService;
use Illuminate\Support\Facades\DB;
use Exception;

class Order extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order {--orderIds= : orderIds} {--storeId= : storeId} {--pullDay= : pullDay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: order';
    protected $created_at = 'ctime';
    protected $updated_at = 'mtime';
    protected $order_status = 'order_status';
    protected $task_no = 'task_no';
    protected $whereKey = 'where';
    protected $typeValue = 'platform';
    protected $pullNum = 'pull_num';

    public function all($updateData) {

        $storeIds = Store::pluck('id');
        $pullDay = $this->option('pullDay') ? $this->option('pullDay') : 180;
        $pullDaySrc = $pullDay;

        $service = OrderWarrantyService::getNamespaceClass();
        $method = 'handleBind';
        $laterTime = 0;
        $eachPullTime = DictService::getByTypeAndKey('pull_order', 'each_pull_time', true); //每个订单拉取需要的时间 单位秒
        foreach ($storeIds as $storeId) {

            $pullDay = $pullDaySrc;

            $storePullDay = DictStoreService::getByTypeAndKey($storeId, 'pull_order', 'pull_day', true);
            !empty($storePullDay) && $pullDay = $storePullDay;

            $this->handleRequest($storeId);

            //设置时区
            FunctionHelper::setTimezone($storeId);
            $nowTime = Carbon::now()->toDateTimeString();
            $updateData[$this->updated_at] = $nowTime;

            $orderModel = OrderWarrantyService::getModel($storeId, '');
            $nowTimestamp = Carbon::now()->timestamp; //当前时间戳
            $mtime = Carbon::createFromTimestamp($nowTimestamp - (3 * 60 * 60))->toDateTimeString(); //3个小时以前的时间
            $minAt = Carbon::createFromTimestamp($nowTimestamp - ($pullDay * 24 * 60 * 60))->toDateTimeString(); //$pullDay 天前时间
            $maxAt = $nowTime;

            if ($storeId != 5) {//如果是不是ikich，匹配 $pullDay 以后, Matching状态的订单统一改为 Failure 状态
                $_where = [
                    [
                        [Constant::DB_TABLE_STORE_ID, '=', $storeId],
                        [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                        [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                        [$this->order_status, '=', OrderWarrantyService::$initOrderStatus],
                        [$this->created_at, '<=', $minAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                    ]
                ];
                $_updateData = [
                    $this->order_status => 3, //订单状态 -1:Matching 0:Pending 1:Shipped 2:Canceled 3:Failure 默认:-1
                ];

                try {
                    $orderModel->buildWhere($_where)->update($_updateData);
                } catch (Exception $exc) {

                }

            }

            $handleData = [
                [//订单未绑定的，每次只拉取3个小时以前的数据  防止重复拉取，尝试拉取最近 180天 的数据
                    $this->order_status => OrderWarrantyService::$initOrderStatus,
                    $this->task_no => FunctionHelper::randomStr(8),
                    $this->whereKey => [
                        Constant::DB_TABLE_STORE_ID => $storeId,
                        $this->task_no => '',
                        [
                            [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                            [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                            [$this->order_status, '=', OrderWarrantyService::$initOrderStatus],
                            [$this->pullNum, '>', 0], //每次只拉取匹配失败的数据  防止重复拉取
                            [$this->updated_at, '<=', $mtime], //每次只拉取3个小时以前的数据  防止重复拉取
                            [$this->created_at, '>', $minAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                            [$this->created_at, '<=', $maxAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                        ]
                    ],
                ],
                [//订单状态为已绑定的，并且金额为0的，每次只拉取当天以前的数据  防止重复拉取,尝试拉取最近 180天 的数据
                    $this->order_status => 0,
                    $this->task_no => FunctionHelper::randomStr(8),
                    $this->whereKey => [
                        Constant::DB_TABLE_STORE_ID => $storeId,
                        $this->task_no => '',
                        [
                            [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                            [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                            [$this->order_status, '=', 1],
                            ['amount', '=', 0],
                            [$this->updated_at, '<=', Carbon::now()->rawFormat('Y-m-d 00:00:00')], //每次只拉取当天以前的数据  防止重复拉取
                            ['order_time', '>', '1000-01-01 00:00:00'],
                            [$this->created_at, '>', $minAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                            [$this->created_at, '<=', $maxAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                        ]
                    ],
                ],
                [//订单Pending，每次只拉取3个小时以前的数据  防止重复拉取，尝试拉取最近 180天 的数据
                    $this->order_status => 0,
                    $this->task_no => FunctionHelper::randomStr(8),
                    $this->whereKey => [
                        Constant::DB_TABLE_STORE_ID => $storeId,
                        $this->task_no => '',
                        [
                            [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                            [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                            [$this->order_status, '=', 0],
                            [$this->pullNum, '>', 0], //每次只拉取匹配失败的数据  防止重复拉取
                            [$this->updated_at, '<=', $mtime], //每次只拉取3个小时以前的数据  防止重复拉取
                            [$this->created_at, '>', $minAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                            [$this->created_at, '<=', $maxAt], //每次扫描 $minAt 到 $maxAt 时段绑定的订单
                        ]
                    ],
                ],
            ];

            foreach ($handleData as $item) {
                data_set($updateData, $this->order_status, data_get($item, $this->order_status, -1));
                data_set($updateData, $this->task_no, data_get($item, $this->task_no, ''));
                $where = data_get($item, $this->whereKey, null);
                if (!empty($where)) {
                    data_set($where, Constant::DB_TABLE_PLATFORM, Constant::PLATFORM_AMAZON);

                    try {
                        $orderModel->buildWhere($where)->update($updateData); //->withTrashed()
                    } catch (Exception $exc) {

                    }

                }
            }

            $requestMark = app('request')->input('request_mark', '');
            foreach ($handleData as $item) {
                $where = [
                    $this->task_no => data_get($item, $this->task_no, ''),
                ];
                $orderModel->buildWhere($where)->select(['id'])
                        ->chunk(100, function ($data) use($storeId, $service, $method, $requestMark, &$laterTime, $eachPullTime) {
                            foreach ($data as $item) {
                                try {
                                    $parameters = [$item->id, $storeId, $requestMark, true];
                                    FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{amazon-order-bind}'); //把任务加入消息队列
//                                FunctionHelper::laterQueue($laterTime, FunctionHelper::getJobData($service, $method, $parameters), null, '{amazon-order-bind}'); //延时 $laterTime 秒再弹出任务
//                                $laterTime = $laterTime + $eachPullTime;

                                    //OrderWarrantyService::handleBind($item->id, $storeId, $requestMark, true);
                                } catch (Exception $exc) {

                                }
                            }
                        });
            }

            $where = [
                [
                    [Constant::DB_TABLE_STORE_ID, '=', $storeId],
                    [Constant::DB_TABLE_TYPE, '=', Constant::DB_TABLE_PLATFORM],
                    [Constant::DB_TABLE_PLATFORM, '=', Constant::PLATFORM_AMAZON],
                    [Constant::DB_TABLE_ORDER_STATUS, '>', OrderWarrantyService::$initOrderStatus],
                ],
                Constant::WARRANTY_AT => ['2019-01-01 00:00:00', '1000-01-01 00:00:00'],
            ];
            $orderModel->buildWhere($where)->select([Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_CUSTOMER_PRIMARY])
                    ->chunk(10000, function ($data) use($storeId, $service) {
                        foreach ($data as $item) {
                            $parameters = [$storeId, data_get($item, Constant::DB_TABLE_PRIMARY, -1), data_get($item, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1)];
                            FunctionHelper::pushQueue(FunctionHelper::getJobData($service, 'handleWarrantyAt', $parameters), null, '{amazon-order-bind}'); //把任务加入消息队列
                        }
                    });

            //拉取 Amazon 订单数据
            $orderModel->from('customer_order as co')
                    ->leftJoin(DB::raw('`ptxcrm`.`crm_platform_orders` as crm_po'), function ($join) {
                        $join->on([['po.orderno', '=', 'co.orderno']])->where('po' . Constant::LINKER . Constant::DB_TABLE_STATUS, '=', 1);
                    }
                    )
                    ->select(['co.' . Constant::DB_TABLE_PRIMARY, 'co.' . Constant::DB_TABLE_ORDER_NO, 'co.' . Constant::DB_TABLE_COUNTRY])
                    ->where([
                        'co.' . Constant::DB_TABLE_TYPE => Constant::DB_TABLE_PLATFORM,
                        'co.' . Constant::DB_TABLE_PLATFORM => Constant::PLATFORM_AMAZON,
                        'co.ext_type' => 'Order',
                        'po.id' => null,
                    ])
                    ->where('co.' . Constant::DB_TABLE_ORDER_STATUS, '!=', 3)
                    ->orderBy('co.id', 'DESC')
                    ->chunk(10000, function ($data) use($storeId) {
                        foreach ($data as $item) {
                            //dump($item->toArray());
                            $_parameters = [$storeId, Constant::PLATFORM_SERVICE_AMAZON, data_get($item, Constant::DB_TABLE_ORDER_NO), false, data_get($item, Constant::DB_TABLE_COUNTRY), data_get($item, Constant::DB_TABLE_PRIMARY, 0)];
                            FunctionHelper::pushQueue(FunctionHelper::getJobData(OrderService::getNamespaceClass(), 'pullOrder', $_parameters), null, '{amazon-order-bind}'); //把任务加入消息队列
                        }
                    });

            $this->handleResponse(); //处理响应
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {

        ini_set('memory_limit', '2048M');

        $orderIds = $this->option('orderIds') ? $this->option('orderIds') : '';
        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;

        $updateData = ['status' => 1, 'deleted_at' => null, $this->pullNum => 0, $this->updated_at => '', $this->order_status => OrderWarrantyService::$initOrderStatus];

        if ($orderIds == 'all') {
            $this->all($updateData);
            return true;
        }

        if (empty($orderIds)) {
            return true;
        }

        $orderIds = array_unique(array_filter(explode(',', $orderIds)));
        if (empty($orderIds)) {
            return true;
        }

        $this->handleRequest($storeId);

        $service = OrderWarrantyService::getNamespaceClass();
        $method = 'handleBind';
        $requestMark = app('request')->input('request_mark', '');

        //设置时区
        FunctionHelper::setTimezone($storeId);
        $updateData[$this->updated_at] = Carbon::now()->toDateTimeString();
        $orderModel = OrderWarrantyService::getModel($storeId, ''); //->withTrashed()
        $orderModel->whereIn('orderno', $orderIds)->select(['id', Constant::DB_TABLE_STORE_ID])
                ->chunk(100, function ($data) use($updateData, $orderModel, $storeId, $service, $method, $requestMark) {
                    foreach ($data as $item) {
                        $orderModel->where(['id' => $item->id])->update($updateData); //->withTrashed()
                        $parameters = [$item->id, $storeId, $requestMark, true];
                        FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{amazon-order-bind}');
                    }
                });

        $this->handleResponse(); //处理响应

        return true;
    }

}
