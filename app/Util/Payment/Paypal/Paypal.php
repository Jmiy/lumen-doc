<?php

namespace App\Util\Payment\Paypal;

// 引用类
use Illuminate\Support\Facades\Session;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ShippingAddress;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use App\Helpers\BLogger;

/**
 * 类
 */
class Paypal {

    public $order;
    public $payment;
    public $apiContext;

    // 构造函数
    public function __construct($order, $payment) {
        $this->order = $order;
        $this->payment = $payment;
        // 配置信息
        $config = unserialize($this->payment->config);

        // 设置clientId和clientSecret
        $clientId = $config['paypal_client'];
        $clientSecret = $config['paypal_secret'];
        $this->apiContext = new ApiContext(
                new OAuthTokenCredential(
                $clientId, $clientSecret
                )
        );
        // 正式环境使用正式接口
        if (config('app.environment') == 'production') {
            $this->apiContext->setConfig(array('mode' => 'live'));
        }
    }

    public function getConfig() {
        $config = [
            'code' => 'paypal',
            'name' => 'Paypal',
            'thumb' => '/image/paypal.png',
            'description' => 'PayPal（www.paypal.com） 是在线付款解决方案的全球领导者，在全世界有超过七千一百六十万个帐户用户。PayPal 可在 56 个市场以 7 种货币（加元、欧元、英镑、美元、日元、澳元、港元）使用。',
            'config' => (object) array(
                (object) array('label' => '账户', 'name' => 'paypal_client', 'type' => 'text', 'value' => '', 'placeholder' => '请输入Paypal Client ID', 'verify' => 'required'),
                (object) array('label' => '密钥', 'name' => 'paypal_secret', 'type' => 'text', 'value' => '', 'placeholder' => '请输入Paypal Secret', 'verify' => 'required')
            ),
        ];
        return $config;
    }

    // 提交函数
    public function pay() {
        // 设置来源
        Session::put('is_paypal', true);

        // 设置支付方式
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // 设置商品详情
        $orderArray = [];
        foreach ($this->order->order_products as $order_product) {
            $item = new Item();
            $item->setName($order_product->title ?: 'PACKAGE')
                    ->setCurrency($this->order->currency_code)
                    ->setQuantity($order_product->amount)
                    ->setSku($order_product->name ?: 'NONE')
                    ->setPrice($order_product->shop_price);
            $orderArray[] = $item;
        }
        // 设置商品信息
        $itemList = new ItemList();
        $itemList->setItems($orderArray);

        // 设置地址
        // 先取消，国家 州 城市 邮编 必须对应
        // $address = new ShippingAddress();
        // $address->setRecipientName($this->order->address_name)
        //         ->setLine1($this->order->address_address)
        //         ->setLine2('')
        //         ->setCity($this->order->address_city)
        //         ->setState($this->order->address_country)
        //         ->setPhone($this->order->address_phone)
        //         ->setPostalCode($this->order->address_code)
        //         ->setCountryCode($this->getCountryCode($this->order->currency_code));
        // $itemList->setShippingAddress($address);
        // 设置细节
        $details = new Details();
        $details->setShipping($this->order->freight)
                ->setTax(0)
                ->setHandlingFee(0)
                ->setInsurance(0)
                ->setShippingDiscount($this->order->cut_price * (-1))
                ->setSubtotal($this->order->order_products->sum('total_price'));

        // 设置价格
        $amount = new Amount();
        $amount->setCurrency($this->order->currency_code)
                ->setTotal($this->order->total_price)
                ->setDetails($details);

        // 设置订单详情
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription($this->order->comment)
                ->setInvoiceNumber($this->order->order_sn);

        // 设置返回地址
        $redirectUrls = new RedirectUrls();

        $redirectUrls->setReturnUrl(url('/api/pay/callback?order_sn=' . $this->order->order_sn . '&success=true'))   //设置支付回调地址
                ->setCancelUrl(url("/order/0/result?order_sn=" . $this->order->order_sn)); //设置取消支付回调地址
        // 设置支付
        $payment = new PayPalRest();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)//设置回调地址
                ->setTransactions(array($transaction));
        $header = array('PayPal-Partner-Attribution-Id' => 'qianhaiimpre_Cart');
        // 创建支付
        try {
            $payment->createPay($this->apiContext, '', $header);
        } catch (\Exception $e) {
            return url('/order/0/result');
        }

        // 获取支付连接
        $approvalUrl = $payment->getApprovalLink();
        return $approvalUrl;
    }

    // 处理函数
    function callback() {
        // 验证来源
        $is_paypal = Session::get('is_paypal');
        Session::forget('is_paypal');
        if (!$is_paypal) {
            return false;
        }

        // 支付成功
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            $paymentId = $_GET['paymentId'];
            $payment = Payment::get($paymentId, $this->apiContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($_GET['PayerID']);
            try {
                $payment->execute($execution, $this->apiContext);
                // 支付成功的操作开始
                // 支付成功的操作结束
                return true;
            } catch (\Exception $ex) {
                try {
                    BLogger::getLogger('order', $this->order->site_id)->info('=====paypal发送数据=====');
                    BLogger::getLogger('order', $this->order->site_id)->info($ex->getMessage());
                } catch (\Exception $ex) {
                    
                }

                return url('/order/0/result');
            }
        } else {
            return false;
        }
    }

    function getCountryCode($currency_code) {
        switch ($currency_code) {
            case 'TWD': $country_code = 'TW';
                break;
            case 'THB': $country_code = 'TH';
                break;
            case 'USD': $country_code = 'US';
                break;
        }

        return $country_code;
    }

}

?>