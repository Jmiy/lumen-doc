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

$router->group(['namespace' => 'Api', 'prefix' => 'api/shop', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {

    $router->group(['middleware' => ['auth']], function() use ($router) {

        $router->group(['middleware' => ['activity']], function () use ($router) {

            //社媒登陆
            $router->post('social/media/login', [
                'as' => 'socialMedia_createCustomer',
                'uses' => 'SocialMediaLoginController@createCustomer',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'store_id' => 'required',
                        'account' => 'required',
                        'login_source' => 'required',
                    ],
                ],
                'source' => 10000, //登录注册
                'account_action' => 'login', //用户行为登录
            ]);

            //社媒登陆后，密码修改
            $router->post('social/media/passwordModify', [
                'as' => 'socialMedia_passwordModify',
                'uses' => 'SocialMediaLoginController@passwordModify',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'store_id' => 'required',
                        'password' => 'required',
                        'account' => 'required',
                    ],
                ],
                'source' => 10100, //密码修改
            ]);

        });

    });

});
