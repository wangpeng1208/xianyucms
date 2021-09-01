<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Channel extends Base{
	protected $rule = array(
		'list_name'   => 'require',
	);
	
	protected $message = array(
		'list_name.require'   => '栏目标题不能为空！',
	);
	
	protected $scene = array(
		'add'   => 'list_name',
		'edit'   => 'list_name'
	);
}