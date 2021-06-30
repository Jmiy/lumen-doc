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

    $router->group(['middleware' => ['activity']], function () use ($router) {

        $router->group(['middleware' => ['auth']], function () use ($router) {

            //获取活动统计次数接口
            $router->post('activity/getNums', [
                'as' => 'activity_getNums',
                'uses' => 'ActivityController@getNums',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'source' => 60023, //获取抽奖次数
            ]);

            //活动关注社媒
            $router->post('activity/follow', [
                'as' => 'activity_follow',
                'uses' => 'ActivityController@follow',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'report' => true, //记录上报数据
            ]);

            //参与活动
            $router->post('activity/handle', [
                'as' => 'activity_handle',
                'uses' => 'ActivityController@handle',
                'validator' => [
                    'messages' => [
                        'size' => 'Oops, only 3 numbers are needed.',
                    ],
                    'rules' => [
                        'act_id' => 'bail|required',
                        'guess_num' => 'required|size:3',
                    ],
                ],
            ]);

            //分享社媒
            $router->post('act/share', [
                'as' => 'act_share',
                'uses' => 'ActivityController@share',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'report' => true, //记录上报数据
            ]);

            //参与活动
            $router->post('act/handle', [
                'as' => 'act_handle',
                'uses' => 'ActivityController@handle',
                'validator' => [
                    'messages' => [
                    ],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
            ]);

        });

    });
});
