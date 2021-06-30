<?php
//https://wiki.swoole.com/#/client?id=isconnected
//同步阻塞客户端
//$client = new Swoole\Client(SWOOLE_SOCK_TCP);
//if (!$client->connect('127.0.0.1', 9505, -1)) {
//    exit("connect failed. Error: {$client->errCode}\n");
//}
//
////Client 的连接状态
////var_dump('Client 的连接状态===>',$client->isConnected());
//
////获取底层的 socket 句柄，返回的对象为 sockets 资源句柄。
//$socket = $client->getSocket();
////var_dump('底层的 socket 句柄===>',$socket);
////使用 socket_set_option 函数可以设置更底层的一些 socket 参数。
//if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
//    echo 'Unable to set option on socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
//}
//
////用于获取客户端 socket 的本地 host:port。
//var_dump($client->getsockname());
//
////发送数据到远程服务器，必须在建立连接后，才可向对端发送数据
//$client->send("hello world\n");
//
////向任意 IP:PORT 的主机发送 UDP 数据包，仅支持 SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6 类型
////$client->sendto(string $ip, int $port, string $data);
//
////从服务器端接收数据。
////Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
//var_dump($client->recv());
//
////获取对端 socket 的 IP 地址和端口 此函数必须在 $client->recv() 之后调用
////var_dump($client->getpeername());
//
////获取服务器端证书信息。
////var_dump($client->getPeerCert());
//
////验证服务器端证书。
////var_dump($client->verifyPeerCert());
//
////发送文件到服务器，本函数是基于 sendfile 操作系统调用实现
////Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
////var_dump($client->sendfile(string $filename, int $offset = 0, int $length = 0));
//
////关闭连接。
////Swoole\Client->close(bool $force = false): bool
//
////启用SSL隧道加密
////if ($client->enableSSL())
////{
////    //握手完成，此时发送和接收的数据是加密的
////    $client->send("hello world\n");
////    echo $client->recv();
////}
//
//$client->close();

//swoole_client_select
//Swoole\Client 的并行处理中用了 select 系统调用来做 IO 事件循环，不是 epoll_wait，与 Event 模块不同的是，此函数是用在同步 IO 环境中的 (如果在 Swoole 的 Worker 进程中调用，会导致 Swoole 自己的 epoll IO 事件循环没有机会执行)。
//$clients = array();
//
//for($i=0; $i< 20; $i++)
//{
//    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
//    $ret = $client->connect('tcp://127.0.0.1', 9999, 0.5, 0);
//    if(!$ret)
//    {
//        echo "Connect Server fail.errCode=".$client->errCode;
//    }
//    else
//    {
//        $client->send("HELLO WORLD\n");
//        echo "send #{$client->sock}: " . $client->recv() . "\n";
//        $clients[$client->sock] = $client;
//    }
//}
//
//while (!empty($clients))
//{
//    $write = $error = array();
//    $read = array_values($clients);
//    $n = swoole_client_select($read, $write, $error, 0.6);
//    if ($n > 0)
//    {
//        foreach ($read as $index => $c)
//        {
//            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
//            unset($clients[$c->sock]);
//        }
//    }
//}

$socket = new Co\Socket(AF_INET, SOCK_STREAM, 0);

go(function () use ($socket) {
    $retval = $socket->connect('127.0.0.1', 9999);
    while ($retval)
    {
        $n = $socket->send("hello");
        var_dump($n);

//        $data = $socket->recv();
//        var_dump($data);
//
//        if (empty($data)) {//发生错误或对端关闭连接，本端也需要关闭
//            $socket->close();
//            break;
//        }
//        co::sleep(1.0);
    }
    var_dump($retval, $socket->errCode);
});
Swoole\Event::wait();


