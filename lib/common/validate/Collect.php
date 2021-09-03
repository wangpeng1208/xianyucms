<?php

namespace app\common\validate;
class Collect extends Base
{
    protected $rule = [
        ['collect_name', 'require', '资源名称不能为空'],
        ['collect_url', 'require|url', '资源地址不能为空|不是正确的域名'],
    ];
}