<?php

namespace app\common\validate;
class Tv extends Base
{
    protected $rule = [
        ['tv_name', 'require', '电台名称不能为空'],
        ['tv_cid', 'require|number', '电台分类不能为空|电台分类必须为数字'],
    ];
}