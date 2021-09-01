<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Config extends Base{
    protected $rule = [
        ['name', 'require|unique:config', '配置标识不能为空|配置标识已经存在'],
        ['title', 'require', '配置标题不能为空'],
		['type', 'require', '配置类型不能为空'],
		['upload_http_prefix', 'url', '图片附件地址部正确必须http://开头'],		
    ];	
	protected $scene = array(
		'update'   => array('site_url', 'site_murl','upload_http_prefix','mail_from','email_test'),					 
	);	
}