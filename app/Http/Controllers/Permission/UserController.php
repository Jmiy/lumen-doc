<?php

namespace App\Http\Controllers\Permission;

use App\Services\Psc\ServiceManager;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Util\Response;
use App\Services\UserService;
use App\Util\Cache\CacheManager as Cache;
use App\Util\Constant;

class UserController extends Controller {

    public function login(Request $request) {

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $username = $request->input(Constant::USERNAME, '');
        $getData = true;
        $password = $request->input(Constant::DB_TABLE_PASSWORD, '');

        if (empty($username) || empty($password)) {
            return Response::json([], '10001', '账号密码不能为空');
        }

        $data = UserService::exists(0, 0, $username, $getData);
        if (empty($data)) {
            return Response::json([], '10002', '账号不存在');
        }

        $stores = explode(',', $data->store_id);
        if (!in_array($storeId, $stores)) {
            return Response::json([], '10011', '请重新选择官网');
        }

        if (md5($password) != $data->pwdmd5) {
            return Response::json([], '10003', '密码错误');
        }

        unset($data->pwdmd5);
        unset($data->password);
        $oldToken = $data->token;
        $data->token = UserService::getToken($username);
        $data->store_id = $storeId;

        //更新token
        UserService::updateToken($data->id, $data->token);

        //清空用户认证缓存
        $tags = config('cache.tags.adminAuth', ['{adminAuth}']);
        Cache::tags($tags)->forget($oldToken);

        return Response::json($data);
    }

    /**
     * 用户退出登录
     * @param Request $request
     * @return type
     */
    public function logout(Request $request)
    {
        //清空用户认证缓存
        $oldToken = $request->headers->get('X-Token', '');
        if ($oldToken) {

            //清空token
            UserService::updateToken(0, '', $oldToken);

            $tags = config('cache.tags.adminAuth', ['{adminAuth}']);
            Cache::tags($tags)->forget($oldToken);
        }

        $isPsc = $request->input('is_psc');//是否为权限系统

        return Response::json($isPsc ? ServiceManager::handle(Constant::PLATFORM_SERVICE_PATOZON, 'User', 'singleSignOff', [$oldToken]) : []);
    }

    /**
     * 用户基本信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request) {

        $isPsc = $request->input('is_psc');

        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $user = $request->user('apiAdmin');

        if(!$isPsc){
            $roleData = StoreService::getModel()->pluck('name', 'id');

            $user->roles = [Arr::get($roleData, $storeId, Arr::get($roleData, 1, ''))];
            unset($user->pwdmd5);
            unset($user->password);
            $user->store_id = $storeId;
        }

        return Response::json($user);
    }

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request) {

        $id = $request->input('id', '');
        $storeId = $request->input(Constant::DB_TABLE_STORE_ID, 0);
        $username = $request->input(Constant::USERNAME, '');

        $where = [];

        $password = $request->input(Constant::DB_TABLE_PASSWORD, '');
        $data = [
            Constant::USERNAME => $request->input(Constant::USERNAME, ''),
            Constant::DB_TABLE_PASSWORD => encrypt($password),
            'pwdmd5' => md5($password),
            'email' => $request->input('email', ''),
            'type' => $request->input('type', 1),
        ];

        if ($id) {
            $where = ['id' => $id];
            unset($data[Constant::DB_TABLE_PASSWORD]);
            unset($data['pwdmd5']);
        } else {
            $isExists = UserService::exists($storeId, 0, $username);
            if ($isExists) {
                return Response::json([], 2, '账号已存在');
            }
        }


        $roleData = $request->input('roles', []);
        UserService::insert($where, $data, $roleData);

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

        if (empty($ids)) {
            return Response::json([], 10014, '删除失败');
        }

        UserService::delete($storeId, $ids);

        return Response::json();
    }

    /**
     * 重置密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request) {
        $id = $request->input('id', 0); //用户id

        if (empty($id)) {
            return Response::json([], 10014, '请求参数错误');
        }

        $password = $request->input(Constant::DB_TABLE_PASSWORD, '');
        $data = UserService::resetPassword($id, $password);

        return Response::json($data);
    }

}
