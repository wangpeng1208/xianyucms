<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
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