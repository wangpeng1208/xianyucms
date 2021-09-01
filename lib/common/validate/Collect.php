<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Collect extends Base{
 protected $rule = [
        ['collect_name', 'require', '资源名称不能为空'],
        ['collect_url', 'require|url', '资源地址不能为空|不是正确的域名'],
    ];
}