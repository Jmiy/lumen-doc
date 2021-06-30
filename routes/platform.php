<?php

$router->group(['namespace' => 'Platform\Notice', 'prefix' => 'api/notice', 'middleware' => ['cors', 'request_init', 'platform']], function() use ($router) {

    $router->post('{store_id:[0-9]+}/{platform}/order/create[/{app_env}]', [
        'as' => 'platform_order_notice_create',
        'uses' => 'OrderController@noticeCreate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'create',
    ]);

    //订单发货
    $router->post('{store_id:[0-9]+}/{platform}/order/delivery[/{app_env}]', [
        'as' => 'platform_order_notice_delivery',
        'uses' => 'OrderController@noticeDelivery',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'delivery',
    ]);

    //订单付款
    $router->post('{store_id:[0-9]+}/{platform}/order/payment[/{app_env}]', [
        'as' => 'platform_order_notice_payment',
        'uses' => 'OrderController@noticePayment',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'payment',
    ]);

    //订单删除
    $router->post('{store_id:[0-9]+}/{platform}/order/delete[/{app_env}]', [
        'as' => 'platform_order_notice_delete',
        'uses' => 'OrderController@noticeDelete',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'delete',
    ]);

    //订单取消
    $router->post('{store_id:[0-9]+}/{platform}/order/cancel[/{app_env}]', [
        'as' => 'platform_order_notice_cancel',
        'uses' => 'OrderController@noticeCancel',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'cancel',
    ]);

    //订单更新
    $router->post('{store_id:[0-9]+}/{platform}/order/update[/{app_env}]', [
        'as' => 'platform_order_notice_update',
        'uses' => 'OrderController@noticeUpdate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'order',
        'business_subtype' => 'update',
    ]);

    //发货创建
    $router->post('{store_id:[0-9]+}/{platform}/fulfillment/create[/{app_env}]', [
        'as' => 'platform_fulfillment_notice_create',
        'uses' => 'FulfillmentController@noticeCreate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'fulfillment',
        'business_subtype' => 'create',
    ]);

    //物流更新
    $router->post('{store_id:[0-9]+}/{platform}/fulfillment/update[/{app_env}]', [
        'as' => 'platform_fulfillment_notice_update',
        'uses' => 'FulfillmentController@noticeUpdate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'fulfillment',
        'business_subtype' => 'update',
    ]);

    //退款创建
    $router->post('{store_id:[0-9]+}/{platform}/refund/create[/{app_env}]', [
        'as' => 'platform_refund_notice_create',
        'uses' => 'RefundController@noticeCreate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'refund',
        'business_subtype' => 'create',
    ]);

    //交易创建
    $router->post('{store_id:[0-9]+}/{platform}/transaction/create[/{app_env}]', [
        'as' => 'platform_transaction_notice_create',
        'uses' => 'TransactionController@noticeCreate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'transaction',
        'business_subtype' => 'create',
    ]);

    //产品创建
    $router->post('{store_id:[0-9]+}/{platform}/product/create[/{app_env}]', [
        'as' => 'platform_product_notice_create',
        'uses' => 'ProductController@noticeCreate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'product',
        'business_subtype' => 'create',
    ]);

    //产品删除
    $router->post('{store_id:[0-9]+}/{platform}/product/delete[/{app_env}]', [
        'as' => 'platform_product_notice_delete',
        'uses' => 'ProductController@noticeDelete',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'product',
        'business_subtype' => 'delete',
    ]);

    //产品更新
    $router->post('{store_id:[0-9]+}/{platform}/product/update[/{app_env}]', [
        'as' => 'platform_product_notice_update',
        'uses' => 'ProductController@noticeUpdate',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
        'business_type' => 'product',
        'business_subtype' => 'update',
    ]);
});

$router->group(['namespace' => 'Platform\Client', 'prefix' => 'api/client', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    $router->group(['middleware' => ['auth']], function() use ($router) {
        //订单列表
        $router->post('order/list', [
            'as' => 'client_order_list',
            'uses' => 'OrderController@index',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'platform' => 'required',
                ],
            ],
            'source' => 30002, //订单列表
        ]);

        //订单详情
        $router->post('order/details', [
            'as' => 'client_order_details',
            'uses' => 'OrderController@details',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'id' => 'required',
                    'platform' => 'required',
                ],
            ],
            'source' => 30017, //订单详情
        ]);

        //订单创建
        $router->post('order/create', [
            'as' => 'client_order_create',
            'uses' => 'OrderController@create',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'platform' => 'required',
                ],
            ],
            'source' => 30027, //订单创建
        ]);

        //订单地址
        $router->post('order/address', [
            'as' => 'client_order_address',
            'uses' => 'OrderController@address',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'platform' => 'required',
                    'order_unique_id' => 'required',
                ],
            ],
            'source' => 30027, //订单地址
        ]);
    });
});
