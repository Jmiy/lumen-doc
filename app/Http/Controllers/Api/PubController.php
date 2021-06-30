<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\SubcribeService;
use App\Util\FunctionHelper;
use App\Util\Constant;

class PubController extends Controller {

    /**
     * 订阅
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subcribe(Request $request) {

        $storeId = $request->input('store_id', 0);
        $account = $request->input('account', '');
        $country = $request->input('country', '');
        $firstName = $request->input('first_name', '');
        $lastName = $request->input('last_name', '');
        $group = 'subcribe';
        $ip = FunctionHelper::getClientIP($request->input('ip'));
        $remark = '会员订阅'; //会员主动订阅
        $createdAt = $request->input(Constant::DB_TABLE_CREATED_AT, '');

        if (!($request->has('country'))) {//has 方法将确定是否所有指定值都存在
            $country = FunctionHelper::getCountry($ip);
        }

        $requestData = $request->all();
        $extData = [
            'actId' => data_get($requestData, 'act_id', 1), //活动id
            'action' => data_get($requestData, 'bk', 'subcribe'), //会员行为
            'accepts_marketing' => 1, //是否订阅 1：是 0：否
        ];
        if (isset($requestData[Constant::DB_TABLE_CREATED_AT])) {
            data_set($extData, Constant::DB_TABLE_CREATED_AT, $requestData[Constant::DB_TABLE_CREATED_AT]);
        }

        if (isset($requestData[Constant::DB_TABLE_UPDATED_AT])) {
            data_set($extData, Constant::DB_TABLE_UPDATED_AT, $requestData[Constant::DB_TABLE_UPDATED_AT]);
        }

        if (isset($requestData['bk'])) {
            data_set($extData, 'bk', $requestData['bk']);
        }
        $data = SubcribeService::handle($storeId, $account, $country, $firstName, $lastName, $group, $ip, $remark, $createdAt, $extData);

        return Response::json([], $data['code'], $data['msg']);
    }

}
