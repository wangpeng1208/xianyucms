<?php

namespace app\common\validate;
class Timming extends Base
{
    protected $rule = [
        ['timming_name', 'require', '任务名称不能为空'],
        ['timming_password', 'require', '任务密码不能为空'],
    ];
}