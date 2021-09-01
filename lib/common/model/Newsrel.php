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
class Newsrel extends Model{
	public function newsrel_update($id,$keyword,$type){
		$keyarray = explode(',',str_replace(array('，','、'),',',$keyword));
		$keyarray=array_unique($keyarray);
        if($type==1){
			foreach($keyarray as $key=>$val){
			$valarray=	explode('#',$val);
			if(!empty($valarray[1]) && !empty($valarray[0])){
			$data['newsrel_did'] = $valarray[1];
			$data['newsrel_name']=$valarray[0];
			}
			elseif(empty($valarray[1]) && !empty($valarray[0])){
			$data['newsrel_did'] = get_vod_info($valarray[0],'vod_name','vod_id');	
			$data['newsrel_name']=$valarray[0];
			}
			else{
			$data['newsrel_did'] = $valarray[1];	
			$data['newsrel_name']=get_vod_info($valarray[1],'vod_id','vod_name');				
			}
		    $data['newsrel_sid']=$type;
		    $data['newsrel_nid']=$id;
			db('Newsrel')->where($data)->delete();
			db('Newsrel')->insert($data);	
			unset($data);
			 }
		}
		elseif($type==3){
			foreach($keyarray as $key=>$val){
			$valarray=	explode('#',$val);
			if(!empty($valarray[1]) && !empty($valarray[0])){
			$data['newsrel_did'] = $valarray[1];
			$data['newsrel_name'] = $valarray[0];
			}
			elseif(empty($valarray[1]) && !empty($valarray[0])){
			$data['newsrel_did'] = get_star_info($valarray[0],'star_name','star_id');	
			$data['newsrel_name'] = $valarray[0];
			}
			else{
			$data['newsrel_did'] = $valarray[1];	
			$data['newsrel_name']=get_star_info($valarray[1],'star_id','star_name');				
			}			
			$data['newsrel_sid'] = $type;
			$data['newsrel_nid']=$id;
			db('Newsrel')->where($data)->delete();
			db('Newsrel')->insert($data);	
			unset($data);
			 }	
		}
		else{
			foreach($keyarray as $key=>$val){
			$data['newsrel_name'] = $val;
			$vodid=get_vod_info($val,'vod_name','vod_id');
			$starid=get_star_info($val,'star_name','star_id');
			if(!empty($vodid)){
				$data['newsrel_did'] = $vodid;
			    $data['newsrel_sid'] = 1;
			}
			elseif($starid){
			$data['newsrel_did'] = $starid ; 	
			$data['newsrel_sid'] = 2 ;
				}
			else{
			$data['newsrel_did'] = 0 ;
			$data['newsrel_sid'] = 0 ;
				}
			$del['newsrel_nid']=$id;
			$del['newsrel_name']=$val;	
			db('Newsrel')->where($data)->delete();
			db('Newsrel')->data($data)->add();	
			 }	
			}			
   }
   public function get_newsid($did="",$name=""){
	   $where['newsrel_did']=array('in',$did);
	   $whereOr['newsrel_name']=array('in',$name);
	   $newsid=$this->where($where)->whereOr($whereOr)->order('newsrel_oid desc')->field('newsrel_nid')->limit(20)->select();
	   if($newsid){
		 foreach($newsid as $key=>$val){
			$list[$key]=$val['newsrel_nid'];
		 }  
		 return $list;
	   }else{
		 return false;   
	   }
	      
   }
   
}