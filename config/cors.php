<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
   
    'supportsCredentials' => env('CORS_ALLOW_CREDENTIAILS', false), // set "Access-Control-Allow-Credentials" 👉 string "false" or "true". 默认情况下，cookie是不包括在CORS请求中的，如果服务器要接受cookie设置为：true  如果不需要cookie设置为：false
    'allowedOrigins' => ['*'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => env('CORS_ACCESS_CONTROL_MAX_AGE', 0),

];
