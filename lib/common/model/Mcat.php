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
class Mcat extends Model{
	public function list_cat($list_id){
		$xianyu_list =db('List')->where("list_id = {$list_id}")->field("list_pid")->find();
		if($xianyu_list && $xianyu_list['list_pid'] > 0){
			$list_id = $xianyu_list['list_pid'];
		}
		return db('Mcat')->where("m_list_id = {$list_id}")->order("m_order asc")->select();
	}
	   //小分类获取
    public function get_mcid($cid,$mcid){	
		  $pid=getlistname($cid,'list_pid');
		  $condition=!empty($pid) ? $pid : $cid;	
          $tagArr = array();
          $tag = array_unique(explode(',',trim($mcid))); 
		  $where['m_list_id']=$condition;
		  $where['m_name']=array('IN', $tag);
		  $mcid=db('Mcat')->field('m_cid')->where($where)->select();
		  if($mcid){
		  foreach($mcid as $k=>$v){
			$tagArr[]=$v['m_cid'];  
		  }
		     return implode(',',$tagArr);
          }else{
			 return  "";  
		}
	 }


}