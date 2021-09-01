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
class Tv extends Model{
   protected $updateTime = 'tv_lasttime';	
   protected function settvlettersAttr($value,$data){
	 if (empty($value)) {
		     return getletters(trim($data['tv_name']),3);
		}else{
		    return trim($value);
		}
    }
	protected function settvaddtimeAttr($value,$data){
		if ($data['checktime']) {
			return time();
		}else{
			return strtotime($value);
		}
	}
	protected function settvletterAttr($value,$data){
		if (empty($value)) {
			return getletter($data['tv_name']);
		}else{
			return trim($value);
		}
	}
	protected function settvdataAttr($value,$data){
		if(!empty($data['tv_apiid']) && !empty($data['tv_apitype'])){
		 	$urlarray=config('http_api');
		    $rand = array_rand($urlarray,1);
		    $apiurl="http://".$urlarray[$rand]."/xianyucms/xianyujiemu.php?id=".$data['tv_apiid']."&type=".$data['tv_apitype'];
		    $tvdata = xianyu_get_url($apiurl,3);
			$datas=json_decode($tvdata,true);	
			if(count($datas)>1){
			return 	$tvdata;
			}else{
			foreach($data["tv_date"] as $key=>$val){
			$val=trim($val);
			if($val){
			$tv['week']=$data["tv_week"][$key];
		    $tv['date']=$data["tv_date"][$key];
			$tv['data']=str_replace(array("\r\n", "\n", "\r"),chr(13),$val);
			}
		$tvdata[]=$tv;
		}	
				
				}
		}
		else{
		foreach($data["tv_date"] as $key=>$val){
			$val=trim($val);
			if($val){
			$tv['week']=$data["tv_week"][$key];
		    $tv['date']=$data["tv_date"][$key];
			$tv['data']=str_replace(array("\r\n", "\n", "\r"),chr(13),$val);
			}
		$tvdata[]=$tv;
		}
	   return json_encode($tvdata);	
		}
	}
	//图片处理
	protected function settvpicAttr($value){
		$img = model('Img');
		return $img->down_load(trim($value),'tv');
	}	
	protected function settvcontentAttr($value){
	 if(!empty($value)){
      return xianyu_content_images(trim($value),'tv');
	  }else{
	    return ""; 	 
	  }	
	}
	
	protected function settvgoldAttr($value){
		if($value > 10){
			$value = 10;
		}	
		return 	$value;	
	}	
	protected function settvuptimeAttr($value){
		if (!empty($value)) {
			return $value;
		}else{
			return 48;
		}
	}
	protected function settvkeywordsAttr($value,$data){
		if ($value) {
			return $value;
		}elseif(config('keywords_openadd')){
			$info=seokeywords($data['tv_name'],config('keywords_openadd'));
			if(!empty($info)){
			return $info;
			}
			else{
			return "";	
				}
		}
		else{
		return "";		
			}
	}
	
 public function tv_week($data,$tvweek,$zhibo){
	 if(!$data){ return false; }
     $week=str_replace('0','7',date("w"));
     $date=date("Y年m月d日");
	 $time=strtotime(date("H:i"));
     $data=json_decode($data,true);	
	 foreach($data as $key=>$value){
			$array_week['week'][]=str_replace(array('星期一','星期二','星期三','星期四','星期五','星期六','星期日'),array('1','2','3','4','5','6','7'),$value['week']);
	 }
	 $week = $array_week['week'];
	foreach($data as $key=>$value){
				$array['tv_datalist'][$week[$key]]['week']=$value['week'];
				$array['tv_datalist'][$week[$key]]['date']=$value['date'];
				$datas=explode(chr(13),trim($value['data']));
				$len = count($datas);
         foreach($datas as $k=>$val){
                $datalist=explode('$',$val);
				$ndatalist=explode('$',$datas[$k+1]);
				if($date==$value['date'] && $time >= strtotime($datalist[0]) && $time < strtotime($ndatalist[0]) || $date==$value['date'] && $time >= strtotime($datalist[0]) && $k == $len-1){
				$array['tv_datalist'][$week[$key]]['live']=$k;
				$array['tv_datalist'][$week[$key]]['data'][$k]['live']=1;
				}	
				$array['tv_datalist'][$week[$key]]['data'][$k]['time']=$datalist[0];
				$array['tv_datalist'][$week[$key]]['data'][$k]['name']=str_replace('#','',$datalist[1]);
				$datalistkey[$k]=explode('#',$datalist[1]);
			    if(count($datalistkey[$k])>1){
				$array['tv_datalist'][$week[$key]]['data'][$k]['keywords']=$datalistkey[$k][1];	
				}else{
				$array['tv_datalist'][$week[$key]]['data'][$k]['keywords']=preg_replace(array("/.*?:/","/(\(.*?)\)/","/.*?：/","/（(.*?)）/","/\d*/"),"",$datalist[1]);
					}
			}
		}
		$counts = count($array['tv_datalist'][$tvweek]['data'])-1;
		$live=$array['tv_datalist'][$tvweek]['live'];
		$arrays['tv_datalist']['week']=$array['tv_datalist'][$tvweek]['week'];
		$arrays['tv_datalist']['date']=$array['tv_datalist'][$tvweek]['date'];
		if(!empty($zhibo)){
		$arrays=$array['tv_datalist'][$tvweek]['data'][$live];	
		}
		else{
         if(is_array($array['tv_datalist'][$tvweek]['data'])){ 
		foreach($array['tv_datalist'][$tvweek]['data'] as $i=>$v){
		if($live == $counts && $i > $live-3 || $live==0 && $i < 3 || $i >= $live-1 && $i <= $live+1){	
		$arrays['tv_datalist']['data'][]=$v;	
		 }
		}
           }
	   }
	  return $arrays['tv_datalist'];
  }

	public function vod_tv($tag){
		if(empty($tag['tv']) || empty($tag['name']) || empty($tag['id'])){return false;}
		$todayweek=str_replace('0','7',date("w"));
		$todaydate=date("Y年m月d日");
		$where['tv_name'] = array('in',$tag['tv']);
        $list = $this->where($where)->select();
		if(empty($list)){return false;}
		  foreach($list as $i=>$value){	
		    $data=json_decode($list[$i]['tv_data'],true);
		    foreach($data as $v){
		    $array_week['week'][]=str_replace(array('星期一','星期二','星期三','星期四','星期五','星期六','星期日'),array('1','2','3','4','5','6','7'),$v['week']);
		    }
		    $week = $array_week['week'];
		    $array['tv_data'][$i]['tv_id']=$list[$i]['tv_id'];
		    $array['tv_data'][$i]['tv_name']=$list[$i]['tv_name'];
		    $array['tv_data'][$i]['tv_pic']=xianyu_img_url($list[$i]['tv_pic']);;
		    $array['tv_data'][$i]['tv_week']=str_replace(array('1','2','3','4','5','6','7'),array('星期一','星期二','星期三','星期四','星期五','星期六','星期日'),$todayweek);
		    $array['tv_data'][$i]['tv_date']=$todaydate;
			$array['tv_data'][$i]['tv_readurl']=xianyu_data_url('home/tv/read',array('id'=>$list[$i]['tv_id'],'pinyin'=>$list[$i]['tv_letters'],'cid'=>$list[$i]['tv_cid'],'dir'=>getlistname($list[$i]['tv_cid'],'list_dir')));
		    $date=strtotime(date('Y-m-d'));
		    $time=strtotime(date("H:i"));
		  foreach($data as $key=>$value){
			  $ddate=strtotime(str_replace(array('年','月','日'),array('-','-',''),$value['date']));
               if($ddate >= $date && (strpos($value['data'],$tag['name']) !== false || !empty($tag['aliases']) && strpos($value['data'],$tag['aliases']) !== false)){
		            $array['tv_data'][$i]['list'][$week[$key]]['week']=$value['week'];
					$weeks=str_replace(array('星期一','星期二','星期三','星期四','星期五','星期六','星期日'),array('1','2','3','4','5','6','7'),$value['week']);
					$array['tv_data'][$i]['list'][$week[$key]]['url']=str_replace('xianyupage',$weeks,xianyu_url('home/tv/read',array('id'=>$list[$i]['tv_id'],'pinyin'=>$list[$i]['tv_letters'],'cid'=>$list[$i]['tv_cid'],'dir'=>getlistname($list[$i]['tv_cid'],'list_dir'),'p'=>$weeks)));
		            $array['tv_data'][$i]['list'][$week[$key]]['date']=strtotime(str_replace(array('年','月','日'),array('-','-',''),$value['date']));	
		            $datas=explode(chr(13),trim($value['data']));
		            foreach($datas as $k=>$val){
		                   $datalist=explode('$',$val);
						   $ndatalist=explode('$',$datas[$k+1]);
                           if(strpos($val,$tag['name']) !== false || (!empty($tag['aliases']) && strpos($val,$tag['aliases']) !== false)){
							  if($todaydate==$value['date'] && $time >= strtotime($datalist[0]) && $time < strtotime($ndatalist[0])){
				              $array['tv_data'][$i]['list'][$week[$key]]['data'][$k]['tvlive']=1;
				               }
		                      $array['tv_data'][$i]['list'][$week[$key]]['data'][$k]['tvtime']=$datalist[0];
		                      $array['tv_data'][$i]['list'][$week[$key]]['data'][$k]['tvname']=str_replace('#','',$datalist[1]); 
		                     continue;
                           } 	    	   
		             }
			     continue;	   
		      }
		   }
		}
		 foreach($array['tv_data'] as $key=>$value){
			 if(!empty($array['tv_data'][$key]['list'])){
			 $arrys['tv_data'][$key]=$value;
			 $arrys['tv_data'][$key]['list']=array_values($value['list']);
			  foreach($arrys['tv_data'][$key]['list'] as $kkk=>$val){
			  $arrys['tv_data'][$key]['list'][$kkk]['data']=array_values($val['data']);	  
			  } 
			 }
		 }
		// print_r($arrys) ;
		//ksort($playlist);
	    return $arrys['tv_data'];
	}
		function tv_update($id,$cid,$sid){
		$rs = db("vodtv",array(),false);
		$data['vodtv_id'] = $id;
		$data['vodtv_name'] = $cid;
		$data['vodtv_sid'] = $sid;
		$rs->where($data)->delete();
		if(is_array($cid)){
		$weekdaycid_arr = $cid;
		}
		else{
		$weekdaycid_arr = explode(',',$cid);
		$weekdaycid_arr = array_filter(array_unique($weekdaycid_arr));	
			}
		foreach($weekdaycid_arr as $key=>$val){
			if($val=="未知"){continue;}
			$datas[$key]['vodtv_id'] = $id;
			$datas[$key]['vodtv_sid'] = $sid;	
			$datas[$key]['vodtv_name'] = $val;
		   }
		if(isset($datas)){
		$rs->insertAll($datas);   
		}
		} 	

}