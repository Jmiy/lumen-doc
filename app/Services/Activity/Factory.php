<?php

namespace App\Services\Activity;

use App\Services\Activity\Contracts\FactoryInterface;

use App\Services\ActivityService;
use App\Util\Constant;
use App\Services\Traits\Base;

class Factory implements FactoryInterface
{
    use Base;

    /**
     * 执行服务
     * @param $storeId
     * @param $actId
     * @param string $method 方法
     * @param array $parameters 参数
     * @param null $serviceName 服务 默认：null(表示 使用默认服务：Service 来处理)
     * @return mixed|null
     */
    public static function handle($storeId, $actId, $method = '', $parameters = [], $serviceName = null)
    {

        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::BASE, [
            Constant::PROVIDER,
            Constant::CONTRACT,
        ]);

        $provider = data_get($actConfig, Constant::BASE . '_' . Constant::PROVIDER . Constant::LINKER . Constant::DB_TABLE_VALUE);
        if ($provider === null) {
            return null;
        }

        $contract = data_get($actConfig, Constant::BASE . '_' . Constant::CONTRACT . Constant::LINKER . Constant::DB_TABLE_VALUE);
        $serviceName = is_array($serviceName) ? $serviceName : [$contract ?? Constant::CONTRACT_DEFAULT];

        return static::managerHandle($provider, $serviceName, $method, $parameters);
    }
}
