<?php
namespace app\common\model;
use think\Model;
use think\DB;
class Member extends Model{
	protected $autoWriteTimestamp = 'int';
	protected $createTime = 'reg_time';
	protected $updateTime = 'last_login_time';	
	protected $insert = array('reg_ip','last_login_ip','status' =>1);	
	protected $update = array('last_login_ip','last_login_time');	
	protected function setRegipAttr($value){
		return get_client_ip(1);
	}
	protected function setLastLoginIpAttr($value){
		return get_client_ip(1);
	}		
	protected function setPasswordAttr($value,$data){
		return md5(md5($value).$data['salt']);
	}
	public function login($username = '', $password = '', $type = 1){
		$map = array();
		if (\think\Validate::is($username,'email')) {
			$type = 2;
		}elseif (preg_match("/^1[34578]{1}\d{9}$/",$username)) {
			$type = 3;
		}
		switch ($type) {
			case 1:
				$map['username'] = $username;
				break;
			case 2:
				$map['email'] = $username;
				break;
			case 3:
				$map['mobile'] = $username;
				break;
			case 4:
				$map['uid'] = $username;
				break;
			case 5:
				$map['uid'] = $username;
				break;
			default:
				return 0; //参数错误
		}

		$user = db('Member')->where($map)->find();
		if(is_array($user) && $user['status']){
			/* 验证用户密码 */
			if(md5(md5($password).$user['salt']) === $user['password']){
				$this->autoLogin($user); //更新用户登录信息
				return $user['uid']; //登录成功，返回用户ID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 用户注册
	 * @param  integer $user 用户信息数组
	 */
	function register($data,$isautologin=true){
		$data['salt'] = rand_string(6);
		$result = $this->validate(true)->allowField(true)->save($data);
		if(false === $result){
			return $this->getError();
		}else{
			if($isautologin) {
				$this->autoLogin($this->data);
			}
			return $this->uid;
		}
	}

	/**
	 * 自动登录用户
	 * @param  integer $user 用户信息数组
	 */
	private function autoLogin($user){
		/* 更新登录信息 */
		$data = array(
			'uid'             => $user['uid'],
			'login'           => Db::raw('login+1'),
			'last_login_time' => time(),
			'last_login_ip'   => get_client_ip(1),
		);
		$this->where(array('uid'=>$user['uid']))->update($data);
		$user = $this->where(array('uid'=>$user['uid']))->find();
		/* 记录登录SESSION和COOKIES */
		$auth = array(
			'uid'             => $user['uid'],
			'username'        => $user['username'],
			'avatar'        => xianyu_img_url($user['avatar'],config('site_path').PUBLIC_PATH."tpl/admin/avatar.png"),
			'last_login_time' => $user['last_login_time'],
		);

		session('member_auth', $auth);
		session('member_auth_sign', data_auth_sign($auth));
	}
	public function getInfo($uid){
		$data = $this->where(array('uid'=>$uid))->find();
		if ($data) {
			return $data->toArray();
		}else{
			return false;
		}
	}

	/**
	 * 修改用户资料
	 */
	public function editUser($data, $ischangepwd = false){
		if ($data['uid']) {
			if (!$ischangepwd || ($ischangepwd && $data['password'] == '')) {
				unset($data['salt']);
				unset($data['password']);
				
			}else{
				$data['salt'] = rand_string(6);
			}
			$result = $this->validate('member.edit')->save($data,array('uid'=>$data['uid']));
		}else{
			$this->error = "非法操作！";
			return false;
		}
	}
	public function editpw($data, $is_reset = false){
		$uid = $is_reset ? $data['uid'] : session('member_auth.uid');
		if (!$is_reset) {
			//后台修改用户时可修改用户密码时设置为true
			$checkPassword=$this->checkPassword($uid,$data['oldpassword']);
			$validate = $this->validate('member.password');
			if (false === $validate || false === $checkPassword) {
				return false;
			}
		}
		$data['salt'] = rand_string(6);
		return $this->save($data, array('uid'=>$uid));
	}

	protected function checkPassword($uid,$password){
		if (!$uid && !$password) {
			$this->error = '原始用户UID和原密码不能为空';
			return false;
		}
		$user = $this->where(array('uid'=>$uid))->find();
		if (md5(md5($password).$user['salt']) === $user['password']) {
			return true;
		}else{
			$this->error = '原始密码错误！';
			return false;
		}
	}
	public function access(){
        // 用户HAS ONE档案关联
        return $this->hasOne('AuthGroupAccess','uid','',['member'=>'user','auth_group_access'=>'info']);
    }
	public function extend(){
		return $this->hasOne('MemberExtend', 'uid');
	}	

}