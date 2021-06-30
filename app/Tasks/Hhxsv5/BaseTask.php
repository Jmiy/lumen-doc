<?php
/**
 * Created by Patazon.
 * @desc   : 注意：Task 进程是同步阻塞的(完全是同步阻塞模式)，不能使用swoole_mysql...等异步方法(使用场景：Task就是给那些写不了协程安全的人用和没有办法协程化的api用) https://wiki.swoole.com/#/learn?id=diff-process
 * @author : Jmiy_cen
 * @email  : Jmiy_cen@patazon.net
 * @date   : 2021/01/08 09:35
 */

namespace App\Tasks\Hhxsv5;

use App\Tasks\TaskManager;
use App\Util\Constant;
use Hhxsv5\LaravelS\Swoole\Task\Task;

class BaseTask extends Task
{

    private $data;
    private $result;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // 处理任务的逻辑，运行在Task进程中，不能投递任务
    public function handle()
    {

        TaskManager::handle(Constant::TASK_PLATFORM_ILLUMINATE, 'Base', 'deliver', $this->data);

        return true;
    }

    // 可选的，完成事件，任务处理完后的逻辑，运行在Worker进程中，可以投递任务
    public function finish()
    {
    }

}
