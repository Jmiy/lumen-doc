<?php

/**
 * 积分服务
 * User: Jmiy
 * Date: 2019-05-16
 * Time: 16:50
 */

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Util\Cache\CacheManager as Cache;
use App\Models\User;

class UserService extends BaseService {

    public static $secret = 'ZXCJS#sds*';

    /**
     * 检查是否存在
     * @param int $storeId 商城id
     * @param int $userId  用户id
     * @param string $username 账号
     * @param bool $getData 是否获取数据 true:是 false:否 默认:false
     * @return mixed|static
     */
    public static function exists($storeId = 0, $userId = 0, $username = '', $getData = false) {

        $where = [];

        if ($storeId) {
            $where['store_id'] = $storeId;
        }

        if ($userId) {
            $where['id'] = $userId;
        }

        if ($username) {
            $where['username'] = $username;
        }

        if (empty($where)) {
            return $getData ? null : true;
        }

        $query = User::where($where);
        if ($getData) {
            $rs = $query->first();
        } else {
            $rs = $query->exists();
        }

        return $rs;
    }

    /**
     * 获取token
     * @param string $username 账号
     * @param string|int $time 时间戳
     * @return string
     */
    public static function getToken($username, $time = '') {

        static::$secret = config('app.token_secret', static::$secret);
        $time = $time ? $time : time();
        $prefix = substr(($time + 6879), -4, -1) * 3;
        $mid = intval(substr($time, 2, 6)) * 3;

        $token = md5(md5($prefix . $mid . md5(self::$secret)) . $username) . '_' . $time;

        return $token;
    }

    /**
     * 验证token
     * @param string $username 账号
     * @param string $token token
     * @return array $result
     */
    public static function checkToken($username, $token) {

        $result = ['code' => 19119, 'msg' => '', 'data' => []];
        if (!$token) {
            $result['msg'] = 'token 错误';
            return $result;
        }
        static::$secret = config('app.token_secret', static::$secret);

        list($flagstr, $time) = explode('_', $token);

        $expire = config('app.token_expire', 60 * 60 * 12);
        if ((time() - $time) < 0 && abs(time() - $time) < ($expire)) {
            $result['msg'] = 'token 超时';
            return $result;
        }

        $server_token = static::getToken($username, $time);
        if ($server_token == $token) {
            $result['code'] = 1;
        } else {
            $result['msg'] = 'token error';
        }
        return $result;
    }

    /**
     * 更新token
     * @param int $id 用户id
     * @param string $token token
     * @param string $oldToken 旧token
     * @return boolean
     */
    public static function updateToken($id, $token, $oldToken = '') {

        $where = [];

        if ($id) {
            $where[] = ['id', '=', $id];
        }

        if ($oldToken) {
            $where[] = ['api_token', '=', $oldToken];
        }

        if (empty($where)) {
            return true;
        }

        return User::where($where)->update(['api_token' => $token]);
    }

    /**
     * 获取db query
     * @param int $storeId 商城id
     * @param array $where
     * @return \Illuminate\Database\Query\Builder|static $query
     */
    public static function getQuery($storeId, $where = []) {
        $query = User::where($where);
        return $query;
    }

    /**
     * 获取公共参数
     * @param array $params 请求参数
     * @param array $order 排序控制
     * @return array
     */
    public static function getPublicData($params, $order = []) {

        $where = [];

        $username = $params['username'] ?? ''; //名称
        $storeId = $params['store_id'] ?? ''; //商城id

        if ($storeId) {
            $where[] = ['store_id', '=', $storeId];
        }

        if ($username) {
            $where[] = ['username', '=', $username];
        }

        $order = $order ? $order : ['id', 'DESC'];
        return Arr::collapse([parent::getPublicData($params, $order), [
                        'where' => $where,
        ]]);
    }

    /**
     * 列表
     * @param array $params 请求参数
     * @param boolean $toArray 是否转化为数组 true:是 false:否 默认:true
     * @param boolean $isPage  是否分页 true:是 false:否 默认:true
     * @param array $select  查询字段
     * @param boolean $isRaw 是否原始 select true:是 false:否 默认:false
     * @param boolean $isGetQuery 是否获取 query
     * @param boolean $isOnlyGetCount 是否仅仅获取总记录数
     * @return array|\Illuminate\Database\Eloquent\Builder 列表数据|Builder
     */
    public static function getListData($params, $toArray = true, $isPage = true, $select = [], $isRaw = false, $isGetQuery = false, $isOnlyGetCount = false) {

        $_data = static::getPublicData($params);

        $where = $_data['where'];
        $order = $_data['order'];
        $pagination = $_data['pagination'];
        $limit = $pagination['page_size'];

        $customerCount = true;
        $storeId = Arr::get($params, 'store_id', 0);
        $query = static::getQuery($storeId, $where);
        if ($isPage || $isOnlyGetCount) {
            $customerCount = $query->count();
            $pagination['total'] = $customerCount;
            $pagination['total_page'] = ceil($customerCount / $limit);

            if ($isOnlyGetCount) {
                return $pagination;
            }
        }

        if (empty($customerCount)) {
            $query = null;
            return [
                'data' => [],
                'pagination' => $pagination,
            ];
        }

        $query = $query->orderBy($order[0], $order[1]);
        $data = [
            'query' => $query,
            'pagination' => $pagination,
        ];

        //static::createModel($storeId, 'VoteItem')->getConnection()->enableQueryLog();
        //var_dump(static::createModel($storeId, 'VoteItem')->getConnection()->getQueryLog());
        $select = $select ? $select : ['*'];
        $data = static::getList($data, $toArray, $isPage, $select, $isRaw, $isGetQuery);

        if ($isGetQuery) {
            return $data;
        }

        $statusData = DictService::getListByType('status', 'dict_key', 'dict_value');
        foreach ($data['data'] as $key => $row) {

            $field = [
                'field' => 'status',
                'data' => $statusData,
                'dataType' => '',
                'default' => $data['data'][$key]['status'],
            ];
            $data['data'][$key]['status'] = FunctionHelper::handleData($row, $field);
        }

        return $data;
    }

    /**
     * 添加/编辑
     * @param array $where where条件
     * @param array $data  数据
     * @param array $roleData  角色数据
     * @return int 添加记录的id|更新的记录条数
     */
    public static function insert($where, $data, $roleData = []) {
        $nowTime = Carbon::now()->toDateTimeString();

        $data['created_at'] = DB::raw("IF(created_at='2019-01-01 00:00:00','$nowTime',created_at)");
        $data['updated_at'] = $nowTime;

//        \Illuminate\Support\Facades\DB::enableQueryLog();
//        var_dump(\Illuminate\Support\Facades\DB::getQueryLog());
        if ($where) {
            $id = Arr::get($where, 'id', 0); //角色id
            User::where($where)->update($data); //updateOrCreate：不可以添加主键id的值  updateOrInsert：可以添加主键id的值
            if ($id) {
                UserRole::where('user_id', $id)->delete();
            }
        } else {
            $id = User::insertGetId($data); //updateOrCreate：不可以添加主键id的值  updateOrInsert：可以添加主键id的值
        }

        $userRoleData = [];
        foreach ($roleData as $role_id) {
            $userRoleData[] = [
                'user_id' => $id,
                'role_id' => $role_id,
            ];
        }
        UserRole::insert($userRoleData);

        return $id;
    }

    /**
     * 删除记录
     * @param int $storeId 商城id
     * @param array $ids id
     * @return int 删除的记录条数
     */
    public static function delete($storeId, $ids) {
//        $connection = User::getConnection();
//        $connection->enableQueryLog();
//        var_dump($connection->getQueryLog());

        $id = User::whereIn('id', $ids)->delete();

        UserRole::whereIn('user_id', $ids)->delete();

        return $id;
    }

    /**
     * 重置密码
     * @param int $id 用户id
     * @param string $password 密码
     * @return boolean|int 更新的记录条数
     */
    public static function resetPassword($id, $password) {

        if (empty($id)) {
            return true;
        }

        $data = [
            'password' => encrypt($password),
            'pwdmd5' => md5($password),
        ];
        return User::where('id', $id)->update($data);
    }

}
