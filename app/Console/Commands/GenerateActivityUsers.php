<?php


namespace App\Console\Commands;


use App\Models\Activity;
use App\Services\ActivityGuessNumberService;
use App\Services\ActivityService;
use App\Util\Constant;
use App\Util\FunctionHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class GenerateActivityUsers extends BaseCommand
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateActivityUser {--storeID= : storeID} {--actID= : actID} {--count= : count} {--appEnv= : appEnv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generating invalid user in activities etc.';

    /**
     * 验证脚本参数
     * @return array|bool
     */
    public function handleValidateArgs()
    {
        $requestData = Arr::collapse([$this->argument(), $this->option()]);
        if (empty($requestData['storeID']) || empty($requestData['actID']) || empty($requestData['count'])) {
            error_log('非法参数， 请验证参数');
            return false;
        }

        if (intval($requestData['count']) < 24) {
            error_log("随机生成数量必须大于24");
            return false;
        }
        return $requestData;
    }

    public function validateDate(array $requestData)
    {
        // 判断活动开始时间是否处于有效   脚本再活动开始时间一天之后执行
        $flag = ActivityService::existsOrFirst(intval($requestData['storeID']), '',
            [Constant::DB_TABLE_PRIMARY => intval($requestData['actID'])], true, [Constant::DB_TABLE_START_AT]);
        $startAt = data_get($flag, Constant::DB_TABLE_START_AT);
        $startDateStr = Carbon::parse($startAt)->toDateString();
        if ($startDateStr === Carbon::today()->toDateString()) {
            return false;
        }
        return true;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function runHandle()
    {
        $requestData = $this->handleValidateArgs();
        if (!$requestData) {
            return false;
        }

        $this->handleRequest($requestData['storeID']);

        if(!$this->validateDate($requestData)) {
            return false;
        }
        // 随机均匀在开始时间和结束时间内生成随机数量
        // 向活动参与表填充数据 从virtual_account表中 加入到 guess_num表中 customer_id  为0

        $service = ActivityGuessNumberService::getNamespaceClass();
        $method = "generateActivityUser";
        $params = [intval($requestData['storeID']), intval($requestData['actID'])];

        if ((!empty($requestData['appEnv']) && $requestData['appEnv'] == 'test')) {
            $this->runTest($requestData);
            return true;
        }

        $data = $this->getRandomArray($requestData['count']);
        // 写入延时队列
        foreach ($data as $h => $count) {
            $tryLaterTime = $h * 3600;  // $h 从 0 开始 到 23
            // $data[$h] 要生成的水军的个数
            error_log("当前是24小时的第{$h}小时，需要增加{$data[$h]}个水军用户");
            if ($data[$h] > 0) {
                for ($i = 0; $i < $data[$h]; $i++) {
                    $randDelay = mt_rand(60, 59*60);
                    $tryLaterTime = $tryLaterTime + $randDelay;  // 增加随机性
                    error_log("再第{$h}小时需要往后延迟{$randDelay}秒, 为".ceil($randDelay / 60). '分钟');
                    error_log("具体延迟秒数为{$tryLaterTime}");
                    $job = FunctionHelper::getJobData($service, $method, $params);
                    FunctionHelper::laterQueue($tryLaterTime, $job, null, '{default}');
                }
            }
        }

        $this->handleResponse(); //处理响应

        return true;
    }

    public function runTest($requestData)
    {
        $data = $this->getRandomArray($requestData['count']);
        error_log("随机生成24小时分布新增数量");
        dump($data);
        foreach ($data as $h => $count) {
            $l = $h + 1;
            error_log("当前是24小时的第{$l}小时，需要增加{$data[$h]}个水军用户");
            if ($data[$h] > 0) {
                for ($i = 0; $i < $data[$h]; $i++) {
                    $randNum = mt_rand(1, 5);
                    error_log("再第{$h}小时需要往后延迟{$randNum}秒");
                    // sleep($randNum);
                    ActivityGuessNumberService::generateActivityUser(
                        intval($requestData['storeID']), intval($requestData['actID']));
                }
            }
        }
        error_log("执行完成");
    }

    public function getRandomArray($end = 240)
    {
        $shuffleArray = [];
        $index = array_map(function (&$item) use ($end) {
            $item = mt_rand(2, ($end - 1));
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
        return $shuffleArray;
    }
}
