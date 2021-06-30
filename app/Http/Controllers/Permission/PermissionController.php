<?php

namespace App\Http\Controllers\Permission;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\PermissionService;

class PermissionController extends Controller {

    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $data = PermissionService::getListData($request->all());
        return Response::json($data);
    }

    /**
     * 详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $id = $request->input('id', '');
        $result = PermissionService::exists($id, true);

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

        $parentIds = $request->input('parent_ids', '0');
        $_parentIds = explode(',', $parentIds);
        $data = [
            'name' => $request->input('name', ''),
            'url' => $request->input('url', ''),
            'type' => $request->input('type', 1),
            'status' => $request->input('status', 1),
            'component' => $request->input('component', ''),
            'router' => $request->input('router', ''),
            'icon' => $request->input('icon', ''),
            'parent_id' => Arr::last($_parentIds),
            'parent_ids' => $parentIds,
            'sort' => $request->input('sort', 0),
            'is_show' => $request->input('is_show', 1),
            'show_name' => $request->input('show_name', ''),
            'number' => $request->input('number', ''),
        ];
        PermissionService::insert($where, $data);

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
        $storeId = $request->input('store_id', 0); //商城ID  1：mpow; 2:vt
        $ids = $request->input('ids', 0); //产品ID

        if (empty($storeId) || empty($ids)) {
            return Response::json([], 10014, '删除失败');
        }

        PermissionService::delete($storeId, $ids);

        return Response::json();
    }

    /**
     * 下拉
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request) {
        $storeId = $request->input('store_id', 0); //商城ID
        $parentId = $request->input('parent_id', 0); //直属父级id

        if (empty($storeId)) {
            return Response::json([], 10014, '请求参数错误');
        }

        $data = PermissionService::select($storeId, $parentId);

        return Response::json($data);
    }

}
