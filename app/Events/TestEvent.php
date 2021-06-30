<?php

namespace App\Events;

use Hhxsv5\LaravelS\Swoole\Task\Event;
use App\Listeners\TestListener1;

class TestEvent extends Event {

    protected $listeners = [
        // 监听器列表
        TestListener1::class,
            // TestListener2::class,
    ];
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

}
