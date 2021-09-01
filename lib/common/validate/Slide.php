<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Slide extends Base{
	protected $rule = array(
		'slide_name'   => 'require',
		'slide_vid'   => 'number',
	);
	protected $message = array(
		'slide_name.require'   => '幻灯片名称不能为空！',
		'slide_vid.number'   => '视频ID必须为数字',
	);
}