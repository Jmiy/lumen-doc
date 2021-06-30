<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityHelpedLogService;
use App\Util\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityHelpController extends Controller
{
    /**
     * 解锁
     * @param  Request  $request
     * @return JsonResponse
     */
    public function handle(Request $request)
    {
        $inviteCode = $request->input('invite_code', ''); //邀请者的邀请码
        $helpAccount = $request->input('help_account', ''); //解锁者账号
        $productId = $request->input('id', 0); //关联id 活动产品id
        $ip = $request->input('ip', 0); //解锁者ip
        $country = $request->input('country', ''); //解锁者ip

        $rs = ActivityHelpedLogService::handle($this->storeId, $this->actId, $inviteCode, $helpAccount, $productId, $ip,
            $country, $request->all());

        return Response::json(data_get($rs, 'data', []), data_get($rs, 'code', 1), data_get($rs, 'msg', ''));
    }

    /**
     * 助力列表
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $customerID = $request->input('apply_id', 0); //申请id
        $page = $request->input('page', 1); //解锁者账号
        $pageSize = $request->input('page_size', 10); //关联id 活动产品id

        $data = ActivityHelpedLogService::getData($this->storeId, $this->actId, $customerID, $page, $pageSize);

        return Response::json($data, 1, '');
    }

}
