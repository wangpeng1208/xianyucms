<?php

namespace app\common\validate;
class Base extends \think\Validate
{
    protected function requireIn($value, $rule, $data)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        } else {
            return true;
        }
        $field = array_shift($rule);
        $val = $this->getDataValue($data, $field);
        if (!in_array($val, $rule) && $value == '') {
            return false;
        } else {
            return true;
        }
    }
}