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
class Mcid extends Model{
	public function mcid_update($id,$mcid,$sid){
		$rs = db('mcid');
		$data['mcid_id'] = $id;
		$data['mcid_sid'] = $sid;
		$rs->where($data)->delete();
		if(is_array($mcid)){
		$mcid_arr = $mcid;
		}
		else{
		$mcid_arr = explode(',',$mcid);
		$mcid_arr = array_filter(array_unique($mcid_arr));	
		}
		foreach($mcid_arr as $key=>$val){
			if($val){
			$datas[$key]['mcid_mid'] = $val;
			$datas[$key]['mcid_sid'] = $sid;
			$datas[$key]['mcid_id'] = $id;
			}
		}
		if(isset($datas)){
		$rs->insertAll($datas);   
		}
		}	


}