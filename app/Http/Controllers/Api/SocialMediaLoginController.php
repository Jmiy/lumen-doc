<?php

namespace App\Http\Controllers\Api;

use App\Services\DictService;
use App\Services\SocialMediaLoginService;
use App\Services\StoreService;
use App\Util\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Util\Constant;

class SocialMediaLoginController extends Controller {
    /**
     * 第三方登陆会员注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCustomer(Request $request) {
        //参数检查
        $paramsData = $this->checkParams($request);
        if (!$paramsData['code']) {
            return Response::json('', $paramsData['code'], $paramsData['msg']);
        }

        $result = SocialMediaLoginService::smLogin($request, $paramsData['params']);

        return Response::json($result['data'], $result['code'], $result['msg']);
    }

    /**
     * 第三方登陆密码修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordModify(Request $request) {
        $customerId = $this->customerId;
        $password = $request->input('password', '');
        $result =SocialMediaLoginService::modifyPassword($customerId, $password);
        return Response::json($result['data'], $result['code'], $result['msg']);
    }

    /**
     * 参数检验及获取
     * @param Request $request
     * @return array
     */
    private function checkParams(Request $request) {
        $storeId = $this->storeId;
        if (!StoreService::getStoreIds()->contains($storeId)) {
            return [
                'code' => 0,
                'msg' => 'store_id参数错误'
            ];
        }
        $account = $request->input('account', '');
        if (empty($account)) {
            return [
                'code' => 0,
                'msg' => 'account参数错误'
            ];
        }
        $loginSoucre = $request->input('login_source', '');
        if (!DictService::getListByType('login_source', null,'dict_key')->contains($loginSoucre)) {
            return [
                'code' => 0,
                'msg' => 'login_source参数错误'
            ];
        }

        $params = [
            'store_id' => $storeId,
            'account' => $account,
            'third_source' => $loginSoucre, //第三方平台登陆标示
            'third_user_id' => $request->input('id', ''), //第三方平台用户id
            'first_name' => $request->input('first_name', ''),
            'last_name' => $request->input('last_name', ''),
            'ip' => $this->ip,
            'phone' => $request->input('phone', ''),
            'gender' => $request->input('gender', ''),
            'birthday' => $request->input('birthday', ''),
            'country' => $request->input('country', ''),
            'user_info' => json_encode($request->all()),
            'created_at' => $request->input('created_at', Carbon::now()->toDateTimeString()),
            'updated_at' => $request->input('updated_at', Carbon::now()->toDateTimeString()),
            'platform' => $request->input('platform', 'Shopify'),
            Constant::DB_TABLE_ACTION => $request->input(Constant::DB_TABLE_ACTION, 'login'),
            'accepts_marketing' => $request->input('accepts_marketing', false),
            'act_id' => $request->input('act_id', 0),
            'true_email' => $request->input('is_true', 1),
        ];

        $request->offsetSet('ip', $params['ip']);
        $request->offsetSet(Constant::DB_TABLE_ACTION, 'register');

        return [
            'code' => 1,
            'params' => $params
        ];
    }
}
