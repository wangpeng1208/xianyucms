<?php

namespace app\common\validate;
class Action extends Base
{
    protected $rule = [
        ['name', 'require|unique:action', '行为标识不能为空|行为标识已存在'],
        ['title', 'require', '行为名称不能为空'],
    ];
}