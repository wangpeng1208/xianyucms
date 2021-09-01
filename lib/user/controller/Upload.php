<?php
namespace app\user\controller;
use think\Controller;
class Upload extends Controller{
    public function _initialize() {
		parent::_initialize();
		$key=htmlspecialchars(input('session_key/s',''));
		$id=htmlspecialchars(input('session_id/d',''));
	    $cms_auth_key = md5($id);
	    $code_res = sys_auth($key,'DECODE',$cms_auth_key);
		$code_arr = explode('|', $code_res);
		//检查登录状态
		if($code_arr[0]!=$id || !cmf_is_user_login()){
		  return json_encode(array('status' =>0,'data' =>'', 'info' =>'请先登录'));
		}
	}		
   public function uploadImg(){
	    $file = $this->request->file('file');
        if(true !== $this->validate(['image' => $file], ['image' => 'require|image'])) {
			return json_encode(array('status' =>0,'data' =>'', 'info' =>"未上传文件或超出服务器上传限制"));
        }else{
			$info=$file->move(URL_PATH.config('upload_path'). DS.'avatar'. DS.'temp'. DS,'temp.jpg');
			$filepath=str_replace('/\/',"/",config('site_path').config('upload_path'). DS.'avatar'. DS.'temp'. DS.$info->getSaveName());
			return json_encode(array('status' =>1,'data' =>$filepath, 'info' =>$info));
        }					
		
    }	
    public function cropImg(){
	    $data=input('post.');
        if(!isset($data) && empty($data)){
			return;
		}
		//图片路径
		$path=URL_PATH.config('upload_path'). DS.'avatar'. DS.'temp'. DS;
		//裁剪后的图片
		$real_path = $path.'avatar.jpg';
		$image = \think\Image::open($path.'temp.jpg');
		$uid= cmf_get_current_user_id();
	    $uid = abs(intval($uid));
	    $uid = sprintf("%09d", $uid);
	    $dir1 = substr($uid, 0, 3);
	    $dir2 = substr($uid, 3, 2);
	    $dir3 = substr($uid, 5, 2);
	    $typeadd = $type == 'real' ? '_real' : '';
		$urlpath=URL_PATH.config('upload_path').'/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/';
		mkdirss($urlpath);
	    $url =  $urlpath.substr($uid, -2).$typeadd."_avatar_";		
		$image->crop($data['w'],$data['h'],$data['x'],$data['y'])->save($real_path);
		$image = \think\Image::open($real_path);
		$image->thumb(200,200)->save($url.'big.jpg');
		$image->thumb(120,120)->save($url.'middle.jpg');
		$image->thumb(80,80)->save($url.'small.jpg');
		db('user')->where('userid',$uid)->setField('pic',1);
        return json(["msg"=>"上传头像成功","rcode" =>"1"]);		
    }
}

