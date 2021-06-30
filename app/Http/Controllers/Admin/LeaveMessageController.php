<?php

namespace App\Http\Controllers\Admin;

use App\Services\ExcelService;
use App\Services\LeaveMessageService;
use App\Util\Cdn\CdnManager;
use App\Util\Constant;
use App\Util\FunctionHelper;
use App\Util\Response;
use Illuminate\Http\Request;

class LeaveMessageController extends Controller {

    /**
     * 订单索评列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {

        $data = LeaveMessageService::getListData($request->all());

        return Response::json($data);
    }

    /**
     * 导出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request) {
        $requestData = $request->all();
        data_set($requestData, 'is_export', 1);
        $data = LeaveMessageService::export($requestData);
        return Response::json($data);
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城ID  1：mpow; 2:vt
        $id = $request->input(Constant::DB_TABLE_PRIMARY, 0); //产品ID

        if (empty($storeId) || empty($id)) {
            return Response::json([], 10014, '删除失败');
        }

        $where = ['id' => $id];
        LeaveMessageService::delete($storeId, $where);

        return Response::json();
    }

    /**
     * 导入
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request) {

        ini_set('memory_limit', '2048M');

        if (!$request->file()) {//$_FILES || current($_FILES)['error']
            return Response::json([], 10031, '文件不能为空或有错误');
        }

        $storeId = $request->input('store_id', 0);

        $fileData = CdnManager::upload(Constant::UPLOAD_FILE_KEY, $request, '/upload/file/');
        if (data_get($fileData, Constant::RESPONSE_CODE_KEY, 0) != 1) {
            $parameters = Response::getResponseData($fileData);
            return Response::json(...$parameters);
        }

        $typeData = [
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_STRING,
            \Vtiful\Kernel\Excel::TYPE_TIMESTAMP,
            \Vtiful\Kernel\Excel::TYPE_STRING,
        ];
        $data = ExcelService::parseExcelFile(data_get($fileData, Constant::RESPONSE_DATA_KEY . Constant::LINKER . Constant::FILE_FULL_PATH, ''), $typeData); //$file->getRealPath()

        if (isset($data[0])) {
            unset($data[0]); //删除excel表中的表头数据
        }

        $rs = LeaveMessageService::import($storeId,$data);

        return Response::json(...Response::getResponseData($rs));

    }
}
