<?php
namespace app\user\model;
use think\Model;
class UserApi extends Model{
	public function detail(){
		return $this->hasOne('User_detail','userid','uid');
    }
	public function user(){
		return $this->hasOne('User','userid','uid');
    }	
   
	
}