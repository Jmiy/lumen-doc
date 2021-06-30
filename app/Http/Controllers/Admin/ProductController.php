<?php

namespace App\Http\Controllers\Admin;

use App\Services\MetafieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\ProductService;
use App\Services\ExcelService;
use App\Util\Constant;
use Illuminate\Support\Facades\DB;
use App\Services\CouponService;

class ProductController extends Controller {

    public $storeProductIdKey = 'store_product_id';

    public function getListSelect(Request $request) {
        return [
            'p.' . Constant::DB_TABLE_PRIMARY,
            'p.' . Constant::DB_TABLE_UNIQUE_ID,
            'p.' . Constant::DB_TABLE_PRODUCT_UNIQUE_ID,
            'p.' . Constant::STORE_PRODUCT_ID,
            'p.' . Constant::DB_TABLE_CREDIT,
            'p.' . Constant::DB_TABLE_QTY,
            'p.' . Constant::EXCHANGED_NUMS,
            'p.' . Constant::EXPIRE_TIME,
            'p.' . Constant::SORTS,
            'p.' . Constant::DB_TABLE_OLD_CREATED_AT,
            'p.' . Constant::DB_TABLE_OLD_UPDATED_AT,
            'p.' . Constant::DB_TABLE_PRODUCT_STATUS,
            'p.' . Constant::DB_TABLE_LAST_SYS_AT,
            'p.' . Constant::DB_TABLE_SKU,
            'p.' . Constant::DB_TABLE_PRIMARY . ' as ' . Constant::DB_TABLE_EXT_ID,
            DB::raw("'" . ProductService::getModelAlias() . "' as " . Constant::DB_TABLE_EXT_TYPE),
        ];
    }

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $request->offsetSet('source', 'admin');
        $select = $this->getListSelect($request);
        $data = ProductService::getL($request->all(), true, true, $select);
        return Response::json($data);
    }

    /**
     * 列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $header = [
            '产品ID' => $this->storeProductIdKey,
            '产品名称' => Constant::DB_TABLE_NAME,
            '产品sku' => Constant::DB_TABLE_SKU,
            '产品国家' => Constant::DB_TABLE_PRODUCT_COUNTRY,
            '产品类型' => Constant::PRODUCT_TYPE,
            '产品价格' => 'variants.0.' . Constant::DB_TABLE_PRICE,
            '产品积分' => $this->creditKey,
            '产品可兑换数' => Constant::DB_TABLE_QTY,
            '产品已兑换数' => Constant::EXCHANGED_NUMS,
            '产品状态' => Constant::DB_TABLE_PRODUCT_STATUS . '_show',
            '拉取时间' => Constant::DB_TABLE_LAST_SYS_AT,
            '截止时间' => Constant::EXPIRE_TIME,
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $request->offsetSet('source', 'admin');
        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = ProductService::getNamespaceClass();
        $method = 'getL';
        $select = $this->getListSelect($request);
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 同步产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request) {

        $requestData = $request->all();

        $storeId = $requestData[$this->storeIdKey] ?? 0;
        $operator = $requestData['operator'] ?? '';

        if ($storeId != 2) {
            return Response::json([], 10024, '此官网暂不支持');
        }

        $checkFreq = ProductService::checkSyncFrequent($storeId, $operator);
        if ($checkFreq) {
            return Response::json([], 10025, '5分钟内只能拉取一次');
        }

        $createdAtMin = $requestData['start_time'] ?? '';
        $createdAtMax = $requestData['end_time'] ?? '';
        $limit = isset($requestData[Constant::DB_EXECUTION_PLAN_LIMIT]) && $requestData[Constant::DB_EXECUTION_PLAN_LIMIT] ? $requestData[Constant::DB_EXECUTION_PLAN_LIMIT] : 1000;
        $source = 5;

        $parameters = [
            'updated_at_min' => $createdAtMin,
            'updated_at_max' => $createdAtMax,
            'limit' => $limit,
            'source' => $source,
            'operator' => $operator,
        ];
        $retData = ProductService::sync($storeId, $parameters);

        if ($retData['code'] != 1) {
            return Response::json($retData['data'], 10024, $retData['msg']);
        }

        return Response::json([], 'ok', $retData['msg']);
    }

    /**
     * 积分产品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $request->input($this->storeIdKey, 0); //商城ID  1：mpow; 2:vt
        $storeProductId = $request->input($this->storeProductIdKey, 0); //商城产品ID
        $result = ProductService::info($storeId, $storeProductId);

        return Response::json($result);
    }

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request) {

        $metaFields = $request->input(Constant::METAFIELDS, '');
        if ($metaFields && is_string($metaFields)) {
            $request->offsetSet(Constant::METAFIELDS, json_decode($metaFields, true));
        }

        $storeId = $request->input($this->storeIdKey, 0); //商城ID  1：mpow; 2:vt
        $storeProductId = $request->input($this->storeProductIdKey, 0); //商城产品ID
        $exists = ProductService::exists($storeId, $storeProductId);
        if ($exists) {
            return Response::json([], 10014, '产品已经存在，请勿重复添加');
        }

        $data = [
            $this->storeProductIdKey => $storeProductId,
            $this->creditKey => $request->input($this->creditKey, 0),
            Constant::DB_TABLE_QTY => $request->input('qty', 0),
            Constant::DB_TABLE_PRODUCT_STATUS => 0,
        ];
        $result = ProductService::insert($storeId, $data);

        //添加属性
        if ($result) {
            $requestData = $request->all();
            data_set($requestData, Constant::OWNER_RESOURCE, ProductService::getModelAlias());
            data_set($requestData, Constant::OP_ACTION, 'add');
            data_set($requestData, Constant::NAME_SPACE, data_get($requestData, Constant::NAME_SPACE, Constant::POINT_STORE_NAME_SPACE));
            MetafieldService::batchHandle($storeId, $result, $requestData);
        }

        return Response::json($result);
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $metaFields = $request->input(Constant::METAFIELDS, '');
        if ($metaFields && is_string($metaFields)) {
            $request->offsetSet(Constant::METAFIELDS, json_decode($metaFields, true));
        }

        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商城ID  1：mpow; 2:vt
        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //产品ID
        if (is_int($id)) {
            $exists = ProductService::exists($storeId, 0, $id);
            if (!$exists) {
                return Response::json([], 10014, '积分产品不存在');
            }
        }

        $data = [];
        $credit = $request->input($this->creditKey, null);
        $qty = $request->input(Constant::DB_TABLE_QTY, null);
        $expireTime = $request->input(Constant::EXPIRE_TIME, null);
        $productStatus = $request->input(Constant::DB_TABLE_PRODUCT_STATUS, null);
        if ($credit !== null) {
            $data[$this->creditKey] = $credit;
        }
        if ($qty !== null) {
            $data[Constant::DB_TABLE_QTY] = $qty;
        }

        if (!empty($expireTime)) {
            $data[Constant::EXPIRE_TIME] = $expireTime;
        }

        if ($productStatus !== null) {
            $data[Constant::DB_TABLE_PRODUCT_STATUS] = $productStatus;
        }

        if ($data) {
            !is_array($id) && $id = [$id];
            foreach ($id as $_id) {
                $where = [Constant::DB_TABLE_PRIMARY => $_id];
                ProductService::update($storeId, $where, $data);
            }
        }

        $requestData = $request->all();

        //编辑属性
        if ($id) {
            data_set($requestData, Constant::OWNER_RESOURCE, ProductService::getModelAlias());
            data_set($requestData, Constant::OP_ACTION, 'edit');
            data_set($requestData, Constant::NAME_SPACE, data_get($requestData, Constant::NAME_SPACE, Constant::POINT_STORE_NAME_SPACE));

            $id = ProductService::getModel($storeId)->BuildWhere([Constant::DB_TABLE_PRIMARY => $id])->pluck(Constant::DB_TABLE_UNIQUE_ID)->toArray(); //获取产品唯一id ->select([Constant::DB_TABLE_UNIQUE_ID])
            MetafieldService::batchHandle($storeId, $id, $requestData);
        }

        $metaFields = data_get($requestData, Constant::METAFIELDS, Constant::PARAMETER_ARRAY_DEFAULT);
        if ($metaFields) {
            $type = MetafieldService::getMetafieldValue($metaFields, Constant::DB_TABLE_TYPE);
            if (in_array(2, $type)) {
                CouponService::importRelatedCoupon($requestData, Constant::POINT_STORE_NAME_SPACE, 1, 1);
            }
        }

        return Response::json();
    }

    /**
     * 添加或者编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addedit(Request $request) {

        $id = $request->input('id', 0); //产品ID

        if (empty($id)) {
            return $this->add($request);
        }

        return $this->edit($request);
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $storeId = $request->input($this->storeIdKey, 0); //商城ID  1：mpow; 2:vt
        $id = $request->input('id', 0); //产品ID

        if (empty($storeId) || empty($id)) {
            return Response::json([], 10014, '删除失败');
        }

        $where = ['id' => $id];
        ProductService::delete($storeId, $where);

        return Response::json();
    }

}
