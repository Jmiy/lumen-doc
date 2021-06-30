<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\RankService;
use App\Util\Constant;

class RankController extends BaseActController {

    /**
     * 排行榜
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $customer = $request->user();
        $customerId = data_get($customer, Constant::DB_TABLE_CUSTOMER_PRIMARY, Constant::PARAMETER_INT_DEFAULT);

        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        if (empty($actId)) {
            $actId = $this->activityData ? $this->activityData['id'] : 0;
        }
        $rankType = $request->input('rank_type', 1); //榜单种类 1：总榜 2：日榜
        $type = $request->input('type', 1);//榜单类型 0:综合榜 1:分享 2:邀请 3:签到
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);

        //var_dump(RankService::del([RankService::getRankKey($storeId, $actId, $type)]));
        //exit;
        //var_dump(RankService::handle($storeId, $customerId, $actId, $type, 1));

        if ($type == Constant::PARAMETER_INT_DEFAULT) {//总榜 综合排行榜
            $rankType = 1;
        } else if ($type == -1) {//日榜 综合排行榜
            $rankType = 2;
            $type = Constant::PARAMETER_INT_DEFAULT;
        }

        $data = RankService::getRankData($customerId, $this->storeId, $actId, $rankType, $type, $page, $pageSize);

        return Response::json($data);
    }

}
