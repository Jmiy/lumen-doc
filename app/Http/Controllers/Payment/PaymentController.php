<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\Payment\PaymentService;

class PaymentController extends Controller {

    /**
     * @param
     * $product 商品
     * $price 价钱
     * $shipping 运费
     * $description 描述内容
     */
    public function pay(Request $request) {
        return PaymentService::pay($this->driver, $request->all());
    }

    /**
     * 回调
     */
    public function callback(Request $request) {
        return PaymentService::callback($this->driver, $request->all());
    }

    public function notify(Request $request) {
        return Response::json(PaymentService::notify($this->driver));
    }

    public function refund(Request $request) {
        return Response::json(PaymentService::refund($this->driver, $request->all()));
    }

}
