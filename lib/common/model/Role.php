<?php
namespace app\common\model;
use think\Model;
class Role extends Model{
    protected $insert = array('role_addtime');
	protected function setroleaddtimeAttr($value,$data){
		if ($data['checktime'] || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	}
	//图片处理
	protected function setrolepicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'role');
	}	
	protected function setrolecontentAttr($value){
	 if(!empty($value)){
	 return xianyu_content_images(trim($value),'role');
	 }else{
	    return ""; 	 
	  }	 
	}
	protected function setrolecidAttr($value,$data){
		if ($value){
			return $value;
		}else{
			return gettypelistcid($data['role_vid'],5);
		}
	}	
    public function actor(){
        return $this->belongsTo('Actor','role_vid','actor_id');
    }	
    public function homeactor(){
        return $this->hasOne('actor','actor_vid','role_vid')->where('actor_status',1);
    }	
	public function homevod(){
		return $this->hasOne('vod','vod_id','role_vid')->where('vod_status',1)->field('vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters');
    }
    public function homerole(){
        return $this->hasMany('Role','role_vid','role_vid')->where('role_status',1)->alias('r')->join('star s','s.star_name = r.role_star','LEFT')->where('role_status',1)->order('role_oid asc');
    }
    public function homestar(){
        return $this->hasOne('star','star_name','role_star')->where('star_status',1);
    }
	public function homestory(){
		return $this->hasOne('story','story_vid','role_vid')->where('story_status',1)->field('story_id,story_cid');
    }	
   public function role_data(){
	   $data = input('post.');
	   $rs=model('Role');
	   $rs->saveAll($data['role']);
	}			
}