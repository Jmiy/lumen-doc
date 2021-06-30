<?php

/**
 * 留言
 * User: Jmiy
 * Date: 2021-05-07
 * Time: 10:18
 */

namespace App\Services;

use App\Services\Permission\AdminConfigService;
use Illuminate\Support\Arr;
use App\Util\Constant;
use App\Util\Response;
use App\Util\FunctionHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveMessageService extends BaseService
{

    /**
     * 处理反馈邮件
     * @param int $storeId 商城id
     * @param array $requestData 请求数据
     * @return array
     */
    public static function add($storeId, $requestData = [])
    {

        $account = data_get($requestData, Constant::DB_TABLE_ACCOUNT, Constant::PARAMETER_STRING_DEFAULT);
        $customerId = data_get($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT);
        $data = [
            Constant::DB_TABLE_ACCOUNT => $account,
            Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
            Constant::EXCEPTION_MSG => data_get($requestData, Constant::EXCEPTION_MSG, Constant::PARAMETER_STRING_DEFAULT),
        ];
        $id = static::getModel($storeId)->insertGetId($data);
        if (empty($id)) {
            return Response::getDefaultResponseData(110000);
        }

        return Response::getDefaultResponseData(Constant::RESPONSE_SUCCESS_CODE);
    }

    /**
     * 获取公共参数
     * @param array $params 请求参数
     * @return array
     */
    public static function getPublicData($params, $order = [])
    {

        $where = [];

        $account = data_get($params, Constant::DB_TABLE_ACCOUNT, Constant::PARAMETER_STRING_DEFAULT); //邮箱
        if ($account) {
            $where[] = [Constant::DB_TABLE_ACCOUNT, '=', $account];
        }

        $_where = [];
        if (data_get($params, Constant::DB_TABLE_PRIMARY, 0)) {
            $_where[Constant::DB_TABLE_PRIMARY] = $params[Constant::DB_TABLE_PRIMARY];
        }

        if ($where) {
            $_where[] = $where;
        }

        $order = $order ? $order : [[Constant::DB_TABLE_CREATED_AT, Constant::ORDER_DESC],[Constant::DB_TABLE_PRIMARY, Constant::ORDER_DESC]];
        return Arr::collapse([parent::getPublicData($params, $order), [
            'where' => $_where,
        ]]);
    }

    /**
     * 获取select的字段数组
     * @return array
     */
    public static function getSelect()
    {
        return [
            Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_CUSTOMER_PRIMARY,
            Constant::DB_TABLE_ACCOUNT,
            Constant::EXCEPTION_MSG,
            Constant::DB_TABLE_NAME,
            Constant::AVATAR,
            Constant::DB_TABLE_CREATED_AT,
            Constant::DB_TABLE_UPDATED_AT,
        ];
    }

    /**
     * 列表
     * @param array $params 请求参数
     * @param boolean $toArray 是否转化为数组 true:是 false:否 默认:true
     * @param boolean $isPage 是否分页 true:是 false:否 默认:true
     * @param array $select 查询字段
     * @param boolean $isRaw 是否原始 select true:是 false:否 默认:false
     * @param boolean $isGetQuery 是否获取 query
     * @param boolean $isOnlyGetCount 是否仅仅获取总记录数
     * @return array|\Illuminate\Database\Eloquent\Builder 列表数据|Builder
     */
    public static function getListData($params, $toArray = true, $isPage = true, $select = [], $isRaw = false, $isGetQuery = false, $isOnlyGetCount = false)
    {
        $_data = static::getPublicData($params, []);

        $where = data_get($_data, Constant::DB_EXECUTION_PLAN_WHERE, null);
        $order = data_get($params, Constant::ORDER_BY, data_get($_data, Constant::ORDER, []));
        $pagination = data_get($_data, Constant::DB_EXECUTION_PLAN_PAGINATION, []);
        $limit = data_get($params, Constant::ACT_LIMIT_KEY, data_get($pagination, Constant::REQUEST_PAGE_SIZE, 10));
        $offset = data_get($params, Constant::DB_EXECUTION_PLAN_OFFSET, data_get($pagination, Constant::DB_EXECUTION_PLAN_OFFSET, Constant::PARAMETER_INT_DEFAULT));
        $storeId = data_get($params, Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);

        $select = $select ? $select : static::getSelect();

        $isExport = data_get($params, 'is_export', data_get($params, 'srcParameters.0.is_export', Constant::PARAMETER_INT_DEFAULT));

        $handleData = [];
        $joinData = [];

        $customerInfoSelect = [
            Constant::DB_TABLE_CUSTOMER_PRIMARY,
            Constant::DB_TABLE_FIRST_NAME, //first name
            Constant::DB_TABLE_LAST_NAME,  //last name
            Constant::AVATAR,  //avatar
        ];

        $with = [
            'customer_info' => FunctionHelper::getExePlan(
                $storeId,
                null,
                Constant::PARAMETER_STRING_DEFAULT,
                Constant::PARAMETER_STRING_DEFAULT,
                $customerInfoSelect,
                [],
                [],
                null,
                null,
                false, [],
                false,
                Constant::PARAMETER_ARRAY_DEFAULT,
                Constant::PARAMETER_ARRAY_DEFAULT,
                Constant::PARAMETER_ARRAY_DEFAULT,
                Constant::PARAMETER_ARRAY_DEFAULT,
                Constant::HAS_ONE,
                false,
                Constant::PARAMETER_ARRAY_DEFAULT
            ), //关联账号
        ];
        $unset = [
            'customer_info',
            Constant::DB_TABLE_FIRST_NAME, //first name
            Constant::DB_TABLE_LAST_NAME,
        ];//
        $exePlan = FunctionHelper::getExePlan($storeId, null, static::getNamespaceClass(), '', $select, $where, $order, $limit, $offset, $isPage, $pagination, $isOnlyGetCount, $joinData, Constant::PARAMETER_ARRAY_DEFAULT, $handleData, $unset);

        if (data_get($params, Constant::DB_EXECUTION_PLAN_IS_ONLY_GET_PRIMARY, false)) {//如果仅仅获取主键id，就不需要处理数据，不关联
            $dbExecutionPlan = [
                Constant::DB_EXECUTION_PLAN_PARENT => $exePlan,
            ];
        } else {

            $itemHandleDataCallback = [
                Constant::DB_TABLE_NAME => function ($item) {//名字
                    $name = implode(' ', array_filter([data_get($item, 'customer_info.' . Constant::DB_TABLE_FIRST_NAME), data_get($item, 'customer_info.' . Constant::DB_TABLE_LAST_NAME)]));
                    return $name ? $name : data_get($item, Constant::DB_TABLE_NAME);
                },
                Constant::AVATAR => function ($item) {//头像
                    $avatar = data_get($item, 'customer_info.' . Constant::AVATAR);
                    return $avatar ? $avatar : data_get($item, Constant::AVATAR);
                },
            ];

            $dbExecutionPlan = [
                Constant::DB_EXECUTION_PLAN_PARENT => $exePlan,
                Constant::DB_EXECUTION_PLAN_WITH => $with,
                Constant::DB_EXECUTION_PLAN_ITEM_HANDLE_DATA => FunctionHelper::getExePlanHandleData(null, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_ARRAY_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, Constant::PARAMETER_STRING_DEFAULT, true, $itemHandleDataCallback),
            ];
        }

        $dataStructure = 'list';
        $flatten = false;
        return FunctionHelper::getResponseData(null, $dbExecutionPlan, $flatten, $isGetQuery, $dataStructure);
    }

    /**
     * 导出
     * @param array $requestData 请求参数
     * @return array
     */
    public static function export($requestData)
    {

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, 0);
        $userId = data_get($requestData, 'adminUserId', 0);
        $route = data_get($requestData, 'route', '');

        $config = AdminConfigService::getAdminConfig($storeId, $route, $userId);

        $userConfigData = data_get($config, 'userConfidData');
        if (empty($userConfigData)) {
            return [Constant::FILE_URL => ''];
        }

        $_header = [];
        foreach ($userConfigData as $item) {
            $_header[data_get($item, 'label')] = data_get($item, 'name');
        }

        $header = Arr::collapse([$_header, [
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => [Constant::DB_TABLE_PRIMARY]
            ],
        ]]);

        $service = static::getNamespaceClass();
        $method = 'getListData';
        $select = static::getSelect();
        $parameters = [$requestData, true, true, $select, false, false];
        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters);

        return [Constant::FILE_URL => $file];
    }

    /**
     * 批量添加
     * @param $listData
     * @return array
     */
    public static function import($storeId, $listData) {

        $data = [];
        foreach ($listData as $item) {
            $data[] = [
                Constant::DB_TABLE_NAME => data_get($item,0,''),
                Constant::EXCEPTION_MSG => data_get($item,1,''),
                Constant::DB_TABLE_CREATED_AT => data_get($item,2,''),
                Constant::AVATAR => data_get($item,3,''),
            ];
        }

        static::getModel($storeId)->insert($data);

        return Response::getDefaultResponseData(1);
    }
}
