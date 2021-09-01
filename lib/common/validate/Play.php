<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Play extends Base{
 protected $rule = [
        ['play_title', 'require', '播放器名称不能为空'],
        ['play_name', 'require|alphaNum|unique:play', '播放器标识不能为空|播放器标识只能为数字和字母|播放标识已存在'],
    ];
}