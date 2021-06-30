<?php

namespace App\Services\Store\Shopify;

use App\Services\BaseService as AppBaseService;
use App\Util\Curl;
use App\Util\FunctionHelper;
use App\Util\Response;
use Illuminate\Http\Request;
use App\Util\Constant;
use App\Services\Traits\Base;

class BaseService {

    use Base;

    protected static $appSecret; //app秘钥
    protected static $apiKey; //apiKey
    protected static $password; //api密码
    protected static $sharedSecret; //api共享秘钥
    protected static $storeUrl; //api url
    protected static $schema; //api schema
    protected static $colid;
    protected static $graphqlUrl; //graphqlUrl
    protected static $accessToken; //X-Shopify-Storefront-Access-Token
    protected static $storeId; //商城id

    public static function request($url, $requestData = [], $username = '', $password = '', $requestMethod = 'POST', $headers = [], $extData = []) {

        $headers = $headers ? $headers : [
            'Content-Type: application/json; charset=utf-8', //设置请求内容为 json  这个时候post数据必须是json串 否则请求参数会解析失败
        ];

        $curlOptions = [
            CURLOPT_CONNECTTIMEOUT_MS => 1000 * 100,
            CURLOPT_TIMEOUT_MS => 1000 * 100,
            CURLOPT_USERNAME => $username ? $username : static::$apiKey, //设置账号
            CURLOPT_PASSWORD => $password ? $password : static::$password, //设置密码
        ];

        $responseData = Curl::request($url, $headers, $curlOptions, $requestData, $requestMethod);

        if (data_get($responseData, 'responseText', false) === false) {//如果请求失败，就重复请求一次
            $responseData = Curl::request($url, $headers, $curlOptions, $requestData, $requestMethod);
        }

        $class = explode('\\', get_called_class());

        $level = data_get($extData, 'logLevel', 'log');
        $type = data_get($extData, 'logType', Constant::PLATFORM_SHOPIFY);
        $subtype = data_get($extData, 'logSubtype', end($class)); //子类型
        $keyinfo = data_get($extData, 'keyInfo', static::$storeId);
        $content = $requestData;
        $subkeyinfo = $url;
        $dataKey = data_get($extData, 'dataKey', null);

        if (data_get($responseData, 'responseText', false) === false) {//如果接口请求异常，就记录异常日志
            $level = 'exception';
            static::logs($level, $type, $subtype, $keyinfo, $content, $subkeyinfo, $responseData);
        }

        static::logs($level, $type, $subtype, $keyinfo, $content, $subkeyinfo, $responseData, $dataKey);

        return $responseData;
    }

    /**
     * 设置配置信息
     * @param int $storeId 商城id
     * @param array $configData 配置数据
     */
    public static function setConf($storeId = 2, $configData = []) {

        $storeId = static::castToString($storeId);

        $request = app('request');
        //dump($request->route());
        $appEnv = $request->input('app_env', null); //开发环境 $request->route('app_env', null)
//        $appEnv = 'sandbox';
        $appEnv = $appEnv === null ? $storeId : ($appEnv . '_' . $storeId);

        //设置时区
        FunctionHelper::setTimezone($storeId);

        $config = config('app.sync.' . $appEnv . '.'.Constant::PLATFORM_SHOPIFY, $configData);
        if (empty($config)) {
            $extWhere = [
                Constant::DICT => [],
                Constant::DICT_STORE => [
                    Constant::DB_TABLE_COUNTRY => $request->input('app_env', null) ?? '',
                ],
            ];
            $config = AppBaseService::getMergeConfig($storeId, Constant::PLATFORM_SHOPIFY, $extWhere);
        }

        static::$appSecret = data_get($config, 'appSecret', '');
        static::$apiKey = data_get($config, 'apiKey', '');
        static::$password = data_get($config, 'password', '');
        static::$sharedSecret = data_get($config, 'sharedSecret', '');
        static::$storeUrl = data_get($config, 'storeUrl', '');
        static::$schema = data_get($config, 'schema', '');

        static::$graphqlUrl = data_get($config, 'graphqlUrl', '');
        static::$accessToken = data_get($config, 'accessToken', '');

        static::$storeId = $storeId;

        return true;
    }

    /**
     * 验证回调参数有效性
     * @param int $storeId 商城id
     * @param string $data  回调参数
     * @param string $hmac_header 回调请求头数据签名
     * @return boolean true:有效 false:无效
     */
    public static function verifyWebhook($storeId, $data, $hmac_header) {
        static::setConf($storeId);
        //$appSecret = "a330bfb9c584aba17ca78e5fa3033488d667015058f322b93afc3ec1bca404a5";
        $appSecret = static::$appSecret;
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $appSecret, true));

        return hash_equals($hmac_header, $calculated_hmac);
    }

    /**
     * 回调验证
     * @param int $storeId 官网id
     * @param string $appEnv 环境
     * @param Request $request 请求对象
     * @return array
     */
    public static function callBackVerify($storeId, Request $request) {

        $key = "X-Shopify-Hmac-Sha256";
        $hmac = $request->headers->get($key, '');
        //$data = file_get_contents('php://input');
        // 调用getContent()来获取原始的POST body，而不能用file_get_contents('php://input')
        $data = $request->getContent();

        $verify = static::verifyWebhook($storeId, $data, $hmac);

        if (!$verify) {
            return Response::getDefaultResponseData(10025, 'verify false');
        }

        return Response::getDefaultResponseData(1);
    }

    /**
     * 获取业务id(order_id|refund_id|fulfillment_id|等)
     * @param string $businessType 业务类型(order|refund|fulfillment|等)
     * @param string $businessSubType 业务子类型(create|update|delete|cancel|paid|等)
     * @param array $data 请求参数
     * @return mix 业务id(order_id|refund_id|fulfillment_id|等)
     */
    public static function getBusinessId($businessType, $businessSubType, $data) {
        return data_get($data, Constant::DB_TABLE_PRIMARY) ?? 0; //订单ID;
    }

    /**
     * 获取业务关联类型(如:refund关联order等)
     * @param string $businessType 业务类型(order|refund|fulfillment|等)
     * @param string $businessSubType 业务子类型(create|update|delete|cancel|paid|等)
     * @param array $data 请求参数
     * @return mix 业务关联id(如:refund_id关联order_id等)
     */
    public static function getBusinessType($businessType, $businessSubType, $data) {
        $extType = '';
        switch ($businessType) {
            case Constant::BUSINESS_TYPE_ORDER:
                $extType = \App\Services\Platform\OrderService::getModelAlias();

                break;
            case Constant::BUSINESS_TYPE_FULFILLMENT:
                $extType = \App\Services\Platform\FulfillmentService::getModelAlias();

                break;
            case Constant::BUSINESS_TYPE_REFUND:
                $extType = \App\Services\Platform\RefundService::getModelAlias();

                break;
            case Constant::BUSINESS_TYPE_TRANSACTION:
                $extType = \App\Services\Platform\TransactionService::getModelAlias();

                break;

            default:
                break;
        }
        return $extType;
    }

    /**
     * 获取业务关联id(如:refund关联order_id等)
     * @param string $businessType 业务类型(order|refund|fulfillment|等)
     * @param string $businessSubType 业务子类型(create|update|delete|cancel|paid|等)
     * @param array $data 请求参数
     * @return mix 业务关联id(如:refund_id关联order_id等)
     */
    public static function getBusinessExtId($businessType, $businessSubType, $data) {

        $extId = '';
        switch ($businessType) {
            case Constant::BUSINESS_TYPE_FULFILLMENT:
            case Constant::BUSINESS_TYPE_REFUND:
            case Constant::BUSINESS_TYPE_TRANSACTION:
                $extId = data_get($data, Constant::DB_TABLE_ORDER_ID) ?? 0; //业务关联id(如:refund关联order_id等)

                break;

            default:
                break;
        }
        return $extId;
    }

    /**
     * 获取业务关联类型(如:refund关联order等)
     * @param string $businessType 业务类型(order|refund|fulfillment|等)
     * @param string $businessSubType 业务子类型(create|update|delete|cancel|paid|等)
     * @param array $data 请求参数
     * @return mix 业务关联id(如:refund_id关联order_id等)
     */
    public static function getBusinessExtType($businessType, $businessSubType, $data) {
        $extType = '';
        switch ($businessType) {
            case Constant::BUSINESS_TYPE_FULFILLMENT:
            case Constant::BUSINESS_TYPE_REFUND:
            case Constant::BUSINESS_TYPE_TRANSACTION:
                $extType = \App\Services\Platform\OrderService::getModelAlias();

                break;

            default:
                break;
        }
        return $extType;
    }

}
