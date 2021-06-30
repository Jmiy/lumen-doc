<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\VoteService;
use App\Services\ExcelService;

class VoteController extends Controller {

    /**
     * 投票产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $requestData = $request->all();

        $data = VoteService::getProductList($requestData);

        return Response::json($data);
    }

    /**
     * 投票产品列表编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $requestData = $request->all();
        $storeId = data_get($requestData, 'store_id', 0);
        $Id = data_get($requestData, 'id', 0);
        if (empty($Id)) {
            return Response::json([], 10005, 'id not exists');
        }

        $data = VoteService::getVoteEdit($storeId, $Id, $requestData);

        return Response::json($data);
    }

    /**
     * 投票产品列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $header = [
            '产品名' => 'name',
            '产品链接' => 'url',
            '产品类型' => 'type',
            'PC主图' => 'img_url',
            '移动主图' => 'mb_img_url',
            '创建时间' => 'created_at',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['vote_items.id']
            ],
        ];

        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = VoteService::getNamespaceClass();
        $method = 'getProductList';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getProductList';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

}
