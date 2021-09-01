<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Tv extends Base{
 protected $rule = [
        ['tv_name', 'require', '电台名称不能为空'],
        ['tv_cid', 'require|number', '电台分类不能为空|电台分类必须为数字'],
    ];
}