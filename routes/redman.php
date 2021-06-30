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

$router->group(['namespace' => 'Redman', 'prefix' => 'api/redman', 'middleware' => ['cors', 'request_init']], function() use ($router) {
    //使用 "App\Http\Controllers\Statistical" 命名空间... 'carbon', 
    //添加访问日志
    $router->post('influencer/formAdd', [
        'as' => 'redman_influencer_formAdd',
        'uses' => '\App\Http\Controllers\Redman\Api\InfluencerController@formAdd',
    ]);
});
