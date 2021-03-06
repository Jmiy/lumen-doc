<?php

namespace App\Services\Store\Amazon\Erp\Finances;

use App\Services\Store\Amazon\Erp\BaseService;
use App\Util\Constant;
use App\Util\FunctionHelper;

class Rate extends BaseService {

    /**
     * 获取汇率
     * @param int $storeId 商城id
     * @param array $parameters              请求参数
     * @return array
     */
    public static function getRate($storeId = 1, $parameters = []) {

        $storeId = static::castToString($storeId);

        static::setConf($storeId);

        $requestData = [
            'jsonrpc' => 2.0,
            'method' => '',
            'id' => 1,
            'params' => [
                'month' => date('Y-m'),
                'orderBy' => 'currencyCode',
                'orderDirection' => 'desc',
                'pageSize' => 100
            ],
        ];

        $url = static::$storeUrl . '/rpc/financeExchangeRate/list';
        $username = static::$apiKey;
        $password = static::$password;
        $requestMethod = 'POST';
        $headers = [];
        $dataKey = 'result.list';
        $curlExtData = [
            'dataKey' => $dataKey,
            'keyInfo' => implode('_', array_filter([static::$storeId, data_get($parameters, 'operator', '')])),
        ];
        $res = static::request($url, $requestData, $username, $password, $requestMethod, $headers, $curlExtData);


        return data_get($res, Constant::RESPONSE_TEXT . Constant::LINKER . $dataKey);
    }

    /**
     * 获取统一平台汇率数据
     * @param int $storeId 品牌商店id
     * @param string $platform 平台标识
     * @param array $data 单条汇率数据
     * @return array
     */
    public static function getRateData($storeId, $platform, $data) {

        $storeId = static::castToString($storeId);

        if (empty($data)) {
            return [];
        }

        return [
            Constant::DB_TABLE_TYPE => 'exchange',
            Constant::DB_TABLE_DICT_KEY => data_get($data, 'currencyCode', ''),
            Constant::DB_TABLE_DICT_VALUE => data_get($data, 'exchangeRate', 0),
            Constant::DB_TABLE_OLD_CREATED_AT => FunctionHelper::handleTime(data_get($data, 'createdTime', '')),
        ];
    }

}
