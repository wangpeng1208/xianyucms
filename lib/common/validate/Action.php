<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Action extends Base{
 protected $rule = [
        ['name', 'require|unique:action', '行为标识不能为空|行为标识已存在'],
        ['title', 'require', '行为名称不能为空'],
    ];
}