<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    /**
     * 启动应用服务
     *
     * @return void
     */
    public function boot() {

        // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
        // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
        Event::listen('laravels.received_request', function (\Illuminate\Http\Request $req, $app) {
            $req->query->set('get_key', 'hhxsv5'); // 修改querystring
            $req->request->set('post_key', 'hhxsv5'); // 修改post body
        });

        // 修改`app/Providers/EventServiceProvider.php`, 添加下面监听代码到boot方法中
        // 如果变量$events不存在，你也可以通过Facade调用\Event::listen()。
        Event::listen('laravels.generated_response', function (\Illuminate\Http\Request $req, \Symfony\Component\HttpFoundation\Response $rsp, $app) {
            $rsp->headers->set('header-key', 'hhxsv5'); // 修改header
        });

        parent::boot();
    }

}
