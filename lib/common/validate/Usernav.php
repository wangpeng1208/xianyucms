<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Usernav extends Base{
 protected $rule = [
        ['name', 'require', '导航名称不能为空'],
        ['url', 'require', '连接地址不能为空'],
    ];
}