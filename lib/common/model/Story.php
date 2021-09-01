<?php
namespace app\common\model;
use think\Model;
class Story extends Model{
	protected $insert = array('story_addtime');
    public function homerole(){
        return $this->hasMany('Role','role_vid','story_vid')->where('role_status',1)->order('role_oid asc');
    }
	public function homeactor(){
		return $this->hasOne('actor','actor_vid','story_vid')->where('actor_status',1);
    }
	public function homevod(){
		return $this->hasOne('vod','vod_id','story_vid')->where('vod_status',1);
    }	
	protected function setstoryaddtimeAttr($value,$data){
		if ($data['checktime'] || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	}
	protected function setstorycontentAttr($value){
	 if(!empty($value)){
	    return xianyu_content_images(trim($value),'story');
	 }else{
	    return ""; 	 
	  }
	}	
	protected function setstorytitleAttr($value,$data){
		if(!empty($data)){
	    $listarray=explode('||',strip_tags($data['story_content']));
	    krsort($listarray);	//	倒叙排列剧情
	    $listarray2=explode('@@',$listarray[count($listarray)-1]);
		return trim($listarray2[0]);
		}
	}	
	protected function setstorypageAttr($value,$data){
		$listarray=explode('||',$data['story_content']);
		return count($listarray);
	}
	protected function setstorycontinuAttr($value,$data){
		$listarray=explode('||',$data['story_content']);
		$count=count($listarray);
		if(empty($value)){
		return $count;
		}elseif($count > $value){
		return $count;	
		}else{
		return $value;		
		}
	}
	protected function setstorycidAttr($value,$data){
		if (!empty($value)) {
			return $value;
		}elseif($data['story_vid']){
			return gettypelistcid($data['story_vid'],4);
		}
		elseif($data['vod_id']){
			return gettypelistcid($data['vod_id'],4);
		}else{
	    return ""; 	 
	  }
	}		
	protected function setstorykeywordsAttr($value,$data){
		if(!empty($value)){
			return $value;
		}else{
			if(config('keywords_openadd') && !empty($data['story_vid'])){	
			return seokeywords(get_vod_info($data['story_vid'],'vod_id','vod_name')."剧情",config('keywords_openadd'));	
		    }
			return "";
	   }
	}
	public function story_data(){
	   $data = input('post.');
	   $rs=model('Story');
	   if(empty($data['story_vid'])){
	      $data['story_vid']=$data['vod_id'];
	   }
	   if(!empty($data['story_stitle']) || !empty($data['story_keywords']) || !empty($data['story_description']) || !empty($data['story_url']) || !empty($data['story_content'])){
			if($data['story_id']){
			$rs->save($data, array('story_id' => $data['story_id']));
			}elseif($data['vod_id']){
				$info=$rs->where('story_vid',$data['vod_id'])->value('story_id');
				if($info){
			    $rs->save($data, array('story_vid' => $data['vod_id']));
				}else{
					$data['story_vid']=$data['vod_id'];
					$rs->save($data);
				}
			}else{
			    $rs->save($data);
		    }
	   }
	}	
 	//剧情
	public function vod_story($params,$where){
			if(!empty($params['cache_name']) && !empty($params['cache_time'])){
			 $data_cache_content = cache($params['cache_name']);
			  if($data_cache_content){
				return $data_cache_content;
			  }
		    }
		    $data=$this->where($where)->find();
			$array_story=$data->toArray();
			$vod_letters=get_vod_info($array_story['story_vid'],'vod_id','vod_letters');
			if(!empty($array_story['story_content'])){//如果存在剧情
			$array_story['story_readurl']=xianyu_data_url('home/story/read',array('id'=>$array_story['story_id'],'pinyin'=>$vod_letters,'cid'=>$array_story['story_cid'],'dir'=>getlistname($array_story['story_cid'],'list_dir')));
			$array_story['story_content']=$array_story['story_content'];
			$listarray=explode('||',$array_story['story_content']); //将每一集分组
			//krsort($listarray);	//	倒叙排列
			foreach($listarray as $key=>$jiinfo){
				$jiinfoarray=explode('@@',$jiinfo);
				$arrays['story_list'][$key+1]['story_name']=$jiinfoarray[0];
				$arrays['story_list'][$key+1]['story_title']=$jiinfoarray[1];
				$arrays['story_list'][$key+1]['story_info']=$jiinfoarray[2];
				if($key+1==1){
				$arrays['story_list'][$key+1]['story_readurl']=xianyu_data_url('home/story/read',array('id'=>$array_story['story_id'],'pinyin'=>$vod_letters,'cid'=>$array_story['story_cid'],'dir'=>getlistname($array_story['story_cid'],'list_dir')));
				}else{
				$arrays['story_list'][$key+1]['story_readurl']=str_replace('{!page!}',$jinum,xianyu_data_url('home/story/read',array('id'=>$array_story['story_id'],'pinyin'=>$array['vod_letters'],'cid'=>$array_story['story_cid'],'dir'=>getlistname($array_story['story_cid'],'list_dir'),'p'=>$key+1)));
				}
			}
			$array_story['story_list']=$arrays['story_list'];
			unset($array_story['story_content']);
			if(!empty($params['cache_name']) && !empty($params['cache_time'])){
			cache($params['cache_name'],$array_story, intval($params['cache_time']) );
	        }
			return $array_story;	
		}
	else{
		return false;
		}
	}  	
}
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------