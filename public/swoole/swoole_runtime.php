<?php
/**
 * 协程容器：https://wiki.swoole.com/#/coroutine/scheduler
 * 所有的协程必须在协程容器里面创建，Swoole 程序启动的时候大部分情况会自动创建协程容器，用 Swoole 启动程序的方式一共有三种
 * 1：调用异步风格服务端程序的 start 方法，此种启动方式会在事件回调中创建协程容器，参考 enable_coroutine。
 * 2：调用 Swoole 提供的 2 个进程管理模块 Process 和 Process\Pool 的 start 方法，此种启动方式会在进程启动的时候创建协程容器，参考这两个模块构造函数的 enable_coroutine 参数。
 * 3：其他直接裸写协程的方式启动程序，需要先创建一个协程容器 (Co\run() 函数，可以理解为 java、c 的 main 函数)，例如：
 * 创建协程容器：Co\run()
 *
 * 协程Coroutine：https://wiki.swoole.com/#/coroutine
 * go关键词创建一个协程，可以简单的理解为创建了一个线程
 *
 * 一键协程化：https://wiki.swoole.com/#/runtime
 */
//
//
use Swoole\Coroutine;
//
//Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
//
////Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
//var_dump(Swoole\Runtime::getHookFlags());

$s = microtime(true);

/******************  在协程容器中  启动一个全协程HTTP服务 整个主进程都被阻塞  start**************/
//Co\run(function () {
//    $server = new Co\Http\Server("127.0.0.1", 9599, false);
//    $server->handle('/', function ($request, $response) {
//        $response->end("<h1>Index</h1>");
//    });
//    $server->handle('/test', function ($request, $response) {
//        $response->end("<h1>Test</h1>");
//    });
//    $server->handle('/stop', function ($request, $response) use ($server) {
//        $response->end("<h1>Stop</h1>");
//        $server->shutdown();
//    });
//    $server->start();//同步阻塞
//});
//echo 1;//得不到执行
/******************  使用协程容器  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成  start**************/

/******************  使用协程容器(注意：协程容器会自动一键协程化(即执行：Swoole\Runtime::enableCoroutine()) 不需要手动执行Swoole\Runtime::enableCoroutine())  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成  start**************/
//Co\run(function() use($s){
//    go(function() {
//        sleep(10);
//        echo "go-1==>Co::run\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//
//    go(function() {
//        echo "go-2==>Co::run\n";
//    });//2协程会马上执行
//    echo 'Co\run===use ' . (microtime(true) - $s) . " s\n";//可以得到执行，并且马上输出，不受 1协程的影响
//});
//echo 'processes===use ' . (microtime(true) - $s) . " s\n";//可以得到执行，但是必须等待 1协程 和 2协程执行完成才可以执行(即 必须等待 1协程 10s 和 2协程执行完成才可以执行)，
/******************  使用协程容器(注意：协程容器会自动一键协程化(即执行：Swoole\Runtime::enableCoroutine()) 不需要手动执行Swoole\Runtime::enableCoroutine())  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成  end**************/


/**
 * 使用协程容器并且在协程容器内手动执行Swoole\Runtime::enableCoroutine() 会导致整个进程卡死，这种使用方式是禁止的，否则会导致服务不可用
 * 如果要手动执行 Swoole\Runtime::enableCoroutine() 必须在 协程容器 创建之前执行，否则就会导致整个进程卡死
 *
 * 整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成  start
 */
//$dd = Co\run(function() use($s){
//    Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
//    go(function() {
//        sleep(10);
//        echo "go-1==>Co::run==>Swoole\Runtime::enableCoroutine\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//
//    go(function() {
//        echo "go-2==>Co::run==>Swoole\Runtime::enableCoroutine\n";
//    });//2协程会马上执行
//    echo 'Co::run==>Swoole\Runtime::enableCoroutine==>use ' . (microtime(true) - $s) . " s\n";//可以得到执行，并且马上输出，不受 1协程的影响
//});
//echo 'processes===Co::run==>Swoole\Runtime::enableCoroutine==>use ' . (microtime(true) - $s) . " s\n";//可以得到执行，但是必须等待 1协程 和 2协程执行完成才可以执行(即 必须等待 1协程 10s 和 2协程执行完成才可以执行)，
/******************  使用协程容器  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成  end**************/

/******************  一键协程化+使用协程  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成 start**************/
//Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
//go(function() {
//    sleep(10);
//});//1协程会异步阻塞 被挂起 10 秒
//
//go(function() {
//    echo "done\n";
//});//2协程会马上执行
//echo 'processes===use ' . (microtime(true) - $s) . ' s\n';//可以得到执行，并且马上输出，不受 1协程的影响
/******************  一键协程化 使用协程  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成 end**************/

/******************  使用协程  整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成**************/
//go(function() {
//    sleep(10);
//});//1协程会同步阻塞 10 秒
//
//go(function() {
//    //Co::sleep(1);
//    echo "done\n";
//});//2协程 要等待 1协程执行完成以后 才执行
//echo 'processes===use ' . (microtime(true) - $s) . ' s\n';//可以得到执行，但是必须等待 1协程 和 2协程执行完成才可以执行(即 必须等待 1协程 10s 后才执行)，

//等同于 以下的串行执行
//sleep(10);//1协程会同步阻塞 10 秒
//echo "done\n";//2协程 要等待 1协程执行完成以后 才执行
//echo 'processes===use ' . (microtime(true) - $s) . ' s\n';//可以得到执行，但是必须等待 1协程 和 2协程执行完成才可以执行(即 必须等待 1协程 10s 后才执行)，
/******************整个主进程还是被阻塞了 必须等待 1协程 10s 才执行完成**************/

//Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节

//Co\run(function() use($s){
    go(function() {
        \Swoole\Runtime::enableCoroutine();
        sleep(10);
        echo "go-1==>Co::run\n";
    });//1协程会异步阻塞 被挂起 10 秒


//    go(function() {
//        sleep(10);
//        echo "go-2==>Co::run\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//
//    go(function() {
//        sleep(10);
//        echo "go-3==>Co::run\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//
//    go(function() {
//        sleep(10);
//        echo "go-4==>Co::run\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//
//    go(function() {
//        sleep(10);
//        echo "go-5==>Co::run\n";
//    });//1协程会异步阻塞 被挂起 10 秒
//    echo 'Co\run===use ' . (microtime(true) - $s) . " s\n";//可以得到执行，并且马上输出，不受 1协程的影响
//});
echo 'processes===use ' . (microtime(true) - $s) . " s\n";//可以得到执行，但是必须等待 1协程 和 2协程执行完成才可以执行(即 必须等待 1协程 10s 后才执行)，

//Coroutine::create(function() {
//
//    Coroutine::create(function () {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, "https://www.baidu.com/");
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        $result = curl_exec($ch);
//        curl_close($ch);
//        var_dump($result);
//    });
//
//    // i just want to sleep...
//    for ($c = 100; $c--;) {
//        Coroutine::create(function () {
//            for ($n = 100; $n--;) {
//                usleep(1000);
//            }
//        });
//    }
//
//    // 10k file read and write
//    for ($c = 100; $c--;) {
//        Coroutine::create(function () use ($c) {
//            $tmp_filename = "/tmp/test-{$c}.php";
//            for ($n = 100; $n--;) {
//                $self = file_get_contents(__FILE__);
//                file_put_contents($tmp_filename, $self);
//                assert(file_get_contents($tmp_filename) === $self);
//            }
//            unlink($tmp_filename);
//        });
//    }
//
//    // 10k pdo and mysqli read
//    for ($c = 50; $c--;) {
//        Coroutine::create(function () {
//            $pdo = new PDO('mysql:host=127.0.0.1;dbname=ptxcrm;charset=utf8', 'root', 'root');
//            $statement = $pdo->prepare('SELECT * FROM `crm_demo`');
//            for ($n = 100; $n--;) {
//                $statement->execute();
//                $dd = $statement->fetchAll();
//                //var_dump($dd);
//                assert(count($dd) > 0);
//            }
//        });
//    }
//    for ($c = 50; $c--;) {
//        Coroutine::create(function () {
//            $mysqli = new Mysqli('127.0.0.1', 'root', 'root', 'ptxcrm');
//            $statement = $mysqli->prepare('SELECT `id` FROM `crm_demo`');
//            for ($n = 100; $n--;) {
//                $statement->bind_result($id);
//                $statement->execute();
//                $statement->fetch();
//                //var_dump($id);
//                assert($id > 0);
//            }
//        });
//    }
//
//    // php_stream tcp server & client with 12.8k requests in single process
//    function tcp_pack(string $data): string {
//        return pack('n', strlen($data)) . $data;
//    }
//
//    function tcp_length(string $head): int {
//        if (empty($head)) {
//            return 0;
//        }
//
//        return unpack('n', $head)[1];
//    }
//
//    Coroutine::create(function () {
//        $errno = '';
//        $errstr = '';
//        $ctx = stream_context_create(['socket' => ['so_reuseaddr' => true, 'backlog' => 128]]);
//        $socket = stream_socket_server(
//                'tcp://127.0.0.1:9502', $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ctx
//        );
//        if (!$socket) {
//            echo "$errstr ($errno)\n";
//        } else {
//            $i = 0;
//            while ($conn = stream_socket_accept($socket, 1)) {
//                stream_set_timeout($conn, 5);
//                for ($n = 100; $n--;) {
//                    $data = fread($conn, tcp_length(fread($conn, 2))); //接收客户端数据
//
//                    //var_dump($data);
//
//                    assert($data === "Hello Swoole Server #{$n}!");
//                    fwrite($conn, tcp_pack("Hello Swoole Client #{$n}!")); //发送数据到客户端
//                }
//                if (++$i === 128) {
//                    fclose($socket);
//                    break;
//                }
//            }
//        }
//    });
//
//    for ($c = 128; $c--;) {
//        Coroutine::create(function () {
//            $fp = stream_socket_client("tcp://127.0.0.1:9502", $errno, $errstr, 1);
//            if (!$fp) {
//                echo "$errstr ($errno)\n";
//            } else {
//                stream_set_timeout($fp, 5);
//                for ($n = 100; $n--;) {
//                    fwrite($fp, tcp_pack("Hello Swoole Server #{$n}!")); //发送数据到服务端
//                    $data = fread($fp, tcp_length(fread($fp, 2))); //接收服务端发送的数据
//                    //var_dump($data);
//
//                    assert($data === "Hello Swoole Client #{$n}!");
//                }
//                fclose($fp);
//            }
//        });
//    }
//
//    // udp server & client with 12.8k requests in single process
//    Coroutine::create(function () {
//        $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
//        $socket->bind('127.0.0.1', 9503);
//        $client_map = [];
//        for ($c = 128; $c--;) {
//            for ($n = 0; $n < 100; $n++) {
//                $recv = $socket->recvfrom($peer);
//                $client_uid = "{$peer['address']}:{$peer['port']}";
//                $id = $client_map[$client_uid] = ($client_map[$client_uid] ?? -1) + 1;
//
//                //var_dump($recv,$client_uid);
//
//                assert($recv === "Client: Hello #{$id}!");
//                $socket->sendto($peer['address'], $peer['port'], "Server: Hello #{$id}!");
//            }
//        }
//        $socket->close();
//    });
//    for ($c = 128; $c--;) {
//        Coroutine::create(function () {
//            $fp = stream_socket_client("udp://127.0.0.1:9503", $errno, $errstr, 1);
//            if (!$fp) {
//                echo "$errstr ($errno)\n";
//            } else {
//                for ($n = 0; $n < 100; $n++) {
//                    fwrite($fp, "Client: Hello #{$n}!"); //发送数据到服务端
//                    $recv = fread($fp, 1024); //接收服务端发送的数据
//
//                    list($address, $port) = explode(':', (stream_socket_get_name($fp, true)));
//
//                    //var_dump($recv,$address, $port);
//
//                    assert($address === '127.0.0.1' && (int) $port === 9503);
//                    assert($recv === "Server: Hello #{$n}!");
//                }
//                fclose($fp);
//            }
//        });
//    }
//});
//echo 'processes===use ' . (microtime(true) - $s) . ' s';
