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

$router->group(['namespace' => 'Common', 'prefix' => 'api/common', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    //返回国家列表
    $router->post('country/list', [
        'as' => 'common_country_list',
        'uses' => 'CountryController@index',
        'source' => 10015, //国家信息
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
            ],
        ],
    ]);

    //返回单个国家信息
    $router->post('country/oneCountry', [
        'as' => 'common_country_oneCountry',
        'uses' => 'CountryController@oneCountry',
        'source' => 10016, //国家信息
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
            ],
        ],
    ]);
    //返回州列表
    $router->post('country/region', [
        'as' => 'common_country_region',
        'uses' => 'CountryController@region',
        'source' => 10017, //国家信息
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
            ],
        ],
    ]);

    //获取指定下拉数据
    $router->post('dict/select', [
        'as' => 'common_dict_select',
        'uses' => 'DictController@select',
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
                'type' => 'bail|required',
            ],
        ],
    ]);

    //导出异常钉钉预警
    $router->post('dingexport/alert', [
        'as' => 'common_dingexport_alert',
        'uses' => 'DingExportController@alert',
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
            ],
        ],
    ]);

    //获取商城指定下拉数据
    $router->post('dict/storeDictSelect', [
        'as' => 'common_dict_storeDictSelect',
        'uses' => 'DictController@storeDictSelect',
        'validator' => [
            'rules' => [
                'account' => '',
                'type' => 'bail|required',
            ],
        ],
    ]);

    //获取指定下拉数据
    $router->post('upload', [
        'as' => 'common_api_public_upload',
        'uses' => 'Api\PublicController@upload',
        'validator' => [
            'rules' => [
                'account' => '',
            ],
        ],
    ]);
});
