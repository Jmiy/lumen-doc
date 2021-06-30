<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 拨号一个连接
$dialer = new \Mix\Grpc\Client\Dialer();
$conn    = $dialer->dial('127.0.0.1', 9595);
// 通过连接创建客户端
$client  = new \Php\Micro\Grpc\Greeter\SayClient($conn);
// 发送请求
$request = new \Php\Micro\Grpc\Greeter\Request();
$request->setName('xiaoming');
$response = $client->Hello($request);
// 打印结果
var_dump($response->getMsg());

