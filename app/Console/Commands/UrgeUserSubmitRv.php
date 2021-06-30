<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/12/8 16:24
 */

namespace App\Console\Commands;

use App\Services\CustomerInfoService;
use App\Services\DictStoreService;
use App\Services\EmailService;
use App\Services\OrderReviewService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Carbon\Carbon;

class UrgeUserSubmitRv extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'urge_user_submit_rv {--storeId= : storeId} {--startTime= : startTime} {--endTime= : endTime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'artisan: urge_user_submit_rv';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function runHandle() {
        $storeId = $this->option('storeId') ? $this->option('storeId') : 0;
        if (empty($storeId)) {
            return true;
        }

        FunctionHelper::setTimezone($storeId);
        $hour = Carbon::now()->hour;
        if ($hour < 16 || $hour > 20) { //只在每天的下午4点到晚上8点之间发送催评邮件
            //return true;
        }

        $maxUrgeRvNums = DictStoreService::getByTypeAndKey($storeId, 'urge_review', 'num', true);
        if (empty($maxUrgeRvNums)) {
            $maxUrgeRvNums = 5000;
        }

        $nowTimestamp = Carbon::now()->timestamp;
        $beforeTime = Carbon::createFromTimestamp($nowTimestamp - (7 * 24 * 60 * 60))->toDateTimeString();

        $startTime = $this->option('startTime');
        $endTime = $this->option('endTime');
        if (empty($startTime)) {
            $startTime = '2020-05-15 00:00:00';
        }
        if (!empty($endTime)) {
            $beforeTime = $endTime;
        }

        $env = config('app.env', 'production');
        $testEmail = '';
        if ($env != 'production') {
            $testEmail = DictStoreService::getByTypeAndKey($storeId, 'urge', 'test_email', true);
            !empty($testEmail) && $testEmail = explode(";", $testEmail);
        }

        $limit = 50;
        $lastId = PHP_INT_MAX;
        $sendEmailCnt = 0;
        while (true) {
            $where = [
                [
                    [Constant::DB_TABLE_STAR, '>=', 4],
                    [Constant::DB_TABLE_STAR, '<=', 5],
                    [Constant::AUDIT_STATUS, '=', -1],
                    [Constant::DB_TABLE_REVIEW_TIME, '=', '2019-01-01 00:00:00'],
                    [Constant::DB_TABLE_CREATED_AT, '>=', $startTime],
                    [Constant::DB_TABLE_CREATED_AT, '<=', $beforeTime],
                    ['urge_email_at', '=', '2019-01-01 00:00:00'],
                    [Constant::DB_TABLE_PRIMARY, '<', $lastId],
                ]
            ];

            if ($env != 'production') {
                empty($testEmail) && $testEmail = ['alexhong465@gmail.com', 'xu715005@gmail.com'];
                $reviews = OrderReviewService::getModel($storeId)
                    ->buildWhere($where)
                    ->whereIn(Constant::DB_TABLE_ACCOUNT, $testEmail)
                    ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_DESC)
                    ->limit($limit)
                    ->get();
            } else {
                $reviews = OrderReviewService::getModel($storeId)
                    ->buildWhere($where)
                    ->orderBy(Constant::DB_TABLE_PRIMARY, Constant::DB_EXECUTION_PLAN_ORDER_DESC)
                    ->limit($limit)
                    ->get();
            }

            if ($reviews->isEmpty()) {
                break;
            }

            $hour = Carbon::now()->hour;
            if ($hour < 16 || $hour > 20) { //只在每天的下午4点到晚上8点之间发送催评邮件
                //break;
            }

            foreach ($reviews as $review) {
                $reviewId = data_get($review, Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT);
                if ($reviewId < $lastId) {
                    $lastId = $reviewId;
                }

                $customerId = data_get($review, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_STRING_DEFAULT);
                if (empty($customerId)) {
                    continue;
                }

                $customerInfo = CustomerInfoService::existsOrFirst($storeId, '', [Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId], true, [Constant::DB_TABLE_ACCOUNT]);
                $account = data_get($customerInfo, Constant::DB_TABLE_ACCOUNT, Constant::PARAMETER_STRING_DEFAULT);
                if (empty($account)) {
                    continue;
                }

                if (strpos($account,'_0_#')) {
                    continue;
                }

                $where = [
                    Constant::DB_TABLE_STORE_ID => $storeId,
                    Constant::DB_EXECUTION_PLAN_GROUP => 'order_review',
                    Constant::DB_TABLE_TYPE => 'audit_6',
                    'to_email' => $account,
                    Constant::DB_TABLE_EXT_ID => $reviewId,
                    Constant::DB_TABLE_EXT_TYPE => OrderReviewService::getMake(),
                    Constant::DB_TABLE_ACT_ID => 0,
                ];

                $isExists = EmailService::exists($storeId, '', $where);
                if ($isExists) {
                    continue;
                }

                $orderId = data_get($review, Constant::DB_TABLE_ORDER_NO, Constant::PARAMETER_STRING_DEFAULT);
                if (empty($orderId)) {
                    continue;
                }

                $where = [
                    [
                        [Constant::DB_TABLE_STORE_ID, '=', $storeId],
                        [Constant::DB_EXECUTION_PLAN_GROUP, '=', 'order_review'],
                        [Constant::DB_TABLE_TYPE, '=', 'audit_6'],
                        ['to_email', '=', $account],
                        [Constant::DB_TABLE_ACT_ID, '=', 0],
                        ['extinfo', 'like', "%$orderId%"],
                    ]
                ];
                $extInfo = EmailService::existsOrFirst($storeId, '', $where, true, ['extinfo']);
                $extInfo = json_decode(data_get($extInfo, 'extinfo', Constant::PARAMETER_STRING_DEFAULT), true);
                $extOrderId = data_get($extInfo, "7.parameters.2.orderno");
                if ($extOrderId == $orderId) {
                    continue;
                }

                data_set($review, 'is_urge_rv', true);
                OrderReviewService::handleReviewEmail($storeId, 0, 6, $review);

                $sendEmailCnt++;
                OrderReviewService::update($storeId, [Constant::DB_TABLE_PRIMARY => $reviewId], ['urge_email_at' => Carbon::now()->toDateTimeString()]);
                if ($sendEmailCnt >= $maxUrgeRvNums) {
                    return true;
                }

                sleep(2);
            }
        }

        return true;
    }

}
