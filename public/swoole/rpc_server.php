<?php

require_once __DIR__ . '/../vendor/autoload.php';

class SayService implements \Php\Micro\Grpc\Greeter\SayInterface {

    public function Hello(\Mix\Context\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        var_dump($request->getName());
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}

$func = function  () {
    $server = new \Mix\Grpc\Server('0.0.0.0', 9595);
    $server->register(SayService::class);
    go(function () use ($server) {
        $server->start();
    });
    
//    $dialer = new \Mix\Grpc\Client\Dialer();
//    $conn = $dialer->dial('127.0.0.1', 9595);
//    $client = new \Php\Micro\Grpc\Greeter\SayClient($conn);
//    $request = new \Php\Micro\Grpc\Greeter\Request();
//    $request->setName('xiaoming');
//    $response = $client->Hello(new \Mix\Context\Context(), $request);
    
    $client = new \Php\Micro\Grpc\Greeter\SayClient("127.0.0.1:9595",[
                                'credentials' => \Grpc\ChannelCredentials::createInsecure()
                ]);
    $request = new \Php\Micro\Grpc\Greeter\Request();
    $request->setName('xiaoming');
    $response = $client->Hello($request);

    var_dump($response);
    
//    $loginname = "loginname";
//    $password = (string)"password";
//    $userrpc = new \App\UserRpc\UserClient("127.0.0.1:9595",[
//                                'credentials' => \Grpc\ChannelCredentials::createInsecure()
//                ]);
//    $request = new \App\UserRpc\LoginInfo();
//    $request->setLoginname($loginname);
//    $request->setPassword($password);
//    list($recv,$status) = $userrpc->UserLogin($request)->wait();
//    $code = $recv->getCode();
//    echo $code;

    $server->shutdown();
};
$func();

//// 编写一个服务，实现 protoc-gen-mix 生成的接口
//class SayService implements \Php\Micro\Grpc\Greeter\SayInterface {
//
//    public function Hello(\Mix\Context\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response {
//        $response = new \Php\Micro\Grpc\Greeter\Response();
//        $response->setMsg(sprintf('hello, %s', $request->getName()));
//        return $response;
//    }
//
//}

//Swoole\Runtime::enableCoroutine(); // 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
////// Co\run()见'协程容器'章节
//Co\run(function() {
//    go(function () {
//        // 创建一个服务器
//        $server = new \Mix\Grpc\Server('0.0.0.0', 9595); // 默认会随机分配端口，也可以指定
//        $server->register(SayService::class);
//        $server->start();
//    });
//});



