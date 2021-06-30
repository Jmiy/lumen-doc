<?php

namespace App\Util\Cache;

use Illuminate\Support\Facades\Redis;

class RedisManager {

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args) {
        return Redis::{$method}(...$args);
    }

}
