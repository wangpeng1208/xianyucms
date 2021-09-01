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
class Gb extends Model{
	protected $insert = ['gb_addtime','gb_status','gb_ip','gb_content','gb_title']; 
	 public function user(){
		return $this->hasOne('User','userid','gb_uid');
     }
	 public function api(){
        return $this->hasMany('User_api','uid','gb_uid');
     }		
	 protected function setgbaddtimeAttr($value,$data){
		if ($data['checktime'] || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	 }
	protected function setGbipAttr(){
		return ip2long(get_client_ip());
	}		 
	 protected function setgbstatusAttr($value){
		if(isset($value)){
		   return $value;	
		}else{
		$userconfig=F('_data/userconfig_cache'); 
		if($userconfig['user_check']==1){
			return 0;
		}else{
			return 1;
			}
	    }
	 }
	protected function setgbcontentAttr($value){
		$userconfig=F('_data/userconfig_cache');
		if($userconfig['user_replace']){
		 $array = explode('|',$userconfig['user_replace']);
		 return remove_xss(trim(str_replace($array,'***',nb(nr(strip_tags($value))))));
		}else{
		 return	strip_tags(remove_xss($value));
			}
	 }	
	protected function setgbtitleAttr($value){
	    $userconfig=F('_data/userconfig_cache');
		if($userconfig['user_replace']){
		 $array = explode('|',$userconfig['user_replace']);
		 return remove_xss(trim(str_replace($array,'***',nb(nr(strip_tags($value))))));
		}else{
		 return	strip_tags(remove_xss($value));
		}
	 }		 
	public function getGbList($userid,$pid,$page){
		 $where['gb_uid'] = $userid;
		 if(!empty($pid)){
		 $where['gb_cid']= $pid;
		 }
		 $list = $this->where($where)->order('gb_addtime desc ')->paginate($page['limit'],false,['page'=>$page['currentpage']]); 
		//print_r($list) ;
		return $list ;
	}
	public function getgbnameAttr($value,$data){
		$gbcid = array(1=>'其他留言',2=>'影片报错',3=>'网站建议',4=>'访问故障');
		return $gbcid[$data['gb_cid']];
	}	
	public function getGbCatalogs($userid){
		$data = db('gb')->field('gb_cid,count(gb_cid) as count')->where('gb_uid',$userid)->group('gb_cid')->select();
		foreach ($data as $key =>$value){
			$datas[$value['gb_cid']]=$value['count'];
		}
		$list = array(1=>'其他留言',2=>'影片报错',3=>'网站建议',4=>'访问故障');
		foreach ($list as $key =>$value){
			   $catalogs[$key]['list_id']=$key;
			   $catalogs[$key]['list_name']=$value;
			   $catalogs[$key]['count']=$datas[$key];
		}
		return $catalogs;
	}
	public function add($data){
		$userconfig=F('_data/userconfig_cache');
		$cachename = 'gb-'.ip2long(get_client_ip()).'-'.intval($data['gb_cid']);
		$id=$this->validate('gb')->save($data);
		if (false === $id) {
			return $this->getError();
		}else{	
		     cache($cachename, 'ture', intval($userconfig['user_second']));
		     return 1;
		}
		
	}

}