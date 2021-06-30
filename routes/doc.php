<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

$router->get('/health.json', function () use ($router) {
    return response()->json(['status'=>'UP']);;
});

$router->group(['prefix' => 'api/shop', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {//'carbon',
    $router->get('test[/{test_data}]', [//
        //'middleware' => 'auth',
        'as' => 'test',
        'uses' => 'DocController@test',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->post('test[/{test_data}]', [//
        //'middleware' => 'auth',
        'as' => 'test',
        'uses' => 'DocController@test',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('api', [
        'as' => 'test_api',
        'uses' => 'DocController@api',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('opcache', [
        'as' => 'test_opcache',
        'uses' => 'ExampleController@opcache',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('cache', [
        'as' => 'test_cache',
        'uses' => 'DocController@cache',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('auth', [
        //'middleware' => 'auth', //api认证
        'middleware' => 'auth:apiAdmin', //管理后台api认证
        'as' => 'test_auth',
        'uses' => 'DocController@auth',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('carbon', [
        'as' => 'test_carbon',
        'uses' => 'DocController@carbon',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('queue', [
        'as' => 'test_queue',
        'uses' => 'DocController@queue',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('clear', [
        'as' => 'test_clear',
        'uses' => 'ExampleController@clear',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('user', [
        'as' => 'test_user',
        'uses' => 'DocController@user',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('order', [
        'as' => 'test_order',
        'uses' => 'DocController@order',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);
    //test文件api测试
    $router->get('apiTest', [
        'as' => 'test_apiTest',
        'uses' => 'TestController@apiTest',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //geoip
    $router->get('geoip', [
        'as' => 'test_geoip',
        'uses' => 'DocController@geoip',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //delOrder
    $router->get('delOrder', [
        'as' => 'test_delOrder',
        'uses' => 'DocController@delOrder',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //excel
    $router->get('excel', [
        'as' => 'test_excel',
        'uses' => 'DocController@excel',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //email
    $router->get('email', [
        'as' => 'test_email',
        'uses' => 'DocController@email',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //storage
    $router->get('storage', [
        'as' => 'test_storage',
        'uses' => 'DocController@storage',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //csv
    $router->get('csv', [
        'as' => 'test_csv',
        'uses' => 'DocController@csv',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    //csvfile
    $router->get('csvfile', [
        'as' => 'test_csvfile',
        'uses' => 'DocController@csvFile',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('hotfix', [
        'as' => 'test_hotfix',
        'uses' => 'ExampleController@hotfix',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('pagePublish', [
        'as' => 'test_pagePublish',
        'uses' => 'ExampleController@pagePublish',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);

    $router->get('tempTest', [
        'as' => 'test_tempTest',
        'uses' => 'ExampleController@tempTest',
        'validator' => [
            'type' => 'test',
            'messages' => [],
            'rules' => [],
        ]
    ]);
});
