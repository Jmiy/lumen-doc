<?php

/**
 * Created by Patazon.
 * @desc   :
 * @author : Roy_qiu
 * @email  : Roy_qiu@patazon.net
 * @date   : 2020/8/29 16:09
 */

namespace App\Http\Controllers\Platform\Notice;

use App\Http\Controllers\Api\Controller;
use App\Services\ProductService;
use App\Util\Response;
use Illuminate\Http\Request;

class ProductController extends Controller {

    /**
     * 产品创建
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeCreate(Request $request) {
        $data = $request->all(); //请求参数

        ProductService::handleProduct($this->storeId, $this->platform, [$data]);

        return Response::json($data);
    }


    /**
     * 产品删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeDelete(Request $request) {
        $data = $request->all(); //请求参数

        $productId = data_get($data, 'id');
        ProductService::deleteProduct($this->storeId, $this->platform, $productId);

        return Response::json($data);
    }

    /**
     * 产品更新
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noticeUpdate(Request $request) {
        $data = $request->all(); //请求参数

        ProductService::handleProduct($this->storeId, $this->platform, [$data]);

        return Response::json($data);
    }
}
