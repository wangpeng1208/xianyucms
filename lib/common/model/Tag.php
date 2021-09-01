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
class Tag extends Model{
		// 将关键字处理为数组格式并去重(用于关联操作的添加方法)	
	public function tag_array($keywords,$sid=1){
		$tag_arr = explode(',',$keywords);
		$tag_arr = array_unique($tag_arr);
		foreach($tag_arr as $key=>$value){
			$data[$key] = array('tag_id','tag_sid'=>$sid,'tag_name'=>$value);
		}
		return $data;
	}
	// 更新Tag 不采用关联模式(用于手动更新关联,删除之前的数据后重新写入)
	public function tag_update($id,$tag,$sid){
		$rs=db('Tag');
		$data['tag_id'] = $id;
		$data['tag_sid'] = $sid;
		$rs->where($data)->delete();
		$tags = explode(',',trim($tag));
		$tags = array_unique($tags);
		foreach($tags as $key=>$val){
			$datas[$key]['tag_id'] = $id;
			$datas[$key]['tag_sid'] = $sid;
			$datas[$key]['tag_name'] = $val;
		}
		if(isset($datas)){
		$rs->insertAll($datas);   
		}
	}
   
}