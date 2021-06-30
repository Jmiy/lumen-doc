<?php

namespace App\Http\Middleware;

use Closure;
use App\Util\FunctionHelper;

class CarbonMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $storeId = $request->input('store_id', 0);
        FunctionHelper::setTimezone($storeId);

        return $next($request);
    }

}
