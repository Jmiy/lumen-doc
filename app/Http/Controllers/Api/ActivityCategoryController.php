<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityCategoryService;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Constant;
use App\Util\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ActivityCategoryController extends Controller {

    /**
     * 活动类目列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryList(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0);
        $actId = $request->input($this->actIdKey, 0);
        $page = $request->input(Constant::REQUEST_PAGE, 1);
        $pageSize = $request->input(Constant::REQUEST_PAGE_SIZE, 20);

        $tags = '{categoryList}';
        $key = md5(json_encode(func_get_args()));
        $ttl = config('cache.ttl', 86400);
        $data = Cache::tags($tags)->remember($key, $ttl, function () use($storeId, $actId, $page, $pageSize) {
            return ActivityCategoryService::getCategoryList($storeId, $actId, $page, $pageSize);
        });

        return Response::json($data, 1, 'ok', false);
    }

    /**
     * 类目下商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryProductList(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0);
        $actId = $request->input($this->actIdKey, 0);
        $categoryId = $request->input('category_id', 0);
        $customer = $request->user();
        $customerId = Arr::get($customer, $this->customerPrimaryKey, 0);
        $page = $request->input(Constant::REQUEST_PAGE, 1);
        $pageSize = $request->input(Constant::REQUEST_PAGE_SIZE, 20);

        $data = ActivityCategoryService::getCategoryProductList($storeId, $actId, $categoryId, $page, $pageSize, $customerId);

        return Response::json($data, 1, 'ok', false);
    }

}
