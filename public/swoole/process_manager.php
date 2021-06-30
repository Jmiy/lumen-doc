<?php
//https://wiki.swoole.com/#/process/process_manager

use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();//SWOOLE_IPC_UNIXSOCK

//for ($i = 0; $i < 2; $i++) {
//    $pm->add(function (Pool $pool, int $workerId) {
//
//        var_dump($workerId);
//
//        $pool->on('workerStart', function (Pool $pool, int $workerId) {
//            $process = $pool->getProcess(0);
//            $socket = $process->exportSocket();
//            if ($workerId == 0) {
//                echo $socket->recv();
//                $socket->send("hello proc1\n");
//                echo "proc0 stop\n";
//            } else {
//                $socket->send("hello proc0\n");
//                echo $socket->recv();
//                echo "proc1 stop\n";
//                $pool->shutdown();
//            }
//        });
//
//        $pool->on("Message", function ($pool, $message) {
//            echo "Message: {$message}\n";
//            $pool->write("hello ");
//            $pool->write("world ");
//            $pool->write("\n");
//        });
//
//        $pool->start();
//
//
//    }, true);
//}

$pm->setIPCType(SWOOLE_IPC_UNIXSOCK);

$pm->addBatch(2, function (Pool $pool, int $workerId) {

    var_dump($workerId);

//    $pool->on("Message", function ($pool, $message) {
//            echo "Message: {$message}\n";
//            $pool->write("hello ");
//            $pool->write("world ");
//            $pool->write("\n");
//        });

}, true);

$pm->start();








