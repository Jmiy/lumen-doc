<?php

namespace App\Services;

use App\Util\Constant;
use App\Util\FunctionHelper;
use Illuminate\Support\Arr;

class ActivityStatService extends BaseService {

    /**
     * 公共参数
     * @param array $params
     * @param array $order
     * @return array
     */
    public static function getPublicData($params, $order = []) {

        $where = [];

        $actId = $params[Constant::DB_TABLE_ACT_ID] ?? ''; //活动id
        $categoryId = $params[Constant::CATEGORY_ID] ?? ''; //商品类目
        $inStock = $params[Constant::IN_STOCK] ?? ''; //是否有货
        $alias = $params['alias'] ?? '';

        if ($alias) {
            if ($actId !== '') {
                $where[] = ["$alias." . Constant::DB_TABLE_ACT_ID, '=', $actId];
            }
        } else {
            if ($actId !== '') {
                $where[] = [Constant::DB_TABLE_ACT_ID, '=', $actId];
            }
        }

        if ($categoryId !== '') {
            $where[] = [Constant::CATEGORY_ID, '=', $categoryId];
        }

        if ($inStock !== '') {
            $where[] = [Constant::IN_STOCK, '=', $inStock];
        }

        $_where = [];
        if (data_get($params, Constant::DB_TABLE_PRIMARY, 0)) {
            $_where[Constant::DB_TABLE_PRIMARY] = $params[Constant::DB_TABLE_PRIMARY];
        }

        if ($where) {
            $_where[] = $where;
        }

        $order = $order ? $order : [Constant::DB_TABLE_PRIMARY, 'DESC'];
        return Arr::collapse([parent::getPublicData($params, $order), [
                        Constant::DB_EXECUTION_PLAN_WHERE => $_where,
        ]]);
    }

}
