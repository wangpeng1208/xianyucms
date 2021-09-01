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
class Special extends Model{
   protected function setspeciallettersAttr($value,$data){
	 if (empty($value)) {
		     return getletters(trim($data['special_name']),10);
		}else{
		    return trim($value);
		}
    }
	protected function setspecialaddtimeAttr($value,$data){
		if($data['checktime']) {
			return time();
		}else{
			return strtotime($value);
		}
	}
	protected function setspecialgoldAttr($value){
		if($value > 10){
			$value = 10;
		}	
		return 	$value;
	}
	//图片处理
	protected function setspeciallogoAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'special');
	}
	//图片处理
	protected function setspecialbannerAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'special');
	}
	protected function setspecialcontentAttr($value){	
	 if(!empty($value)){
	    return xianyu_content_images(trim($value),'special');
	 }else{
	    return ""; 	 
	  }
	}
	
	
}