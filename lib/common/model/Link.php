<?php
namespace app\common\model;
use think\Model;
class Link extends Model{
		public function getlistnameAttr($value,$data){
		$gbcid = array(1=>'文字连接',2=>'图片连接');
		return $gbcid[$data['link_type']];
	    }

}