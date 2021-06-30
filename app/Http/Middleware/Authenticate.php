<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Util\Response;
use App\Util\Constant;

class Authenticate {

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth) {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null) {
        if ($this->auth->guard($guard)->guest()) {
            //return response('Unauthorized.', 401);

            $registerResponse = $request->input(Constant::REGISTER_RESPONSE, []);
            $msg = data_get($registerResponse, Constant::RESPONSE_MSG_KEY, 'customer not exists');
            $code = data_get($registerResponse, Constant::RESPONSE_CODE_KEY, 20019);

            return $guard ? Response::json([], 20019, '登录状态已失效，请重新登录') : Response::json([], $code, $msg);
        }

        return $next($request);
    }

}
