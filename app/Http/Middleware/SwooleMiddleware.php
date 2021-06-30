<?php

namespace App\Http\Middleware;

use Closure;
use App\Util\FunctionHelper;

class SwooleMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if(\App\Util\FunctionHelper::isSwooleRun()){
            app('config')->set('database.redis', require base_path('config/swoole/redis.php'));
        }

        return $next($request);
    }

}
