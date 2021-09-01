<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Ads extends Base{
 protected $rule = [
        ['ads_name', 'require|alphaDash|unique:ads', '广告标识不能为空|广告标识必须为数字字母下划线_及破折号-|广告标识存在'],
        ['ads_title', 'require', '广告名称不能为空'],
    ];
}