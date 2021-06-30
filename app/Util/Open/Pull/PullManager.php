<?php

namespace App\Util\Open\Pull;

use App\Helpers\BLogger;
use App\Model\Order;
use App\Model\Product;

class PullManager {

    /**
     * 获取提供商
     * @param string $provider 提供商
     * @return string $provider 提供商
     */
    public static function getProvider($provider = 'Shopify') {
        $provider = "\\App\\Util\\Open\\Pull\\{$provider}\\{$provider}Manager";
        if (!class_exists($provider)) {//如果没有对应的CDN提供商，就直接返回原始url
            return '';
        }
        return $provider;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public static function handle($site = [], $requestData = [], $requestMethod = 'GET', $provider = 'Shopify') {

        $provider = static::getProvider($provider);
        if (empty($provider)) {//如果没有对应的CDN提供商，就直接返回原始url
            return [];
        }

        $data = $provider::handle($site, $requestData, $requestMethod);

        return $data;
    }

    /**
     * 获取统一的 中间件服务 订单数据
     * @param array $site 站点数据
     * @param string $provider 供应商
     * @param array $requestData 请求参数
     * @param string $requestMethod 请求方法
     * @return boolean|array $orderData 统一的 中间件服务 订单数据
     */
    public static function getOrderData($site = [], $provider = 'Shopify', $requestData = [], $requestMethod = 'GET') {

        $provider = static::getProvider($provider);
        if (empty($provider)) {//如果没有对应的CDN提供商，就直接返回原始url
            return [];
        }

        //拉取提供商订单数据
        $t = microtime(true);
        $orderData = $provider::getOrders($site, $requestData, $requestMethod);
        BLogger::getLogger('pull-order', 'queue')->info('=====拉取提供商订单数据=====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($orderData);
        if (empty($orderData)) {
            return $orderData;
        }

        /*         * *************获取中间件服务订单数据*************************** */
        $t = microtime(true);
        $orderData = $provider::getOrderData($site, $orderData);
        BLogger::getLogger('pull-order', 'queue')->info('=====中间件服务订单数据=====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($orderData);
        if (empty($orderData)) {
            return $orderData;
        }

        $t = microtime(true);
        $orderData = Order::add($orderData);
        BLogger::getLogger('pull-order', 'queue')->info('=====中间件服务订单数据 导入到 中间件服务 数据库 =====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($orderData);

        return $orderData;
    }

    /**
     * 获取统一的订单产品数据
     * @param array $site 站点数据
     * @param array $orderData  统一的 中间件服务 订单数据
     * @param string $provider 供应商
     * @param array $requestData 请求参数
     * @param string $requestMethod 请求方法
     * @return boolean|array $productData 统一的订单产品数据
     */
    public static function getOrderProducts($site = [], $orderData = [], $provider = 'Shopify', $requestData = [], $requestMethod = 'GET') {
        $provider = static::getProvider($provider);
        if (empty($provider)) {//如果没有对应的提供商，就直接返回
            return [];
        }

        /*         * *************拉取供应商订单产品数据************************* */
        $t = microtime(true);
        $productData = $provider::getOrderProducts($site, $orderData, $requestData);
        BLogger::getLogger('pull-order', 'queue')->info('=====拉取供应商订单产品数据=====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($productData);
        if (empty($productData)) {
            return $productData;
        }

        /*         * *************将供应商产品导入到 中间件服务 数据库************************* */
        $t = microtime(true);
        $productData = Product::add($productData);
        BLogger::getLogger('pull-order', 'queue')->info('=====将供应商产品导入到 中间件服务 数据库=====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($productData);

        return $productData;
    }

    /**
     * 导入完整的订单数据
     * @param array $site 站点数据
     * @param array $orderData  统一的 中间件服务 订单数据
     * @param array $productData 统一的订单产品数据
     * @param string $provider 供应商
     * @return array $orderData
     */
    public static function importOrders($site = [], $orderData = [], $productData = [], $provider = 'Shopify') {

        if (empty($orderData) || empty($productData)) {
            return $orderData;
        }

        $provider = static::getProvider($provider);
        if (empty($provider)) {//如果没有对应的提供商，就直接返回
            return [];
        }

        /*         * *************获取完整的中间件服务订单数据*************************** */
        $t = microtime(true);
        $orderData = $provider::getCompleteOrderData($site, $orderData, $productData);
        BLogger::getLogger('pull-order', 'queue')->info('=====获取完整的中间件服务订单数据=====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($orderData);

        $t = microtime(true);
        $orderData = Order::add($orderData);
        BLogger::getLogger('pull-order', 'queue')->info('=====修复 中间件服务订单数据 =====' . (number_format(microtime(true) - $t, 8, '.', '') * 1000) . ' ms');
        BLogger::getLogger('pull-order', 'queue')->info($orderData);

        return $orderData;
    }

}
