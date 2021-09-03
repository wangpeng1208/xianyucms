<?php

namespace app\common\validate;
class Config extends Base
{
    protected $rule = [
        ['name', 'require|unique:config', '配置标识不能为空|配置标识已经存在'],
        ['title', 'require', '配置标题不能为空'],
        ['type', 'require', '配置类型不能为空'],
        ['upload_http_prefix', 'url', '图片附件地址部正确必须http://开头'],
    ];
    protected $scene = array(
        'update' => array('site_url', 'site_murl', 'upload_http_prefix', 'mail_from', 'email_test'),
    );
}