<?php

return [
    'allow-credentials'  => env('CORS_ALLOW_CREDENTIAILS', false), // set "Access-Control-Allow-Credentials" 👉 string "false" or "true".
    'allow-headers'      => ['*'], // ex: Content-Type, Accept, X-Requested-With
    'expose-headers'     => [],
    'origins'            => ['*'], // ex: http://localhost
    'methods'            => ['*'], // ex: GET, POST, PUT, PATCH, DELETE
    'max-age'            => env('CORS_ACCESS_CONTROL_MAX_AGE', 0),//用来指定本次预检请求的有效期，在此期间，不用发出另一条预检请求。
];
