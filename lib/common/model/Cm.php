<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\model;
use app\common\library\Insert;
use think\Model;
class Cm extends Model{
	 protected $insert = ['cm_addtime','cm_ip','cm_status','cm_content'];
	 public function user(){
		return $this->hasOne('User','userid','cm_uid');
     }
	 public function api(){
        return $this->hasMany('User_api','uid','cm_uid');
     }	 
	 protected function setcmaddtimeAttr($value,$data){
		if ($data['checktime'] || empty($value)) {
			return time();
		}else{
			return strtotime($value);
		}
	 }
	protected function setcmipAttr(){
		return ip2long(get_client_ip());
	}
    protected function setcmcontentAttr($value){
		$userconfig=F('_data/userconfig_cache');
		if($userconfig['user_replace']){
		 $array = explode('|',$userconfig['user_replace']);
		 $content=remove_xss(trim(str_replace($array,'***',nb(nr(strip_tags($value))))));
		}else{
		$content= remove_xss(strip_tags($value));
		}
		return $content;
	 }	
	 
	 protected function setcmstatusAttr($value){
		if(isset($value)){
		    return $value;	
		}else{
		$userconfig=F('_data/userconfig_cache'); 
		if($userconfig['user_check']==1){
			return 0;
		}else{
			return 1;
			}
		}
	}	
	public function douban($id,$vid){
		$pages = -1;
		$flg = 0;
		$data = $this->get_comment($id);
		if($data){
			$add= new Insert;
            return $add->cm_add($data,$vid);
		}else{
            $msg['msg']="没有采集到任何评论";				
			return $msg;			
		}
	}
   public function mianbao($vid,$id,$url,$p){
		$pages = -1;
		$flg = 0;
		if(!empty($url) && empty($id)){
		$id = $this->get_video_id($url);
		}
		$data = $this->get_comments($id,$p);
		if($data){
			$add= new Insert;
            return $add->cm_add($data,$vid);	
		}else{
            $msg['msg']="没有采集到任何评论";				
			return $msg;
			}

	}	
   public function get_comment($id){
		$co = new \com\Curl();
		$urlarray=config('http_api');
		$rand = array_rand($urlarray,1);
		$apiurl="http://".$urlarray[$rand]."/xianyucms/douban.php";
		$data = $co->get($apiurl,array('id'=>$id));	
	    return json_decode($data,true);
	}
	public function get_video_id($url){
		$co = new \com\Curl();
		$data = $co->get($url);	
		preg_match_all("|Id='(.*)'|U", $data, $id);
		if(isset($id[1])){
			return $id[1][0];
		}else{
			return 0;
		}
	}	
    public function get_comments($vid,$p){
		$co = new \com\Curl();
		$urlarray=config('http_api');
		$rand = array_rand($urlarray,1);
		$apiurl="http://".$urlarray[$rand]."/xianyucms/comment.php";
		$data = $co->get($apiurl,array('vid'=>$vid,'p'=>$p));
		return json_decode($data,true);
	}	
	public function gettcomments($where,$limit,$order){
		$list = $this->alias('c')->join('user u','u.userid=c.cm_uid')->relation('api')->where($where)->order($order.' desc')->limit($limit)->select(); 
		return $list ;
	}	
	public function getcomments($where,$page){
		$list = $this->alias('c')->join('user u','u.userid=c.cm_uid')->relation('api')->where($where)->order('cm_id desc ')->paginate($page['limit'],false,['page'=>$page['currentpage']]); 
		return $list ;
	}
	public function getpidcomments($where){
		$list = $this->alias('c')->join('user u','u.userid=c.cm_uid')->relation('api')->where($where)->order('cm_id desc ')->select(); 
		return $list ;
	}	
	public function getcmcount($where){
		return $this->where($where)->count('cm_id');
	}	 

}