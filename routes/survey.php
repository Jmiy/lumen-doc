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

$router->group(['namespace' => 'Survey\Api', 'prefix' => 'api/shop', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    // 使用 "App\Http\Controllers\Survey\Api" 命名空间... 'carbon', 
    //获取调查问券详情
    $router->post('survey/info', [
        'as' => 'survey_info',
        'uses' => 'SurveyController@info',
        'validator' => [
            'messages' => [],
            'rules' => [
                'id' => 'required',
                'account' => '',
            ],
        ],
        'source' => 100000, //获取邀请者的会员信息
    ]);

    //提交调查问券
    $router->post('survey/handle', [
        'as' => 'survey_handle',
        'uses' => 'SurveyController@handle',
        'validator' => [
            'messages' => [],
            'rules' => [
                'id' => 'required',
            ],
        ],
        'source' => 100001, //提交调查问券
    ]);
});
