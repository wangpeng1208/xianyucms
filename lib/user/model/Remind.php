<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
//订阅
namespace app\user\model;
use think\Db;
use think\Model;
class Remind extends Model{
	public function isRemindVod($where){
		$count = $this->where($where)->count();
		return $count > 0 ? true : false ;
	}
	public function getRemindList($userid,$pid,$page){
		 $where['remind_uid'] = $userid;
		 if(!empty($pid)){
		 $where['remind_cid']= $pid;
		 }
		 $list = $this->field('vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_addtime,vod_jumpurl,remind_id,remind_addtime,remind_uptime,remind_cid')->alias('r')->join('vod b','b.vod_id=r.remind_vid')->where($where)->order('vod_addtime desc ')->paginate($page['limit'],false,['page'=>$page['currentpage']]);
		return $list ;	
		
	}
	public function getRemindDatas($userid){
        $list = Db::query("SELECT vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl FROM ".config('database.prefix')."vod  inner join ( select b.remind_vid,count(b.remind_vid) as fnum from ".config('database.prefix')."remind b where not exists(select remind_vid from ".config('database.prefix')."remind c where remind_uid = ".$userid." and b.remind_vid = c.remind_vid ) group by b.remind_vid ) a  on ".config('database.prefix')."vod.vod_id = a.remind_vid  ORDER BY  a.fnum desc , ".config('database.prefix')."vod.vod_hits_day desc  LIMIT 10");
		if(empty($list)){
		$list = Db::query("SELECT vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl FROM ".config('database.prefix')."vod  WHERE  vod_id  NOT  IN  ( SELECT remind_vid FROM ".config('database.prefix')."remind) ORDER BY ".config('database.prefix')."vod.vod_hits desc  LIMIT 10");
		}
		return $list ;
	}
	public function getRemindCatalogs($userid){
		$list = db('remind')->field('*,count(list_id) as count')->alias('r')->join('list b','b.list_id=r.remind_cid')->where('remind_uid',$userid)->group('list_id,list_name')->select();
		return $list;
	}	

}