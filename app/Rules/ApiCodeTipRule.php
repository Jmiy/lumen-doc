<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ApiCodeTipRule implements Rule {

    /**
     * 判断验证是否通过
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
        return strtoupper($value) === $value;
    }

    /**
     * 获取验证错误信息
     *
     * @return string
     */
    public function message() {
        return trans('validation.apiCodeTipRule');//'The :attribute must be uppercase.';
    }

}
