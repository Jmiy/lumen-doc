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
        ],
        'db_xc' => [
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
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('XC_DB_ENGINE', null),
            'timezone' => env('XC_DB_TIMEZONE', '+00:00'),
        ],
        'db_xc_order' => [//销参清洗库
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
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('XC_ORDER_DB_ENGINE', null),
            'timezone' => env('XC_ORDER_DB_TIMEZONE', '+00:00'),
        ],
        'db_xc_cleanout' => [//销参清洗库
            'read' => [
                'host' => [env('XC_ORDER_DB_HOST', '127.0.0.1')],
            ],
            'write' => [
                'host' => [env('XC_ORDER_DB_HOST', '127.0.0.1')],
            ],
            'sticky' => true,
            'driver' => 'mysql',
            'port' => env('XC_ORDER_DB_PORT', 3306),
            'database' => 'cleanout_xc',
            'username' => env('XC_ORDER_DB_USERNAME', 'forge'),
            'password' => env('XC_ORDER_DB_PASSWORD', ''),
            'unix_socket' => env('XC_ORDER_DB_SOCKET', ''),
            'charset' => env('XC_ORDER_DB_CHARSET', 'utf8mb4'),
            'collation' => env('XC_ORDER_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('XC_ORDER_DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('XC_ORDER_DB_ENGINE', null),
            'timezone' => env('XC_ORDER_DB_TIMEZONE', '+00:00'),
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
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('XC_SINGLE_PRODUCT_DB_ENGINE', null),
            'timezone' => env('XC_SINGLE_PRODUCT_DB_TIMEZONE', '+00:00'),
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
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('XC_PTX_DB_ENGINE', null),
            'timezone' => env('XC_PTX_DB_TIMEZONE', '+00:00'),
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
        ],
        'statistical_analysis' => [
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
            'database' => 'ptx_statistical_analysis',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
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
    'redis' => require base_path('config/swoole/database.php'),
];
