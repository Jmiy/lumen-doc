<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Store;
use App\Services\DingService;
use App\Services\CustomerService;
use App\Services\CreditService;
use App\Services\OrderWarrantyService;
use App\Util\Constant;
use App\Services\Monitor\MonitorServiceManager;

class DingSummaryAlarm extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ding_summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: ding_summary';

    /**
     * 钉钉预警
     * @return mixed
     */
    public function handle() {
        $storeIDs = Store::pluck('name', 'id'); //字段名为：id的值作为key 字段名为：name的值作为value
        $typeData = [1, 2, 3]; //类型 1 会员列表 2 积分管理 3 订单列表 4....
        $customerModel = CustomerService::getModel(0, '');

        foreach ($storeIDs as $storeId => $name) {
            foreach ($typeData as $type) {
                $customeTotal = $customerModel->where('store_id', $storeId)->count();
                $creditTotal = CreditService::getModel($storeId)->count();
                $orderTotal = OrderWarrantyService::getModel($storeId)->count();
                $time = Carbon::now()->toDateString();

                DingService::getDingData($storeId, $customeTotal, $creditTotal, $orderTotal, $time, $type); //把今天的数据添加或者更新到数据库
                //查询昨天的数据
                $pastData = DingService::getModel('default_connection_ding', '')->select('type', Constant::TOTAL)->where([['created_at', '=', date('Y-m-d', strtotime($time . ' -1 day'))], ['store_id', '=', $storeId], ['type', '=', $type]])->first();

                $exceptionName = '';
                $message = '';
                switch ($pastData['type']) {
                    case 1:
                        if ($customeTotal < $pastData[Constant::TOTAL]) {
                            $exceptionName = $name . '官网的会员列表数据有异常';
                            $message = '今天总数据： ' . $customeTotal . ' 昨天总数据： ' . $pastData[Constant::TOTAL];
                        }
                        break;
                    case 2:
                        if ($creditTotal < $pastData[Constant::TOTAL]) {
                            $exceptionName = $name . '官网的积分管理数据有异常';
                            $message = '今天总数据： ' . $creditTotal . ' 昨天总数据： ' . $pastData[Constant::TOTAL];
                        }
                        break;
                    case 3:
                        if ($orderTotal < $pastData[Constant::TOTAL]) {
                            $exceptionName = $name . '官网的订单列表数据有异常';
                            $message = '今天总数据： ' . $orderTotal . ' 昨天总数据： ' . $pastData[Constant::TOTAL];
                        }
                        break;

                    default:
                        break;
                }

                if ($exceptionName || $message) {
                    $parameters = [$exceptionName, $message, ''];
                    MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);
                }
            }
        }
        return true;
    }

}
