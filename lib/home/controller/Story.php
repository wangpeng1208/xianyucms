<?php
namespace app\home\controller;
use app\common\controller\Home;
use think\Db;	
use think\Cache;
class Story extends Home{  
    public function show(){
		if($this->Url['dir']){
			$this->Url['id'] = getlist($this->Url['dir'],'list_dir','list_id');
			$this->Url['dir'] = getlist($this->Url['id'],'list_id','list_dir');
		}else{
			$this->Url['dir'] = getlist($this->Url['id'],'list_id','list_dir');
		}
		$List = list_search(F('_data/list'),'list_id='.$this->Url['id']);
		if($List && $List[0]['list_sid']==4){
		$channel = $this->Lable_List($this->Url,$List[0]);
        $this->assign('param',$this->Url);
		$this->assign($channel);
		return view('/'.$channel['list_skin']);
		}else{	
		abort(404,'页面不存在');
		}
    }
 public function read(){
	   	if(!empty($this->Url['pinyin']) && empty($this->Url['vid'])){
			$this->Url['vid'] = get_vod_info($this->Url['pinyin'],'vod_letters','vod_id');
			$this->Url['id'] = get_story_info($this->Url['vid'],'story_vid','story_id');
		}elseif(!empty($this->Url['vid'])){
			$this->Url['id'] = get_story_info($this->Url['vid'],'story_vid','story_id');
		}
		$this->Url['p'] = input('p/d',1);
	    $array_detail = $this->get_cache_detail($this->Url['id']);
		if($array_detail && $this->Url['p'] <= count($array_detail['read']['story_list'])){
			$this->assign($array_detail['show']);
			$this->assign($array_detail['read']);
			$this->assign($array_detail['read']['story_list'][$this->Url['p']]);
			if($this->Url['p']==1){
			$this->assign('thisurl',$array['story_readurl'] = xianyu_data_url('home/story/read',array('id'=>$array_detail['read']['story_id'],'pinyin'=>$array_detail['read']['vod_letters'],'cid'=>$array_detail['read']['story_cid'],'dir'=>$array_list[0]['list_dir'])));
			}else{
			$this->assign('thisurl',$array['story_readurl'] = str_replace('xianyupage',$this->Url['p'],xianyu_data_url('home/story/read',array('id'=>$array_detail['read']['story_id'],'pinyin'=>$array_detail['read']['vod_letters'],'cid'=>$array_detail['read']['story_cid'],'dir'=>$array_list[0]['list_dir'],'p'=>$this->Url['p']))));
				}
			return view('/'.$array_detail['read']['story_skin']);
		}else{
		abort(404,'页面不存在');
		}
    }	
	
	// 从数据库获取数据
	private function get_cache_detail($id){
		if(!$id){ return false; }
		//优先读取缓存数据
		if(config('data_cache_story')){
			$array_detail = Cache::get('data_cache_story_'.$id);
			if($array_detail){
				return $array_detail;
			}
		}
		//未中缓存则从数据库读取
		$where = array();
		$where['story_id'] = $id;
		$where['story_status'] = array('eq',1);
		$array = Db::name('story')->field('s.*,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_play,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('s')->join('actor a','a.actor_vid = s.story_vid','LEFT')->join('vod v','v.vod_id = s.story_vid','LEFT')->where($where)->find();
		if($array){
			//解析标签
			$array_detail = $this->Lable_Story_Read($array);
			if( config('data_cache_story') ){
				Cache::tag('model_story')->set('data_cache_story_'.$id,$array_detail,intval(config('data_cache_story'))); 
			}
			return $array_detail;
		}
		return false;
	}		
}