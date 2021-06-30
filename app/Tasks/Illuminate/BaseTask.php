<?php
/**
 * Created by Patazon.
 * @desc   : 注意：Illuminate Task 借助消息队列实现异步操作  如果消息队列异常会导致调用本 Task 的主进程无法工作，并不能实现真正意义的协程或者多线程(真正意义的协程或者多线程异常是不影响主进程的执行的)
 * @author : Jmiy_cen
 * @email  : Jmiy_cen@patazon.net
 * @date   : 2021/01/08 09:35
 */
namespace App\Tasks\Illuminate;

use App\Util\FunctionHelper;

class BaseTask
{
    private $data;

    /**
     * The number of seconds before the task should be delayed.
     * @var int
     */
    protected $delay = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Delay in seconds, null means no delay.
     * @param int $delay
     * @return $this
     */
    public function delay($delay)
    {
        if ($delay < 0) {
            throw new \InvalidArgumentException('The delay must be greater than or equal to 0');
        }
        $this->delay = (int)$delay;
        return $this;
    }

    /**
     * Return the delay time.
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Deliver a task
     * @param BaseTask $task The task object
     * @return bool
     */
    public static function deliver(self $task)
    {
        foreach ($task->data as $item) {
            FunctionHelper::pushQueue($item);//记录接口请求日志
        }

        return true;
    }

    public function handle()
    {
    }

}
