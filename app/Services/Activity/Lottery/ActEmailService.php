<?php

namespace App\Services\Activity\Lottery;

use App\Services\Activity\Traits\ActBase;
use App\Services\ActivityService;
use App\Services\BaseService;
use App\Services\EmailService;
use App\Util\Constant;
use App\Util\FunctionHelper;


class ActEmailService extends BaseService
{
    use ActBase;

    /**
     * 判断活动是否有效
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $customerId 用户id
     * @param array $lotteryResult 抽奖结果
     * @return array
     */
    public static function getEmailData($storeId, $actId, $customerId, $lotteryResult)
    {
        $emailData = [
            Constant::RESPONSE_CODE_KEY => 1,
            'storeId' => $storeId,
            Constant::ACT_ID => $actId,
            'content' => '',
            'subject' => '',
            Constant::DB_TABLE_COUNTRY => '',
            Constant::DB_TABLE_IP => '',
            'extId' => '',
            'extType' => '',
        ];

        $prizeData = data_get($lotteryResult, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::PRIZE_DATA);
        $prizeType = data_get($prizeData, Constant::DB_TABLE_TYPE); //奖品类型（礼品卡/coupon/实物/活动积分/其他） 奖品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分

        //获取活动形式
        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::DB_TABLE_EMAIL, ['subject_' . $prizeType, 'view_' . $prizeType]);
        $subject = data_get($actConfig, Constant::DB_TABLE_EMAIL . '_subject_' . $prizeType . Constant::LINKER . Constant::DB_TABLE_VALUE);
        $emailView = data_get($actConfig, Constant::DB_TABLE_EMAIL . '_view_' . $prizeType . Constant::LINKER . Constant::DB_TABLE_VALUE);

        $replacePairs = [
            '{{$account_name}}' => '',
        ];

        data_set($emailData, 'subject', $subject);
        data_set($emailData, 'content', strtr($emailView, $replacePairs));

        return $emailData;

    }

    /**
     * 处理活动邮件
     * @param $storeId 商城id
     * @param $actId 活动id
     * @param $customerId 用户id
     * @param array $lotteryResult 抽奖结果
     * @param array $requestData 请求参数
     * @return array
     */
    public static function handleEmail($storeId, $actId, $customerId, $lotteryResult = [], $requestData = [])
    {
        //如果没有中奖，就直接返回
        $prizeData = data_get($lotteryResult, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::PRIZE_DATA);
        $prizeType = data_get($prizeData, Constant::DB_TABLE_TYPE); //奖品类型（礼品卡/coupon/实物/活动积分/其他） 奖品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
        if (data_get($prizeData, Constant::DB_TABLE_EXT_ID) === null) {
            return false;
        }

        $offset = 0;
        $cacheKey = implode(':', [$storeId, $actId, $customerId, $prizeType]);
        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'getbit', [$cacheKey, $offset], []);
        $isExists = static::handleCache('', $handleCacheData);
        if ($isExists) {//如果已经发过相同类型的邮件，就直接返回
            return false;
        }

        $isValidAct = static::isValidAct($storeId, $actId);
        if (data_get($isValidAct, Constant::RESPONSE_CODE_KEY) != 1) {//如果活动无效，就直接返回
            return false;
        }

        //通过活动配置 获取禁止抽奖的国家
        $activityConfigData = ActivityService::getActivityConfigData($storeId, $actId, Constant::ACT_CONFIG_TYPE_WINNING);

        $isEmail = data_get($activityConfigData, Constant::ACT_CONFIG_TYPE_WINNING . '_email.value', 0);; //抽奖后是否发邮件 1:发  0:不发 默认:0
        $emailPrizeType = data_get($activityConfigData, Constant::ACT_CONFIG_TYPE_WINNING . '_email_prize_type.value');; //需要发送邮件的奖品类型
        if (!$isEmail) {//如果不需要发邮件，就直接返回
            return false;
        }

        if ($emailPrizeType === null) {//如果不需要发邮件，就直接返回
            return false;
        }

        $emailPrizeType = explode(',', $emailPrizeType);
        if (!in_array($prizeType, $emailPrizeType)) {//如果 $prizeType 对应的类型不需要发送邮件，就直接返回
            return false;
        }

        //获取活动数据
        $actData = data_get($isValidAct, Constant::RESPONSE_DATA_KEY);
        $limitKeyData = ActivityService::getLimitKeyData($storeId, $actId, $customerId, [Constant::REQUEST_DATA_KEY => $requestData], $actData);
        $ttl = data_get($limitKeyData, Constant::TTL);//限制级别：'limit':整个活动期间 'month_limit':按月 'week_limit':按周 'day_limit':按天 'hour_limit':按小时 默认：按天

        //统计邮件发送次数
        $where = [
            Constant::DB_TABLE_TYPE => $cacheKey,
        ];
        $count = EmailService::existsOrFirst($storeId, '', $where);
        if ($count > 0) {//如果已经发过相同类型的邮件，就直接返回

            //记录 $cacheKey 对应类型的邮件已经发送过
            $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setbit', [$cacheKey, $offset, 1, $ttl], []);
            static::handleCache('', $handleCacheData);

            return false;
        }

        //发送邮箱
        $extService = static::getNamespaceClass();
        $extMethod = 'getEmailData';
        $extParameters = [$storeId, $actId, $customerId, $lotteryResult];

        $extData = FunctionHelper::getJobData($extService, $extMethod, $extParameters, [], [
            Constant::ACT_ID => $actId,
            Constant::ACTIVITY_CONFIG_TYPE => 'email',
            Constant::STORE_DICT_TYPE => 'email',
        ]);

        $service = EmailService::getNamespaceClass();
        $method = 'handle';

        $toEmail = data_get($requestData, Constant::DB_TABLE_ACCOUNT, '');
        $group = 'lottery';
        $type = $cacheKey;//奖品类型（礼品卡/coupon/实物/活动积分/其他） 奖品类型 0:其他 1:礼品卡 2:coupon 3:实物 5:活动积分
        $remark = '抽奖活动中奖了';
        $extId = data_get($prizeData, Constant::DB_TABLE_EXT_ID, 0);
        $extType = data_get($prizeData, Constant::DB_TABLE_EXT_TYPE, '');
        $parameters = [$storeId, $toEmail, $group, $type, $remark, $extId, $extType, $extData];

        $rs = FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters));

        if (empty($rs)) {//如果发送邮件失败，就直接返回
            return $rs;
        }

        //记录 $cacheKey 对应类型的邮件已经发送过
        $handleCacheData = FunctionHelper::getJobData(static::getNamespaceClass(), 'setbit', [$cacheKey, $offset, 1, $ttl], []);
        static::handleCache('', $handleCacheData);

        return $rs;

    }
}
