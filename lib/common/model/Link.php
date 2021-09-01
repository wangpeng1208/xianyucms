<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;
class Link extends Model{
		public function getlistnameAttr($value,$data){
		$gbcid = array(1=>'文字连接',2=>'图片连接');
		return $gbcid[$data['link_type']];
	    }

}