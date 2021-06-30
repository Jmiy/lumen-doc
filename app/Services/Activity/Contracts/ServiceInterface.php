<?php

namespace App\Services\Activity\Contracts;;

interface ServiceInterface
{
    /**
     * 判断活动是否有效
     * @param $storeId 商城id
     * @param $actId 活动id
     * @return array
     */
    public static function isValidAct($storeId, $actId);

    /**
     * 获取活动统计数据（包括 累计总次数 剩余次数  累计添加次数  已使用次数 等）
     * @param array $requestData
     * @return array $lotteryData
     */
    public static function getNums($requestData);

    /**
     * 触发活动事件
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $requestData 请求参数
     * @param string $type 事件名称
     * @param array $key 事件 监听器
     * @return array|bool
     */
    public static function event($storeId, $actId, $requestData, $type = '', $key = []);

    /**
     * 更新活动次数 Jmiy_cen 2021-05-24 09:17 add
     * @param int $storeId 商城id
     * @param int $actId 活动id
     * @param int $customerId 账号id
     * @param array $requestData 请求数据
     * @param string $type 类型
     * @param string $key key
     * @param int $num 更新数量
     * @return mix
     */
    public static function baseUpdateNum($storeId = 0, $actId = 0, $customerId = 0, $requestData = [], $type = 'add_nums', $key = '', $num = 1);

    /**
     * 处理邀请
     * @param $requestData
     * @return mixed
     */
    public static function handleInvite($requestData);

    /**
     * 处理关注
     * @param $requestData
     * @return mixed
     */
    public static function handleFollow($requestData);

    public static function handle($storeId, $actId, $customerId, $extData = []);
}
