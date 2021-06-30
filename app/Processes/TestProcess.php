<?php

namespace App\Processes;

use App\Tasks\TaskManager;
use App\Tasks\TestTask;
use App\Util\Constant;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Swoole\Coroutine;
use Swoole\Http\Server;
use Swoole\Process;
use Swoole\Runtime;

class TestProcess implements CustomProcessInterface {

    /**
     * @var bool 退出标记，用于Reload更新
     */
    private static $quit = false;

    public static function callback(Server $swoole, Process $process) {
        // 进程运行的代码，不能退出，一旦退出Manager进程会自动再次创建该进程。
//        while (!self::$quit) {
////            \Log::info('Test process: running');
////            // sleep(1); // Swoole < 2.1
////            Coroutine::sleep(1); // Swoole>=2.1 已自动为callback()方法创建了协程并启用了协程Runtime。
////            // 自定义进程中也可以投递Task，但不支持Task的finish()回调。
////            // 注意：修改config/laravels.php，配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/#/server/setting?id=task_ipc_mode
////            $ret = Task::deliver(new TestTask('task data'));
////            var_dump($ret);
////            // 上层会捕获callback中抛出的异常，并记录到Swoole日志，然后此进程会退出，3秒后Manager进程会重新创建进程，所以需要开发者自行try/catch捕获异常，避免频繁创建进程。
////            // throw new \Exception('an exception');
//
//            while ($data = $process->read()) {
//                //\Log::info('TestProcess: read data', [$data]);
//                $process->write(__CLASS__ . ': ' . $data);
//                //dump(__METHOD__ , $data);
//            }
//        }

        Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节

        Coroutine::create(function () use ($process) {

            Coroutine::create(function () use ($process) {

                while ($data = $process->read()) {

                    //\Log::info('TestProcess: read data', [$data]);
                    try{
                        $parameters = json_decode($data,true);
                        $isDeliverTask = Task::deliver(new TestTask('task data'));;
                        $process->write($isDeliverTask);
                    }catch (\Exception $exc){
                        $process->write($data);
                        dump($data);
                    }
                }
            });

        });




    }

    // 要求：LaravelS >= v3.4.0 并且 callback() 必须是异步非阻塞程序。
    public static function onReload(Server $swoole, Process $process) {
        // Stop the process...
        // Then end process
        //\Log::info('Test process: reloading');
        self::$quit = true;
        // $process->exit(0); // 强制退出进程
        //dump(__METHOD__);
    }

    // 要求：LaravelS >= v3.7.4 并且 callback() 必须是异步非阻塞程序。
    public static function onStop(Server $swoole, Process $process) {
        // Stop the process...
        // Then end process
        //\Log::info('Test process: stopping');
        self::$quit = true;
        // $process->exit(0); // 强制退出进程
        //dump(__METHOD__);
    }

}
