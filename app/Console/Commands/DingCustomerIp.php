<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Services\CustomerService;
use App\Services\DictStoreService;
use App\Services\Monitor\MonitorServiceManager;

class DingCustomerIp extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ding_customer_ip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: ding_customer_ip';

    /**
     * 相同的ip注册的账号不能大于限制的次数，否则钉钉预警
     * @return mixed
     */
    public function handle() {
        $storeIDs = Store::pluck('name', 'id'); //字段名为：id的值作为key 字段名为：name的值作为value
        $customerModel = CustomerService::getModel(0, '');
        foreach ($storeIDs as $storeId => $name) {
            $registeredIpLimit = DictStoreService::getByTypeAndKey($storeId, 'signup', 'ip_limit', true); //根据商店id获取限制的次数
            if (empty($registeredIpLimit)) {//获取不到使用默认的商店id获取
                $registeredIpLimit = DictStoreService::getByTypeAndKey(0, 'signup', 'ip_limit', true);
            }
            $customerModel->from('customer as a')
                    ->leftJoin('customer_info as b', 'a.customer_id', '=', 'b.customer_id')
                    ->where([['a.store_id', '=', $storeId], ['b.ip', '!=', '']])->groupBy('b.ip')->havingRaw('COUNT(*) > ' . $registeredIpLimit)->orderBy('a.customer_id', 'desc')
                    ->select(DB::raw('COUNT(*)'), 'a.account', 'b.ip')
                    ->chunk(100, function ($data) use($name, $registeredIpLimit) {//chunk()方法一次获取结果集的一小块，并将其传递给 闭包 函数进行处理
                        $messageData = [];
                        foreach ($data as $customer) {
                            $count = data_get($customer, 'COUNT(*)', '');
                            $ip = data_get($customer, 'ip', '');
                            $messageData[] = '(重复次数 ' . $count . ' - ' . $ip . ')'; //把超出限制的ip放到数组里
                        }
                        $exceptionName = $name . '官网的注册账号ip大于' . $registeredIpLimit . '的账号如下：';
                        $message = implode(',', $messageData);
                        $parameters = [$exceptionName, $message, ''];
                        MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);
                    });
        }
        return true;
    }

}
