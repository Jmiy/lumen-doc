<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Util\Constant;

class Controller extends BaseController {

    public $storeIdKey = Constant::DB_TABLE_STORE_ID;
    public $accoutKey = Constant::DB_TABLE_ACCOUNT;
    public $customerPrimaryKey = Constant::DB_TABLE_CUSTOMER_PRIMARY;
    public $countryKey = Constant::DB_TABLE_COUNTRY;
    public $actIdKey = Constant::DB_TABLE_ACT_ID;
    public $storeId = 0; //商城id
    public $account = ''; //会员账号
    public $actId = 0; //活动id
    public $customerId = 0; //会员id
    public $ip = ''; //ip
    public $page = 1; //页码
    public $pageSize = 10; //每页记录条数
    public $platform = ''; //平台
    public $driver = ''; //驱动

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(Request $request) {
        $this->storeId = $request->input($this->storeIdKey, 0); //商城id
        $this->account = $request->input($this->accoutKey, ''); //会员账号
        $this->actId = $request->input($this->actIdKey, 0); //活动id
        $this->customerId = $request->input($this->customerPrimaryKey, 0); //会员id
        $this->ip = $request->input(Constant::DB_TABLE_IP, ''); //ip

        $this->page = $request->input(Constant::REQUEST_PAGE, $this->page); //页码
        $this->pageSize = $request->input(Constant::REQUEST_PAGE_SIZE, $this->pageSize); //每页记录条数

        $this->platform = $request->input(Constant::DB_TABLE_PLATFORM, Constant::PARAMETER_STRING_DEFAULT);
        $this->driver = $request->input(Constant::DRIVER, Constant::PARAMETER_STRING_DEFAULT);//驱动
    }

}
