<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
dirname(__DIR__)
))->bootstrap();

/*
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | Here we will load the environment and create the application instance
  | that serves as the central piece of this framework. We'll use this
  | application as an "IoC" container and router for this framework.
  |
 */

$app = new Laravel\Lumen\Application(
        dirname(__DIR__)
);

$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class); //开启 LaravelS

// 配置-新增
$app->configure('app');
$app->configure('auth');
$app->configure('broadcasting');
$app->configure('cache');
$app->configure('database');
$app->configure('filesystems');
$app->configure('logging');
$app->configure('view');

$app->withFacades(); //启用 Facade

$app->withEloquent(); //启用 Eloquent ORM

/*
  |--------------------------------------------------------------------------
  | Register Container Bindings
  |--------------------------------------------------------------------------
  |
  | Now we will register a few bindings in the service container. We will
  | register the exception handler and the console kernel. You may add
  | your own bindings here if you like or you can make another file.
  |
 */

$app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class
);

$app->singleton(
        Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class
);

/*
  |--------------------------------------------------------------------------
  | Register Middleware
  |--------------------------------------------------------------------------
  |
  | Next, we will register the middleware with the application. These can
  | be global middleware that run before and after each request into a
  | route or middleware that'll be assigned to some specific routes.
  |
 */

//全局中间件 在应用处理每个 HTTP 请求期间运行
// 载入session相关配置
$app->configure('session');

// 注册Cookie服务提供者
//$app->register(Illuminate\Cookie\CookieServiceProvider::class);
$app->register(App\Services\Cookie\CookieServiceProvider::class);
// 设置cookie别名
$app->alias(Illuminate\Contracts\Cookie\QueueingFactory::class, 'cookie');

// 注册 SessionServiceProvider
//$app->register(Illuminate\Session\SessionServiceProvider::class);
$app->register(App\Providers\SessionServiceProvider::class);

// 设置session别名
$app->alias('session', 'Illuminate\Session\SessionManager');

$app->middleware([
//    App\Http\Middleware\SwooleMiddleware::class,
//    App\Http\Middleware\ExampleMiddleware::class,
//     Illuminate\Session\Middleware\StartSession::class,
//     \Illuminate\Session\Middleware\AuthenticateSession::class,
//     \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//    \App\Http\Middleware\CorsMiddleware::class,//自定义解决跨越问题中间件
//    \Barryvdh\Cors\HandleCors::class,//解决跨越问题中间件
    App\Http\Middleware\LangMiddleware::class,
    App\Services\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        //App\Http\Middleware\CreateVisitorPass::class,//用户身份编号cookie
]);

//路由中间件 如果你想将中间件分配给特定的路由，首先需要在 bootstrap/app.php 文件中调用 $app->routeMiddleware() 方法时为中间件分配一个简短的键
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'cors' => \Barryvdh\Cors\HandleCors::class,
    'request_init' => App\Http\Middleware\RequestMiddleware::class,
    'carbon' => App\Http\Middleware\CarbonMiddleware::class,
    'public_validator' => App\Http\Middleware\ValidatorMiddleware::class,
    'activity' => App\Http\Middleware\ActivityMiddleware::class,
    'platform' => App\Http\Middleware\PlatformMiddleware::class,
    'session' => Illuminate\Session\Middleware\StartSession::class,
    'create_visitor' => App\Http\Middleware\CreateVisitorPass::class,//用户身份编号cookie
]);

/*
  |--------------------------------------------------------------------------
  | Register Service Providers
  |--------------------------------------------------------------------------
  |
  | Here we will register all of the application's service providers which
  | are used to bind services into the container. Service providers are
  | totally optional, so you are not required to uncomment this line.
  |
 */
// 开启AppServiceProvider-取消注释
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class); //认证服务提供者 用于用户认证
$app->register(App\Providers\EventServiceProvider::class);

//Redis Support
$app->register(Illuminate\Redis\RedisServiceProvider::class); //composer require illuminate/redis

/*
  |--------------------------------------------------------------------------
  | Load The Application Routes
  |--------------------------------------------------------------------------
  |
  | Next we will include the routes file so that they can all be added to
  | the application. This will provide all of the URLs the application
  | can respond to, as well as the controllers that may handle them.
  |
 */

$app->router->group([
    'namespace' => 'App\Http\Controllers',
        ], function ($router) {
    require __DIR__ . '/../routes/web.php';
});



//解决跨越问题
//$app->configure('cors'); // 如果想 `config/cors.php` 的配置生效，请务必添加这行代码！如果没有添加，则使用默认配置。
//$app->register(Medz\Cors\Lumen\ServiceProvider::class);
$app->configure('cors');
$app->register(Barryvdh\Cors\ServiceProvider::class);

//启动消息队列服务  要使用消息队列发送邮件一定要开启消息队列服务
$app->configure('queue');
$app->register(Illuminate\Queue\QueueServiceProvider::class);

//配置邮件
//###邮件驱动预备知识
//基于 API 的驱动如 Mailgun 和 SparkPost 通常比 SMTP 服务器更简单、更快，所以如果可以的话，尽可能使用这些服务。所有的 API 驱动要求应用已经安装 Guzzle HTTP 库，你可以通过 Composer 包管理器来安装它：composer require guzzlehttp/guzzle
//###Postmark 驱动程序
//要使用 Postmark 驱动程序，请通过 Composer 安装 Postmark 的 SwiftMailer：composer require wildbit/swiftmailer-postmark
$app->configure('services');
$app->configure('mail');
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

//通过 IP 获取到对应的地理位置信息 https://learnku.com/courses/laravel-package/get-the-corresponding-geo-location-information-through-ip-toranngeoip/2024
//直接调用 geoip:update 命令可以将数据信息同步至本地：php artisan geoip:update  数据文件下载到了 storage/app/geoip.mmdb
//清空缓存：php artisan cache:clear
$app->configure('geoip');
//$app->register(Torann\GeoIP\GeoIPServiceProvider::class);
$app->register(App\Services\Torann\GeoIP\GeoIPServiceProvider::class);

//在使用 SFTP、S3 或 Rackspace 等驱动之前，你需要通过 Composer 安装相应的软件包：
//Amazon S3:composer require league/flysystem-aws-s3-v3 ~1.0
//SFTP:composer require league/flysystem-sftp ~1.0
//Rackspace: composer require league/flysystem-rackspace ~1.0
//使用缓存适配器是提高性能的一个绝对必要条件。你需要一个额外的包：
//CachedAdapter: composer require league/flysystem-cached-adapter ~1.0
//composer require wujunze/dingtalk-exception
$app->configure('ding');
$app->register(DingNotice\DingNoticeServiceProvider::class);

//composer require jenssegers/agent https://learnku.com/laravel/t/782/extended-recommendation-laravel-user-agent-easily-identify-client-information
$app->register(Jenssegers\Agent\AgentServiceProvider::class);

//composer require laravel/socialite https://learnku.com/docs/laravel/5.8/socialite/3947
$app->register(Laravel\Socialite\SocialiteServiceProvider::class);

//支付 https://learnku.com/articles/26282?order_by=vote_count&
$app->register(App\Services\Payment\PaymentServiceProvider::class);


return $app;
