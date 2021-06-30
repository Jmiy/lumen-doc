<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;

class CreateVisitorPass {

    /**
     * The cookie jar instance.
     *
     * @var \Illuminate\Contracts\Cookie\QueueingFactory
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param  \Illuminate\Contracts\Cookie\QueueingFactory  $cookies
     * @return void
     */
    public function __construct(CookieJar $cookies) {
        $this->cookies = $cookies;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!$request->cookie('visitor_id')) {
            $host = $request->getHost();
            $title = substr($host, strpos($host, '.') + 1);
            $title = substr($title, 0, strrpos($title, '.'));
            $key = md5((str_random(16) . microtime(true) . $title));

            $this->cookies->queue('visitor_id', $key, 2628000);
            $request->cookies->set('visitor_id', $key);
        }

        return $next($request);
    }

}
