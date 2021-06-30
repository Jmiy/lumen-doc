<?php

$router->group(['namespace' => 'Auth', 'prefix' => 'api/auth', 'middleware' => ['cors', 'request_init']], function() use ($router) {//'cors', 'request_init' , 'session', 'session'
    $router->group(['middleware' => ['public_validator']], function() use ($router) {
        //提交参与活动接口
        $router->post('login', [
            'as' => 'auth_login',
            'uses' => 'LoginController@login',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'password' => 'required',
                ],
            ],
            'source' => 10011,
            'report' => true,
        ]);
    });


    $router->get('socialite/{store_id:[0-9]+}/{driver}', [
        'as' => 'auth_socialite_login',
        'uses' => 'LoginController@redirectToProvider',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);

    $router->get('socialite/{store_id:[0-9]+}/{driver}/callback', [
        'as' => 'auth_login_callback',
        'uses' => 'LoginController@handleProviderCallback',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);
});
