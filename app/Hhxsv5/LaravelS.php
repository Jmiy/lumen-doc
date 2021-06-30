<?php

namespace App\Hhxsv5;

use Hhxsv5\LaravelS\Illuminate\Laravel;

//use App\Hhxsv5\Illuminate\Laravel;
use Hhxsv5\LaravelS\Swoole\Request;
use Swoole\Http\Request as SwooleRequest;
use Hhxsv5\LaravelS\LaravelS as HhxsvLaravelS;


/**
 * Swoole Request => Laravel Request
 * Laravel Request => Laravel handle => Laravel Response
 * Laravel Response => Swoole Response
 */
class LaravelS extends HhxsvLaravelS
{

    protected function convertRequest(Laravel $laravel, SwooleRequest $request)
    {
        $rawGlobals = $laravel->getRawGlobals();
        //$_request = (new Request($request))->toIlluminateRequest($rawGlobals['_SERVER'], $rawGlobals['_ENV']);
        //$_request->offsetSet('APP_START', microtime(true));
        return (new Request($request))->toIlluminateRequest($rawGlobals['_SERVER'], $rawGlobals['_ENV']);
    }
}
