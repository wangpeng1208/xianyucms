<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Auth extends Base{
 protected $rule = [
        ['title', 'require', '分类名称不能为空'],
        ['name', 'require|unique:auth_rule', '分类标识不能为空|分类标识存在'],
    ];
}