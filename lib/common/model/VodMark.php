<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
//评分
namespace app\common\model;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;
class VodMark extends Model{
	public function getRating($data){
		$f1 = $data['F1'] ;
		$f2 = $data['F2'] ;
		$f3 = $data['F3'] ;
		$f4 = $data['F4'] ;
		$f5 = $data['F5'] ;
		$ratingTotal = $f1 + $f2 +  $f3 + $f4 + $f5 ;
		$array = array();
		if($ratingTotal > 0){
			$rating = ($f1/$ratingTotal)*1 + ($f2/$ratingTotal)*2  + ($f3/$ratingTotal)*3 + ($f4/$ratingTotal)*4 + ($f5/$ratingTotal)*5 ;
			$r1 = round($rating * 2,1) ;
			$array["R1"] = number_format($r1,1) ;
			$array["R2"] = $r1*10 ;
		}
		return $array;
	}	
 	public function getMark($vod_id){
		$field ='sum(F1) as F1 ,sum(F2) as F2 ,sum(F3) as F3 ,sum(F4) as F4 ,sum(F5) as F5' ;
        return $this->field($field)->where(array('mark_vid' => $vod_id))->find();
    }
	
    public function getMarkValue($vod_id,$ip){
		$data = $this->field("F1,F2,F3,F4,F5")->where(array('mark_vid'=>$vod_id,'mark_ip'=>$ip))->find();
		$value = -1 ;
		if($data != null){
			if($data['F1'] == 1){
				$value = 1 ;	
			}else if($data['F2'] == 1){
				$value = 2 ;
			}else if($data['F3'] == 1){
				$value = 3 ;
			}else if($data['F4'] == 1){
				$value = 4 ;
			}else if($data['F5'] == 1){
				$value = 5 ;
			}
		}
		return $value ;
	}	   
	
}