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

$router->group(['namespace' => 'Admin', 'prefix' => 'api/admin', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    // 使用 "App\Http\Controllers\Admin" 命名空间... 'carbon',
    $router->post('user/login', [
        'as' => 'admin_user_login',
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
        'as' => 'admin_user_logout',
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
        'as' => 'admin_store_getStore',
        'uses' => 'StoreController@getStore',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
            ],
        ],
    ]);

    //获取系统字典数据
    $router->post('store/actionlist', [
        'as' => 'admin_store_actionlist',
        'uses' => 'StoreController@actionList',
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

    //获取商城活动名称列表
    $router->post('activity/list', [
        'as' => 'admin_activity_list',
        'uses' => 'ActivityController@index',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
                'store_id' => 'required',
            ],
        ],
    ]);

    $router->group(['middleware' => ['auth:apiAdmin']], function() use ($router) {
        //用户基本信息
        $router->post('user/info', [
            'as' => 'admin_user_info',
            'uses' => '\App\Http\Controllers\Permission\UserController@info',
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

        //会员列表
        $router->post('customer/list', [
            'as' => 'admin_customer_list',
            'uses' => 'CustomerController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //导出注册会员信息
        $router->post('customer/export', [
            'as' => 'admin_customer_export',
            'uses' => 'CustomerController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //同步会员信息
        $router->post('customer/sync', [
            'as' => 'admin_customer_sync',
            'uses' => 'CustomerController@sync',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'start_time' => 'required',
                    'end_time' => 'required',
                ],
            ],
        ]);

        //会员信息
        $router->post('customer/info', [
            'as' => 'admin_customer_info',
            'uses' => 'CustomerController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //编辑会员信息
        $router->post('customer/edit', [
            'as' => 'admin_customer_edit',
            'uses' => 'CustomerController@edit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'customer_id' => 'required',
                ],
            ],
        ]);

        //会员详情列表
        $router->post('customer/detailsList', [
            'as' => 'admin_customer_detailsList',
            'uses' => 'CustomerController@detailsList',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //导出会员详情列表
        $router->post('customer/exportDetailsList', [
            'as' => 'admin_customer_exportDetailsList',
            'uses' => 'CustomerController@exportDetailsList',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //删除会员
        $router->post('customer/forceDelete', [
            'as' => 'admin_customer_forceDelete',
            'uses' => 'CustomerController@forceDelete',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'account' => 'required',
                ],
            ],
        ]);

        //积分编辑
        $router->post('credit/edit', [
            'as' => 'admin_credit_edit',
            'uses' => 'CreditController@edit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'action' => 'required',
                    'customer_id' => 'required',
                    'value' => 'required',
                    'add_type' => 'required',
                    'remark' => 'required',
                ],
            ],
        ]);

        //积分列表
        $router->post('credit/list', [
            'as' => 'admin_credit_list',
            'uses' => 'CreditController@index',
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

        //积分列表导出
        $router->post('credit/export', [
            'as' => 'admin_credit_export',
            'uses' => 'CreditController@export',
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

        //经验编辑
        $router->post('exp/edit', [
            'as' => 'admin_exp_edit',
            'uses' => 'ExpController@edit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'action' => 'required',
                    'customer_id' => 'required',
                    'value' => 'required',
                    'add_type' => 'required',
                    'remark' => 'required',
                ],
            ],
        ]);

        //经验列表
        $router->post('exp/list', [
            'as' => 'admin_exp_list',
            'uses' => 'ExpController@index',
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

        //经验列表导出
        $router->post('exp/export', [
            'as' => 'admin_exp_export',
            'uses' => 'ExpController@export',
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

        //订单列表
        $router->post('order/list', [
            'as' => 'admin_order_list',
            'uses' => 'OrderWarrantyController@index',
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

        //订单列表导出
        $router->post('order/export', [
            'as' => 'admin_order_export',
            'uses' => 'OrderWarrantyController@export',
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

        //订单详情
        $router->post('order/info', [
            'as' => 'admin_order_info',
            'uses' => 'OrderWarrantyController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'orderno' => 'required',
                ],
            ],
        ]);

        //产品列表
        $router->post('product/list', [
            'as' => 'admin_product_list',
            'uses' => 'ProductController@index',
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

        //产品列表导出
        $router->post('product/export', [
            'as' => 'admin_product_export',
            'uses' => 'ProductController@export',
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

        //同步产品
        $router->post('product/sync', [
            'as' => 'admin_product_sync',
            'uses' => 'ProductController@sync',
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

        //产品详情
        $router->post('product/info', [
            'as' => 'admin_product_info',
            'uses' => 'ProductController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'store_product_id' => 'required',
                ],
            ],
        ]);

        //产品添加
        $router->post('product/add', [
            'as' => 'admin_product_add',
            'uses' => 'ProductController@add',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'store_product_id' => 'required',
                ],
            ],
        ]);

        //产品添加或者编辑
        $router->post('product/addedit', [
            'as' => 'admin_product_addedit',
            'uses' => 'ProductController@addedit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'store_product_id' => 'required',
                ],
            ],
        ]);

        //产品编辑
        $router->post('product/edit', [
            'as' => 'admin_product_edit',
            'uses' => 'ProductController@edit',
            'noLog' => ['file'],
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //删除产品
        $router->post('product/delete', [
            'as' => 'admin_product_delete',
            'uses' => 'ProductController@delete',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //邮件列表
        $router->post('email/list', [
            'as' => 'admin_email_list',
            'uses' => 'EmailController@index',
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

        //邮件列表导出
        $router->post('email/export', [
            'as' => 'admin_order_export',
            'uses' => 'EmailController@export',
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

        //优惠券列表
        $router->post('coupon/list', [
            'as' => 'admin_coupon_list',
            'uses' => 'CouponController@index',
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

        //导出优惠券列表
        $router->post('coupon/export', [
            'as' => 'admin_coupon_export',
            'uses' => 'CouponController@export',
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

        //导入优惠券列表
        $router->post('coupon/import', [
            'as' => 'admin_coupon_import',
            'uses' => 'CouponController@import',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //兴趣列表
        $router->post('interest/list', [
            'as' => 'admin_interest_list',
            'uses' => 'InterestController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //兴趣列表导出
        $router->post('interest/export', [
            'as' => 'admin_interest_export',
            'uses' => 'InterestController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);
        //邀请列表
        $router->post('invite/list', [
            'as' => 'admin_invite_list',
            'uses' => 'InviteController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //邀请列表导出
        $router->post('invite/export', [
            'as' => 'admin_invite_export',
            'uses' => 'InviteController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //邀请关系列表编辑
        $router->post('invite/edit', [
            'as' => 'admin_invite_edit',
            'uses' => 'InviteController@edit',
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

        //分享列表
        $router->post('share/list', [
            'as' => 'admin_share_list',
            'uses' => 'ShareController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //分享列表导出
        $router->post('share/export', [
            'as' => 'admin_share_export',
            'uses' => 'ShareController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //审核分享
        $router->post('share/audit', [
            'as' => 'admin_share_audit',
            'uses' => 'ShareController@audit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                    'audit_status' => 'required',
                    'remarks' => 'required',
//                'value' => 'required',
//                'add_type' => 'required',
//                'action' => 'required',
                ],
            ],
        ]);

        //订单评论列表
        $router->post('order/reviewList', [
            'as' => 'admin_order_reviewList',
            'uses' => 'OrderWarrantyController@reviewList',
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

        //审核评论链接
        $router->post('order/reviewCheck', [
            'as' => 'admin_order_reviewCheck',
            'uses' => 'OrderWarrantyController@reviewCheck',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                    'review_status' => 'required',
                    'review_remark' => 'required',
//                'value' => 'required',
//                'add_type' => 'required',
//                'action' => 'required',
                ],
            ],
        ]);

        //产品申请列表
        $router->post('activity/apply/list', [
            'as' => 'admin_activity_apply_list',
            'uses' => 'ActivityApplyController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                    "account" => '',
//                    "sku" => '',
//                    "country" => '',
//                    "audit_status" => '',
//                    "start_at" => '',
//                    "end_at" => '',
                ],
            ],
        ]);

        //产品申请列表导出
        $router->post('activity/apply/export', [
            'as' => 'admin_activity_apply_export',
            'uses' => 'ActivityApplyController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                    "account" => '',
//                    "sku" => '',
//                    "country" => '',
//                    "audit_status" => '',
//                    "start_at" => '',
//                    "end_at" => '',
                ],
            ],
        ]);

        //产品申请审核
        $router->post('activity/apply/audit', [
            'as' => 'admin_activity_apply_audit',
            'uses' => 'ActivityApplyController@audit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'audit_status' => 'required',
                    'reviewer' => 'required',
                //"remarks" => 'required',
                ],
            ],
        ]);

        //申请资料详情
        $router->post('activity/applyInfo/info', [
            'as' => 'admin_activity_applyInfo_info',
            'uses' => 'ActivityApplyInfoController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'act_id' => 'required',
                    'customer_id' => 'required',
                ],
            ],
        ]);

        //评论审核列表导出
        $router->post('order/reviewExport', [
            'as' => 'admin_order_reviewExport',
            'uses' => 'OrderWarrantyController@reviewExport',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
//                'account' => 'required',
//                'country' => 'required',
//                'created_at' => 'required',
                ],
            ],
        ]);

        //deal VT优惠券列表
        $router->post('coupon/dealList', [
            'as' => 'admin_coupon_dealList',
            'uses' => 'CouponController@dealList',
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

        //deal VT导入优惠券列表
        $router->post('coupon/importDeal', [
            'as' => 'admin_coupon_importDeal',
            'uses' => 'CouponController@importDeal',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //deal VT活动产品列表
        $router->post('activity/product/list', [
            'as' => 'admin_activity_product_list',
            'uses' => 'ActivityProductController@index',
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

        //deal VT活动产品列表编辑
        $router->post('activity/product/edit', [
            'as' => 'admin_activity_product_edit',
            'uses' => 'ActivityProductController@edit',
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

        //deal VT活动产品列表操作
        $router->post('activity/product/operate', [
            'as' => 'admin_activity_product_operate',
            'uses' => 'ActivityProductController@operate',
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

        //deal VT活动产品列表导出
        $router->post('activity/product/export', [
            'as' => 'admin_activity_product_export',
            'uses' => 'ActivityProductController@export',
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

        //deal VT活动产品列表导入
        $router->post('activity/product/import', [
            'as' => 'admin_activity_product_import',
            'uses' => 'ActivityProductController@import',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //活动抽奖产品列表
//        $router->post('activity/prize/list', [
//            'as' => 'admin_activity_prize_list',
//            'uses' => 'ActivityPrizeController@index',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //活动抽奖产品编辑
//        $router->post('activity/prize/edit', [
//            'as' => 'admin_activity_prize_edit',
//            'uses' => 'ActivityPrizeController@edit',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //活动抽奖产品导出
//        $router->post('activity/prize/export', [
//            'as' => 'admin_activity_prize_export',
//            'uses' => 'ActivityPrizeController@export',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //抽奖产品导入
//        $router->post('activity/prize/import', [
//            'as' => 'admin_activity_prize_import',
//            'uses' => 'ActivityPrizeController@import',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//            'noLog' => ['file'],
//        ]);
//
//        //活动抽奖产品item添加
//        $router->post('activity/prize/itemimport', [
//            'as' => 'admin_activity_prize_itemimport',
//            'uses' => 'ActivityPrizeController@itemimport',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //活动抽奖产品item列表
//        $router->post('activity/prize/itemList', [
//            'as' => 'admin_activity_prize_itemList',
//            'uses' => 'ActivityPrizeController@itemList',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //活动抽奖产品item列表编辑
//        $router->post('activity/prize/itemEdit', [
//            'as' => 'admin_activity_prize_itemEdit',
//            'uses' => 'ActivityPrizeController@itemEdit',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//        ]);
//
//        //活动抽奖产品item导入
//        $router->post('activity/prize/importItem', [
//            'as' => 'admin_activity_prize_importItem',
//            'uses' => 'ActivityPrizeController@importItem',
//            'validator' => [
//                'type' => 'admin',
//                'messages' => [],
//                'rules' => [
//                    'store_id' => 'required',
//                    'operator' => 'required',
//                    'token' => 'required',
//                ],
//            ],
//            'noLog' => ['file'],
//        ]);
//
        //活动抽奖中奖列表
        $router->post('activity/winning/list', [
            'as' => 'admin_activity_winning_list',
            'uses' => 'ActivityWinningController@index',
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
        //活动抽奖中奖列表导出
        $router->post('activity/winning/export', [
            'as' => 'admin_activity_winning_export',
            'uses' => 'ActivityWinningController@export',
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

        //活动抽奖中奖实物收货地址查询
        $router->post('activity/winning/showAddress', [
            'as' => 'admin_activity_winning_showAddress',
            'uses' => 'ActivityWinningController@showAddress',
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
        //活动投票产品列表
        $router->post('vote/list', [
            'as' => 'admin_vote_list',
            'uses' => 'VoteController@index',
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
        //订阅列表
        $router->post('pub/list', [
            'as' => 'admin_pub_list',
            'uses' => 'PubController@index',
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

        //订阅列表导出
        $router->post('pub/export', [
            'as' => 'admin_pub_export',
            'uses' => 'PubController@export',
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
        //活动投票产品列表编辑
        $router->post('vote/edit', [
            'as' => 'admin_vote_edit',
            'uses' => 'VoteController@edit',
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
        //活动投票产品列表导出
        $router->post('vote/export', [
            'as' => 'admin_vote_export',
            'uses' => 'VoteController@export',
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

        //活动通用产品导入
        $router->post('activity/product/importUniversal', [
            'as' => 'admin_activity_product_importUniversal',
            'uses' => 'ActivityProductController@importUniversal',
            'noLog' => ['file'],
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

        //活动产品列表删除
        $router->post('activity/product/delete', [
            'as' => 'admin_activity_product_delete',
            'uses' => 'ActivityProductController@delete',
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

        //活动列表导出
        $router->post('activity/export', [
            'as' => 'admin_activity_export',
            'uses' => 'ActivityController@export',
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

        //活动列表下拉选择
        $router->post('activity/select', [
            'as' => 'admin_activity_select',
            'uses' => 'ActivityController@select',
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

        //活动添加
        $router->post('activity/insert', [
            'as' => 'admin_activity_insert',
            'uses' => 'ActivityController@insert',
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

        //活动编辑
        $router->post('activity/edit', [
            'as' => 'admin_activity_edit',
            'uses' => 'ActivityController@edit',
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

        //活动删除
        $router->post('activity/del', [
            'as' => 'admin_activity_del',
            'uses' => 'ActivityController@del',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //中奖名单新增接口
        $router->post('activity/addAwardUser', [
            'as' => 'admin_activity_addAwardUser',
            'uses' => 'ActivityController@addAwardUser',
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

        //活动产品导入
        $router->post('activity/product/importActProduct', [
            'as' => 'admin_activity_product_importActProduct',
            'uses' => 'ActivityProductController@importActProduct',
            'noLog' => ['file'],
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

        //活动产品列表
        $router->post('activity/product/actProductList', [
            'as' => 'admin_activity_product_actProductList',
            'uses' => 'ActivityProductController@actProductList',
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

        //导出活动产品列表
        $router->post('activity/product/exportActProducts', [
            'as' => 'admin_activity_product_exportActProducts',
            'uses' => 'ActivityProductController@exportActProducts',
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

        //导出活动产品列表
        $router->post('activity/product/delActProducts', [
            'as' => 'admin_activity_product_delActProducts',
            'uses' => 'ActivityProductController@delActProducts',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //获取活动产品item列表
        $router->post('activity/product/getActProductItems', [
            'as' => 'admin_activity_product_getActProductItems',
            'uses' => 'ActivityProductController@getActProductItems',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //编辑活动产品item
        $router->post('activity/product/editActProductItems', [
            'as' => 'admin_activity_product_editActProductItems',
            'uses' => 'ActivityProductController@editActProductItems',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //活动产品申请列表
        $router->post('activity/apply/actApplyList', [
            'as' => 'admin_activity_apply_actApplyList',
            'uses' => 'ActivityApplyController@actApplyList',
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

        //活动产品申请列表
        $router->post('activity/apply/exportActApplyList', [
            'as' => 'admin_activity_apply_exportActApplyList',
            'uses' => 'ActivityApplyController@exportActApplyList',
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

        //礼品添加
        $router->post('reward/add', [
            'as' => 'admin_reward_add',
            'uses' => 'RewardController@add',
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

        //礼品列表
        $router->post('reward/list', [
            'as' => 'admin_reward_list',
            'uses' => 'RewardController@index',
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

        //礼品编辑
        $router->post('reward/edit', [
            'as' => 'admin_reward_edit',
            'uses' => 'RewardController@edit',
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

        //礼品详情
        $router->post('reward/info', [
            'as' => 'admin_reward_info',
            'uses' => 'RewardController@info',
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

        //礼品导出
        $router->post('reward/export', [
            'as' => 'admin_reward_export',
            'uses' => 'RewardController@export',
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

        //订单索评列表
        $router->post('orderReview/list', [
            'as' => 'admin_order_review_list',
            'uses' => 'OrderReviewController@index',
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

        //订单索评导出
        $router->post('orderReview/export', [
            'as' => 'admin_order_review_export',
            'uses' => 'OrderReviewController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'route'=> 'required',
                ],
            ],
        ]);

        //订单索评审核
        $router->post('orderReview/audit', [
            'as' => 'admin_order_review_audit',
            'uses' => 'OrderReviewController@audit',
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

        //订单索评列表
        $router->post('orderReview/statList', [
            'as' => 'admin_order_review_statList',
            'uses' => 'OrderReviewController@statList',
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

        //订单绑定
        $router->post('order/bind', [
            'as' => 'admin_order_bind',
            'uses' => 'OrderWarrantyController@bind',
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

        //订单解除绑定
        $router->post('order/unbind', [
            'as' => 'admin_order_unbind',
            'uses' => 'OrderWarrantyController@unBind',
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

        //礼品添加
        $router->post('reward/add', [
            'as' => 'admin_reward_add',
            'uses' => 'RewardController@add',
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

        //礼品列表
        $router->post('reward/list', [
            'as' => 'admin_reward_list',
            'uses' => 'RewardController@index',
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

        //礼品编辑
        $router->post('reward/edit', [
            'as' => 'admin_reward_edit',
            'uses' => 'RewardController@edit',
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

        //礼品详情
        $router->post('reward/info', [
            'as' => 'admin_reward_info',
            'uses' => 'RewardController@info',
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

        //礼品导出
        $router->post('reward/export', [
            'as' => 'admin_reward_export',
            'uses' => 'RewardController@export',
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

        //订单索评列表
        $router->post('orderReview/list', [
            'as' => 'admin_order_review_list',
            'uses' => 'OrderReviewController@index',
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

        //订单索评导出
        $router->post('orderReview/export', [
            'as' => 'admin_order_review_export',
            'uses' => 'OrderReviewController@export',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'route'=> 'required',
                ],
            ],
        ]);

        //订单索评审核
        $router->post('orderReview/audit', [
            'as' => 'admin_order_review_audit',
            'uses' => 'OrderReviewController@audit',
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

        //订单索评列表
        $router->post('orderReview/statList', [
            'as' => 'admin_order_review_statList',
            'uses' => 'OrderReviewController@statList',
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

        //订单绑定
        $router->post('order/bind', [
            'as' => 'admin_order_bind',
            'uses' => 'OrderWarrantyController@bind',
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

        //订单解除绑定
        $router->post('order/unbind', [
            'as' => 'admin_order_unbind',
            'uses' => 'OrderWarrantyController@unBind',
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

        //删除活动产品items
        $router->post('activity/product/delActProductItems', [
            'as' => 'admin_activity_product_delActProductItems',
            'uses' => 'ActivityProductController@delActProductItems',
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

        //批量积分导入
        $router->post('credit/import', [
            'as' => 'admin_credit_import',
            'uses' => 'CreditController@import',
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

        //礼品状态编辑
        $router->post('reward/updateRewardStatus', [
            'as' => 'admin_reward_updateRewardStatus',
            'uses' => 'RewardController@updateRewardStatus',
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

        //礼品删除
        $router->post('reward/delete', [
            'as' => 'admin_reward_delete',
            'uses' => 'RewardController@delete',
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

        //订单列表
        $router->post('platform/order/list', [
            'as' => 'admin_platform_order_list',
            'uses' => 'OrderController@index',
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

        //订单导出
        $router->post('platform/order/export', [
            'as' => 'admin_platform_order_export',
            'uses' => 'OrderController@export',
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

        //导入活动中奖名单
        $router->post('activity/prize/customer/import', [
            'as' => 'admin_prize_customer_import',
            'uses' => 'ActivityPrizeCustomerController@import',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //导入活动中奖名单
        $router->get('activity/prize/customer/import', [
            'as' => 'admin_prize_customer_import',
            'uses' => 'ActivityPrizeCustomerController@import',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //统计
        $router->post('statistics/userNumsByField', [
            'as' => 'admin_statistics_userNumsByField',
            'uses' => 'StatisticsController@userNumsByField',
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

        //统计
        $router->post('statistics/userNumsByTime', [
            'as' => 'admin_statistics_userNumsByTime',
            'uses' => 'StatisticsController@userNumsByTime',
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

        //评测产品列表
        $router->post('activity/product/indexFreeTesting', [
            'as' => 'admin_activity_product_indexFreeTesting',
            'uses' => 'ActivityProductController@indexFreeTesting',
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

        //评测产品编辑
        $router->post('activity/product/editFreeTesting', [
            'as' => 'admin_activity_product_editFreeTesting',
            'uses' => 'ActivityProductController@editFreeTesting',
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

        //评测产品导入
        $router->post('activity/product/importFreeTestingProducts', [
            'as' => 'admin_activity_product_importFreeTestingProducts',
            'uses' => 'ActivityProductController@importFreeTestingProducts',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //评测产品导出
        $router->post('activity/product/exportFreeTestingProducts', [
            'as' => 'admin_activity_product_exportFreeTestingProducts',
            'uses' => 'ActivityProductController@exportFreeTestingProducts',
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

        //评测申请2.0列表
        $router->post('activity/apply/freeTestingList', [
            'as' => 'admin_activity_apply_freeTestingList',
            'uses' => 'ActivityApplyController@freeTestingList',
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

        //评测申请2.0列表导出
        $router->post('activity/apply/exportFreeTestingList', [
            'as' => 'admin_activity_apply_exportFreeTestingList',
            'uses' => 'ActivityApplyController@exportFreeTestingList',
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

        //评测申请2.0列表导出
        $router->post('activity/apply/freeTestingInfo', [
            'as' => 'admin_activity_apply_freeTestingInfo',
            'uses' => 'ActivityApplyController@freeTestingInfo',
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

        //编辑评测列表备注
        $router->post('activity/apply/editFreeTestingRemark', [
            'as' => 'admin_activity_apply_editFreeTestingRemark',
            'uses' => 'ActivityApplyController@editFreeTestingRemark',
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

        //测评标签展示接口
        $router->post('activity/apply/tagList', [
            'as' => 'admin_activity_apply_tagList',
            'uses' => 'ActivityApplyController@tagList',
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

        //测评用户批量设置标签接口
        $router->post('activity/apply/setUserTag', [
            'as' => 'admin_activity_apply_setUserTag',
            'uses' => 'ActivityApplyController@setUserTag',
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

        //测评用户批量设置标签接口
        $router->post('activity/apply/editTag', [
            'as' => 'admin_activity_apply_editTag',
            'uses' => 'ActivityApplyController@editTag',
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

        //测评用户编辑信息接口
        $router->post('activity/apply/editInfo', [
            'as' => 'admin_activity_apply_editInfo',
            'uses' => 'ActivityApplyController@editInfo',
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

        //类目下拉列表
        $router->post('category/select', [
            'as' => 'admin_category_select',
            'uses' => 'CategoryController@select',
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

        //按时间统计各个官网注册人数 环比 同比
        $router->post('statistics/userNumsByCompared', [
            'as' => 'admin_statistics_userNumsByCompared',
            'uses' => 'StatisticsController@userNumsByCompared',
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

        //延保统计
        $router->post('statistics/orderWarraytySta', [
            'as' => 'admin_statistics_orderWarraytySta',
            'uses' => 'StatisticsController@orderWarraytySta',
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

        //产品-文件类目添加
        $router->post('product/driven/categoryAdd', [
            'as' => 'admin_product_driven_categoryAdd',
            'uses' => 'ProductFileCategoryController@add',
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

        //产品-文件类目
        $router->post('product/driven/categoryList', [
            'as' => 'admin_product_driven_categoryList',
            'uses' => 'ProductFileCategoryController@index',
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

        //产品-文件添加
        $router->post('product/driven/fileAdd', [
            'as' => 'admin_product_driven_fileAdd',
            'uses' => 'ProductFileController@add',
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

        //产品-文件编辑
        $router->post('product/driven/fileEdit', [
            'as' => 'admin_product_driven_fileEdit',
            'uses' => 'ProductFileController@edit',
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

        //产品-文件删除
        $router->post('product/driven/fileDelete', [
            'as' => 'admin_product_driven_fileDelete',
            'uses' => 'ProductFileController@del',
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

        //产品-文件列表
        $router->post('product/driven/list', [
            'as' => 'admin_product_driven_list',
            'uses' => 'ProductFileController@index',
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

        //产品-文件导出
        $router->post('product/driven/export', [
            'as' => 'admin_product_driven_export',
            'uses' => 'ProductFileController@export',
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

        //产品-文件导入
        $router->post('product/driven/import', [
            'as' => 'admin_product_driven_import',
            'uses' => 'ProductFileController@import',
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

        //文件添加
        $router->post('file/add', [
            'as' => 'admin_file_add',
            'uses' => 'FileController@add',
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

        //文件删除
        $router->post('file/del', [
            'as' => 'admin_file_del',
            'uses' => 'FileController@del',
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

        //文件列表
        $router->post('file/index', [
            'as' => 'admin_file_index',
            'uses' => 'FileController@index',
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

        //文件列表导出
        $router->post('file/export', [
            'as' => 'admin_file_export',
            'uses' => 'FileController@export',
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

        //修改各品牌延保时间
        $router->post('editConfig/editInsurance', [
            'as' => 'edit_extended_insurance',
            'uses' => 'EditConfigController@editInsurance',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //修改各品牌邮件模板配置
        $router->post('editConfig/editEmailTemple', [
            'as' => 'edit_email_temple',
            'uses' => 'EditConfigController@editEmailTemple',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //新增各品牌邮件模板配置
        $router->post('editConfig/addEmailTemple', [
            'as' => 'add_email_temple',
            'uses' => 'EditConfigController@addEmailTemple',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //修改测评活动时间
        $router->post('editConfig/editActivityTime', [
            'as' => 'edit_activity_time',
            'uses' => 'EditConfigController@editActivityTime',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                ],
            ],
        ]);

        //订单索评备注
        $router->post('orderReview/auditRemarks', [
            'as' => 'admin_order_review_auditRemarks',
            'uses' => 'OrderReviewController@auditRemarks',
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

        //获取表头接口
        $router->post('adminConfig/getAdminConfig', [
            'as' => 'admin_user_getAdminConfig',
            'uses' => '\App\Http\Controllers\Permission\AdminConfigController@getAdminConfig',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'route' => 'required',
                ],
            ],
        ]);

        //自定义表头存储接口
        $router->post('adminConfig/userConfig', [
            'as' => 'admin_user_userConfig',
            'uses' => '\App\Http\Controllers\Permission\AdminConfigController@userConfig',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'route' => 'required',
                    'data' => 'required',
                ],
            ],
        ]);

        //测评订单备注编辑
        $router->post('freeTestingReview/edit', [
            'as' => 'freeTestingReview_edit',
            'uses' => 'FreeTestingReviewController@edit',
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

        //测评订单列表
        $router->post('freeTestingReview/list', [
            'as' => 'freeTestingReview_list',
            'uses' => 'FreeTestingReviewController@index',
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

        //测评订单列表导出
        $router->post('freeTestingReview/export', [
            'as' => 'freeTestingReview_export',
            'uses' => 'FreeTestingReviewController@export',
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

        //测评订单统计
        $router->post('freeTestingReview/statistics', [
            'as' => 'freeTestingReview_statistics',
            'uses' => 'FreeTestingReviewController@statistics',
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

        //测评订单审核
        $router->post('freeTestingReview/audit', [
            'as' => 'freeTestingReview_audit',
            'uses' => 'FreeTestingReviewController@audit',
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

        //评测产品新增
        $router->post('activity/product/addFreeTesting', [
            'as' => 'admin_activity_product_addFreeTesting',
            'uses' => 'ActivityProductController@addFreeTesting',
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

        //文件关联具体业务
        $router->post('file/business/associated', [
            'as' => 'file_business_associated',
            'uses' => 'FileAssociatedBusinessController@associated',
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

        //新增黑名单
        $router->post('blacklist/add', [
            'as' => 'admin_blacklist_add',
            'uses' => 'BlackListController@add',
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

        //黑名单编辑备注
        $router->post('blacklist/remark', [
            'as' => 'admin_blacklist_remark',
            'uses' => 'BlackListController@remark',
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

        //黑名单列表
        $router->post('blacklist/list', [
            'as' => 'admin_blacklist_list',
            'uses' => 'BlackListController@list',
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

        //黑名单删除
        $router->post('blacklist/delete', [
            'as' => 'admin_blacklist_delete',
            'uses' => 'BlackListController@delete',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //黑名单导入
        $router->post('blacklist/importBlackList', [
            'as' => 'admin_blacklist_importBlackList',
            'uses' => 'BlackListController@importBlackList',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
            'noLog' => ['file'],
        ]);

        //黑名单导出
        $router->post('blacklist/exportBlackList', [
            'as' => 'admin_blacklist_exportBlackList',
            'uses' => 'BlackListController@exportBlackList',
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

        //黑名单原因下拉
        $router->post('blacklist/reasonList', [
            'as' => 'admin_blacklist_reasonList',
            'uses' => 'BlackListController@reasonList',
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

        //导入shopify app 账号 接口
        $router->post('customer/importShopfiyAppAccount', [
            'as' => 'admin_customer_importShopfiyAppAccount',
            'uses' => 'CustomerController@importShopfiyAppAccount',
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

        //导入留言接口
        $router->post('leaveMessage/import', [
            'as' => 'admin_leaveMessage_import',
            'uses' => 'LeaveMessageController@import',
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
