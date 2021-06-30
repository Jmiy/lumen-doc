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

$router->get('/', function () use ($router) {
    return response('hello==>'.(number_format(microtime(true) - app('request')->server->get('REQUEST_TIME_FLOAT', 0), 8, '.', '') * 1000) . ' ms');
});

//$router->group(['middleware' => ['create_visitor', 'session']], function() use ($router) {//
//    $router->get('/', function () use ($router) {
//        return response($router->app->version() . '===>' . \Illuminate\Support\Facades\Cookie::get('visitor_id'))->withCookie(\Illuminate\Support\Facades\Cookie::make('cookie_key', 'cookie_value'));
//    });
//
//    $router->get('/test-5555', function () use ($router) {
//        return response($router->app->version() . app('request')->cookie('cookie_key'));
//    });
//});

$router->group(['namespace' => 'Api', 'prefix' => 'api/shop', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    // 使用 "App\Http\Controllers\Api" 命名空间... 'carbon',
    //获取邀请者的会员信息
    $router->post('customer/getInviteCustomer', [
        'as' => 'customer_getInviteCustomer',
        'uses' => 'CustomerController@getInviteCustomer',
        'validator' => [
            'messages' => [],
            'rules' => [
                'invite_code' => 'required',
                'account' => '',
            ],
        ],
        'source' => 10008, //获取邀请者的会员信息
    ]);

    //订阅
    $router->post('pub/subcribe', [
        'as' => 'pub_subcribe',
        'uses' => 'PubController@subcribe',
        'validator' => [
            'messages' => [],
            'rules' => [
            ],
        ],
        'source' => 70000, //订阅
    ]);

    //获取走马灯数据
    $router->post('activity/getLanternData', [
        'as' => 'activity_getLanternData',
        'uses' => 'ActivityController@getLanternData',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 60001, //获取走马灯数据
    ]);

    //上传文件
    $router->post('public/upload', [
        'as' => 'public_upload',
        'uses' => 'PublicController@upload',
        'noLog' => ['file'],
        'validator' => [
            'messages' => [],
            'rules' => [
                'file' => 'bail|required',
            ],
        ],
        'source' => 60002, //分享上传文件
    ]);

    //获取排行榜数据
    $router->post('rank/list', [
        'as' => 'rank_index',
        'uses' => 'RankController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'type' => 'required',
                'account' => '',
            ],
        ],
        'source' => 60003, //获取排行榜数据
    ]);

    //投票列表
    $router->post('vote/list', [
        'as' => 'vote_index',
        'uses' => 'VoteController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 60005, //获取投票列表
    ]);

    //投票排行数据
    $router->post('vote/getRankData', [
        'as' => 'vote_getRankData',
        'uses' => 'VoteController@getRankData',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 60007, //投票排行数据
    ]);

    //产品列表
    $router->post('product/list', [
        'as' => 'product_list',
        'uses' => 'ProductController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 30003, //产品列表
    ]);

    //订单回调
    $router->post('order/shopify[/{app_env}]', [
        'as' => 'order_shopify',
        'uses' => 'OrderWarrantyController@shopify',
        'validator' => [
            'messages' => [],
            'rules' => [
                'email' => 'bail|required',
                'line_items' => 'bail|required',
                'store_id' => '',
                'account' => '',
            ],
        ],
        'source' => 30006, //订单回调
        'isAutoSetStoreId' => false, //是否自动设置商城id false:否
    ]);

    //创建订单回调
    $router->post('order/{store_id}/creatNotice[/{app_env}]', [
        'as' => 'order_store_creatNotice',
        'uses' => 'OrderWarrantyController@creatNotice',
        'validator' => [
            'messages' => [],
            'rules' => [
                'email' => 'bail|required',
                'line_items' => 'bail|required',
                'store_id' => '',
                'account' => '',
            ],
        ],
        'source' => 30006, //订单回调
        'isAutoSetStoreId' => false, //是否自动设置商城id false:否
    ]);

    //检查是否注册
    $router->post('customer/checking', [
        'as' => 'customer_checking',
        'uses' => 'CustomerController@checking',
        'source' => 10018,
    ]);

    //返回国家列表
    $router->post('country/list', [
        'as' => 'country_list',
        'uses' => '\App\Http\Controllers\Common\CountryController@index',
        'source' => 10015, //国家信息
        'validator' => [
            'rules' => [
                'store_id' => '',
                'account' => '',
            ],
        ],
    ]);

    //获取活动商品列表
    $router->post('activity/product/list', [
        'as' => 'activity_product_list',
        'uses' => 'ActivityProductController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 60009, //获取活动商品列表
    ]);

    //获取deal活动商品列表
    $router->post('activity/product/dealIndex', [
        'as' => 'activity_product_dealIndex',
        'uses' => 'ActivityProductController@dealIndex',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 60015, //获取活动商品列表
    ]);

    //奖品列表
    $router->post('activity/prize/list', [
        'as' => 'activity_prize_list',
        'uses' => 'ActivityPrizeController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
            ],
        ],
        'source' => 60017, //奖品列表
    ]);

    //获取中奖排行榜
    $router->post('activity/winning/getRankData', [
        'as' => 'activity_winning_getRankData',
        'uses' => 'ActivityWinningController@getRankData',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
            ],
        ],
        'source' => 60021, //获取中奖排行榜
    ]);

    //获取活动倒计时时间戳
    $router->post('activity/getCountdownTime', [
        'as' => 'activity_getCountdownTime',
        'uses' => 'ActivityController@getCountdownTime',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
            ],
        ],
        'source' => 60025, //获取活动倒计时时间戳
    ]);

    //获取申请产品详情
    $router->post('activity/product/getDetails', [
        'as' => 'activity_product_getDetails',
        'uses' => 'ActivityProductController@getDetails',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'id' => 'bail|required',
            ],
        ],
        'source' => 60026, //获取申请产品详情
    ]);

    //创建客户回调
    $router->post('customer/{store_id}/accountCallback', [
        'as' => 'customer_store_accountCallback',
        'uses' => 'CustomerController@accountCallback',
        'validator' => [
            'messages' => [],
            'rules' => [
                'email' => 'bail|required',
                'line_items' => 'bail|required',
                'store_id' => '',
                'account' => '',
            ],
        ],
        'source' => 30010, //客户回调
        'isAutoSetStoreId' => false, //是否自动设置商城id false:否
    ]);

    //活动banner列表
    $router->post('activity/banner/list', [
        'as' => 'activity_banner_list',
        'uses' => 'ActivityBannerController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => '',
            ],
        ],
        'source' => 60030, //获取活动banner列表
    ]);

    //VT deal 通用模板产品列表
    $router->post('activity/product/universalList', [
        'as' => 'activity_product_universalList',
        'uses' => 'ActivityProductController@universalList',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => '',
            ],
        ],
        'source' => 60031, //通用产品模板列表
    ]);

    //VT deal 通用模板产品点击数
    $router->post('activity/product/clicks', [
        'as' => 'activity_product_clicks',
        'uses' => 'ActivityProductController@clicks',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => '',
            ],
        ],
        'source' => 60032, //通用模板点击
    ]);

    $router->group(['middleware' => ['activity']], function() use ($router) {
        //解锁申请产品
        $router->post('activity/helped/handle', [
            'as' => 'activity_helped_handle',
            'uses' => 'ActivityHelpController@handle',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'account' => '',
                    'act_id' => 'bail|required',
                    'invite_code' => 'bail|required',
                    'help_account' => 'bail|required',
                    'id' => 'bail|required',
                ],
            ],
            'source' => 60027, //解锁申请产品
        ]);
    });

    //助力列表
    $router->post('activity/helped/list', [
        'as' => 'activity_helped_list',
        'uses' => 'ActivityHelpController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'apply_id' => 'bail|required',
            ],
        ],
        'source' => 60028, //助力列表
    ]);


    $router->group(['middleware' => ['auth']], function() use ($router) {

        //编辑会员信息
        $router->post('customer/edit', [
            'as' => 'customer_edit',
            'uses' => 'CustomerController@edit',
            'source' => 10007, //编辑注册会员信息
        ]);

        //获取会员信息
        $router->post('customer/info', [
            'as' => 'customer_info',
            'uses' => 'CustomerController@info',
            'source' => 10001, //获取会员基本信息
        ]);

        //会员行为记录
        $router->post('customer/record', [
            'as' => 'customer_record',
            'uses' => 'CustomerController@record',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'action' => 'bail|required',
                ],
            ],
            'source' => 10002, //登录注册
        ]);

        //会员激活
        $router->post('customer/activate[/{data}]', [
            'as' => 'customer_activate',
            'uses' => 'CustomerController@activate',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'code' => 'bail|required',
                ],
            ],
            'source' => 10003, //会员激活
        ]);

        $router->get('customer/activate[/{data}]', [
            'as' => 'customer_activate_get',
            'uses' => 'CustomerController@activate',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'code' => 'bail|required',
                ],
            ],
            'source' => 10003, //会员激活
        ]);

        //积分列表
        $router->post('credit/list', [
            'as' => 'credit_list',
            'uses' => 'CreditController@index',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 20000, //积分列表
        ]);

        //会员积分
        $router->post('credit/info', [
            'as' => 'credit_info',
            'uses' => 'CreditController@info',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 20001, //会员积分
        ]);

        //订单绑定
        $router->post('order/bind', [
            'as' => 'order_bind',
            'uses' => 'OrderWarrantyController@bind',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'country' => 'required',
                    'orderno' => 'required',
                ],
            ],
            'source' => 30000, //订单绑定
        ]);

        //获取会员最新订单
        $router->post('order/info', [
            'as' => 'order_info',
            'uses' => 'OrderWarrantyController@info',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'type' => 'required',
                ],
            ],
            'source' => 30001, //获取会员最新订单
        ]);

        //订单列表
        $router->post('order/list', [
            'as' => 'order_list',
            'uses' => 'OrderWarrantyController@index',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'type' => 'required',
                ],
            ],
            'source' => 30002, //订单列表
        ]);

        //积分兑换校验
        $router->post('order/creditexchange', [
            'as' => 'order_creditexchange',
            'uses' => 'OrderWarrantyController@creditExchange',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'products' => 'required',
                ],
            ],
            'source' => 30005, //积分兑换校验
        ]);

        //添加分享url
        $router->post('share/add', [
            'as' => 'share_add',
            'uses' => 'ShareController@add',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'url' => 'bail|required|active_url',
                ],
            ],
            'source' => 50000, //添加分享url
        ]);

        //更新分享汇总数据
        $router->post('share/update', [
            'as' => 'share_update',
            'uses' => 'ShareController@update',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 50001, //分享
        ]);

        //获取倒计时时间戳
        $router->post('public/getCountdownTime', [
            'as' => 'public_getCountdownTime',
            'uses' => 'PublicController@getCountdownTime',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 60000, //获取倒计时时间戳
        ]);

        //获取邀请码数据
        $router->post('invite/getInviteCode', [
            'as' => 'invite_getInviteCode',
            'uses' => 'InviteController@getInviteCode',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 60008, //获取邀请码数据
        ]);

        //获取用户的邀请记录
        $router->post('invite/getInviteHistory', [
            'as' => 'invite_getInviteHistory',
            'uses' => 'InviteController@getInviteHistory',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'invite_code_type' => 'required',
                    'account' => '',
                ],
            ],
            'source' => 60020, //获取用户邀请记录数据
        ]);

        //添加客户访问次数
        $router->post('guide/addTimes', [
            'as' => 'guide_addTimes',
            'uses' => 'GuideController@addTimes',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 10009, //添加客户访问次数
        ]);

        //查询客户访问次数
        $router->post('guide/checkTimes', [
            'as' => 'guide_checkTimes',
            'uses' => 'GuideController@checkTimes',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 10010, //查询客户访问次数
        ]);

        //提交产品申请资料
        $router->post('activity/apply/insert', [
            'as' => 'activity_apply_insert',
            'uses' => 'ActivityApplyInfoController@insert',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'first_name' => 'required',
                    //'last_name' => 'required',
                    'country' => 'required',
                    //'city' => 'required',
                    //'brithday' => 'bail|required|date',
                    //'gender' => 'required',
                    'profile_url' => 'required',
                    //'id' => 'required',
                ],
            ],
            'source' => 60010,
            'account_action' => 'act_apply_insert', //用户行为提交产品申请资料
        ]);

        //获取产品申请资料详情
        $router->post('activity/apply/info', [
            'as' => 'activity_apply_info',
            'uses' => 'ActivityApplyInfoController@info',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 60011,
        ]);

        //获取中奖列表
        $router->post('activity/winning/list', [
            'as' => 'activity_winning_list',
            'uses' => 'ActivityWinningController@index',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'act_id' => 'bail|required',
                ],
            ],
            'source' => 60022, //获取中奖列表
        ]);

        //活动分享
        $router->post('activity/share/handle', [
            'as' => 'activity_share_handle',
            'uses' => 'ActivityShareController@handle',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'act_id' => 'bail|required',
                    'social_media' => 'bail|required',
                ],
            ],
            'source' => 60033, //活动分享
        ]);

        //获取产品申请状态
        $router->post('activity/apply/getAuditStatus', [
            'as' => 'activity_apply_getAuditStatus',
            'uses' => 'ActivityApplyController@getAuditStatus',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 60013,
        ]);

        //编辑会员账号
        $router->post('customer/editAccount', [
            'as' => 'customer_edit_account',
            'uses' => 'CustomerController@editAccount',
            'source' => 10007, //编辑注册会员信息
        ]);

        //分享得积分
        $router->post('activity/share', [
            'as' => 'activity_share',
            'uses' => 'ActivityShareController@shareHandle',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'act_id' => 'bail|required',
                    'social_media' => 'bail|required',
                ],
            ],
            'source' => 60033, //活动分享
        ]);

        $router->group(['middleware' => ['activity']], function() use ($router) {

            //注册会员信息
            $router->post('customer/signup', [
                'as' => 'customer_signup',
                'uses' => 'CustomerController@signup',
                'source' => 10005, //注册会员信息  除mpow邀请注册以外  都是使用此接口注册
                'account_action' => 'signup', //用户行为注册
            ]);

            //被邀请者注册
            $router->post('customer/actReg', [
                'as' => 'customer_actReg',
                'uses' => 'CustomerController@actReg',
                'source' => 10006, //被邀请者注册
                'account_action' => 'signup', //用户行为注册
            ]);

            //同步会员到各个平台
            $router->post('customer/createCustomer[/{app_env}]', [
                'as' => 'customer_createCustomer',
                'uses' => 'CustomerController@createCustomer',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'platform' => 'required',
                        'password' => 'required',
                    ],
                ],
                'source' => 10000, //登录注册
                'account_action' => 'login', //用户行为登录
            ]);

            //提交产品申请接口
            $router->post('activity/apply/product', [
                'as' => 'activity_apply_product',
                'uses' => 'ActivityApplyController@insert',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'id' => 'required',
                    ],
                ],
                'source' => 60012,
                'account_action' => 'act_apply_product', //用户行为申请产品
            ]);

            //提交产品申请接口
            $router->post('activity/apply/free', [
                'as' => 'activity_apply_free',
                'uses' => 'ActivityApplyController@free',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'id' => 'required',
                    ],
                ],
                'source' => 60015,
                'account_action' => 'act_apply_free', //用户行为申请产品
            ]);

            //投票
            $router->post('vote/vote', [
                'as' => 'vote_vote',
                'uses' => 'VoteController@vote',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'vote_item_id' => 'required',
                    ],
                ],
                'source' => 60006, //投票
            ]);

            //获取抽奖次数
            $router->post('activity/winning/getLotteryNum', [
                'as' => 'activity_winning_getLotteryNum',
                'uses' => 'ActivityWinningController@getLotteryNum',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'source' => 60023, //获取抽奖次数
            ]);

            //抽奖
            $router->post('activity/winning/handle', [
                'as' => 'activity_winning_handle',
                'uses' => 'ActivityWinningController@handle',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'source' => 60024, //抽奖
            ]);

            //积分抽奖
            $router->post('activity/winning/handleCreditLottery', [
                'as' => 'activity_winning_handleCreditLottery',
                'uses' => 'ActivityWinningController@handleCreditLottery',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'act_id' => 'bail|required',
                    ],
                ],
                'source' => 60024, //积分抽奖
            ]);
        });

        //提交订单评论链接
        $router->post('activity/apply/newAddReview', [
            'as' => 'activity_apply_newAddReview',
            'uses' => 'ActivityApplyController@newAddReview',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 60014,
        ]);

        //订单评论链接列表
        $router->post('order/warrantyList', [
            'as' => 'order_warrantyList',
            'uses' => 'OrderWarrantyController@warrantyList',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'type' => 'required',
                ],
            ],
            'source' => 30007, //订单评论列表
        ]);

        //添加订单评论链接
        $router->post('order/addReview', [
            'as' => 'order_addReview',
            'uses' => 'OrderWarrantyController@addReview',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 30008, //添加评论
        ]);

        //订单评论链接列表(新的)
        $router->post('order/newWarrantyList', [
            'as' => 'order_newWarrantyList',
            'uses' => 'OrderWarrantyController@newWarrantyList',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'type' => 'required',
                ],
            ],
            'source' => 30009, //订单评论列表
        ]);

        //发送激活邮件
        $router->post('email/activate', [
            'as' => 'email_activate',
            'uses' => 'EmailController@activate',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'code' => 'bail|required',
                ],
            ],
            'source' => 10010, //发送激活邮件
        ]);

        //添加收件地址
        $router->post('activity/address/add', [
            'as' => 'email_address_add',
            'uses' => 'ActivityAddressController@add',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'activity_winning_id' => 'bail|required',
                ],
            ],
            'source' => 90000, //添加收件地址
        ]);

        //获取收件地址
        $router->post('activity/address/info', [
            'as' => 'email_address_info',
            'uses' => 'ActivityAddressController@info',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'activity_winning_id' => 'bail|required',
                ],
            ],
            'source' => 90001, //获取收件地址
        ]);

        //deal 获取优惠劵code
        $router->post('activity/product/getCoupon', [
            'as' => 'activity_product_getCoupon',
            'uses' => 'ActivityProductController@getCoupon',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'account' => '',
                ],
            ],
            'source' => 60080, //获取coupon
        ]);

        //deal 点击领取code
        $router->post('activity/product/clickReceiveCode', [
            'as' => 'activity_product_clickReceiveCode',
            'uses' => 'ActivityProductController@clickReceiveCode',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'account' => '',
                ],
            ],
            'source' => 60081, //领取coupon
        ]);

        //用户行为
        $router->post('action/handle', [
            'as' => 'action_handle',
            'uses' => 'ActionLogController@handle',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 110000, //签到
        ]);

        //获取订单索评奖励
        $router->post('reward/getOrderReviewReward', [
            'as' => 'reward_getOrderReviewReward',
            'uses' => 'RewardController@getOrderReviewReward',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'orderno' => 'bail|required',
                ],
            ],
            'source' => 30011, //获取订单索评奖励
        ]);

        //提交订单索评
        $router->post('order/review/input', [
            'as' => 'order_review_input',
            'uses' => 'OrderReviewController@input',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'orderno' => 'bail|required',
                ],
            ],
            'source' => 30012, //提交订单索评
        ]);

        //获取订单review列表
        $router->post('order/review/getReviewList', [
            'as' => 'order_review_getReviewList',
            'uses' => 'OrderReviewController@getReviewList',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 30013, //获取订单review列表
        ]);

        //获取头像
        $router->post('customer/getAvatar', [
            'as' => 'customer_getAvatar',
            'uses' => 'CustomerController@getAvatar',
            'validator' => [
                'messages' => [],
                'rules' => [
                    //'code' => 'bail|required',
                ],
            ],
            'source' => 10019, //获取头像
        ]);

        //提交订单索评
        $router->post('order/review/orderReview', [
            'as' => 'order_review_orderReview',
            'uses' => 'OrderReviewController@orderReview',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'orderno' => 'bail|required',
                ],
            ],
            'source' => 30012, //提交订单索评
        ]);

        //提交订单评星
        $router->post('order/review/playStar', [
            'as' => 'order_review_playStar',
            'uses' => 'OrderReviewController@playStar',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'orderno' => 'bail|required',
                ],
            ],
            'source' => 30012, //提交订单索评
        ]);

        //获取订单索评奖励
        $router->post('reward/v2/getOrderReviewReward', [
            'as' => 'reward_getOrderReviewReward_v2',
            'uses' => 'RewardController@getOrderReviewRewardV2',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'orderno' => 'bail|required',
                ],
            ],
            'source' => 30011, //获取订单索评奖励
        ]);

        //获取订单review列表
        $router->post('order/review/v2/getReviewList', [
            'as' => 'order_review_getReviewList_V2',
            'uses' => 'OrderReviewController@getReviewListV2',
            'validator' => [
                'messages' => [],
                'rules' => [
                ],
            ],
            'source' => 30013, //获取订单review列表
        ]);

        //提交参与活动接口
        $router->post('activity/apply/joinAct', [
            'as' => 'activity_apply_joinAct',
            'uses' => 'ActivityApplyController@joinAct',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'ext_id' => 'required',
                ],
            ],
            'source' => 60016,
            'account_action' => 'act_apply_joinAct', //用户行为申请产品
            'report' => true,
            'action_type' => 21, //参与活动
        ]);

        //评测产品申请2.0
        $router->post('activity/freeTestingProductApply', [
            'as' => 'api_activity_freeTestingProductApply',
            'uses' => 'ActivityApplyController@freeTestingProductApply',
            'validator' => [
                'messages' => [],
                'rules' => [],
            ],
        ]);

        //根据申请数据的类型获取数据
        $router->post('activity/getApply', [
            'as' => 'api_activity_getApply',
            'uses' => 'ActivityApplyController@getApply',
            'validator' => [
                'messages' => [],
                'rules' => [],
            ],
        ]);

        //根据申请数据的类型获取数据
        $router->post('activity/applyList', [
            'as' => 'api_activity_applyList',
            'uses' => 'ActivityApplyController@applyList',
            'validator' => [
                'messages' => [],
                'rules' => [],
            ],
        ]);

        //关注
        $router->post('action/follow', [
            'as' => 'action_follow',
            'uses' => 'ActionLogController@follow',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'social_media' => 'required',
                ],
            ],
            'source' => 1,
        ]);
    });

    //联系我们
    $router->post('contactus/add', [
        'as' => 'contactus_add',
        'uses' => 'ContactUsController@add',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 30004, //产品列表
    ]);

    //根据申请数据的类型获取数据
    $router->post('activity/product/productDetails', [
        'as' => 'api_activity_product_productDetails',
        'uses' => 'ActivityProductController@productDetails',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //评测产品列表
    $router->post('activity/product/freeTestingList', [
        'as' => 'api_activity_product_freeTestingList',
        'uses' => 'ActivityProductController@freeTestingList',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    $router->group(['middleware' => ['activity']], function() use ($router) {

        //获取类目列表
        $router->post('activity/category/list', [
            'as' => 'activity_category_list',
            'uses' => 'ActivityCategoryController@categoryList',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'account' => '',
                ],
            ],
            'source' => 60009, //获取活动商品列表
        ]);

        //获取类目下商品列表
        $router->post('activity/category/product/list', [
            'as' => 'activity_category_product_list',
            'uses' => 'ActivityCategoryController@categoryProductList',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'account' => '',
                ],
            ],
            'source' => 60009, //获取活动商品列表
        ]);

        $router->group(['middleware' => ['auth']], function () use ($router) {

            //配件申请
            $router->post('activity/apply/accessories', [
                'as' => 'activity_accessories_apply',
                'uses' => 'ActivityApplyController@accessoriesApply',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
                'source' => 60015,
                'account_action' => 'act_apply_free', //用户行为申请产品
            ]);

            //分享
            $router->post('activity/task/share', [
                'as' => 'activity_task_share',
                'uses' => 'ActivityTaskController@share',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //profile url 回填
            $router->post('activity/task/fillInUrl', [
                'as' => 'activity_task_fill_in_url',
                'uses' => 'ActivityTaskController@fillInUrl',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //vip club
            $router->post('activity/task/vipClub', [
                'as' => 'activity_task_vip_club',
                'uses' => 'ActivityTaskController@vipClub',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //配件申请资料提交
            $router->post('activity/apply/submit', [
                'as' => 'activity_apply_info_submit',
                'uses' => 'ActivityApplyInfoController@applyInfoSubmit',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //任务完成情况
            $router->post('activity/task/statusAndInfos', [
                'as' => 'activity_task_status_infos',
                'uses' => 'ActivityTaskController@taskStatusAndInfos',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);
        });

        //订单是否存在
        $router->post('activity/order/exists', [
            'as' => 'activity_order_exists',
            'uses' => 'OrderWarrantyController@orderExists',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'account' => '',
                ],
            ],
        ]);
    });

    $router->group(['middleware' => ['activity']], function() use ($router) {

        $router->group(['middleware' => ['auth']], function () use ($router) {

            //用户投票项上传
            $router->post('activity/vote/add', [
                'as' => 'activity_vote_add',
                'uses' => 'VoteController@addVote',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
                'noLog' => ['file'],
            ]);

            //用户图片上传
            $router->post('activity/image/upload', [
                'as' => 'activity_image_upload',
                'uses' => 'VoteController@uploadImage',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
                'noLog' => ['file'],
            ]);

            //获取用户添加的投票项内容
            $router->post('activity/vote/info', [
                'as' => 'activity_vote_info',
                'uses' => 'VoteController@voteInfo',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //预生成图片结果数据
            $router->post('activity/game/preGeneration', [
                'as' => 'activity_game_preGeneration',
                'uses' => 'GameController@preGenerationGameResult',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //游戏接口
            $router->post('activity/game/play', [
                'as' => 'activity_game_play',
                'uses' => 'GameController@playGame',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //游戏接口
            $router->post('activity/game/keyword', [
                'as' => 'activity_game_keyword',
                'uses' => 'GameController@keyword',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);

            //状态
            $router->post('activity/game/taskFlag', [
                'as' => 'activity_game_taskFlag',
                'uses' => 'GameController@taskFlag',
                'validator' => [
                    'messages' => [],
                    'rules' => [
                        'account' => '',
                    ],
                ],
            ]);
        });

        //点赞
        $router->post('activity/vote/like', [
            'as' => 'activity_vote_like',
            'uses' => 'VoteController@doLike',
            'validator' => [
                'messages' => [],
                'rules' => [
                    'account' => '',
                ],
            ],
        ]);
    });

    //获取列表
    $router->post('activity/vote/list', [
        'as' => 'activity_vote_list',
        'uses' => 'VoteController@voteList',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //活动配置
    $router->post('activity/vote/confInfo', [
        'as' => 'activity_vote_confInfo',
        'uses' => 'VoteController@getActivity',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //获取活动数据
    $router->post('activity/getActivityData', [
        'as' => 'activity_getActivityData',
        'uses' => 'ActivityController@getActivityData',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_unique' => 'bail|required',
            ],
        ],
        'source' => 60029, //产品列表
    ]);

    //中奖名单列表接口
    $router->post('activity/awardList', [
        'as' => 'admin_activity_awardList',
        'uses' => 'ActivityController@awardList',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
                'store_id' => 'required',
            ],
        ],
    ]);

    //老虎机游戏图片配置
    $router->post('activity/game/images', [
        'as' => 'activity_game_images',
        'uses' => 'GameController@getImages',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //获取中奖名单接口
    $router->post('activity/prize/customer', [
        'as' => 'activity_prize_customer',
        'uses' => 'ActivityPrizeCustomerController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'account_action' => 'activity_prize_customer', //用户行为申请产品
    ]);

    //产品详情
    $router->post('product/details', [
        'as' => 'product_details',
        'uses' => 'ProductController@details',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 30003, //产品详情
    ]);

    //产品队列列表
    $router->post('product/exchangeList', [
        'as' => 'product_exchangeList',
        'uses' => 'ProductController@exchangeList',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //活动首页pointStore产品接口
    $router->post('product/pointProducts', [
        'as' => 'product_pointProducts',
        'uses' => 'ProductController@pointProducts',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //产品-文件类目
    $router->post('product/driven/getCategories', [
        'as' => 'product_driven_getCategories',
        'uses' => 'ProductFileController@getCategories',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //产品-文件列表
    $router->post('product/driven/index', [
        'as' => 'product_driven_index',
        'uses' => 'ProductFileController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //产品-文件下载记录
    $router->post('product/driven/fileDownload', [
        'as' => 'product_driven_product_fileDownload',
        'uses' => 'ProductFileController@fileDownload',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
    ]);

    //留言列表
    $router->post('leaveMessage/list', [
        'as' => 'leaveMessage_list',
        'uses' => 'LeaveMessageController@index',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
            ],
        ],
        'source' => 1,
    ]);

    // 当天开奖结果
    $router->post('activity/guess/prize-result', [
        'as' => 'activity_guess_prize_result',
        'uses' => 'ActivityGuessNumberController@prize',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => 'bail|required',
                'act_id' => 'bail|required',
            ],
        ],
    ]);

    //猜数字活动助力列表
    $router->post('activity/guess/helped', [
        'as' => 'activity_guess_helped_list',
        'uses' => 'ActivityGuessNumberController@helped',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'store_id' => 'bail|required',
            ],
        ],
    ]);

    //猜数字获取中奖排行榜
    $router->post('activity/guess/winners', [
        'as' => 'activity_guess_winners',
        'uses' => 'ActivityGuessNumberController@winners',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
            ],
        ],
    ]);

    // 猜数字参与者列表
    $router->post('activity/guess/users', [
        'as' => 'activity_guess_user_list',
        'uses' => 'ActivityGuessNumberController@users',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => 'bail|required',
                'act_id' => 'bail|required',
            ],
        ],
    ]);

    // 猜数字参与历史记录
    $router->post('activity/guess/history', [
        'as' => 'activity_guess_user_history',
        'uses' => 'ActivityGuessNumberController@history',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'store_id' => 'bail|required',
                'act_id' => 'bail|required',
            ],
        ],
    ]);

    //获取参与者奖品列表接口
    $router->post('activity/guess/own', [
        'as' => 'activity_prize_customer_own',
        'uses' => 'ActivityGuessNumberController@own',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'store_id' => 'bail|required',
            ],
        ]
    ]);

    //获取猜数字活动邀请码用户信息
    $router->post('activity/guess/invite', [
        'as' => 'activity_prize_customer_invite',
        'uses' => 'ActivityGuessNumberController@invite',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'store_id' => 'bail|required',
                'invite_code' => 'bail|required',
            ],
        ]
    ]);

    // 猜数字活动邮件订阅处理
    $router->post('activity/guess/push', [
        'as' => 'activity_lucky_numbers_handle_push',
        'uses' => 'ActivityGuessNumberController@push',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'store_id' => 'bail|required',
                'type' => 'bail|required',
            ],
        ]
    ]);

    // 猜数字活动发送邀请朋友的邮件
    $router->post('activity/guess/share', [
        'as' => 'activity_lucky_numbers_invite_email',
        'uses' => 'ActivityGuessNumberController@inviteEmail',
        'validator' => [
            'messages' => [],
            'rules' => [
                'account' => '',
                'act_id' => 'bail|required',
                'store_id' => 'bail|required',
                'share_url' => 'bail|url',
                'email' => 'bail|email',
            ],
        ]
    ]);

});


require 'common.php';
require 'statistical.php';
require 'permission.php';
require 'admin.php';
require 'doc.php';
require 'redman.php';
require 'redman_admin.php';
require 'survey.php';
require 'social_media.php';
require 'platform.php';
require 'auth.php';
require 'payment.php';
require 'act.php';
