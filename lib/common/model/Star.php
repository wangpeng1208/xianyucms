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
class Star extends Model{
   protected $auto = ['staraddtime','starletters','star_info','star_letter','starpic','starbigpic','starcontent','starkeywords'];
   protected function setstaraddtimeAttr($value,$data){
		if ($data['checktime']) {
			return time();
		}else{
			return strtotime($value);
		}
	} 
   protected function setstarlettersAttr($value,$data){
	 if (empty($value)) {
		     return getletters(trim($data['star_name']),3);
		}else{
		    return trim($value);
		}
    }
	protected function setstarinfoAttr($value,$data){
     if(!empty($data["star_reurl"]) && !empty($data["star_caiji"])){	
	    $data["star_info"]=$data["star_data_info"][0];
		}
		else{
		$title = $data["star_data_title"];
		$info =  $data["star_data_info"];
		foreach($title as $key=>$val){
			$val=trim($val);
			if($val){
		    $tv=$val."@@".$info[$key];
			}
		$tvdata[]=$tv;
		}
		$data["star_info"]=implode("||",array_unique($tvdata));
	 }
	 return xianyu_content_images(trim($data["star_info"]),'star');
	}
	protected function setstarletterAttr($value,$data){
			return getletter($data['star_name']);
	}	
	//图片处理
	protected function setstarpicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'star');
	}
	//图片处理
	protected function setstarbigpicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'star');
	}	
	protected function setstarcontentAttr($value){
	 if(!empty($value)){
	 return xianyu_content_images(trim($value),'star');
	 }else{
	    return ""; 	 
	  }	
	}
	protected function setstarkeywordsAttr($value,$data){
		if (!empty($value)) {
			return $value;
		}elseif(config('keywords_openadd') &&!empty($data['star_name'])){
			$info=seokeywords($data['star_name'],config('keywords_openadd'));
			return $info;
		} else{
	    return ""; 	 
	  }
	}	
	
}