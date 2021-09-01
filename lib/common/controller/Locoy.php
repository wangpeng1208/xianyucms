<?php
namespace app\common\controller;
use think\Db;
use think\Controller;
class Locoy extends Controller {
    public function _initialize() {
		parent::_initialize();
		$getwpd=input('pwd/s','');
		$config = get_addon_config('locoy');
		if($getwpd!=$config['loco_pwd']){
			return $this->error('验证密码错误');
		}
	}
	public function getlist(){
		$select_start='<select name="vod_cid" id="vod_cid" style="width:100px">';
        $select_option=$select_option.'<option value="连接成功">连接成功</option>';
		$select_end='</select>';
		$select=$select_start.$select_option.$select_end;
		print_r($select);exit;
    }
    function getapilist($str,$seach,$type,$sid){
	   if(!empty($str)){
	   $model = list_search(F('_data/list'),'list_sid='.$sid);
       $arr = list_search($model,$seach.'='.$str);
	   if(empty($arr)){
		 return false;
	   }else{
	      return $arr[0][$type];
	   }
	  }
    }	
}