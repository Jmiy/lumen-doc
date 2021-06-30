<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Redis Databases
      |--------------------------------------------------------------------------
      |
      | Redis is an open source, fast, and advanced key-value store that also
      | provides a richer set of commands than a typical key-value systems
      | such as APC or Memcached. Laravel makes it easy to dig right in.
      |
     */
    //'client' => 'predis',
    'client' => env('REDIS_CLIENT', 'predis'), //'phpredis',//使用 PhpRedis PHP extension
    'cluster' => env('REDIS_CLUSTER', false),
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
        //'read_write_timeout' => 60,//读写超时时间
        //'persistent' => true, // 开启持久连接
    ],
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1),
        'persistent' => true, // 开启持久连接
    ],
    'queue' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_QUEUE_DB', 2),
        //'read_write_timeout' => 60,//读写超时时间
        //'persistent' => true, // 开启持久连接
    ],
    'session' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_SESSION_DB', 3),
        //'persistent' => true, // 开启持久连接
    ],
];
