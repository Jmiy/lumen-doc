<?php

namespace App\Util\Payment\Creditcard;

use App\Helpers\BLogger;

/**
 * @link     MBP-api.doc 
 * @copyright Copyright (c) 2019-
 * @license   https://gateway.ssltrustpayment.com/MBPayment/api/transaction
 */
class Creditcard {

    public static $payUrl = 'https://gateway.ssltrustpayment.com/MBPayment/api/transaction';

    public static function getConfig($key = null, $default = null) {
        $config = [
            'code' => 'Creditcard',
            'name' => 'Credit Card Payment',
            'thumb' => '/image/paypal.png',
            'description' => '盟宝支付：http://merchants.moonbapay.com/MBMerchant/',
            'config' => (object) [
                (object) ['label' => '商户号', 'name' => 'MerchantNo', 'type' => 'text', 'value' => '100118', 'placeholder' => '请输入盟宝商户号', 'verify' => 'required'],
                (object) ['label' => '终端号', 'name' => 'TerminalNo', 'type' => 'text', 'value' => '88001', 'placeholder' => '请输入终端号', 'verify' => 'required'],
                (object) ['label' => '安全码', 'name' => 'Key', 'type' => 'text', 'value' => '8a00260a6b414f63921617d10ea034bf', 'placeholder' => '请输入安全码', 'verify' => 'required'],
                (object) ['label' => '回调地址', 'name' => 'ReturnURL', 'type' => 'text', 'value' => '', 'placeholder' => '可不填，系统自动生成', 'verify' => ''],
            ],
        ];
        return $key === null ? $config : \App\Util\ListData::getValidData($config, $key, $default);
    }

    /**
     * 支付
     * @param obj $order
     * @param obj $payment
     * @return int
     */
    public static function pay($orderSn, $payment) {

        // 设置来源
        //Session::put('is_paypal', true);

        $order = \App\Model\Order::constructOrderData($orderSn);

        // 配置信息
        $config = unserialize($payment->config);

        $data = [
            'MerchantNo' => $config['MerchantNo'], //商户号
            'TerminalNo' => $config['TerminalNo'], //终端号
            'TransactionType' => 'sales', //交易类型
            'Type' => 1, //接口类型 默认传递1；(1普通接口、2 app sdk、3快捷支付、4 虚拟
            'Model' => 'M', //交易模式
            'Encryption' => 'SHA256', //加密方式类型
            'CharacterSet' => 'UTF8', //字符编码
            'Amount' => $order->total_price, //支付金额
            'CurrencyCode' => strtoupper($order->currency_code), //交易币种
            'OrderNo' => $order->order_sn, //网店订单编号
            'Remark' => encrypt($order->id . '|Creditcard'), //商户预留字段
            'ReturnURL' => (isset($config['ReturnURL']) && !empty($config['ReturnURL'])) ? $config['ReturnURL'] : url('/api/pay/callback'), //返回支付结果到商户地址
            'TransactionURL' => request()->getHost(), //交易网站
            'BillCountry' => $order->user_addresses->country ? strtoupper($order->user_addresses->country->iso_code_2) : '', //账单国家 账单签收国家(国家代码简称，2位，大写)
            'BillState' => $order->user_addresses->zone ? strtoupper($order->user_addresses->zone->name) : '', //账单州(省)
            'BillCity' => $order->address_city, //账单城市
            'BillAddress' => $order->address_address, //账单详细地址
            'BillZipCode' => $order->address_code, //账单邮编
            'IpAddress' => getIP(), //客户的IP
            'BillFullName' => $order->address_name, //持卡人姓名 FristName.LastName (中间用点连接)
            'BillPhone' => $order->address_phone, //持卡人电话
            'ShipCountry' => $order->user_addresses->country ? strtoupper($order->user_addresses->country->iso_code_2) : '', //收货国家 规则:收货国家(国家代码简称，2位，大写)
            'ShipState' => $order->user_addresses->zone ? strtoupper($order->user_addresses->zone->name) : '', //收货州(省)
            'ShipCity' => $order->address_city, //收货城市
            'ShipAddress' => $order->address_address, //收货地址
            'ShipZipCode' => $order->address_code, //收货地址邮编
            'ShipEmail' => $order->address_email, //收货邮箱地址
            'ShipPhone' => $order->address_phone, //收货人电话
            'ShipFullName' => $order->address_name, //收货人姓名 FristName.LastName (中间用点连接)
            'CardNo' => preg_replace('/[(\xc2\xa0)|\s]+/', '', $order->card_no), //卡号
            'ExpYear' => $order->card_expiration_year, //有效年
            'ExpMonth' => $order->card_expiration_month, //信用卡有效月
            'CVV' => $order->card_cvc, //cvv
            'GoodsJson' => static::getFormattedGoodsString($order), //GoodsJson
        ];

        $strSignCode = $data['Encryption'] . '&' . $data['CharacterSet'] . '&' . $data['MerchantNo'] . '&' . $data['TerminalNo'] . '&' . $data['OrderNo'] . '&' . $data['CurrencyCode'] . '&' . $data['Amount'] . '&' . $data['IpAddress'] . '&' . $config['Key'];
        $SignCode = hash("sha256", $strSignCode);
        $data['SignCode'] = $SignCode;
        $postData = $data;
        $data = urldecode(http_build_query($data)); //必须使用 urldecode() 方法处理明文字符串

        $header = [
            'Content-Type: application/x-www-form-urlencoded;', //以表达形式提交
        ];

        $curlOptions = array(
            CURLOPT_URL => static::$payUrl, //访问URL
            CURLOPT_HTTPHEADER => $header, //一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100')
            CURLOPT_HEADER => false, //获取返回头信息
            CURLOPT_POST => true, //发送时带有POST参数
            CURLOPT_POSTFIELDS => $data, //请求的POST参数字符串 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 10,
            CURLOPT_TIMEOUT_MS => 1000 * 10,
        );

        /* 获取响应信息并验证结果 */
        $responseData = \App\Util\Curl::handle($curlOptions, true); //

        $logFileName = static::getConfig('code');
        $path = config('app_pay.log.path');
        BLogger::getLogger($logFileName, $path)->info('=====Creitcard开始支付=====');
        BLogger::getLogger($logFileName, $path)->info($postData);
        BLogger::getLogger($logFileName, $path)->info($data);
        BLogger::getLogger($logFileName, $path)->info($responseData);

        if ($responseData['responseText'] === false || $responseData['curlInfo']['http_code'] != 200) {//请求接口失败
            $url = url(config('app_pay.url.failure') . "?order_sn=" . $order->order_sn);
            BLogger::getLogger($logFileName, $path)->info('=====Creitcard支付失败记录==请求接口失败===');

            return ['code' => 6, 'url' => $url];
        }

        //$responseData['responseText'] = '{"tradeNo":"MBF1901140757140301","orderNo":"www.wdivorce88.com-714","currencyCode":"USD","amount":"49.99","merchantNo":"100118","terminalNo":"88001","erroCode":"00","erroMsg":"succeed"}';
        $responseData = json_decode($responseData['responseText'], true);
//        $responseData = [
//            'tradeNo' => 'MBF1901140757140301', //支付渠道交易流水号
//            'orderNo' => 'www.wdivorce88.com-714', //商户订单号
//            'currencyCode' => 'USD', //订单币种
//            'amount' => '49.99', //订单总金额
//            'merchantNo' => '100118', //商户号
//            'terminalNo' => '88001', //终端号
//            'erroCode' => '00', //成功标志
//            'erroMsg' => 'succeed', //返回交易成功失败信息 交易结束后给予的支付结果提示
//        ];

        $order = static::handleOrder($responseData, $order, $postData);

        return $order;
    }

    /**
     * 获取订单产品数据
     * @param obj $order
     * @return string $goods 订单产品数据
     */
    public static function getFormattedGoodsString($order) {

        $goodData = [];
        foreach ($order->order_products as $key => $order_product) {
            $goodData[] = [
                'goodsName' => $order_product->title,
                'goodsPrice' => $order_product->shop_price,
                'quantity' => $order_product->amount,
            ];
        }

        $goods = [
            'goodsInfo' => $goodData,
        ];

        return json_encode($goods);
    }

    /**
     * 处理支付回调结果
     * @param type $responseData
     * @param type $order
     * @param type $requestData
     * @return type
     */
    public static function handleOrder($responseData, $order = null, $requestData = null) {

//        $responseData = [
//            'tradeNo' => 'MBF1901140757140301', //支付渠道交易流水号
//            'orderNo' => $requestData['OrderNo'], //商户订单号
//            'currencyCode' => $requestData['CurrencyCode'], //订单币种
//            'amount' => $requestData['Amount'], //订单总金额
//            'merchantNo' => $requestData['MerchantNo'], //商户号
//            'terminalNo' => $requestData['TerminalNo'], //终端号
//            'erroCode' => '00', //成功标志
//            'erroMsg' => 'succeed', //返回交易成功失败信息 交易结束后给予的支付结果提示
//        ];

        $payStatus = 0; //支付状态 0:下单 1:支付成功 2:支付处理中 3:支付处理中 4:支付失败 5:支付异常
        $payCode = ''; //00是成功，01失败，02、03待处理
        $payMsg = '';
        $order_sn = $order ? $order->order_sn : 0;
        $currency_code = '';
        $total_price = '';
        $isPaymentException = false;
        if (isset($responseData['ErroCode'])) {//2、	交易异常返回
            $payCode = intval($responseData['ErroCode']);
            $payMsg = $responseData['ErroMsg'];
            $isPaymentException = true;
        } else if (isset($responseData['erroCode'])) {//1、	交易正常返回
            //{"tradeNo":"MBF1901140757140301","orderNo":"www.wdivorce88.com-714","currencyCode":"USD","amount":"49.99","merchantNo":"100118","terminalNo":"88001","erroCode":"00","erroMsg":"succeed"}
            $payCode = intval($responseData['erroCode']);
            $payMsg = $responseData['erroMsg'];

            if (isset($responseData['orderNo'])) {
                $order_sn = $responseData['orderNo'];
            }

            if (isset($responseData['currencyCode'])) {
                $currency_code = $responseData['currencyCode'];
            }

            if (isset($responseData['amount'])) {
                $total_price = $responseData['amount'];
            }
        }

        $url = url(config('app_pay.url.success') . $order_sn . "?success=true");
        $callback = empty($requestData) ? '异步回调' : '同步回调';
        $msg = '';
        switch ($payCode) {
            case 0://00是成功
                $payStatus = 1;
                $msg = '=====Creitcard支付成功====';
                break;

            case 1://01失败
                $payStatus = 4;
                $url = url(config('app_pay.url.failure') . "?msg=" . $msg);
                $msg = '=====Creitcard支付失败记录====';
                break;

            case 2://02、03待处理
            case 3:
                $payStatus = $payCode;
                $msg = '=====Creitcard支付处理中====';
                break;

            default:
                $payStatus = 5;
                $url = url(config('app_pay.url.failure') . "?msg=" . $payMsg);
                $msg = '=====Creitcard  支付异常 ====' . $msg;
                break;
        }

        $logFileName = static::getConfig('code');
        $path = config('app_pay.log.path');
        BLogger::getLogger($logFileName, $path)->info($msg . '=====order_sn==' . $order_sn . '=====' . $callback);
        if (empty($requestData)) {//如果是异步回调记录 回调数据
            BLogger::getLogger($logFileName, $path)->info($responseData);
        }

        if (!empty($order_sn)) {

            $where = [
                ['order_sn', '=', $order_sn],
                ['pay_status', '<>', 1],
            ];

            if ($isPaymentException === false) {
                $where[] = ['total_price', '=', $total_price];
                $where[] = ['currency_code', '=', $currency_code];
            }

            $data = [
                'pay_status' => $payStatus,
                'pay_code' => $payCode,
                'pay_msg' => $payMsg,
            ];
            \App\Model\Order::where($where)->update($data);
        }

        $rs = ['code' => $payStatus, 'url' => $url];

        return $rs;
    }

    //支付回调
    public static function callback() {
        $requestData = request()->all();
        return static::handleOrder($requestData);
    }

}

?>