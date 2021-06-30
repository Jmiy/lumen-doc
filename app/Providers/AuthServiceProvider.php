<?php

namespace App\Providers;

//use App\User;
//use Illuminate\Support\Facades\Gate;
use App\Services\SocialMediaLoginService;
use Illuminate\Support\ServiceProvider;
use App\Models\Auths\Customer;
use App\Models\Auths\User as AdminUser;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Constant;
use App\Services\Psc\ServiceManager;

class AuthServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot() {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        //api认证服务
        $this->app['auth']->viaRequest('api', function ($request) {

//            if ($request->input('api_token')) {
//                return User::where('api_token', $request->input('api_token'))->first();
//            }

            $tags = config('cache.tags.auth', ['{auth}']);
            $authTtl = config('auth.auth_ttl', 600); //认证缓存时间 单位秒
            if ($request->has(Constant::DB_TABLE_CUSTOMER_PRIMARY)) {
                $customerId = $request->input(Constant::DB_TABLE_CUSTOMER_PRIMARY, '');
                $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
                $key = $storeId . ':' . $customerId;
                return Cache::tags($tags)->remember($key, $authTtl, function () use($customerId, $storeId) {
                            return Customer::select(Customer::getColumns())->where([Constant::DB_TABLE_CUSTOMER_PRIMARY => $customerId, Constant::DB_TABLE_STORE_ID => $storeId])->first();
                        });
            }

            //判断是否存在第三方注册时传入的参数
            if ($request->has('is_true') && $request->has('login_source') && $request->input('is_true') === 0) {
                //第三方未获取到邮箱的情况，生成假邮箱
                $params = [
                    'third_source' => $request->input('login_source', ''), //第三方平台登陆标示
                    'third_user_id' => $request->input('id', ''), //第三方平台用户id
                ];
                $gAccount = SocialMediaLoginService::generateAccount($request->input(Constant::DB_TABLE_STORE_ID, 0), $params, $request->all());
                //覆盖request的account参数值
                $request->offsetSet(Constant::DB_TABLE_ACCOUNT, $gAccount);
            }

            $account = $request->input(Constant::DB_TABLE_ACCOUNT, '');
            $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
            $key = $storeId . ':' . $account;
            $user = Cache::tags($tags)->remember($key, $authTtl, function () use($account, $storeId, $request) {
                if (empty($account)) {
                    return null;
                }

                $user = Customer::select(Customer::getColumns())->where([Constant::DB_TABLE_STORE_ID => $storeId, Constant::DB_TABLE_ACCOUNT => $account])->first();
                if (empty($user)) {
                    $customer = new \App\Http\Controllers\Api\CustomerController($request);
                    $registerResponse = $customer->registered($request);
                    $request->offsetSet(Constant::REGISTER_RESPONSE, $registerResponse);

                    $user = Customer::select(Customer::getColumns())->where([Constant::DB_TABLE_STORE_ID => $storeId, Constant::DB_TABLE_ACCOUNT => $account])->first();
                }

                return $user;
            });

            $request->offsetSet(Constant::DB_TABLE_CUSTOMER_PRIMARY, data_get($user, Constant::DB_TABLE_CUSTOMER_PRIMARY, 0));

            return $user;
        });

        //管理后台认证服务
        $this->app['auth']->viaRequest('apiAdmin', function ($request) {
            if ($request->input('token')) {
                $isPsc = $request->input('is_psc');
                $tags = config('cache.tags.adminAuth', ['{adminAuth}']);
                $authTtl = config('auth.auth_ttl', 600); //认证缓存时间 单位秒

                $token = $request->input('token');

                if (!$isPsc) {
                    $requestData = $request->all();
                    if (isset($requestData['bk'])) {
                        $operator = $request->input('operator');
                        $user = Cache::tags($tags)->remember($operator, $authTtl, function () use ($operator) {
                            return AdminUser::where(['username' => $operator])->first();
                        });
                        return $user;
                    }

                    $user = Cache::tags($tags)->remember($token, $authTtl, function () use ($token) {
                        return AdminUser::where(['api_token' => $token])->first();
                    });

                    $request->offsetSet('adminUserId', data_get($user, Constant::DB_TABLE_PRIMARY, 0));

                    return $user;
                }

                $user = Cache::tags($tags)->remember($token, $authTtl, function () use ($token) {
                    $user = ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'User','tokenAuthentication',[$token]);
                    if($user){
                        $permissionsData = ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'Permission','getPermissionByRole',[$token]);

                        data_set($user,'permissionsData',$permissionsData);
//                        data_set($user,'roles',data_get($permissionsData,'store',collect([]))->first());
//                        data_set($user,'store_id',data_get($permissionsData,'store',collect([]))->keys()->first());
                    }

                    return $user;

                });

                $request->offsetSet('adminUserId', data_get($user, Constant::DB_TABLE_PRIMARY, 0));

                return $user;
            }
        });
    }

}
