<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ActivityService;
use App\Services\AwardUserService;
use App\Util\Constant;
use App\Services\ExcelService;

class ActivityController extends Controller {

    /**
     * 活动名称列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $data = ActivityService::getListData($request->all());

        return Response::json($data);
    }

    /**
     * 导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $header = [
            '活动名称' => Constant::DB_TABLE_NAME,
            '活动类型' => 'act_type_show',
            '活动时间（UTF-8）' => 'act_time',
            '活动链接' => Constant::FILE_URL,
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => [Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $requestData[Constant::REQUEST_PAGE_SIZE] = 20000; //
        $requestData[Constant::REQUEST_PAGE] = 1;

        $service = ActivityService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 活动下拉
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = ActivityService::getActivityList($storeId);

        return Response::json($data);
    }

    /**
     * 添加活动
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = ActivityService::input($storeId, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 编辑活动
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $data = ActivityService::input($storeId, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 编辑活动
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request) {

        $requestData = $request->all();

        $storeId = data_get($requestData, Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $ids = data_get($requestData, Constant::DB_TABLE_PRIMARY, []);

        $data = ActivityService::delAct($storeId, $ids);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 中奖名单新增接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAwardUser(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_STRING_DEFAULT);
        $account = $request->input(Constant::DB_TABLE_ACCOUNT, Constant::DB_TABLE_ACCOUNT);

        if (empty($storeId) || empty($account)) {
            return Response::json([], -1, Constant::PARAMETER_STRING_DEFAULT);
        }
        $res = AwardUserService::addAwardUser($storeId, $account);
        if ($res){
            $rdata['code'] = 1;
        } else {
            $rdata['code'] = 0;
        }

        $parameters = Response::getResponseData($rdata);

        return Response::json(...$parameters);
    }

}
