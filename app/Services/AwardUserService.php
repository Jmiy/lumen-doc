<?php
namespace App\Services;

use App\Util\Constant;
use App\Util\FunctionHelper;


class AwardUserService extends BaseService
{
    /**
     * 添加中奖名单
     * @param Int $storeId 官网id
     * @param string $account 中奖名单数据
     */
    public static function addAwardUser($storeId, $account) {
        $accountList = explode(',',$account);
        foreach ($accountList as $key => $value){
            $insertData = [
                Constant::DB_TABLE_ACCOUNT => $value, //活动名字
            ];
            //新增邮件模板配置
            $res = static::createModel($storeId, 'AwardUser')->insert($insertData);
        }
        return $res;
    }

    /**
     * 返回中奖名单列表
     * @param int $storeId 官网id
     * @return bool|object|null $rs
     */
    public static function awardList($storeId) {
        $list = static::createModel($storeId, 'AwardUser')->select(Constant::DB_TABLE_PRIMARY, Constant::DB_TABLE_ACCOUNT)->orderBy(Constant::DB_TABLE_PRIMARY, 'asc')->get();
        $list = !empty($list) ? json_decode($list,true) : [] ;

        if ($list){
            foreach ($list as $key => $value){
                $list[$key]['account'] = FunctionHelper::handleAccount(data_get($value, Constant::DB_TABLE_ACCOUNT, ''));
            }
        }

        return $list;
    }
}
