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

$router->group(['namespace' => 'Redman_Admin', 'prefix' => 'api/redman_admin', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {//
    // 使用 "App\Http\Controllers\Admin" 命名空间... 'carbon', 
    $router->post('user/login', [
        'as' => 'redman_admin_user_login',
        'uses' => '\App\Http\Controllers\Permission\UserController@login',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
                'store_id' => 'required',
                'username' => 'required',
                'password' => 'required',
            ],
        ],
    ]);

    $router->post('user/logout', [
        'as' => 'redman_admin_user_logout',
        'uses' => '\App\Http\Controllers\Permission\UserController@logout',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
            ],
        ],
    ]);

    //获取商城下拉数据用于登录选择商城
    $router->post('store/getStore', [
        'as' => 'redman_admin_store_getStore',
        'uses' => '\App\Http\Controllers\Admin\StoreController@getStore',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
            ],
        ],
    ]);

    //获取系统字典数据
    $router->post('store/actionlist', [
        'as' => 'redman_admin_store_actionlist',
        'uses' => '\App\Http\Controllers\Admin\StoreController@actionList',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
//                'store_id' => 'required',
//                'operator' => 'required',
//                'token' => 'required',
            ],
        ],
    ]);
    $router->group(['middleware' => ['auth:apiAdmin']], function() use ($router) {
        //红人系统申请表格列表
        $router->post('influencer/List', [
            'as' => 'redman_admin_influencer_List',
            'uses' => '\App\Http\Controllers\Redman\Admin\InfluencerController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);
        //红人系统申请表格列表导出
        $router->post('influencer/export', [
            'as' => 'redman_admin_influencer_export',
            'uses' => '\App\Http\Controllers\Redman\Admin\InfluencerController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);
    });
});
