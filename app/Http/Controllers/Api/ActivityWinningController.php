<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityService;
use App\Services\ActivityWinningService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class ActivityWinningController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $data = ActivityWinningService::getItemData($this->storeId, $this->actId, $this->customerId, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 获取抽奖次数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLotteryNum(Request $request) {

        $requestData = $request->all();
        $actionData = [
            Constant::SERVICE_KEY => ActivityService::getNamespaceClass(),
            Constant::METHOD_KEY => 'get',
            Constant::PARAMETERS_KEY => [],
            Constant::REQUEST_DATA_KEY => $requestData,
        ];
        $customerId = data_get($requestData, 'act_form', 'lottery') == 'lottery' ? $this->customerId : $this->account;
        if (data_get($requestData, 'act_form', Constant::PARAMETER_STRING_DEFAULT) == Constant::ACT_FORM_SLOT_MACHINE) {
            $customerId = $this->customerId;
        }
        $lotteryData = ActivityService::handleLimit($this->storeId, $this->actId, $customerId, $actionData);
        $lotteryNum = data_get($lotteryData, 'lotteryNum', 0);
        $lotteryNum = $lotteryNum > 0 ? $lotteryNum : 0;
        data_set($lotteryData, 'lotteryNum', $lotteryNum);

        return Response::json($lotteryData);
    }

    /**
     * 抽奖
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request) {

        $requestData = $request->all();

        $data = ActivityWinningService::handle($this->storeId, $this->actId, $this->customerId, $this->account, $requestData);

        return Response::json($data['data'], $data['code'], $data['msg']);
    }

    /**
     * 排行榜
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRankData(Request $request) {
        $data = ActivityWinningService::getRankData($this->storeId, $this->actId, $this->account, $this->page, $this->pageSize);

        return Response::json($data);
    }

    /**
     * 积分抽奖
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCreditLottery(Request $request) {

        $data = ActivityWinningService::handleCreditLottery($this->storeId, $this->actId, $this->customerId, $this->account, $request->all());

        return Response::json(...Response::getResponseData($data));
    }

}
