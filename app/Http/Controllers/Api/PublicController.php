<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Util\Response;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Cdn\CdnManager;
use App\Services\InviteService;
use App\Util\Constant;

class PublicController extends Controller {

    /**
     * 获取倒计时
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountdownTime(Request $request) {

        $ttl = 24 * 60 * 60; //缓存24小时 单位秒 
        $tags = config('cache.tags.countdown');
        $account = $request->input('account', '');
        $storeId = $request->input('store_id', 0);
        $key = $storeId . ':' . $account;
        $timestamp = Cache::tags($tags)->remember($key, $ttl, function () use($ttl) {
            return Carbon::now()->timestamp + $ttl;
        });

        return Response::json(['countdown' => $timestamp - (Carbon::now()->timestamp)]);
    }

    /**
     * 上传文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request) {

        $file = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request);

        $inviteCodeData = [];

        $customer = $request->user();
        if ($customer) {
            $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT);
            $inviteCodeData = InviteService::getInviteCodeData($customerId);
        }

        $data = [
            'url' => data_get($file, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_URL, ''),
            'invite_code' => data_get($inviteCodeData, 'invite_code', ''),
        ];

        return Response::json($data);
    }

}
