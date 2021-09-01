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
class Actor extends Model{
    protected $insert = array('actor_addtime');
	protected function setactoraddtimeAttr($value,$data){
		if ($data['checktime'] || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	}
	protected function setactorcidAttr($value,$data){
		if ($value) {
			return $value;
		}else{
			return gettypelistcid($data['actor_vid'],6);
		}
	}
	protected function setactorkeywordsAttr($value,$data){
        if(!empty($value)){
			return $value;
		}else{
			if(config('keywords_openadd') && !empty($data['actor_vid'])){
			return seokeywords(get_vod_info($data['actor_vid'],'vod_id','vod_name')."演员");	
		    }
			return "";
	   }

	}
    public function role(){
        return $this->hasMany('Role','role_vid','actor_vid')->where('role_status',1)->order('role_oid asc');
    }	
    public function homerole(){
        return $this->hasMany('Role','role_vid','actor_vid')->alias('r')->join('star s','s.star_name = r.role_star','LEFT')->where('role_status',1)->order('role_oid asc');
    }
	public function homestory(){
		return $this->hasOne('story','story_vid','actor_vid')->where('story_status',1)->field('story_id,story_cid');
    }
	public function homevod(){
		return $this->hasOne('vod','vod_id','actor_vid')->where('vod_status',1)->field('vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters');
    }
   public function actor_data(){
	   $data = input('post.');
	   $rs=model('Actor');
	   if(empty($data['actor_vid'])){
	   $data['actor_vid']=$data['vod_id'];
	   }
	   if(!empty($data['actor_title']) || !empty($data['actor_keywords']) || !empty($data['actor_description'])){
			if($data['actor_id']){
			$rs->save($data, array('actor_id' => $data['actor_id']));
			}elseif($data['vod_id']){
				$info=$rs->where('actor_vid',$data['vod_id'])->value('actor_id');
				if($info){
			    $rs->save($data, array('actor_vid' => $data['vod_id']));
				}else{
					$rs->save($data);
				}
			}else{
			$rs->save($data);
		    }
	   }
	}	
	
	
}