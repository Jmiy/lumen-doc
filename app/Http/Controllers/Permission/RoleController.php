<?php

namespace App\Http\Controllers\Permission;

use Illuminate\Http\Request;
use App\Util\Response;
use App\Services\RoleService;
use App\Util\Constant;

class RoleController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $data = RoleService::getListData($request->all());
        return Response::json($data);
    }

    /**
     * 详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, '');
        $id = $request->input('id', '');
        $result = RoleService::getQuery($storeId, ['id' => $id])->first();

        return Response::json($result);
    }

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request) {

        $id = $request->input('id', '');

        $where = [];
        if ($id) {
            $where = ['id' => $id];
        }

        $data = [
            'name' => $request->input('name', ''),
            Constant::DB_TABLE_STORE_ID => $request->input(Constant::DB_TABLE_STORE_ID, ''),
        ];
        $permissionData = $request->input('permissions', []);
        RoleService::insert($where, $data, $permissionData);

        return Response::json();
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request) {
        return $this->insert($request);
    }

    /**
     * 删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城ID  1：mpow; 2:vt
        $ids = $request->input('ids', 0); //产品ID

        if (empty($storeId) || empty($ids)) {
            return Response::json([], 10014, '删除失败');
        }

        RoleService::delete($storeId, $ids);

        return Response::json();
    }

    /**
     * 下拉
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request) {
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0); //商城ID

        if (empty($storeId)) {
            return Response::json([], 10014, '请求参数错误');
        }

        $data = RoleService::select($storeId);

        return Response::json($data);
    }

}
