<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/7/23 13:45
 */

namespace App\Http\Controllers\Admin;

use App\Services\CustomerService;
use App\Services\ReportLogService;
use App\Services\StatisticsService;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;

class StatisticsController extends Controller {

    /**
     * 统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userNumsByField(Request $request) {
        $field = $request->input('field', Constant::DB_TABLE_STORE_ID);
        empty($field) && $field = Constant::DB_TABLE_STORE_ID;

        $data = StatisticsService::userNumsByField($field, $request->all());
        return Response::json($data);
    }

    /**
     * 统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userNumsByTime(Request $request) {

        $type = $request->input('type', 'day');
        $statType = $request->input('stat_type', 1);

        empty($type) && $type = 'day';
        empty($statType) && $statType = 1;

        $statDate = StatisticsService::getStatDate($request->all(),false);

        $data = StatisticsService::userNumsByTime(data_get($statDate, Constant::START_TIME), data_get($statDate, Constant::DB_TABLE_END_TIME), $type, $statType);

        return Response::json($data);
    }

    /**
     * 按时间统计各个官网注册人数 环比 同比
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userNumsByCompared(Request $request) {

        $requestData = $request->all();
        $format = 'Y-m-d';


        $startTime = data_get($requestData, Constant::START_TIME, date($format));

        $data = StatisticsService::userNumsByCompared($startTime);

        return Response::json($data);
    }

    /**
     * 统计延保
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderWarraytySta(Request $request) {

        $data = StatisticsService::orderWarraytySta($request->all());

        return Response::json($data);
    }

}
