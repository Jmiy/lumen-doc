<?php

namespace App\Services;

use App\Util\Constant;
use Illuminate\Support\Arr;

class AttributeService extends BaseService
{

    /**
     * 获取属性值
     * @param array $attributeData
     * @param string|array $key
     * @param type $glue
     * @return array|string
     */
    public static function getAttributeValue($attributeData, $keys, $glue = null) {

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $data = [];
        foreach ($keys as $key) {
            $_data = array_map(function($item) use($key) {
                return data_get($item, Constant::DB_TABLE_KEY) == $key ? data_get($item, Constant::DB_TABLE_VALUE) : null;
            }, $attributeData);

            $_data = array_filter($_data, function($value) {
                return $value !== null;
            });

            $_data = Arr::flatten($_data);
            $data[$key] = $glue === null ? $_data : implode($glue, $_data);
        }
        return count($keys) > 1 ? $data : Arr::first($data);
    }

}
