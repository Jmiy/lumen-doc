<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use Carbon\Carbon;
use App\Services\ActivityProductService;
use App\Services\ExcelService;
use App\Util\Constant;
use App\Util\Cdn\CdnManager;

class ActivityProductController extends Controller {

    public $showKey = '_show';
    public $memoryLimit = 'memory_limit';

    /**
     * deal 活动产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $requestData = $request->all();
        $data = ActivityProductService::getDealList($requestData);
        return Response::json($data);
    }

    /**
     * deal 编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {

        $id = $request->input(Constant::DB_TABLE_PRODUCT_ID, Constant::PARAMETER_INT_DEFAULT); //产品id
        if (empty($id)) {
            return Response::json([], 9999999998, '参数product_id不能为空');
        }

        $storeId = $request->input($this->storeIdKey, 0); //商店ID
        $name = $request->input(Constant::DB_TABLE_NAME, Constant::PARAMETER_STRING_DEFAULT); //产品标题
        $sku = $request->input(Constant::DB_TABLE_SKU, Constant::PARAMETER_STRING_DEFAULT); //产品sku
        $asin = $request->input(Constant::DB_TABLE_ASIN, Constant::PARAMETER_STRING_DEFAULT); //产品asin
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT); //活动id
        $actName = $request->input(Constant::DB_TABLE_ACTIVITY_NAME, Constant::PARAMETER_STRING_DEFAULT); //活动名称

        $data = ActivityProductService::editDeal($id, $storeId, $name, $sku, $asin, $actId, $actName, $request->all());

        return Response::json($data);
    }

    /**
     * deal 操作
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operate(Request $request) {

        $id = $request->input(Constant::DB_TABLE_PRODUCT_ID, Constant::PARAMETER_INT_DEFAULT); //产品id
        if (empty($id)) {
            return Response::json([], 10015, '数据不存在');
        }

        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商店ID
        $mbType = $request->input('mb_type', Constant::PARAMETER_STRING_DEFAULT); //产品类型
        $productStatus = $request->input(Constant::DB_TABLE_PRODUCT_STATUS, Constant::PARAMETER_STRING_DEFAULT); //产品状态
        $country = $request->input(Constant::DB_TABLE_COUNTRY, Constant::PARAMETER_STRING_DEFAULT); //产品国家

        $data = ActivityProductService::operateDeal($id, $storeId, $mbType, $productStatus, $country);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * deal 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {

        $id = $request->input(Constant::DB_TABLE_PRODUCT_ID, Constant::PARAMETER_INT_DEFAULT); //产品id
        if (empty($id)) {
            return Response::json([], 10015, '数据不存在');
        }

        $storeId = $request->input($this->storeIdKey, Constant::PARAMETER_INT_DEFAULT); //商店ID
        $data = ActivityProductService::delete($storeId, $id, $request->all());

        return Response::json($data);
    }

    /**
     * deal 活动产品列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $requestData = $request->all();
        $mb_type = data_get($requestData, Constant::DB_TABLE_MB_TYPE, Constant::PARAMETER_INT_DEFAULT);
        $header = [
            '产品图片' => Constant::DB_TABLE_IMG_URL,
            '产品标题' => Constant::DB_TABLE_NAME,
            '产品sku' => Constant::DB_TABLE_SKU,
            '产品asin' => Constant::DB_TABLE_ASIN,
            '国家' => Constant::DB_TABLE_COUNTRY,
            '上传人' => 'upload_user',
            '模板类型' => Constant::DB_TABLE_MB_TYPE . $this->showKey,
            '产品状态' => Constant::DB_TABLE_PRODUCT_STATUS . $this->showKey,
            '点击量' => 'click',
            '活动名称' => 'activity_name',
            '活动链接' => 'act_' . Constant::FILE_URL,
            '上传时间' => Constant::DB_TABLE_CREATED_AT,
            'distinctField' => [
                'primaryKey' => Constant::DB_TABLE_PRIMARY,
                'primaryValueKey' => Constant::DB_TABLE_PRIMARY,
                'select' => ['ap.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];
        if ($mb_type == 4) {//目前只有通用模板的产品有描述
            data_set($header, '产品描述', Constant::DB_TABLE_DES);
        }

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID
        if ($storeId == 1) {
            data_set($header, '星级', Constant::DB_TABLE_STAR);
            data_set($header, '折扣率', 'discount');
            data_set($header, '产品描述', Constant::DB_TABLE_DES);
        }

        $requestData[Constant::REQUEST_PAGE_SIZE] = 20000; //
        $requestData[Constant::REQUEST_PAGE] = 1;

        $service = ActivityProductService::getNamespaceClass();
        $method = 'getDealList';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * deal 一次性 coupon 产品导入
     * @return string
     */
    public function import(Request $request) {

        ini_set($this->memoryLimit, '2048M');

        if (!$request->file()) {//$_FILES || current($_FILES)['error']
            return Response::json([], 10031, '文件不能为空或有错误');
        }

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $mb_type = $request->input(Constant::DB_TABLE_MB_TYPE, Constant::PARAMETER_STRING_DEFAULT); //模板类型 0 未选择 1 新品 2 常规 3 主推
        $user = $request->input(Constant::DB_TABLE_OPERATOR, Constant::PARAMETER_STRING_DEFAULT);
        $actID = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $time = Carbon::now()->toDateTimeString();

//        $filekey = current(array_keys($request->file()));
//        $file = $request->file($filekey);

        $file = current($request->file());

        $typeData = [
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
        ];

        $nextRowCallback = null;
        $nextCellCallback = null;
//        $nextCellCallback = function ($row, $cell, $cellData, $sheetName = null) {
//            dump('cell:' . $cell . ', row:' . $row . ', value:' . $cellData, $sheetName);
//        };
        $data = ExcelService::parseExcelFile($file->getRealPath(), $typeData, $nextRowCallback, $nextCellCallback);
        if (isset($data[0])) {
            $name = trim(data_get($data, '0.1', Constant::PARAMETER_STRING_DEFAULT)); //产品标题
            if ($name != '产品标题') {
                return Response::json([], 0, '请检查上传文件的模板正确！');
            }
            unset($data[0]); //删除excel表中的表头数据
        }

        $tableData = ActivityProductService::convToTableData($data, $mb_type, $user, $time, $actID);
        unset($data);

        $retData = ActivityProductService::addBatch($storeId, $tableData);
        if ($retData[Constant::RESPONSE_CODE_KEY] != 1) {
            return Response::json([], 10020, $retData[Constant::RESPONSE_MSG_KEY]);
        }

        return Response::json($retData[Constant::RESPONSE_DATA_KEY], 1, $retData[Constant::RESPONSE_MSG_KEY]);
    }

    /**
     * deal 通用coupon 产品模板导入
     * @return string
     */
    public function importUniversal(Request $request) {

        ini_set($this->memoryLimit, '2048M');

        if (!$request->file()) {
            return Response::json([], 10031, '文件不能为空或有错误');
        }

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT);
        $mb_type = $request->input(Constant::DB_TABLE_MB_TYPE, Constant::PARAMETER_STRING_DEFAULT); //模板类型 0 未选择 1 新品 2 常规 3 主推 4:通用
        $user = $request->input(Constant::DB_TABLE_OPERATOR, Constant::PARAMETER_STRING_DEFAULT);
        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT);
        $time = Carbon::now()->toDateTimeString();

//        $filekey = current(array_keys($request->file()));
//        $file = $request->file($filekey);

        $file = current($request->file());

        $realName = $file->getFileName();
        $file->move(storage_path('logs'), $realName);
        $excelPath = storage_path('logs');
        $config = ['path' => $excelPath];

        $retData = ActivityProductService::dealImportData($config, $realName, $storeId, $mb_type, $user, $time, $actId);

        $parameters = Response::getResponseData($retData);

        return Response::json(...$parameters);
    }

    /**
     * 导入 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 活动产品
     * @return string
     */
    public function importActProduct(Request $request) {

        ini_set($this->memoryLimit, '1024M');

        $actId = $request->input(Constant::DB_TABLE_ACT_ID, Constant::PARAMETER_INT_DEFAULT); //活动形式 1:抽奖 2:邀请助力 3:众测 4:投票 5:排名 6:其他
        $storeId = $request->input($this->storeIdKey, 0);
        $user = $request->input(Constant::DB_TABLE_OPERATOR, Constant::PARAMETER_STRING_DEFAULT);

        if (empty($actId) || empty($storeId)) {
            return Response::json([], 0, '非法参数');
        }

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {

            $parameters = Response::getResponseData($fileData);

            return Response::json(...$parameters);
        }

        $fileFullPath = data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, '');
        $data = ActivityProductService::importActProduct($storeId, $actId, $fileFullPath, $user, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 活动产品列表 1:九宫格 2:转盘 3:砸金蛋 4:翻牌 5:邀请好友注册 活动产品
     * @return string
     */
    public function actProductList(Request $request) {

        $data = ActivityProductService::getListData($request->all());

        return Response::json($data, 1, '');
    }

    /**
     * 导出活动产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportActProducts(Request $request) {

        $requestData = $request->all();
        $header = [
            '活动名称' => 'act_' . Constant::DB_TABLE_NAME,
            '活动类型' => Constant::DB_TABLE_ACT_TYPE . $this->showKey,
            '产品名称' => Constant::DB_TABLE_NAME,
            '产品图片' => Constant::DB_TABLE_IMG_URL,
            '总库存' => Constant::DB_TABLE_QTY,
            '剩余库存' => 'last_qty',
            '产品类别' => Constant::DB_TABLE_TYPE . $this->showKey,
            '活动时间' => 'act_time',
            'distinctField' => [
                'primaryKey' => Constant::DB_TABLE_PRIMARY,
                'primaryValueKey' => Constant::DB_TABLE_PRIMARY,
                'select' => [
                    'ap.' . Constant::DB_TABLE_PRIMARY,
                    'p.' . Constant::DB_TABLE_PRIMARY . ' as ' . Constant::DB_TABLE_PRODUCT_ID,
                ]
            ],
        ];

        $requestData[Constant::REQUEST_PAGE_SIZE] = 20000; //
        $requestData[Constant::REQUEST_PAGE] = 1;
        data_set($requestData, 'isGetQuery', false); //设置获取处理后的结果，默认是:true 获取查询句柄，不执行数据处理

        $service = ActivityProductService::getNamespaceClass();
        $method = 'getListData';
        $select = [];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 活动产品删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delActProducts(Request $request) {

        $ids = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_ARRAY_DEFAULT); //产品id
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID
        $data = ActivityProductService::delActProducts($storeId, $ids);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 获取活动产品Items
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActProductItems(Request $request) {

        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_ARRAY_DEFAULT); //产品id
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID

        $data = ActivityProductService::getActProductItems($storeId, $id);

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 编辑活动产品Items
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editActProductItems(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID

        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_INT_DEFAULT); //产品id
        $itemData = $request->input('item_data', Constant::PARAMETER_ARRAY_DEFAULT); //产品 item 数据

        $data = ActivityProductService::editActProductItems($storeId, $id, $itemData, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 删除活动产品item
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delActProductItems(Request $request) {

        $id = $request->input(Constant::DB_TABLE_PRIMARY, Constant::PARAMETER_ARRAY_DEFAULT); //产品id
        $itemIds = $request->input('item_id', Constant::PARAMETER_ARRAY_DEFAULT); //产品item id
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, Constant::PARAMETER_INT_DEFAULT); //商店ID
        $data = ActivityProductService::delActProductItems($storeId, $id, $itemIds, $request->all());

        $parameters = Response::getResponseData($data);

        return Response::json(...$parameters);
    }

    /**
     * 评测产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexFreeTesting(Request $request) {
        $requestData = $request->all();
        data_set($requestData, Constant::DB_TABLE_SOURCE, 'admin');
        $select = [
            'activity_products.' . Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_SORT,
            Constant::DB_TABLE_NAME,
            Constant::FILE_URL,
            Constant::DB_TABLE_IMG_URL,
            Constant::DB_TABLE_MB_IMG_URL,
            Constant::DB_TABLE_SHOP_SKU,
            Constant::DB_TABLE_SKU,
            Constant::DB_TABLE_ASIN,
            Constant::DB_TABLE_QTY,
            Constant::DB_TABLE_QTY_APPLY,
            'show_apply',
            'price_source',
            Constant::DB_TABLE_REGULAR_PRICE,
            Constant::DB_TABLE_LISTING_PRICE,
            Constant::DB_TABLE_PRODUCT_STATUS,
            Constant::EXPIRE_TIME,
            'activity_products.' . Constant::DB_TABLE_CREATED_AT,
            'activity_products.' . Constant::DB_TABLE_UPDATED_AT,
            'uploader',
            'activity_products.' . Constant::DB_TABLE_COUNTRY . ' as product_country',
            Constant::DB_TABLE_DES . ' as product_des',
        ];
        $data = ActivityProductService::getFreeTestingList($requestData, true, true, $select);
        return Response::json($data);
    }

    /**
     * 评测产品编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editFreeTesting(Request $request) {
        $requestData = $request->all();
        $storeId = $this->storeId;
        $ids = $request->input('id', Constant::PARAMETER_ARRAY_DEFAULT);
        if (empty($storeId) || empty($ids)) {
            return Response::json([], -1);
        }
        $data = ActivityProductService::editFreeTesting($storeId, $ids, $requestData);
        return Response::json($data);
    }

    /**
     * 评测产品导入
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importFreeTestingProducts(Request $request) {
        $storeId = $request->input($this->storeIdKey, 0);
        $user = $request->input(Constant::DB_TABLE_OPERATOR, Constant::PARAMETER_STRING_DEFAULT);
        $requestData = $request->all();
        $data = ActivityProductService::importFreeTestingProducts($storeId, $user, $requestData);
        return Response::json($data);
    }

    /**
     * 评测产品导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportFreeTestingProducts(Request $request) {
        $requestData = $request->all();
        data_set($requestData, Constant::DB_TABLE_SOURCE, 'admin');
        data_set($requestData, 'is_export', 1);
        $header = [
            '产品图片' => Constant::DB_TABLE_IMG_URL,
            '产品标题' => Constant::DB_TABLE_NAME,
            '产品描述' => Constant::DB_TABLE_DES,
            '店铺sku' => 'export_sku',
            '产品asin' => Constant::DB_TABLE_ASIN,
            '产品国家' => Constant::DB_TABLE_COUNTRY,
            '产品价格' => Constant::DB_TABLE_LISTING_PRICE,
            '所需评测数' => Constant::DB_TABLE_QTY,
            '实际用户申请数' => Constant::DB_TABLE_QTY_APPLY,
            '显示用户申请数' => 'show_apply',
            '产品状态' => Constant::DB_TABLE_PRODUCT_STATUS,
            '申请截止时间' => Constant::EXPIRE_TIME,
            '产品上传时间' => Constant::DB_TABLE_CREATED_AT,
            '产品更新时间' => Constant::DB_TABLE_UPDATED_AT,
            Constant::EXPORT_DISTINCT_FIELD => [
                Constant::EXPORT_PRIMARY_KEY => Constant::DB_TABLE_PRIMARY,
                Constant::EXPORT_PRIMARY_VALUE_KEY => 'activity_products.' . Constant::DB_TABLE_PRIMARY,
                Constant::DB_EXECUTION_PLAN_SELECT => ['activity_products.' . Constant::DB_TABLE_PRIMARY]
            ],
        ];

        $service = ActivityProductService::getNamespaceClass();
        $method = 'getFreeTestingList';
        $select = [
            'activity_products.' . Constant::DB_TABLE_PRIMARY,
            Constant::DB_TABLE_SORT,
            Constant::DB_TABLE_NAME,
            Constant::FILE_URL,
            Constant::DB_TABLE_IMG_URL,
            Constant::DB_TABLE_MB_IMG_URL,
            Constant::DB_TABLE_SKU,
            Constant::DB_TABLE_SHOP_SKU,
            Constant::DB_TABLE_ASIN,
            Constant::DB_TABLE_QTY,
            Constant::DB_TABLE_QTY_APPLY,
            'show_apply',
            'price_source',
            Constant::DB_TABLE_REGULAR_PRICE,
            Constant::DB_TABLE_LISTING_PRICE,
            Constant::DB_TABLE_PRODUCT_STATUS,
            Constant::EXPIRE_TIME,
            'activity_products.' . Constant::DB_TABLE_CREATED_AT,
            'activity_products.' . Constant::DB_TABLE_UPDATED_AT,
            'uploader',
        ];
        $parameters = [$requestData, true, true, $select, false, false];
        $countMethod = $method;
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters);

        return Response::json([Constant::FILE_URL => $file]);
    }
}
