<?php


namespace App\Http\Controllers\Api;


use App\Models\Activity;
use App\Services\ActivityGuessNumberService;
use App\Services\ActivityService;
use App\Services\InviteHistoryService;
use App\Services\InviteCodeService;
use App\Util\Constant;
use App\Util\Response;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenCloud\Common\Exceptions\ForbiddenOperationException;

class ActivityGuessNumberController extends Controller
{

    /**
     * 获取用户的中奖记录
     * @param  Request  $request
     * @return JsonResponse
     */
    public function own(Request $request)
    {
        $data = ActivityGuessNumberService::getMyPrize(
            intval($this->storeId), intval($this->actId), $this->account, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 猜数字活动的当天中奖用户列表
     * @param  Request  $request
     * @return JsonResponse
     */
    public function winners(Request $request)
    {
        $data = ActivityGuessNumberService::getWinnerList(
            intval($this->actId), intval($this->storeId), $this->page, $this->pageSize);
        return Response::json($data);
    }

    /**
     * 获取活动的奖品结果
     * @param  Request  $request
     * @return JsonResponse
     */
    public function prize(Request $request)
    {
        $flag = ActivityService::existsOrFirst(intval($this->storeId), '',
            [Constant::DB_TABLE_PRIMARY => intval($this->actId)], true, [Constant::DB_TABLE_END_AT]);
        $endAt = data_get($flag, Constant::DB_TABLE_END_AT);

        $data = ActivityGuessNumberService::getDailyResult(intval($this->actId), intval($this->storeId));

        // 结束时间小于当前时间则活动继续
        if (is_null($endAt) || (!is_null($endAt) && Carbon::parse($endAt)->timestamp - Carbon::now()->timestamp > 0)) {
            $res['end'] = false;
            $res['next'] = Carbon::tomorrow()->timestamp - Carbon::now()->timestamp;
        } else {
            $res['end'] = Carbon::parse($endAt)->timestamp > Carbon::now()->timestamp ? false : true;
        }
        return Response::json(array_merge($res, $data));
    }


    /**获取当天猜数字参与活动的用户
     * @param  Request  $request
     * @return JsonResponse
     */
    public function users(Request $request)
    {
        $users = ActivityGuessNumberService::getUserList($this->storeId, $this->actId, $this->page, $this->pageSize);
        return Response::json($users);
    }

    /**
     * 获取用户猜数字的历史记录
     * @param  Request  $request
     * @return JsonResponse
     */
    public function history(Request $request)
    {
        $data = ActivityGuessNumberService::getUserGuessHistory(
            $this->storeId, $this->actId, $this->account, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 猜数字活动的助力列表
     * @param  Request  $request
     * @return JsonResponse
     */
    public function helped(Request $request)
    {
        $data = InviteHistoryService::getHelpedList($this->storeId, $this->actId, $this->account, $this->page,
            $this->pageSize);
        return Response::json($data);
    }

    /**
     * 查询活动邀请关系的信息
     * @param  Request  $request
     * @return JsonResponse
     */
    public function invite(Request $request)
    {
        $inviteCode = $request->input('invite_code', Constant::PARAMETER_STRING_DEFAULT);
        $data = InviteCodeService::getInviteInfo($this->storeId, $inviteCode);
        return Response::json($data);
    }

    /**
     * 处理邮件推送配置
     * @param  Request  $request
     * @return JsonResponse
     */
    public function push(Request $request)
    {
        // 推送處理   -2接受通知推送  -1接收开奖结果推送 1 取消开奖结果推送  2 取消通知推送   3 取消两种邮件推送
        $type = $request->input('type', 0);
        if (!$type) {
            return Response::json([], 0);
        }
        $code = ActivityGuessNumberService::handlePush($this->storeId, $this->actId, $this->account, $type);
        return Response::json([], $code);
    }

    /**
     * 发送邀请邮件
     * @param  Request  $request
     * @return JsonResponse
     */
    public function inviteEmail(Request $request)
    {
        // 解析传过来的邀请链接 判断邀请码是否存在
        $urlArr = parse_url($request->input('share_url'));
        if (strpos($urlArr['query'], 'invite_code') === false) {
            return Response::json([], 0);
        }
        $inviteEmail = $request->input('email');

        $code = ActivityGuessNumberService::sendInviteEmail($this->storeId, $this->actId,
            $inviteEmail, $this->account, $request->input('share_url'));
        return Response::json([], $code);
    }



}
