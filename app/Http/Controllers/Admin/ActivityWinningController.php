<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ActivityWinningService;
use App\Services\ExcelService;
use App\Services\DictStoreService;

class ActivityWinningController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();

        $data = ActivityWinningService::getAdminList($requestData);

        return Response::json($data);
    }

    /**
     * 中奖流水导出
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $storeId = data_get($requestData, 'store_id', 0);
        $address = DictStoreService::getByTypeAndKey($storeId, 'sweepstakes', 'address', true); //是否配置中奖收货地址
        $header = [
            '活动名称' => 'act_name',
            '邮箱' => 'account',
            '国家' => 'country',
            'ip' => 'ip',
            //'是否激活' => 'isactivate',
            '奖品名称' => 'name',
            '奖品类型' => 'type',
            '奖品类型值' => 'type_value',
            '是否参与奖' => 'is_participation_award',
            '分享次数' => 'share_total',
            '注册时间' => 'created_at',
            '中奖时间' => 'updated_at',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['w.id']
            ],
        ];
        if ($address) {
            $header = Arr::collapse([$header, [
                            '收货国家' => 'usercountry',
                            '收货名称' => 'full_name',
                            '收货街道' => 'street',
                            '收货住址' => 'apartment',
                            '收货城市' => 'city',
                            '收货州/省' => 'state',
                            '收货邮编' => 'zip_code',
                            '收货电话' => 'phone',
            ]]);
            if ($storeId == 3) {//holife 1 月新品活动
                $header = Arr::collapse([$header, [
                                '社媒账号链接' => 'account_link'
                ]]);
            }
        }

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = ActivityWinningService::getNamespaceClass();
        $method = 'getAdminList';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getAdminList';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 中奖收货地址查询
     * @param Request $request
     * @return JsonResponse
     */
    public function showAddress(Request $request) {

        $requestData = $request->all();
        $storeId = data_get($requestData, 'store_id', 0);
        $winningId = data_get($requestData, 'id', 0);
        $type = data_get($requestData, 'type', 0);
        if (empty($winningId)) {
            return Response::json([], 10027, 'id not exists');
        }
        if ($type != 3) {
            return Response::json([], 10028, 'Not physical, no delivery address');
        }
        $data = ActivityWinningService::shippingAddress($storeId, $winningId);

        return Response::json($data);
    }

}
