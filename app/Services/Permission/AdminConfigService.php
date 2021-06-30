<?php

/**
 * 后台配置服务
 * User: Jmiy
 * Date: 2021-03-06
 * Time: 15:39
 */

namespace App\Services\Permission;

use App\Services\BaseService;
use App\Services\Traits\GetDefaultConnectionModel;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Services\UniqueIdService;

class AdminConfigService extends BaseService {

    use GetDefaultConnectionModel;

    /**
     * 获取要清空的tags
     * @return array
     */
    public static function getClearTags() {
        return ['adminConfig'];
    }

    public static function getCacheTags() {
        return 'adminConfig';
    }

    /**
     * 获取配置
     * @param $storeId
     * @param $route
     * @param int $userId
     * @return array|mixed
     */
    public static function getAdminConfig($storeId, $route, $userId = 0)
    {

        if (empty($route)) {
            return [
                'allConfigData' => [],
                'userConfidData' => [],
            ];
        }

        $key = md5($route);
        $ttl = static::getTtl();
        $parameters = [$key, $ttl, function () use ($storeId, $route) {
            $where = [
                //Constant::DB_TABLE_STORE_ID => $storeId,
                Constant::ROUTE_ID => UniqueIdService::getUniqueId($route),
            ];

            $select = [
                Constant::DB_TABLE_PRIMARY,
                'name',
                'label',
            ];

            $orderby = [
                ['sort', 'asc'],
            ];

            $dbExecutionPlan = [
                Constant::DB_EXECUTION_PLAN_PARENT => FunctionHelper::getExePlan($storeId, null, static::getNamespaceClass(), '', $select, $where, $orderby),
            ];

            return FunctionHelper::getResponseData(null, $dbExecutionPlan, false, false, 'list');
        }];

        $_confidData = [];
        $allConfigData = static::handleCache(static::getCacheTags(), FunctionHelper::getJobData(static::getNamespaceClass(), 'remember', $parameters));
        if (empty($allConfigData)) {
            return [
                'allConfigData' => $allConfigData,
                'userConfidData' => $_confidData ? $_confidData : $allConfigData,
            ];
        }

        if (empty($userId)) {
            return [
                'allConfigData' => $allConfigData,
                'userConfidData' => $_confidData ? $_confidData : $allConfigData,
            ];
        }

        $userConfidData = AdminUserConfigService::getAdminUserConfig($storeId, $route, $userId);
        if ($userConfidData) {
            $confidData = collect($allConfigData)->pluck(null, Constant::DB_TABLE_PRIMARY);
            foreach ($userConfidData as $item) {
                $_confidData[] = data_get($confidData, data_get($item, 'config_id'));
            }
        }

        return [
            'allConfigData' => $allConfigData,
            'userConfidData' => $_confidData ? $_confidData : $allConfigData,
        ];
    }

}
