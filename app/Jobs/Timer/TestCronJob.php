<?php

namespace App\Jobs\Timer;

use App\Tasks\TestTask;
use Swoole\Coroutine;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class TestCronJob extends CronJob {

    protected $i = 0;

    // !!! 定时任务的`interval`和`isImmediate`有两种配置方式（二选一）：一是重载对应的方法，二是注册定时任务时传入参数。
    // --- 重载对应的方法来返回配置：开始
    public function interval() {
        return 1000; // 每1秒运行一次
    }

    public function isImmediate() {
        return false; // 是否立即执行第一次，false则等待间隔时间后执行第一次
    }

    // --- 重载对应的方法来返回配置：结束
    public function run() {
        \Log::info(__METHOD__, ['start', $this->i, microtime(true)]);
        // do something
        // sleep(1); // Swoole < 2.1
        Coroutine::sleep(1); // Swoole>=2.1 run()方法已自动创建了协程。
        $this->i++;
        \Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        if ($this->i >= 10) { // 运行10次后不再执行
            \Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);
            $this->stop(); // 终止此定时任务，但restart/reload后会再次运行
            // CronJob中也可以投递Task，但不支持Task的finish()回调。
            // 注意：修改config/laravels.php，配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
            $ret = Task::deliver(new TestTask('cron job task data===' . $this->i));
            var_dump($ret);
        }
        // throw new \Exception('an exception');// 此时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }

}
