<?php


namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerInfo;
use App\Services\Traits\GetDefaultConnectionModel;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\obj;
use App\Util\Response;

class InviteHistoryService extends BaseService
{
    use GetDefaultConnectionModel;


    /**
     *  * 获取排行榜数据
     * @param  int  $storeID
     * @param  int  $actID
     * @param  string  $account  会员账号
     * @param  int  $page  当前页码
     * @param  int  $pageSize  每页记录条数
     * @return obj|array
     */
    public static function getHelpedList(
        int $storeID = 0,
        int $actID = 0,
        string $account = '',
        int $page = 1,
        int $pageSize = 10
    ) {
        $_data = static::getPublicData([Constant::REQUEST_PAGE => $page, Constant::REQUEST_PAGE_SIZE => $pageSize]);

        $pagination = data_get($_data, Constant::DB_EXECUTION_PLAN_PAGINATION, []);
        $limit = $_data[Constant::DB_EXECUTION_PLAN_PAGINATION][Constant::REQUEST_PAGE_SIZE];
        $offset = $_data[Constant::DB_EXECUTION_PLAN_PAGINATION]['offset'];
        $order = [['id', 'desc']];
        $unset = ['invite_account', 'customer_id', 'created_at'];
        $select = ['invite_account', 'customer_id', 'created_at'];
        $where = ['act_id' => $actID, 'account' => $account, 'store_id' => $storeID];
        $exePlan = FunctionHelper::getExePlan('default_connection_'.$storeID, null,
            'InviteHistory', '', $select, $where, $order, $limit, $offset, true, $pagination,
            false, [], [], [], $unset);

        $itemHandleDataCallback = [
            'account' => function ($item) {
                return FunctionHelper::handleAccountEmail(data_get($item, 'invite_account'), 2);
            },
            'time' => function ($item) {
                return date('d/m/Y H:i:s', strtotime(data_get($item, 'created_at')));
            }
        ];

        $exeHandlePlan = FunctionHelper::getExePlanHandleData();
        data_set($exeHandlePlan, 'callback', $itemHandleDataCallback);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            'itemHandleData' => $exeHandlePlan,
            // 'sqlDebug' => true
        ];

        return FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');
    }


}
