<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Services\CustomerService;
use App\Services\Monitor\MonitorServiceManager;

class DingCustomerAccount extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ding_customer_account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: ding_customer_account';

    /**
     * 相同的ip和邮箱注册的账号不能大于1，即账号重复，否则钉钉预警
     * @return mixed
     */
    public function handle() {
        $storeIDs = Store::pluck('name', 'id'); //字段名为：id的值作为key 字段名为：name的值作为value
        $customerModel = CustomerService::getModel(0, '');
        foreach ($storeIDs as $storeId => $name) {
            $customerModel->from('customer as a')
                    ->leftJoin('customer_info as b', 'a.customer_id', '=', 'b.customer_id')
                    ->where([['a.store_id', '=', $storeId], ['b.ip', '!=', '']])->groupBy('a.account', 'b.ip')->havingRaw('COUNT(*) > 1')->orderBy('a.customer_id', 'desc')
                    ->select(DB::raw('COUNT(*)'), 'a.account', 'b.ip')
                    ->chunk(100, function ($data) use($name) {//chunk()方法一次获取结果集的一小块，并将其传递给 闭包 函数进行处理
                        $messageData = [];
                        foreach ($data as $customer) {
                            $account = data_get($customer, 'account', '');
                            $ip = data_get($customer, 'ip', '');
                            $messageData[] = '(' . $account . ' - ' . $ip . ')';
                        }

                        $exceptionName = $name . '官网的注册账号ip重复的账号如下：';
                        $message = implode(',', $messageData);

                        $parameters = [$exceptionName, $message, ''];
                        MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);
                    });
        }
        return true;
    }

}
