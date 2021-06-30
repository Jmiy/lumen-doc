<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Util\FunctionHelper;
use App\Services\EmailService;

class SendCouponEmail extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendCouponEmail {storeId : The ID of the store} {--account= : account} {--createdAt= : createdAt} {--remark= : remark}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: SendCouponEmail';
    
    protected $accountKey = 'account';
    protected $remarkKey = 'remark';
    protected $storeIdKey = 'store_id';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $storeId = $this->argument('storeId'); //商城id
        if (empty($storeId)) {
            return true;
        }

        FunctionHelper::setTimezone($storeId); //设置时区

        $account = $this->option($this->accountKey) ? $this->option($this->accountKey) : '';
        if ($account) {
            $account = explode(',', $account);
            $account = implode("','", $account);
            $sql = "SELECT 
c.customer_id,c.account,c.store_id,ci.country,ci.first_name,ci.last_name,ci.ip
FROM crm_customer c 
LEFT JOIN crm_customer_info ci ON ci.customer_id=c.customer_id
WHERE c.account IN('" . $account . "')";
        } else {

            $createdAt = $this->option('createdAt') ? $this->option('createdAt') : '2019-08-01 00:00:00';
            if ($createdAt) {
                $createdAt = Carbon::parse($createdAt)->toDateTimeString();
            }

            $sql = "select c1.customer_id,c1.account,c1.store_id,ci1.country,ci1.first_name,ci1.last_name,ci1.ip 
from crm_customer c1
LEFT JOIN  crm_customer_info ci1 ON ci1.customer_id=c1.customer_id
where c1.customer_id in(SELECT 
MIN(c.customer_id) AS customer_id
FROM crm_customer c 
LEFT JOIN  crm_customer_info ci ON ci.customer_id=c.customer_id
LEFT JOIN crm_email_history mc ON mc.to_email=c.account AND mc.type='coupon' AND mc.group='customer' AND mc.store_id=$storeId
WHERE c.store_id=$storeId AND c.ctime>='$createdAt' AND mc.id IS NULL GROUP BY ci.ip) and c1.store_id=$storeId AND c1.ctime>='$createdAt'";
        }

        $remark = $this->option($this->remarkKey) ? $this->option($this->remarkKey) : '补发coupon';

        $data = DB::select($sql);
        $group = 'customer';
        foreach ($data as $item) {
            $ip = FunctionHelper::getClientIP($item->ip);

            $emialLogWhere = [
                $this->storeIdKey => $item->store_id,
                'type' => 'coupon',
                'ip' => $ip,
            ];
            EmailService::getModel($storeId, '')->where($emialLogWhere)->delete();

            $params = [
                $this->storeIdKey => $item->store_id,
                'country' => $item->country, //"US", "CA", "UK", "DE", "FR", "JP", "IN", "IT", "ES"
                'customer_id' => $item->customer_id,
                $this->accountKey => $item->account,
                $this->remarkKey => $remark,
                'group' => $group,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'ip' => $ip,
            ];
            EmailService::sendCouponEmail($params[$this->storeIdKey], $params);
        }

        return true;
    }

}
