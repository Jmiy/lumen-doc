<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Default Database Connection Name
      |--------------------------------------------------------------------------
      |
      | Here you may specify which of the database connections below you wish
      | to use as your default connection for all database work. Of course
      | you may use many connections at once using the Database library.
      |
     */

    'default' => env('DB_CONNECTION', 'mysql'),
    /*
      |--------------------------------------------------------------------------
      | Database Connections
      |--------------------------------------------------------------------------
      |
      | Here are each of the database connections setup for your application.
      | Of course, examples of configuring each database platform that is
      | supported by Laravel is shown below to make development simple.
      |
      |
      | All database work in Laravel is done through the PHP PDO facilities
      | so make sure you have the driver for your particular database of
      | choice installed on your machine before you begin development.
      |
     */
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => env('DB_PREFIX', ''),
        ],
        'mysql' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'options' => [
                // 开启持久连接
                //\PDO::ATTR_PERSISTENT => true,
            ],
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_mpow' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'ptxcrm_mpow',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'options' => [
                // 开启持久连接
                //\PDO::ATTR_PERSISTENT => true,
            ],
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_victsing' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'ptxcrm_victsing',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'options' => [
                // 开启持久连接
                //\PDO::ATTR_PERSISTENT => true,
            ],
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_xc' => [//销参产品价格库
            'read' => [
                'host' => [env('XC_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_DB_PORT', 3306),
            'database' => env('XC_DB_DATABASE', 'forge'),
            'username' => env('XC_DB_USERNAME', 'forge'),
            'password' => env('XC_DB_PASSWORD', ''),
            'unix_socket' => env('XC_DB_SOCKET', ''),
            'charset' => env('XC_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_DB_PREFIX', ''),
            'strict' => env('XC_DB_STRICT_MODE', false),
            'engine' => env('XC_DB_ENGINE', null),
            'timezone' => env('XC_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('XC_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_xc_order' => [//销参订单库
            'read' => [
                'host' => [env('XC_ORDER_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_ORDER_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_ORDER_DB_PORT', 3306),
            'database' => env('XC_ORDER_DB_DATABASE', 'forge'),
            'username' => env('XC_ORDER_DB_USERNAME', 'forge'),
            'password' => env('XC_ORDER_DB_PASSWORD', ''),
            'unix_socket' => env('XC_ORDER_DB_SOCKET', ''),
            'charset' => env('XC_ORDER_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_ORDER_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_ORDER_DB_PREFIX', ''),
            'strict' => env('XC_ORDER_DB_STRICT_MODE', false),
            'engine' => env('XC_ORDER_DB_ENGINE', null),
            'timezone' => env('XC_ORDER_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('XC_ORDER_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_xc_cleanout' => [//销参价格爬虫库
            'read' => [
                'host' => [env('XC_CLEANOUT_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_CLEANOUT_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_CLEANOUT_DB_PORT', 3306),
            'database' => env('XC_CLEANOUT_DB_DATABASE', 'forge'),
            'username' => env('XC_CLEANOUT_DB_USERNAME', 'forge'),
            'password' => env('XC_CLEANOUT_DB_PASSWORD', ''),
            'unix_socket' => env('XC_CLEANOUT_DB_SOCKET', ''),
            'charset' => env('XC_CLEANOUT_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_CLEANOUT_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_CLEANOUT_DB_PREFIX', ''),
            'strict' => env('XC_CLEANOUT_DB_STRICT_MODE', false),
            'engine' => env('XC_CLEANOUT_DB_ENGINE', null),
            'timezone' => env('XC_CLEANOUT_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('XC_CLEANOUT_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_xc_single_product' => [
            'read' => [
                'host' => [env('XC_SINGLE_PRODUCT_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_SINGLE_PRODUCT_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_SINGLE_PRODUCT_DB_PORT', 3306),
            'database' => env('XC_SINGLE_PRODUCT_DB_DATABASE', 'forge'),
            'username' => env('XC_SINGLE_PRODUCT_DB_USERNAME', 'forge'),
            'password' => env('XC_SINGLE_PRODUCT_DB_PASSWORD', ''),
            'unix_socket' => env('XC_SINGLE_PRODUCT_DB_SOCKET', ''),
            'charset' => env('XC_SINGLE_PRODUCT_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_SINGLE_PRODUCT_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_SINGLE_PRODUCT_DB_PREFIX', ''),
            'strict' => env('XC_SINGLE_PRODUCT_DB_STRICT_MODE', false),
            'engine' => env('XC_SINGLE_PRODUCT_DB_ENGINE', null),
            'timezone' => env('XC_SINGLE_PRODUCT_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('XC_SINGLE_PRODUCT_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_xc_ptx_db' => [
            'read' => [
                'host' => [env('XC_PTX_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_PTX_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_PTX_DB_PORT', 3306),
            'database' => env('XC_PTX_DB_DATABASE', 'forge'),
            'username' => env('XC_PTX_DB_USERNAME', 'forge'),
            'password' => env('XC_PTX_DB_PASSWORD', ''),
            'unix_socket' => env('XC_PTX_DB_SOCKET', ''),
            'charset' => env('XC_PTX_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_PTX_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_PTX_DB_PREFIX', ''),
            'strict' => env('XC_PTX_DB_STRICT_MODE', false),
            'engine' => env('XC_PTX_DB_ENGINE', null),
            'timezone' => env('XC_PTX_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('XC_PTX_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_permission' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('DB_PORT', 3306),
            'database' => 'ptx_permission',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+08:00'),
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'statistical_analysis' => [
            'read' => [
                'host' => [env('LOG_DB_HOST_READ', env('DB_HOST_READ', '127.0.0.1'))],
            ],
            'write' => [
                'host' => [env('LOG_DB_HOST', env('DB_HOST', '127.0.0.1'))],
            ],
            'sticky' => true,
            'driver' => env('LOG_DB_DRIVER', env('DB_DRIVER', 'mysql')),
            'port' => env('LOG_DB_PORT', env('DB_PORT', 3306)),
            'database' => env('LOG_DB_DATABASE', 'ptx_statistical_analysis'),
            'username' => env('LOG_DB_USERNAME', env('DB_USERNAME', 'forge')),
            'password' => env('LOG_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('LOG_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('LOG_DB_CHARSET', env('DB_CHARSET', 'utf8mb4')),
            'collation' => env('LOG_DB_COLLATION', env('DB_COLLATION', 'utf8mb4_unicode_ci')),
            'prefix' => env('LOG_DB_PREFIX', env('DB_PREFIX', '')),
            'strict' => env('LOG_DB_STRICT_MODE', env('DB_STRICT_MODE', false)),
            'engine' => env('LOG_DB_ENGINE', env('DB_ENGINE', null)),
            'timezone' => env('LOG_DB_TIMEZONE', env('DB_TIMEZONE', '+00:00')),
            'connection_pool_provider' => env('LOG_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'redman' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'ptx_redman',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'survey' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'ptx_survey',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'db_order' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            //'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => 'ptx_order',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'sync' => [
            'read' => [
                'host' => [env('SYNC_DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('SYNC_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => env('SYNC_DB_DRIVER', 'mysql'),
            'port' => env('SYNC_DB_PORT', 3306),
            'database' => env('SYNC_DB_DATABASE', 'ptx_sync'),
            'username' => env('SYNC_DB_USERNAME', 'forge'),
            'password' => env('SYNC_DB_PASSWORD', ''),
            'unix_socket' => env('SYNC_DB_SOCKET', ''),
            'charset' => env('SYNC_DB_CHARSET', 'utf8mb4'),
            'collation' => env('SYNC_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('SYNC_DB_PREFIX', env('DB_PREFIX', '')),
            'strict' => env('SYNC_DB_STRICT_MODE', false),
            'engine' => env('SYNC_DB_ENGINE', null),
            'timezone' => env('SYNC_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('SYNC_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => env('DB_PREFIX', ''),
            'schema' => env('DB_SCHEMA', 'public'),
            'sslmode' => env('DB_SSL_MODE', 'prefer'),
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 1433),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => env('DB_PREFIX', ''),
        ],
        'online_store' => [
            'read' => [
                'host' => [env('DB_HOST_READ', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('DB_PORT', 3306),
            'database' => 'ptx_online_store',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('DB_CONNECTION_POOL_PROVIDER', ''),
        ],
        'ptx_single_product' => [
            'read' => [
                'host' => [env('PTX_SINGLE_PRODUCT_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('PTX_SINGLE_PRODUCT_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('PTX_SINGLE_PRODUCT_DB_PORT', 3306),
            'database' => env('PTX_SINGLE_PRODUCT_DB_DATABASE', 'forge'),
            'username' => env('PTX_SINGLE_PRODUCT_DB_USERNAME', 'forge'),
            'password' => env('PTX_SINGLE_PRODUCT_DB_PASSWORD', ''),
            'unix_socket' => env('PTX_SINGLE_PRODUCT_DB_SOCKET', ''),
            'charset' => env('PTX_SINGLE_PRODUCT_DB_CHARSET', 'utf8mb4'),
            'collation' => env('PTX_SINGLE_PRODUCT_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('PTX_SINGLE_PRODUCT_DB_PREFIX', ''),
            'strict' => env('PTX_SINGLE_PRODUCT_DB_STRICT_MODE', false),
            'engine' => env('PTX_SINGLE_PRODUCT_DB_ENGINE', null),
            'timezone' => env('PTX_SINGLE_PRODUCT_DB_TIMEZONE', '+00:00'),
            'connection_pool_provider' => env('PTX_SINGLE_PRODUCT_DB_CONNECTION_POOL_PROVIDER', ''),
        ],
    ],
    /*
      |--------------------------------------------------------------------------
      | Migration Repository Table
      |--------------------------------------------------------------------------
      |
      | This table keeps track of all the migrations that have already run for
      | your application. Using this information, we can determine which of
      | the migrations on disk haven't actually been run in the database.
      |
     */
    'migrations' => 'migrations',
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
    'redis' => [
        //'client' => 'predis',
        'client' => env('REDIS_CLIENT', 'predis'), //'phpredis',//使用 PhpRedis PHP extension
        'cluster' => env('REDIS_CLUSTER', false),
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            //'read_write_timeout' => 60,//读写超时时间
        ],
        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],
        env('QUEUE_REDIS_CONNECTION', 'queue') => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_QUEUE_DB', 2),
            //'read_write_timeout' => 60,//读写超时时间
        ],
        'session' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_SESSION_DB', 3),
        ],
        env('MAIL_QUEUE_DRIVER_CONNECTION', env('QUEUE_REDIS_CONNECTION', 'queue')) => [//用于发送email的消息队列
            'host' => env('MAIL_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('MAIL_REDIS_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('MAIL_REDIS_PORT', env('REDIS_PORT', 6379)),
            'database' => env('MAIL_REDIS_QUEUE_DB', env('REDIS_QUEUE_DB', 2)),
            //'read_write_timeout' => 60,//读写超时时间
        ],
        env('LOG_QUEUE_DRIVER_CONNECTION', env('QUEUE_REDIS_CONNECTION', 'queue')) => [//用于处理log的消息队列
            'host' => env('LOG_REDIS_HOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('LOG_REDIS_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('LOG_REDIS_PORT', env('REDIS_PORT', 6379)),
            'database' => env('LOG_REDIS_QUEUE_DB', env('REDIS_QUEUE_DB', 2)),
            //'read_write_timeout' => 60,//读写超时时间
        ],
    ],
];
