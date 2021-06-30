<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleListener implements ShouldQueue{

    use InteractsWithQueue;

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    //public $connection = 'redis';//sqs

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    //public $queue = 'listeners';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    //public $delay = 60;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(ExampleEvent $event) {
        //__METHOD__, $event, $event->data
        dump(__METHOD__, $event, $event->data);
    }

    /**
     * 处理失败任务。
     *
     * @param  \App\Events\OrderShipped  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(ExampleEvent $event, $exception) {
        //
    }

}
