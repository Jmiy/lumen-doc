<?php

require '../public/index.php';

//$storeId = 5;
//$emails = [
//    'email_from.value' => 'support@ikich.com,newsletter@ikich.com,marketing@ikich.com'
//];
//$ret = \App\Services\EmailStatisticsService::handleEmailConfigs($storeId, $emails, []);
//var_dump($ret);
//$ret = \App\Services\EmailStatisticsService::updateSendNums($storeId, $ret, []);
//var_dump($ret);

//$parameters = [$storeId, $actId, $applyId, $extType, 'email', 'view_audit_unlock_1'];
//\App\Services\ActivityHelpedLogService::emailUnlockedUsers(5, 3, 982, 'ActivityApply', 'email', 'view_audit_unlock_1');


//$str = "{\"displayName\":\"App\\\\Jobs\\\\PublicJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"delay\":null,\"timeout\":null,\"timeoutAt\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\PublicJob\",\"command\":\"O:18:\\\"App\\\\Jobs\\\\PublicJob\\\":8:{s:4:\\\"data\\\";a:4:{s:7:\\\"service\\\";s:26:\\\"\\\\App\\\\Services\\\\EmailService\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:10:\\\"parameters\\\";a:8:{i:0;i:5;i:1;s:22:\\\"rkhexd37896@chacuo.net\\\";i:2;s:5:\\\"apply\\\";i:3;s:19:\\\"view_audit_unlock_1\\\";i:4;s:34:\\\"2020 unlocked activity\\u89e3\\u9501\\u6210\\u529f\\\";i:5;i:982;i:6;s:13:\\\"ActivityApply\\\";i:7;a:5:{s:5:\\\"actId\\\";i:3;s:7:\\\"service\\\";s:38:\\\"\\\\App\\\\Services\\\\ActivityHelpedLogService\\\";s:6:\\\"method\\\";s:12:\\\"getEmailData\\\";s:10:\\\"parameters\\\";a:10:{i:0;i:5;i:1;i:3;i:2;s:22:\\\"rkhexd37896@chacuo.net\\\";i:3;i:287067;i:4;s:14:\\\"172.16.179.197\\\";i:5;i:982;i:6;s:13:\\\"ActivityApply\\\";i:7;s:5:\\\"email\\\";i:8;s:19:\\\"view_audit_unlock_1\\\";i:9;a:1:{s:9:\\\"applyUser\\\";a:1:{s:9:\\\"firstName\\\";s:7:\\\"first_0\\\";}}}s:8:\\\"callBack\\\";a:0:{}}}s:7:\\\"extData\\\";a:3:{s:7:\\\"service\\\";s:26:\\\"\\\\App\\\\Services\\\\EmailService\\\";s:6:\\\"method\\\";s:6:\\\"handle\\\";s:10:\\\"parameters\\\";a:8:{i:0;i:5;i:1;s:22:\\\"rkhexd37896@chacuo.net\\\";i:2;s:5:\\\"apply\\\";i:3;s:19:\\\"view_audit_unlock_1\\\";i:4;s:34:\\\"2020 unlocked activity\\u89e3\\u9501\\u6210\\u529f\\\";i:5;i:982;i:6;s:13:\\\"ActivityApply\\\";i:7;a:5:{s:5:\\\"actId\\\";i:3;s:7:\\\"service\\\";s:38:\\\"\\\\App\\\\Services\\\\ActivityHelpedLogService\\\";s:6:\\\"method\\\";s:12:\\\"getEmailData\\\";s:10:\\\"parameters\\\";a:10:{i:0;i:5;i:1;i:3;i:2;s:22:\\\"rkhexd37896@chacuo.net\\\";i:3;i:287067;i:4;s:14:\\\"172.16.179.197\\\";i:5;i:982;i:6;s:13:\\\"ActivityApply\\\";i:7;s:5:\\\"email\\\";i:8;s:19:\\\"view_audit_unlock_1\\\";i:9;a:1:{s:9:\\\"applyUser\\\";a:1:{s:9:\\\"firstName\\\";s:7:\\\"first_0\\\";}}}s:8:\\\"callBack\\\";a:0:{}}}}}s:6:\\\"\\u0000*\\u0000job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:5:\\\"delay\\\";N;s:7:\\\"chained\\\";a:0:{}}\"},\"id\":\"fODTo0IJNnp1xjaETz5WqQ2eEe65pUUs\",\"attempts\":0}";
//$data = json_decode($str,true);
//$pram = (array)unserialize($data['data']['command']);
//$service = $pram['data']['service'];
//$method = $pram['data']['method'];
//$parameters = $pram['data']['parameters'];
//$service::$method(...$parameters);


//($storeId, $email, $phone, $lineItems, $shippingAddress, $billingAddress, $transactions, $financialStatus, $discountCodes)
$storeId = 2;
$email = "qiufengtao002@163.com";
$phone = "";
$lineItems[] = [
    "variant_id" => '31556108320820',
    "quantity" => 2
];
$shippingAddress = [
    "first_name" => "qiu211",
    "last_name" => "fengtao",
    "address1"=> "123 Fake Street",
    "phone"=> "777-777-7777",
    "city"=> "shenzhen",
    "province"=> "guangdong",
    "country"=> "china",
    "zip"=> "123123"
];
$billingAddress = [
    "first_name" => "qiu",
    "last_name" => "fengtao",
    "address1"=> "123 Fake Street",
    "phone"=> "777-777-7777",
    "city"=> "shenzhen",
    "province"=> "guangdong",
    "country"=> "china",
    "zip"=> "123123"
];
$transactions = [

];
$financialStatus = "paid";
$discountCodes[] = [
    "code"=>"points exchange",
    "amount"=> 100,
    "type"=> "percentage"
];
$noteAttributes = [
    [
        "name" => "order_type",
        "value" => "points exchange"
    ]
];
$tags = [
    "test_tags"
];
//var_dump(\App\Services\Store\Shopify\Orders\Order::create($storeId, $email, $phone, $lineItems, $shippingAddress, $billingAddress, $transactions, $financialStatus, $discountCodes, $noteAttributes, $tags));


$ret = \App\Services\Psc\PscService::tokenAuthentication('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJwYXRvem9uLm5ldCIsImlhdCI6MTYwNTE0NzgzNiwiaWQiOiI2NzYifQ.0pksyg6Y1Nz5hKe8E72cMQJTOnYYvgg_BF-5BgUQpFg');
var_dump($ret);
