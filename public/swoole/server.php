<?php

//创建Server对象，监听 127.0.0.1:9501 端口
$server = new Swoole\Server('127.0.0.1', 9505);

//设置异步任务的工作进程数量
$server->set(array('task_worker_num' => 4));

//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});

//监听数据接收事件
//$server->on('Receive', function ($server, $fd, $from_id, $data) {
//    $server->send($fd, "Server: " . $data);
//});
//监听数据接收事件 此回调函数在worker进程中执行
$server->on('Receive', function($server, $fd, $from_id, $data) {
    //投递异步任务
    $task_id = $server->task($data);
    echo "Dispatch AsyncTask: id=$task_id\n";
    $server->send($fd, "Server: " . $data);
});

//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});

//处理异步任务(此回调函数在task进程中执行)
$server->on('task', function ($server, $task_id, $from_id, $data) {

    sleep(5);

    echo "New AsyncTask[id=$task_id]" . PHP_EOL;
    //返回任务执行的结果
    $server->finish("$data -> OK");
});

//处理异步任务的结果(此回调函数在worker进程中执行)
$server->on('finish', function ($server, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data" . PHP_EOL;
});

//启动服务器
$server->start();

