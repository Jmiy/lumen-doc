<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Util\Constant;
//use Socialite;
use Laravel\Socialite\Contracts\Factory;
use App\Services\Auth\AuthService;
use App\Util\Response;

//use Illuminate\Support\Facades\Cookie;
//use Symfony\Component\HttpFoundation\Cookie as SCookie;


class LoginController extends Controller {

    /**
     * 登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $data = AuthService::login($request->all());
        return Response::json(...Response::getResponseData($data));
    }

    /**
     * Redirect the user to the Socialite authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, null); //驱动
        $driver = $request->input('driver', null); //驱动
        return app(Factory::class)->driver($driver)->redirect();
    }

    /**
     * Obtain the user information from Socialite.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, null); //驱动
        $driver = $request->input('driver', null); //驱动

        $user = app(Factory::class)->driver($driver)->user();
        dump($user, data_get($user, 'token'));

        // $user->token;
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
//    public function redirectToFacebookProvider(Request $request) {
//
////        $request->cookie('visitor_id',898989);
////
////        //return response('8888')->withCookie(Cookie::make('cookie_key', 'cookie_value'));
////
////
////        dump($request->cookie('visitor_id'),$request->cookie('lumen_session'));//,$r = Cookie::get('lumen_session')
////
////        return response('this is test') ->withCookie(new SCookie('ssss', 'sid9999', time()+3600)) ->withCookie(new SCookie('uid', 'uid9999', time()+3600));
//        // 写入一条数据至 session 中...
//        //app('session')->put('test_sessoin_key', 'value');
//        // 获取session中键值未key的数据
//        //app('session')->get('test_sessoin_key');
////        dump(config('session'),app('session')->get('test_sessoin_key'),app(Factory::class));//->get('test_sessoin_key')
////        return [];
//
//        return app(Factory::class)->driver('facebook')->redirect();
//    }
}
