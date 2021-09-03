<?php

namespace app\common\validate;
class AuthGroup extends \think\Validate
{
    protected $rule = [
        ['title', 'require', '用户组名不能为空'],
        ['description', 'require', '分组描述不能为空'],
    ];

}