<?php
/**
 * Created by Patazon.
 * @desc   : Task 进程是同步阻塞的，不能使用swoole_mysql...等异步方法(使用场景：Task就是给那些写不了协程安全的人用和没有办法协程化的api用)
 * @author : Jmiy_cen
 * @email  : Jmiy_cen@patazon.net
 * @date   : 2021/01/08 09:35
 */

namespace App\Tasks;

use App\Services\Traits\Base;

class TaskManager
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
    public static function handle($platform = '', $serviceProvider = '', $method = 'deliver', $parameters = [])
    {
        $_serviceProvider = '';
        switch ($serviceProvider) {
            case 'User':
                $_serviceProvider = 'Users';
                break;

            case 'Base':
                $serviceProvider = 'BaseTask';
                break;

            default:
                break;
        }

        $serviceProvider = is_array($serviceProvider) ? $serviceProvider : [$_serviceProvider, $serviceProvider];

        $service = static::getServiceProvider($platform, $serviceProvider);
        if (!($service && class_exists($service))) {
            return null;
        }

        $task = new $service($parameters);

        return $service::{$method}($task);
    }

}
