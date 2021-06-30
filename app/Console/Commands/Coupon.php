<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CouponService;

class Coupon extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: coupon';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        
        CouponService::monitorCoupon();

        return true;
    }

}
