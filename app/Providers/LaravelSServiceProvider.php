<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelSServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        dump(__METHOD__,\App\Util\FunctionHelper::isSwooleRun());
//        $this->mergeConfigFrom(
//            __DIR__ . '/../../config/swoole/redis.php', 'database.redis'
//        );
    }

    /**
     * 启动应用服务
     *
     * @return void
     */
    public function boot() {
        dump(__METHOD__,__DIR__ . '/../../config/swoole/redis.php',\App\Util\FunctionHelper::isSwooleRun());
//        $this->publishes([
//            __DIR__ . '/../../config/swoole/redis.php' => base_path('config/swoole/redis.php'),
//        ]);
    }

}
