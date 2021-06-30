<?php

namespace App\Http\Middleware;

use Closure;
use App\Util\PublicValidator;

class ValidatorMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $routeInfo = $request->route();

        $rules = [];
        $messages = [];
        $type = 'api';
        $validatorData = data_get($routeInfo, '1.validator', []);
        if ($validatorData) {
            $rules = data_get($validatorData, 'rules', []);
            $messages = data_get($validatorData, 'messages', []);
            $type = data_get($validatorData, 'type', $type);
        }
        $validator = PublicValidator::handle($request->all(), $rules, $messages, $type);
        if ($validator !== true) {//如果验证没有通过就提示用户
            return $validator;
        }

        return $next($request);
    }

}
