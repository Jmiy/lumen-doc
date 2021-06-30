<?php
//https://wiki.swoole.com/#/process/process_pool?id=__construct

//$workerNum = 10;
//$pool = new Swoole\Process\Pool($workerNum);
//
//$pool->on("WorkerStart", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is started\n";
//    $redis = new Redis();
//    $redis->pconnect('127.0.0.1', 6379);
//    $key = "key1";
//    while (true) {
//        $msg = $redis->brpop($key, 2);
//        if ( $msg == null) continue;
//        var_dump($msg);
//    }
//});
//
//$pool->on("WorkerStop", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is stopped\n";
//});
//
//$pool->start();

//协程模式
//在 v4.4.0 版本中 Process\Pool 模块增加了对协程的支持，可以配置第 4 个参数为 true 来启用。启用协程后底层会在 onWorkerStart 时自动创建一个协程和协程容器，在回调函数中可直接使用协程相关 API，例如：
//$pool = new Swoole\Process\Pool(1, SWOOLE_IPC_NONE, 0, true);
//
//$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
////    while (true) {
////        Co::sleep(0.5);
////        echo "hello world\n";
////    }
//
//    Co::sleep(0.5);
//    echo "hello world\n";
//});
//
//$pool->start();

//开启协程后 Swoole 会禁止设置 onMessage 事件回调，需要进程间通讯的话需要将第二个设置为 SWOOLE_IPC_UNIXSOCK 表示使用 unixSocket 进行通信，然后使用 $pool->getProcess()->exportSocket() 导出 Coroutine\Socket 对象，实现 Worker 进程间通信。例如：
//$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);
//
//$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
//    $process = $pool->getProcess(0);
//    $socket = $process->exportSocket();
//    if ($workerId == 0) {
//        echo $socket->recv();
//        $socket->send("hello proc1\n");
//        echo "proc0 stop\n";
//    } else {
//        $socket->send("hello proc0\n");
//        echo $socket->recv();
//        echo "proc1 stop\n";
//        $pool->shutdown();
//    }
//});
//
//$pool->start();

//$workerNum = 10;
//$pool = new Swoole\Process\Pool($workerNum);
//
//$pool->on("WorkerStart", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is started\n";
//    $redis = new Redis();
//    $redis->pconnect('127.0.0.1', 6379);
//    $key = "key1";
//    while (true) {
//        $msg = $redis->brpop($key, 2);
//        if ( $msg == null){
//            continue;
//        }
//        var_dump($msg);
//    }
//});
//
//$pool->on("WorkerStop", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is stopped\n";
//});
//
//$pool->start();

//https://wiki.swoole.com/#/process/process_pool?id=write  配合  process_client.php 使用
//$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_SOCKET);
//
//$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
//    var_dump($workerId);
//});
//
//$pool->on("Message", function ($pool, $message) {
//    echo "Message: {$message}\n";
//    $pool->write("hello ");
//    $pool->write("world ");
//    $pool->write("\n");
//});
//
//$pool->listen('127.0.0.1', 9999);
//$pool->start();

//https://wiki.swoole.com/#/process/process_pool?id=getprocess
//$workerNum = 1;
//$pool = new Swoole\Process\Pool($workerNum);
//
//$pool->on("WorkerStart", function ($pool, $workerId) {
//    $process = $pool->getProcess();
//    //$process->exec("/bin/sh", ["ls", '-l']);
//    $process->exec('/usr/local/php/bin/php', array('-v'));
//    //$process->exec('/usr/bin/ls', array('-l'));
//});
//
//$pool->on("WorkerStop", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is stopped\n";
//});
//
//$pool->start();







