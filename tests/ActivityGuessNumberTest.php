<?php

use App\Services\ActivityGuessNumberService;
use App\Services\ActivityService;
use App\Services\CustomerService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ActivityGuessNumberTest extends TestCase
{
    public $storeID = 1;
    public $actID = 36;
    public $email = "david_wan@patozon.net";

    public function testValidateStartDate()
    {
        $flag = ActivityService::existsOrFirst(intval($this->storeID), '',
            [Constant::DB_TABLE_PRIMARY => intval($this->actID)], true, [Constant::DB_TABLE_START_AT]);
        $startAt = data_get($flag, Constant::DB_TABLE_START_AT);
        $this->assertNotEmpty($startAt);
        $startDateStr = Carbon::parse($startAt)->toDateString();
        dump($startAt);
        dump($startDateStr);
        $this->assertIsString($startDateStr);
        $this->assertTrue($startDateStr == Carbon::today()->toDateString());
        // if ($startDateStr == Carbon::today()->toDateString()) {
        //     return false;
        // }
    }



    public function testHandleAccountEmail()
    {
        // 测试修正之后的邮箱脱敏哈数
        $lenLimit = 10;
        $suffix = '@example.com';
        $symbolNum = mt_rand(2,4);
        dump($symbolNum);
        $emailLen = mt_rand(1,16);
        dump($emailLen);
        $email = Str::random($emailLen) . $suffix;
        dump($email);
        $str = FunctionHelper::handleAccountEmail($email, $symbolNum, "*", $lenLimit);
        dump($str);
        $this->assertContains($suffix, $str);
        $str = str_replace($suffix, '', $str);
        $this->assertTrue(Str::length($str) <= $lenLimit);
    }

    public function testSendResultEmail()
    {
        $luckyNumberID = 19;
        $res = ActivityGuessNumberService::sendResultEmail(
            $this->storeID, $this->actID,$luckyNumberID);
        dump($res);
        $this->assertIsBool($res);
    }


    public function testSendInviteEmail()
    {
        // 测试发送邀请注册活动邮件
        $account = "jsilver71@outlook.com";
        $url = "https://www.xmpow.com/pages/giveaway-100mpods?invite_code=ZDTarMBF";
        $service = ActivityGuessNumberService::sendInviteEmail(
            $this->storeID, $this->actID, $this->email, $account, $url);
        dump($service);
        $this->assertNotEmpty([1]);
    }

    public function testInsertInviteEmail2Queue()
    {
        $data = [
            'account' => 'jsilver71@outlook.com',
            'act_id' =>  $this->actID,
            'store_id' => $this->storeID,
            'share_url' => 'https://www.xmpow.com/pages/giveaway-100mpods?invite_code=ZDTarMBF',
            'email' => 'david_wan@patozon.net',
        ];
        $this->post('/api/shop/activity/guess/share', $data);
        $res = $this->response->getContent();
        dump($res);
        $this->assertResponseOk();
        $this->assertJson($res);
        $this->assertArrayHasKey('data', json_decode($res, true));
    }



    public function testGetWinnerEmail()
    {
        // $selected = [23,24];
        $selected = [];
        $res = ActivityGuessNumberService::getWinnerEmail(
            $this->storeID, $this->actID, 19, $selected);
        dump($res);
        $this->assertIsArray($res);
    }

    public function testGetLoserEmail()
    {
        $res = ActivityGuessNumberService::getLoserEmail(
            $this->storeID, $this->actID, 19);
        dump($res);
        $this->assertIsArray($res);
    }

    public function testSendNotificationEmail()
    {
        $customerID = 67701;
        $entries = mt_rand(1,10);
        // $entries = 3;
        dump($entries);
        $service = ActivityGuessNumberService::sendNotificationEmail(
            $this->storeID, $this->actID, $customerID, $entries);
        dump($service);
        $this->assertIsBool($service);
    }


    public function testSendEmail()
    {
        $arr = [
            'email' => "david_wan@patazon.net",
            'subject' => 'testing',
            'body' => 'testing body'
        ];
        $res = ActivityGuessNumberService::_sendEmail($this->storeID, $this->actID, $arr, "notification");

        $this->assertNotEmpty($res);
    }


    public function testGetCustomerInfo()
    {
        $account = "fagemart@yahoo.com";
        $customer = CustomerService::customerExists($this->storeID, 0, $account, 0, true);
        dump($customer);
        dump($customer->toArray());
        $this->assertNotEmpty($customer);

    }



    public function testStopAllPush()
    {
        $type = mt_rand(1, 3);
        $data = [
            'act_id' => $this->actID,
            'store_id' => $this->storeID,
            'type' => $type,
            'account' => 'johnnymaas@hotmail.com'

        ];
        $this->post('/api/shop/activity/guess/push', $data);
        $res = $this->response->getContent();
        dump($res);
        $this->assertResponseOk();

        $configModel =ActivityGuessNumberService::createModel($this->storeID, 'ActivityEmailSet');
        unset($data['store_id']);
        if ($type === 3) {
            unset($data['type']);
            $rec = $configModel->where($data)->whereIn('type', [1,2])
                ->orderBy('id','desc')->get();
            dump($rec);
            $this->assertTrue($rec->isNotEmpty());
            $this->assertEquals(2, $rec->count());
        } else {
            $rec = $configModel->where($data)->orderBy('id','desc')->first();
            $this->assertNotEmpty($rec);
            $this->assertEquals($type, $rec->type);
            $this->assertEquals(1, $rec->type_sub);
        }
    }


    /**
     * 测试获取活动的邮件配置
     */
    public function testGetEmailTemplate()
    {
        $configModel = ActivityGuessNumberService::createModel($this->storeID, 'ActivityConfig');
        $win = mt_rand(0, 1);
        $typekey = $win ? "winner" : "loser";
        $resultEmailSubjectKey = "lucky_number_{$typekey}_result_subject";
        $resultEmailBodyKey = "lucky_number_{$typekey}_result_body";
        $where = [
            'type' => 'email',
            'activity_id' => $this->actID,
        ];
        dump($resultEmailSubjectKey);
        dump($resultEmailBodyKey);

        // $content = $configModel->where($where)->orWhere($orWhere)->pluck('value');
        $content = $configModel->where($where)
            ->whereIn('key', [$resultEmailBodyKey,$resultEmailSubjectKey])
            ->orderBy('sort', 'asc')
            ->pluck('value')->toArray();
        dump($content);
        list($subject, $body) = $content;
        dump($subject);
        dump($body);
        $this->assertNotEmpty($content);
        $this->assertIsArray($content);

    }



    public function testGetEmailSetCustomerID()
    {
        $storeID = 1;
        $actID = 36;
        $model = ActivityGuessNumberService::createModel($storeID, 'ActivityEmailSet');
        $where = [
            'type_sub' => 1,
            'act_id' => $actID
        ];
        $res = $model->where($where)->select(['customer_id'])->get();
        dump($res->isEmpty());
        dump($res->toArray());
        $this->assertNotTrue($res);
    }

    public function testFilterIDs()
    {
        $storeID = 1;
        $actID = 36;
        $model = ActivityGuessNumberService::createModel($storeID, 'ActivityGuessNumber');
        // dump($model);
        $arr = $model->select(['account', 'customer_id', 'win_log_id', 'show_guess_num', 'act_id'])
            ->limit(4)->get()->toArray();
        $model = ActivityGuessNumberService::createModel($storeID, 'ActivityEmailSet');
        $where = [
            'type_sub' => 1,
            'act_id' => $actID
        ];
        $res = $model->where($where)->pluck('customer_id');
        // dump($res->isEmpty());
        dump($arr);
        $filterIDs= $res->toArray();
        dump($filterIDs);
        // exit;
        $flip = [];
        foreach($arr as $key =>$value) {
            $flip[$value['customer_id']] = $value;
        }

        dump($flip);
        foreach($filterIDs as $id => $value) {
            dump($value);
            if (!empty($flip[$value])) {
                unset($flip[$value]);
            }
        }
        dump($flip);
        dump(array_values($flip));
        $this->assertIsArray($flip);
    }

    public function testGetEmailObjects()
    {
        // 测试获取开奖邮件列表
        $actID = 36;
        $storeID = 1;

        $date = date('M jS', 1619958362);
        $this->assertNotEmpty($date);
        // dump($date);
        $where = [
            [
                ['act_id', '=', $actID],
                ['customer_id', '!=', 0],
                ['lucky_num_id', '=', 1],
            ]
        ];

        $res = ActivityGuessNumberService::getEmailObjects($storeID, $where);
        dump($res);
        dump(count($res['data']));
        $arr = [];
        foreach($res['data'] as $key=> $value){
            $arr[$value['email']] = $value;
        }
        dump($arr);
        dump(count($arr));
        dump(array_values($arr));
        $this->assertIsArray($res);
        $this->assertArrayHasKey('pagination', $res);
    }

    public function testInsertGuessNumberTable()
    {
        // 测试插入猜数字流水表
        $actID = 35;
        $storeID = 1;
        try {
            $res = ActivityGuessNumberService::generateActivityUser($storeID, $actID, 100);
            $this->assertIsArray($res);
            $this->assertArrayHasKey('code', $res);
            $this->assertEquals(1, $res['code']);
        } catch (Exception $e) {
            $this->assertResponseStatus($e->getCode());
        }
    }


    public function testGenerateRandNum()
    {
        // 测试随机生成猜数字三位数
        $num = mt_rand(0, 999);
        dump($num);
        $res = FunctionHelper::getRandNum(3, $num);
        dump($res);
        $this->assertIsString($res);
        $this->assertEquals(3, strlen($res));

        $num = 44;
        $res = FunctionHelper::getRandNum(3, $num);
        dump($res);
        $this->assertIsString($res);
        $this->assertEquals(3, strlen($res));
        $num = 11;
        $res = FunctionHelper::getRandNum(3, $num, "5");
        dump($res);
        $this->assertEquals(3, strlen($res));
        $this->assertStringStartsWith("5", $res);
    }

    public function testGenerateRandomArray()
    {
        // 测试随机时间和随机数量
        $cls = new App\Console\Commands\GenerateActivityUsers();
        $res1 = $cls->getRandomArray(240);
        $res2 = array_map(function (&$item) {
            $item = mt_rand(2, 30);
            return $item;
        }, array_fill(0, 23, 1));
        $this->assertIsArray($res1);
        $this->assertEquals(240, array_sum($res1));
        $this->assertIsArray($res2);
    }

    public function testGenerateActivityUser()
    {
        function getRandomArray($end = 240)
        {
            $shuffleArray = [];
            $index = array_map(function (&$item) use ($end) {
                $item = random_int(2, ($end - 1));
                return $item;
            }, array_fill(0, 23, 1));

            sort($index);
            $index[] = $end;
            foreach ($index as $key => $value) {
                if ($key === 0) {
                    $shuffleArray[] = $value;
                } else {
                    $shuffleArray[] = $value - $index[$key - 1];
                }
            }
            if ($end <= 60) {
                shuffle($shuffleArray);
            }
            return $shuffleArray;
        }

        $service = ActivityGuessNumberService::class;
        $method = "generateActivityUser";

        $data = getRandomArray(240);
        $time = getRandomArray(60);
        $currentTime = Carbon::today()->toDateString();
        $h = mt_rand(0, 23);
        $hitTime = $currentTime ." ".($h >= 10 ? $h : "0".$h).':00:00';
        dump($hitTime);
        dump(Carbon::parse($hitTime)->timestamp);
        dump(Carbon::now()->timestamp);
        dump(Carbon::now()->toDateTimeString());
        dd(Carbon::parse($hitTime)->timestamp - Carbon::now()->timestamp);
        // Queue::later(10, new ExampleJob(['a' => 123]), null, 'QueueName');
        // 写入延时队列
        $currentTime = config('app.debug') ? Carbon::tomorrow()->toDateString() : Carbon::today()->toDateString();
        foreach ($time as $h => $min) {

            $hitTime = $currentTime." ".($h >= 10 ? $h : "0".$h).':00:00';
            $tryLaterTime = Carbon::parse($hitTime)->timestamp - Carbon::now()->timestamp;
            // $data[$h] 要生成的水军的个数
            $job = FunctionHelper::getJobData($service, $method, [$data[$h]]);
            $this->assertIsArray($job);
            $res = FunctionHelper::laterQueue(10, $job, null, '{default}');
            // $this->assertTrue($res);
        }

    }



    public function testSelectVirtualAccount()
    {
        // 测试查询水军帐号信息
        $storeID = 1;

        $select = ['account'];
        $limit = 9999;
        $where = ['type' => 1, 'type_sub' => 1];
        $exePlan = FunctionHelper::getExePlan($storeID, null, 'ActivityVirtualAccount',
            '', $select, $where, [], $limit);
        $dbExecutionPlan = [
            'parent' => $exePlan,
            // 'sqlDebug' => true
        ];

        $data = FunctionHelper::getResponseData(
            null, $dbExecutionPlan, false, false, 'list');
        // dump($data);
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
    }


    /**
     * A basic test example.
     *
     * @return void
     */
    public function testWinnersList()
    {
        // 测试活动获奖的赢家列表
        $data = [
            "act_id" => mt_rand(1, 10),
            "store_id" => 1,
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        $this->post('/api/shop/activity/guess/winners', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        $this->assertArrayHasKey(Constant::DB_EXECUTION_PLAN_PAGINATION, $res['data']);
    }

    public function testHelpedList()
    {
        // 测试当前用户的互助用户列表
        $data = [
            "act_id" => 36,
            "store_id" => 1,
            "account"=>"11111xf@163.com",
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        $this->post('/api/shop/activity/guess/helped', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        $this->assertArrayHasKey(Constant::DB_EXECUTION_PLAN_PAGINATION, $res['data']);
    }

    public function testRemoveSensitiveInfo()
    {
        // 测试邮箱信息脱敏
        $emailSuffix = "@example.com";
        $randStr = Str::random(mt_rand(1, 16));
        $email = $randStr.$emailSuffix;
        $str = FunctionHelper::handleAccountEmail($email);
        $this->assertNotEquals($str, $randStr.$emailSuffix);
        $len = Str::length($randStr);
        if ($len > 4) {
            $this->assertContains(str_repeat("*", 4), str_replace($emailSuffix, "", $str));
        } else {
            $this->assertContains(str_repeat("*", $len - 1), str_replace($emailSuffix, "", $str));
        }
    }

    public function testTodayJoinUsers()
    {
        // 测试今日参与人员列表
        $data = [
            // "act_id" => mt_rand(1, 10),
            "act_id" => 1,
            "store_id" => 1,
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        dump($data);
        $this->post('/api/shop/activity/guess/users', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        dump($res);
        $this->assertArrayHasKey(Constant::DB_EXECUTION_PLAN_PAGINATION, $res['data']);
    }

    public function testYesterdayPrizeNumber()
    {
        // 测试今日开奖数字
        $data = [
            // "act_id" => mt_rand(1, 10),
            "act_id" => 1,
            "store_id" => 1,
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        dump($data);
        $this->post('/api/shop/activity/guess/prize-result', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        dump($res);
        $this->assertArrayHasKey(Constant::RESPONSE_DATA_KEY, $res);
        if (!empty($res['num'])) {
            $this->assertIsString($res['data']['num']);
            $this->assertArrayHasKey('date', $res['data']);
            $this->assertArrayHasKey('time', $res['data']);
            $this->assertArrayHasKey('next', $res['data']);
        }
    }


    public function testMyOwnPrize()
    {
        // 测试我自己的猜中数字的列表
        $data = [
            "act_id" => 35,
            "customer_id" => 236000,
            "store_id" => 1,
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        dump($data);
        $this->post('/api/shop/activity/guess/own', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        dump($res);
        $this->assertArrayHasKey(Constant::RESPONSE_DATA_KEY, $res);
        if (!empty($res['data'])) {
            $this->assertArrayHasKey(Constant::RESPONSE_DATA_KEY, $res['data']);
            $this->assertArrayHasKey(Constant::DB_EXECUTION_PLAN_PAGINATION, $res['data']);
            if (!empty($res['data']['data'])) {
                foreach ($res['data']['data'] as $key => $value) {
                    $this->assertArrayHasKey('guess', $value);
                    $this->assertArrayHasKey('prize', $value);
                    $this->assertIsArray($value['prize']);
                    $this->assertNotEmpty($value['prize']['img_url']);
                    $this->assertNotEmpty($value['prize']['mb_img_url']);
                    $this->assertNotEmpty($value['prize']['name']);
                    $this->assertArrayHasKey('time', $value);
                }
            }
        }
    }

    public function testGuessNumberHistory()
    {
        // 测试我提交 的猜数字记录
        $data = [
            // "act_id" => mt_rand(1, 10),
            "act_id" => 1,
            "customer_id" => 236000,
            "store_id" => 1,
            "page_size" => mt_rand(10, 20),
            "page" => 1
        ];
        dump($data);
        $this->post('/api/shop/activity/guess/history', $data);
        $res = $this->response->getContent();
        $this->assertResponseOk();
        $this->assertJson($res);
        $res = json_decode($res, true);
        dump($res);
        $this->assertArrayHasKey(Constant::RESPONSE_DATA_KEY, $res);
        if (!empty($res['data'])) {
            $this->assertArrayHasKey(Constant::RESPONSE_DATA_KEY, $res['data']);
            $this->assertArrayHasKey(Constant::DB_EXECUTION_PLAN_PAGINATION, $res['data']);
            if (!empty($res['data']['data'])) {
                foreach ($res['data']['data'] as $key => $value) {
                    $this->assertArrayHasKey('guess', $value);
                    $this->assertArrayHasKey('time', $value);
                    $this->assertNotEmpty($value['guess']);
                    $this->assertNotEmpty($value['time']);
                }
            }
        }
    }

}
