<?php

namespace App\Util\Payment\Cod;

// 引用类
use Illuminate\Support\Facades\Session;

/**
 * 类
 */
class Cod {

    public $order;
    public $payment;

    // 构造函数
    public function __construct($order, $payment) {
        $this->order = $order;
        $this->payment = $payment;
    }

    public static function getConfig() {
        $config = [
            'code' => 'cod',
            'name' => '貨到付款',
            'thumb' => '/image/cod.png',
            'description' => '貨到付款 （Payment after Arrival of Goods） 指的是由快遞公司代收買家貨款，貨先送到客戶手上，客戶驗貨之後客戶再把錢給送貨員，也就是我們常說的"一手交錢一手交貨"，之後貨款再轉到賣家賬戶裏去。',
            'config' => [],
        ];
        return $config;
    }

    // 提交函数
    function redirect() {
        // 设置来源
        Session::put('is_cod', true);

        // 跳转链接
        $url = "/order/" . $this->order->id . "/result";
        
        return $url;
    }

    // 处理函数
    function callback() {
        // 验证来源
        $is_cod = Session::get('is_cod');
        Session::forget('is_cod');
        if ($is_cod) {
            return true;
        } else {
            return false;
        }
    }

}
