<?php

/**
 * 活动配置服务
 * User: Jmiy
 * Date: 2020-03-25
 * Time: 08:51
 */

namespace App\Services\Permission;

use App\Services\BaseService;
use App\Services\Traits\GetDefaultConnectionModel;
use App\Services\UniqueIdService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Illuminate\Support\Arr;

class AdminUserConfigService extends BaseService
{
    use GetDefaultConnectionModel;

    /**
     * 获取要清空的tags
     * @return array
     */
    public static function getClearTags()
    {
        return ['adminUserConfig'];
    }

    public static function getCacheTags()
    {
        return 'adminUserConfig';
    }

    /**
     * 获取用户配置缓存key
     * @param int $storeId 品牌id
     * @param string $route 后台路由
     * @param int $userId 用户id
     * @return string 用户配置缓存key
     */
    public static function getUserConfigCacheKey($storeId = 0, $route = '', $userId = 0)
    {
        return md5(json_encode([$storeId, $route, $userId]));
    }

    /**
     * 获取用户配置
     * @param int $storeId 品牌id
     * @param string $route 后台路由
     * @param int $userId 用户id
     * @return array|mixed
     */
    public static function getAdminUserConfig($storeId = 0, $route = '', $userId = 0)
    {

        if (empty($userId) || empty($route)) {
            return [];
        }

        $key = static::getUserConfigCacheKey($storeId, $route, $userId);
        $ttl = static::getTtl();
        $parameters = [$key, $ttl, function () use ($storeId, $route, $userId) {
            $where = [
                Constant::DB_TABLE_STORE_ID => $storeId,
                Constant::ROUTE_ID => UniqueIdService::getUniqueId($route),
                'user_id' => $userId,
            ];

            $select = [
                'config_id',
            ];

            $orderby = [
                ['sort', 'asc'],
            ];

            $dbExecutionPlan = [
                Constant::DB_EXECUTION_PLAN_PARENT => FunctionHelper::getExePlan($storeId, null, static::getNamespaceClass(), '', $select, $where, $orderby),
            ];

            return FunctionHelper::getResponseData(null, $dbExecutionPlan, false, false, 'list');
        }];

        return static::handleCache(static::getCacheTags(), FunctionHelper::getJobData(static::getNamespaceClass(), 'remember', $parameters));

    }

    /**
     * 更新用户配置
     * @param int $storeId 品牌id
     * @param string $route 后台路由
     * @param int $userId 用户id
     * @param array $requestData 请求参数
     * @return bool true:更新成功  false:更新失败
     */
    public static function userConfig($storeId = 0, $route = '', $userId = 0, $requestData = [])
    {
        if (empty($userId)) {
            return false;
        }

        $data = data_get($requestData, 'data');
        if (null === $data) {//如果用户未设置，就直接返回
            return true;
        }
        $data = array_values(array_filter(array_unique($data)));//去重去空 防止数据无效

        //删除用户配置
        $where = [
            Constant::DB_TABLE_STORE_ID => $storeId,
            Constant::ROUTE_ID => UniqueIdService::getUniqueId($route),
            'user_id' => $userId,
        ];
        static::delete($storeId, $where);

        //添加用户配置
        $insertData = [];
        foreach ($data as $key => $configId) {
            $insertData[] = Arr::collapse([$where, [
                'config_id' => $configId,
                'sort' => $key
            ]]);
        }

        if (!empty($insertData)) {
            static::getModel($storeId)->insert($insertData);
        }

        //清空当前用户配置缓存
        static::handleCache(static::getCacheTags(), FunctionHelper::getJobData(static::getNamespaceClass(), 'forget', [static::getUserConfigCacheKey($storeId, $route, $userId)]));

        return true;

    }

}
