<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Link extends Base{
 protected $rule = [
        ['link_url', 'require|url', '友情连接网址不能为空|友情连接网址格式不正确并且必须http://开头'],
        ['link_name', 'require', '友情连接名称不能为空'],
    ];
}