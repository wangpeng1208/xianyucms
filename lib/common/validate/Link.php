<?php

namespace app\common\validate;
class Link extends Base
{
    protected $rule = [
        ['link_url', 'require|url', '友情连接网址不能为空|友情连接网址格式不正确并且必须http://开头'],
        ['link_name', 'require', '友情连接名称不能为空'],
    ];
}