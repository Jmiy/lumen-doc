<?php

namespace App\Listeners;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use App\Tasks\TestTask;

class TestListener1 extends Listener {

    /**
     * @var TestEvent
     */
    protected $event;

    public function handle() {
        \Log::info(__CLASS__ . ':handle start', [$this->event->getData()]);

        var_dump(__CLASS__ . ':handle start ' . $this->event->getData());

        sleep(10); // 模拟一些慢速的事件处理
        // 监听器中也可以投递Task，但不支持Task的finish()回调。
        // 注意：config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
        $ret = Task::deliver(new TestTask('task data'));
        var_dump(__METHOD__, $ret);
        // throw new \Exception('an exception');// handle时抛出的异常上层会忽略，并记录到Swoole日志，需要开发者try/catch捕获处理
    }

}
