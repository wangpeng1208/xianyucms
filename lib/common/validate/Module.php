<?php

namespace app\common\validate;
class Module extends Base
{
    protected $rule = [
        ['module_name', 'require|unique:wechat_config', '配置名称不能为空|配置名称已存在'],
        ['module_title', 'require', '配置标题不能为空'],
    ];
}