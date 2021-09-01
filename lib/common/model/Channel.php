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
class Channel extends Model{
   protected $name = "List";
   protected function setListdirAttr($value,$data){
	 $pinyin = new \com\Hzpy();
	 if (empty($value)) {
		     $listdir=getletters(trim($data['list_name']),'list');
		     return $listdir;
	     }else{
		     return trim($value);
	     }
    }

}