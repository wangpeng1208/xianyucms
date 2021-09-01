<?php
namespace app\home\controller;
use app\common\controller\Home;
use think\Db;

class Hits extends Home {
   public function show(){
	    $mode= list_search(F('_data/modellist'),'id='.$this->Url['sid']);
        if($mode[0]['name']){
			$where[$mode[0]['name'].'_id'] = $this->Url['id'];
			$array = Db::name(ucfirst($mode[0]['name']))->field($mode[0]['name'].'_id,'.$mode[0]['name'].'_hits,'.$mode[0]['name'].'_hits_month,'.$mode[0]['name'].'_hits_week,'.$mode[0]['name'].'_hits_day,'.$mode[0]['name'].'_addtime,'.$mode[0]['name'].'_hits_lasttime')->where($where)->find();
			if($this->Url['type'] == 'insert'){
			return json($this->modelhits_insert($mode[0]['name'],$array));
			}
			return json($array);	
        }
    }
	//处理各模块的人气值刷新
	private function modelhits_insert($sid,$array){
		//初始化值
		$hits[$sid.'_hits'] = $array[$sid.'_hits'];
		$hits[$sid.'_hits_month'] = $array[$sid.'_hits_month'];
		$hits[$sid.'_hits_week'] = $array[$sid.'_hits_week'];
		$hits[$sid.'_hits_day'] = $array[$sid.'_hits_day'];
		$new = getdate();
		$old = getdate($array[$sid.'_hits_lasttime']);
		//月
		if($new['year'] == $old['year'] && $new['mon'] == $old['mon']){
			$hits[$sid.'_hits_month'] ++;
		}else{
			$hits[$sid.'_hits_month'] = 1;
		}
		//周
		$weekStart = mktime(0,0,0,$new["mon"],$new["mday"],$new["year"]) - ($new["wday"] * 86400);//本周开始时间,本周日0点0
		$weekEnd = mktime(23,59,59,$new["mon"],$new["mday"],$new["year"]) + ((6 - $new["wday"]) * 86400);//本周结束时间,本周六12点59
		if($array[$sid.'_hits_lasttime'] >= $weekStart && $array[$sid.'_hits_lasttime'] <= $weekEnd){
			$hits[$sid.'_hits_week'] ++;
		}else{
			$hits[$sid.'_hits_week'] = 1;
		}
		//日
		if($new['year'] == $old['year'] && $new['mon'] == $old['mon'] && $new['mday'] == $old['mday']){
			$hits[$sid.'_hits_day'] ++;
		}else{
			$hits[$sid.'_hits_day'] = 1;
		}
		//更新数据库
		$hits[$sid.'_id'] = $array[$sid.'_id'];
		$hits[$sid.'_hits'] = $hits[$sid.'_hits']+1;
		$hits[$sid.'_hits_lasttime'] = time();
		//print_r($hits) ;
		DB::name(ucfirst($sid))->update($hits,[$sid.'_id' => $hits[$sid.'_id']]);
		return $hits;
	}	
}