<?php

namespace app\common\validate;
class Group extends Base
{
    protected $rule = [
        ['title', 'require|unique:auth_rule', '分类名称不能为空|分类名称存在'],
        ['name', 'require|unique:auth_rule', '分类标识不能为空|分类标识存在'],
    ];
}