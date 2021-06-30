<?php

namespace App\Services\Activity\Lottery;

use App\Services\Activity\Contracts\ServiceInterface;
use App\Services\Activity\Lottery\Traits\BaseTrait;
use App\Services\ActivityService;
use App\Services\ActivityShareService;
use App\Services\BaseService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\Response;
use App\Services\Activity\Traits\ActBase;


class Service extends BaseService implements ServiceInterface
{
    use ActBase,
        BaseTrait;

    /**
     * 邀请
     * @param $requestData
     * @return bool|mixed
     */
    public static function handleShare($requestData)
    {
        $cacheKeyData = [md5(__METHOD__), md5(json_encode($requestData))];
        $parameters = [
            function () use ($requestData) {

                $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, 0);
                $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID, 0);
                $customerId = data_get($requestData, Constant::DB_TABLE_CUSTOMER_PRIMARY, -1);//邀请者id
                $socialMedia = strtolower(data_get($requestData, Constant::SOCIAL_MEDIA, '')); //社媒平台 FB TW
                $fromUrl = data_get($requestData, Constant::CLIENT_ACCESS_URL, '');//分享的连接
                $account = data_get($requestData, Constant::DB_TABLE_ACCOUNT, '');//账号

                $isValidAct = static::isValidAct($storeId, $actId);
                if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
                    return $isValidAct;
                }

                //获取活动形式
                $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_FORM, Constant::ACT_FORM);
                $actForm = data_get($actConfig, Constant::ACT_FORM . Constant::LINKER . Constant::DB_TABLE_VALUE);
                if (empty($actForm)) {
                    return Response::getDefaultResponseData(1);
                }
                data_set($requestData, Constant::ACT_FORM, $actForm);

                $key = Constant::ACTION_SHARE . ($socialMedia ? ('_' . $socialMedia):'');

                //获取活动配置次数
                $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, $actForm, [
                    Constant::ACTION_SHARE,
                    Constant::ACTION_SHARE_FB,
                    Constant::ACTION_SHARE_TW,
                ]);

                $playNums = data_get($activityConfigData, $actForm . '_' . $key . Constant::LINKER . Constant::DB_TABLE_VALUE); //次数;
                if (empty($playNums)) {//如果当前活动没有配置更新的次数，就直接返回
                    return Response::getDefaultResponseData(1);
                }

                //添加分享流水
                $data = [
                    Constant::DB_TABLE_ACT_ID => $actId,
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                    Constant::DB_TABLE_ACCOUNT => $account,
                    Constant::SOCIAL_MEDIA => $socialMedia,
                    Constant::FILE_URL => $fromUrl,
                    Constant::DB_TABLE_COUNTRY => data_get($requestData, Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT),
                    Constant::DB_TABLE_IP => data_get($requestData, Constant::DB_TABLE_IP, Constant::PARAMETER_STRING_DEFAULT),
                ];
                $shareId = ActivityShareService::insert($storeId, '', $data);
                if (empty($shareId)) {
                    return Response::getDefaultResponseData(9900000003);
                }

                //获取活动数据
                $actData = data_get($isValidAct, Constant::RESPONSE_DATA_KEY);

                $limitKeyData = ActivityService::getLimitKeyData($storeId, $actId, $customerId, [Constant::REQUEST_DATA_KEY => $requestData], $actData);
                $ttl = data_get($limitKeyData, Constant::TTL);//限制级别：'limit':整个活动期间 'month_limit':按月 'week_limit':按周 'day_limit':按天 'hour_limit':按小时 默认：按天

                $offset = 0;
                $cacheKey = implode(':', [$storeId, $actId, $actForm, $key, $customerId]);
                $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'getbit', [$cacheKey, $offset], []);
                $isExists = static::handleCache('', $handleCacheData);
                if ($isExists) {//如果已经通过分享添加了活动次数，就直接返回
                    return Response::getDefaultResponseData(1);
                }

                //统计分享次数
                $where = [
                    Constant::DB_TABLE_ACT_ID => $actId,
                    Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId,
                    Constant::SOCIAL_MEDIA => $socialMedia
                ];
                $shareSum = ActivityShareService::existsOrFirst($storeId, '', $where);
                if ($shareSum>1) {//如果分享次数大于1，就记录已经通过分享添加了活动次数
                    $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setbit', [$cacheKey, $offset, 1, $ttl], []);
                    static::handleCache('', $handleCacheData);
                    return Response::getDefaultResponseData(1);
                }

                //更新活动次数
                static::baseUpdateNum($storeId, $actId, $customerId, $requestData, 'add_nums', $key, $playNums);

                $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setbit', [$cacheKey, $offset, 1, $ttl], []);
                static::handleCache('', $handleCacheData);

                return Response::getDefaultResponseData(1);
            }
        ];

        $rs = static::handleLock($cacheKeyData, $parameters);

        return $rs === false ? Response::getDefaultResponseData(110001) : $rs;

    }

    /**
     * 参与活动
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $customerId 用户id
     * @param array $requestData 请求参数
     * @return array
     */
    public static function handle($storeId, $actId, $customerId, $requestData = [])
    {
        $lotteryResult = static::handleBase($storeId, $actId, $customerId, $requestData);

        ActEmailService::handleEmail($storeId, $actId, $customerId, $lotteryResult, $requestData);

        return $lotteryResult;
    }
}
