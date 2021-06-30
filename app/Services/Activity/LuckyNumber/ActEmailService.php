<?php

namespace App\Services\Activity\LuckyNumber;

use App\Services\ActivityService;
use App\Services\BaseService;
use App\Services\EmailService;
use App\Util\Constant;
use App\Util\FunctionHelper;


class ActEmailService extends BaseService
{

    /**
     * 判断活动是否有效
     * @param $storeId 商城id
     * @param $actId 活动id
     * @return array
     */
    public static function getInviteEmailData($storeId, $actId)
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

        //获取活动形式
        $actConfig = ActivityService::getActivityConfigData($storeId, $actId, Constant::DB_TABLE_EMAIL, ['lucky_number_invite_subject', 'lucky_number_invite_body']);
        $subject = data_get($actConfig, Constant::DB_TABLE_EMAIL . '_lucky_number_invite_subject' . Constant::LINKER . Constant::DB_TABLE_VALUE);
        $emailView = data_get($actConfig, Constant::DB_TABLE_EMAIL . '_lucky_number_invite_body' . Constant::LINKER . Constant::DB_TABLE_VALUE);

        $replacePairs = [
            '{{$account_name}}' => '',
        ];

        data_set($emailData, 'subject', $subject);
        data_set($emailData, 'content', strtr($emailView, $replacePairs));

        return $emailData;

    }

    /**
     * 邀请
     * @param $requestData
     * @return bool|mixed
     */
    public static function handleInviteEmail($requestData)
    {
        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID);
        $actId = data_get($requestData, Constant::DB_TABLE_ACT_ID);
        $inviteId = data_get($requestData, Constant::EVENT_DATA . Constant::LINKER . Constant::INVITE_DATA . Constant::LINKER . Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::DB_TABLE_PRIMARY);
        $customerId = data_get($requestData, Constant::EVENT_DATA . Constant::LINKER . Constant::DB_TABLE_CUSTOMER_PRIMARY);//邀请者id

        $extService = static::getNamespaceClass();
        $extMethod = 'getInviteEmailData';
        $extParameters = [$storeId, $actId];

        $extData = FunctionHelper::getJobData($extService,$extMethod,$extParameters,[],[
            Constant::ACT_ID => $actId,
            Constant::ACTIVITY_CONFIG_TYPE=> 'email',
            Constant::STORE_DICT_TYPE => 'email',
        ]);

        $service = EmailService::getNamespaceClass();
        $method = 'handle';
        $parameters = [$storeId, 'Jmiy_cen@patazon.net', 'lucky_numbers', 'emailType', '', 1, 'EmailService', $extData];

        return FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters));

    }
}
