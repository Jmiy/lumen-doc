<?php

namespace App\Events;

use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Swoole\Http\Server;

class WorkerStartEvent implements WorkerStartInterface {

    public function __construct() {

    }

    public function handle(Server $server, $workerId) {
        // 初始化一个数据库连接池对象
        //DatabaseConnectionPool::init();
    }

}
