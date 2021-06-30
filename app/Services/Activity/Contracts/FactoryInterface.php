<?php

namespace App\Services\Activity\Contracts;

interface FactoryInterface
{

    /**
     * 执行服务
     * @param $storeId
     * @param $actId
     * @param string $method 方法
     * @param array $parameters 参数
     * @param null $serviceName 服务 默认：null(表示 使用默认服务：Service 来处理)
     * @return mixed|null
     */
    public static function handle($storeId, $actId, $method = '', $parameters = [], $serviceName = null);
}
