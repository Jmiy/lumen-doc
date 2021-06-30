<?php

namespace App\Processes\Hhxsv5;

use App\Processes\ProcessManager;
use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Http\Server;
use Swoole\Process;

class BaseProcess implements CustomProcessInterface
{

    /**
     * @var bool 退出标记，用于Reload更新
     */
    private static $quit = false;

    public static function callback(Server $swoole, Process $process)
    {
        // 进程运行的代码，不能退出，一旦退出Manager进程会自动再次创建该进程。
        /**
         * 从管道中读取数据。https://wiki.swoole.com/wiki/page/217.html
         * $buffer_size是缓冲区的大小，默认为8192，最大不超过64K
         * 管道类型为DGRAM数据报时，read可以读取完整的一个数据包
         * 管道类型为STREAM时，read是流式的，需要自行处理包完整性问题
         * 读取成功返回二进制数据字符串，读取失败返回false
         * $data = $process->exportSocket()->recv(65535, 10.0);
         */
        while ($data = $process->read(65535)) {//同步阻塞读取
            try {
                $parameters = json_decode($data, true);
                ProcessManager::handle(...$parameters);
            } catch (\Exception $exc) {
            }
        }

    }

    // 要求：LaravelS >= v3.4.0 并且 callback() 必须是异步非阻塞程序。
    public static function onReload(Server $swoole, Process $process)
    {
        // Stop the process...
        // Then end process
        self::$quit = true;
        $process->exit(0); // 强制退出进程
    }

    // 要求：LaravelS >= v3.7.4 并且 callback() 必须是异步非阻塞程序。
    public static function onStop(Server $swoole, Process $process)
    {
        // Stop the process...
        // Then end process
        self::$quit = true;
        $process->exit(0); // 强制退出进程
    }

    /**
     * 向管道内写入数据
     * @param array $data
     * @param string $customProcesses
     * @return mixed
     */
    public static function write($data = [], $customProcesses = 'baseProcess')
    {
        $process = app('swoole')->customProcesses[$customProcesses];
        /**
         * 向管道内写入数据。https://wiki.swoole.com/wiki/page/216.html
         * 在子进程内调用write，父进程可以调用read接收此数据
         * 在父进程内调用write，子进程可以调用read接收此数据
         * Swoole底层使用Unix Socket实现通信，Unix Socket是内核实现的全内存通信，无任何IO消耗。在1进程write，1进程read，每次读写1024字节数据的测试中，100万次通信仅需1.02秒。
         * 管道通信默认的方式是流式，write写入的数据在read可能会被底层合并。可以设置swoole_process构造函数的第三个参数为2改变为数据报式。
         */
        //$process->write(json_encode($data, JSON_UNESCAPED_UNICODE));

        $process->exportSocket()->send(json_encode($data, JSON_UNESCAPED_UNICODE), 10);

        //return $process->read();//同步阻塞读取
        return true;
    }

}
