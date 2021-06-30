<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\CouponService;
use App\Services\ExcelService;
use App\Util\Constant;
use App\Util\Cdn\CdnManager;
use App\Util\FunctionHelper;

class CouponController extends Controller {

    public $memoryLimit = 'memory_limit';

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $request->offsetSet('source', 'admin');
        $data = CouponService::getListData($request->all());
        return Response::json($data);
    }

    /**
     * 列表导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {

        $request->offsetSet('source', 'admin');

        $header = [
            '兑换码' => 'code',
            '类型' => 'type',
            '国家' => 'country',
            '状态' => 'status',
            '创建时间' => 'ctime',
            '发送时间' => 'mtime',
            'distinctField' => [
                'primaryKey' => 'id',
                'primaryValueKey' => 'id',
                'select' => ['id']
            ],
        ];

        $requestData = $request->all();
        $requestData['page_size'] = 20000; //
        $requestData['page'] = 1;

        $service = CouponService::getNamespaceClass();
        $method = 'getListData';
        $select = ['id', 'code', 'type', 'country', 'status', 'ctime', 'mtime'];
        $parameters = [$requestData, true, true, $select, false, false];

        $countMethod = 'getListData';
        $countParameters = Arr::collapse([$parameters, [true]]);
        $file = ExcelService::createCsvFile($header, $service, $countMethod, $countParameters, $method, $parameters); //1000

        return Response::json(['url' => $file]);
    }

    /**
     * 优惠券导入
     * @return string
     */
    public function import(Request $request) {

        ini_set($this->memoryLimit, '2048M');

        if (!$request->file()) {//$_FILES || current($_FILES)['error']
            return Response::json([], 10031, '文件不能为空或有错误');
        }

        $storeId = $request->input('store_id', 0);

//        $filekey = current(array_keys($request->file()));
//        $file = $request->file($filekey);

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            $parameters = Response::getResponseData($fileData);
            return Response::json(...$parameters);
        }

        $typeData = [
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_TIMESTAMP,
            \Vtiful\Kernel\Excel::TYPE_TIMESTAMP,
        ];
        $data = ExcelService::parseExcelFile(data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, ''), $typeData); //$file->getRealPath()

        if (isset($data[0])) {
            unset($data[0]); //删除excel表中的表头数据
        }

        $dataBatch = array_chunk($data, 2000);
        $service = CouponService::getNamespaceClass();
        $method = 'addBatch';
        foreach ($dataBatch as $_data) {
            $parameters = [$storeId, $_data];
            FunctionHelper::pushQueue(FunctionHelper::getJobData($service, $method, $parameters), null, '{data-import}');
        }

        return Response::json([], 1, count($data) . ' 条数据上传成功，正在写入系统，大概需要 3 分钟完成');
    }

    /**
     * VT deal coupon列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealList(Request $request) {

        $data = CouponService::getListDeal($request->all());
        return Response::json($data);
    }

    /**
     * VT deal 优惠券导入
     * @return string
     */
    public function importDeal(Request $request) {

        ini_set($this->memoryLimit, '1024M');

        $storeId = $request->input($this->storeIdKey, 0);
        $type = $request->input('use_type', 0);
        if (empty($type)) {
            return Response::json([], 10055, 'code类型不允许不选择');
        }

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            $parameters = Response::getResponseData($fileData);
            return Response::json(...$parameters);
        }

//        $filekey = current(array_keys($_FILES));
//        $file = $request->file($filekey);
//        $realName = $file->getFileName();
//        $file->move(storage_path('logs'), $realName);
//        $excelPath = storage_path('logs');
//        $config = ['path' => $excelPath];

        $retData = CouponService::dealCouponImport(data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, ''), $storeId, $type);

        return Response::json($retData['data'], 1, $retData['msg']);
    }

}
