<?php
namespace app\common\model;
use think\Model;
class Vod extends Model{
	 protected $resultSetType = '';
     protected $insert = ['vod_addtime'];  
	 protected function setvodaddtimeAttr($value,$data){
		if (!empty($data['vod_checktime']) || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	 }
	protected function setvodletterAttr($value,$data){
		if ($data['vod_name']) {
			return getletter($data['vod_name']);
		}
	}
   protected function setvodlettersAttr($value,$data){
	 if (empty($value)) {
		     return getletters(trim($data['vod_name']),1);
		}else{
		    return trim($value);
		}
    }
	protected function setvodpicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'vod');
	}	
	protected function setvodbigpicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'vod');
	}
	protected function setvodcontentAttr($value){	
	 if(!empty($value)){
	 return xianyu_content_images(trim($value),'vod');
	 }
	 else{
	    return ""; 	 
	  }
	}
	protected function setvodtvexpAttr($value){	
	 if(!empty($value)){
	 return xianyu_content_images(trim($value),'vod');
	 }
	 else{
		return ""; 
		 }
	}	
	protected function setvodgoldAttr($value){
		if($value > 10){
			$value = 10;
		}	
		return 	$value;
	}	
	protected function setvodfilmtimeAttr($value){
		if ($value) {
			return strtotime($value.' 0:0:0');
		}else{
			return "";
		}
	}	
	protected function setvodskeywordsAttr($value,$data){
		if($value){
			return $value;
			}
		elseif(empty($value) && config('keywords_openadd') && !empty($data['vod_name'])){
			$info=seokeywords($data['vod_name']);
			return $info;
		}
		else{
			return $value;
			}
	}
	protected function setvodactorAttr($value){	
	    if(!empty($value)){
	       return format_vodactor($value);
	    }else{
		   return ""; 
		}
	}
	protected function setvoddirectorAttr($value){	
	    if(!empty($value)){
	        return format_vodactor($value);
	    }else{
		    return ""; 
		}
	}
	  public function homestory(){
		return $this->hasOne('Story','story_vid')->where('story_status',1);
      }
	  public function homerole(){
        return $this->hasMany('Role','role_vid')->where('role_status',1)->order('role_oid asc');
      }
	  public function homeactor(){
        return $this->hasOne('Actor','actor_vid')->where('actor_status',1);
      }	
	  public function homevodtv(){
        return $this->hasMany('Vodtv','vodtv_id');
      }	  	  
	  public function story(){
		return $this->hasOne('Story','story_vid');
      }
	  public function role(){
        return $this->hasMany('Role','role_vid')->order('role_oid asc');
      }
	  public function actor(){
        return $this->hasOne('Actor','actor_vid');
      }	  
	 public function weekday(){
        return $this->hasMany('Weekday','weekday_id');
      }
	 public function mcid(){
        return $this->hasMany('Mcid','mcid_id');
      }	
	 public function tag(){
        return $this->hasMany('Tag','tag_id');
      } 	  
	 public function prty(){
        return $this->hasMany('Prty','prty_id');
      }		  
}