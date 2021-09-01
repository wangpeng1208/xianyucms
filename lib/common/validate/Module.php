<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Module extends Base{
 protected $rule = [
        ['module_name', 'require|unique:wechat_config', '配置名称不能为空|配置名称已存在'],
        ['module_title', 'require', '配置标题不能为空'],
    ];
}