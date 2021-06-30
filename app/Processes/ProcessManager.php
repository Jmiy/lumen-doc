<?php

namespace App\Processes;

use App\Services\Traits\Base;

class ProcessManager
{

    use Base;

    /**
     * 执行服务
     * @param string $platform 平台
     * @param string|array $serviceProvider 服务提供者
     * @param string $method 执行方法
     * @param array $parameters 参数
     * @return boolean|max
     */
    public static function handle($platform = '', $serviceProvider = '', $method = 'write', $parameters = [])
    {
        $_serviceProvider = '';
        switch ($serviceProvider) {
            case 'User':
                $_serviceProvider = 'Users';
                break;

            case 'Base':
                $serviceProvider = 'BaseProcess';
                break;

            default:
                break;
        }

        $serviceProvider = is_array($serviceProvider) ? $serviceProvider : [$_serviceProvider, $serviceProvider];

        return static::managerHandle($platform, $serviceProvider, $method, $parameters);

    }

}
