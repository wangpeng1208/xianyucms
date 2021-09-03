<?php

namespace app\common\validate;
class Usernav extends Base
{
    protected $rule = [
        ['name', 'require', '导航名称不能为空'],
        ['url', 'require', '连接地址不能为空'],
    ];
}