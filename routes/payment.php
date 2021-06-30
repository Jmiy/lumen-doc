<?php

$router->group(['namespace' => 'Payment', 'prefix' => 'api/payment', 'middleware' => ['cors', 'request_init']], function() use ($router) {//'cors', 'request_init'
    $router->get('{driver}/pay', [
        'as' => 'payment_pay',
        'uses' => 'PaymentController@pay',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);

    $router->get('{driver}/callback', [
        'as' => 'payment_callback',
        'uses' => 'PaymentController@callback',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);
    
    $router->post('{driver}/notify', [
        'as' => 'payment_notify',
        'uses' => 'PaymentController@notify',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);
    
    $router->get('{driver}/refund', [
        'as' => 'payment_refund',
        'uses' => 'PaymentController@refund',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => '',
            ],
        ],
    ]);
});
