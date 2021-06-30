<?php

namespace App\Http\Controllers\Admin;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Util\Constant;

class Controller extends BaseController {

    public $storeIdKey = Constant::DB_TABLE_STORE_ID;
    public $accoutKey = Constant::DB_TABLE_ACCOUNT;
    public $customerPrimaryKey = Constant::DB_TABLE_CUSTOMER_PRIMARY;
    public $remarkKey = 'remark';
    public $actionKey = 'action';
    public $countryKey = Constant::DB_TABLE_COUNTRY;
    public $listMethod = 'getListData';
    public $creditKey = 'credit';
    public $storeId = Constant::PARAMETER_INT_DEFAULT; //商城id
    public $token = Constant::PARAMETER_STRING_DEFAULT; //后台token
    public $operator = Constant::PARAMETER_STRING_DEFAULT; //后台用户

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(Request $request) {
        $this->storeId = $request->input(Constant::DB_TABLE_STORE_ID, $this->storeId); //商城id
        $this->token = $request->input(Constant::TOKEN, $this->token); //商城id
        $this->operator = $request->input(Constant::DB_TABLE_OPERATOR, $this->operator); //后台用户
    }

}
