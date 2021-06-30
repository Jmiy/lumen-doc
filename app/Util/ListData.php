<?php

namespace App\Util;

class ListData {

    /**
     * 获取需要查询的字段
     * @param string $table 表名或者别名
     * @param array $addColumns 额外要查询的字段 array('s.softid','s.file1024_md5 f1024md5')
     * @return string app列表需要的字段
     */
    public static function getColumns($columns, $addColumns = array()) {

        $columns = array_merge($columns, $addColumns);
        $columns = array_filter(array_unique($columns));
        foreach ($columns as $key => $val) {
            if (is_numeric($val)) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

    /**
     * 获取有效数据
     * @return string|array
     */
    public static function getValidData($data = null, $name = null, $default = null) {

        return \Illuminate\Support\Arr::get($data, $name, $default);

//        if (empty($data)) {
//            return $default;
//        }
//
//        return $name === null ? $data : (isset($data[$name]) ? $data[$name] : $default);
    }

    /**
     * 获取产品列表数据
     * @param Illuminate\Database\Query\Builder $builder 查询构造器
     * @param int|null $page  分页  页码
     * @param int|null $pageSize  分页 每页记录条数
     * @param int|null $medium    兴趣
     * @param array $cateIds      分类id数据
     * @param boolean $isNeedModule  是否需要模块数据  true：需要  false:不需要  默认：true
     * @return array 产品列表数据
     */
    public static function getProductListData($builder, $page = null, $pageSize = null, $medium = null, $cateIds = [], $isNeedModule = true) {

        if ($page && $pageSize) {
            $offset = ($page - 1) * $pageSize;
            $builder = $builder->offset($offset);
        }

        if ($pageSize) {
            $builder = $builder->limit($pageSize);
        }

        //\Illuminate\Support\Facades\DB::enableQueryLog();
        $productData = $builder->with('advance_promotion')
                ->get()
                ->each(function ($item, $key) {
                    if ($item->advance_promotion) {
                        $item->skus = null;
                        $item->quantity_skill = $item->advance_promotion->quantity_skill;
                        $item->price_skill = $item->advance_promotion->price_skill;
                        return \App\Model\Topic_promotion::handleProduct($item);
                    } else {
                        return $item;
                    }
                })
                ->keyBy('product_id')
                ->toArray();
        $_data = array_values($productData);

        if ($isNeedModule) {//如果需要模块数据，就获取模块数据
            //获取模块数据
            $moduleData = \App\Model\Module::getMoudelsProductImages($cateIds, $page, $pageSize, $medium, 4, 1);

            $is_insert = false;
            foreach ($moduleData as $key => $item) {
                if ($item['sort'] > 0) {
                    $is_insert = true;
                    $_offset = $item['sort'] - $offset - 1;
                    array_splice($_data, $_offset, 0, [$item]); //将
                }
            }

            if (!$is_insert) {
                $offset = count($_data);
                array_splice($_data, $offset, 0, $moduleData); //将广告数据插入到最后面
            }
        }

        $data['data'] = $_data;
        $data['prod_id'] = implode(',', array_keys($productData));

        return $data;
    }

    /**
     * 获取统一的列表数据
     * @param array $data
     * @return array 统一的列表数据
     */
    public static function getListData($data) {

        if (empty($data['data'])) {
            return $data;
        }

        $now = \Carbon\Carbon::now()->toDateTimeString(); //服务器当前时间
        foreach ($data['data'] as $key => $item) {
            if ($item['data_type'] == 'product') {//如果是产品数据，就根据秒杀活动调整价格
                if ($item['advance_promotion'] && $item['advance_promotion']['start_at'] <= $now && $now <= $item['advance_promotion']['end_at']) {
                    $data['data'][$key]['special'] = $item['shop_price'];
                    $data['data'][$key]['price'] = $item['market_price'];
                }
                $data['data'][$key]['discount'] = $data['data'][$key]['price'] ? (($data['data'][$key]['special'] - $data['data'][$key]['price']) / $data['data'][$key]['price']) : 0;
            }
        }

        $handleKeys = [
            1 => ['image'], //图片
            //100 => [],//html
            200 => ['price', 'special'], //价格
            201 => ['discount'], //折扣
        ];
        $data = \App\Util\Resources::handleResources($data, $handleKeys); //图片cdn加速

        return $data;
    }

    public static function myHash($str) {
        // hash(i) = hash(i-1) * 33 + str[i]
        $hash = 0;
        $s = md5($str);
        $seed = 5;
        $len = 32;
        for ($i = 0; $i < $len; $i++) {
            // (hash << 5) + hash 相当于 hash * 33
            //$hash = sprintf("%u", $hash * 33) + ord($s{$i});
            //$hash = ($hash * 33 + ord($s{$i})) & 0x7FFFFFFF;
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }

        return $hash & 0x7FFFFFFF;
    }

    // server列表
    public static $_server_list = array();
    // 延迟排序，因为可能会执行多次addServer
    public static $_layze_sorted = FALSE;

    public static function addServer($server) {
        $hash = static::myHash($server);
        static::$_layze_sorted = FALSE;

        if (!isset(static::$_server_list[$server])) {
            static::$_server_list[$server] = $hash;
        }

        return static::$_server_list;
    }

    public static function find($key) {
        // 排序
        if (!static::$_layze_sorted) {
            arsort(static::$_server_list);
            static::$_layze_sorted = TRUE;
        }

        $hash = static::myHash($key);
        $len = sizeof(static::$_server_list);
        if ($len == 0) {
            return FALSE;
        }

        $keys = array_keys(static::$_server_list);
        $values = array_values(static::$_server_list);

        // 如果不在区间内，则返回最后一个server
        if ($hash <= $values[0]) {
            return $keys[0];
        }

        if ($hash >= $values[$len - 1]) {
            return $keys[$len - 1];
        }

        foreach ($values as $key => $pos) {
            $next_pos = NULL;
            if (isset($values[$key + 1])) {
                $next_pos = $values[$key + 1];
            }

            if (is_null($next_pos)) {
                return $keys[$key];
            }

            // 区间判断
            if ($hash >= $pos && $hash <= $next_pos) {
                return $keys[$key];
            }
        }
    }

}
