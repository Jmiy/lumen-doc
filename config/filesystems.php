<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'cache' => [
                'store' => 'redis',
                'expire' => 600,
                'prefix' => 'cache-prefix',
            ],
        ],
        
        'app_root' => [
            'driver' => 'local',
            'root'   => storage_path(env('UPLOAD_PATH')),
        ],
        
        'app_resources' => [
            'driver' => 'local',
            'root'   => resource_path(),
        ],
        
        'ftp' => [
            'driver' => 'ftp',
            'host' => 'ftp.example.com',
            'username' => 'your-username',
            'password' => 'your-password',
        // 可选的 FTP 配置项...
        // 'port'     => 21,
        // 'root'     => '',
        // 'passive'  => true,
        // 'ssl'      => true,
        // 'timeout'  => 30,
        ],
        
        'sftp' => [
            'driver' => 'sftp',
            'host' => 'example.com',
            'username' => 'your-username',
            'password' => 'your-password',
        // 基于 SSH 密钥的身份验证设置...
        // 'privateKey' => '/path/to/privateKey',
        // 'password' => 'encryption-password',
        // 可选的 SFTP 配置...
        // 'port' => 22,
        // 'root' => '',
        // 'timeout' => 30,
        ],
        
        'rackspace' => [
            'driver' => 'rackspace',
            'username' => 'your-username',
            'key' => 'your-key',
            'container' => 'your-container',
            'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region' => 'IAD',
            'url_type' => 'publicURL',
        ],
        
        'front' => [
            'driver' => 'local',
            'root' => resource_path(env('BRAND_FRONT_HTML')),
        ],
    ],

];
