<?php

namespace app\user\controller;

use app\common\controller\User;
use OAuth\OAuth;
class Snslogin extends User
{
    protected $config;
	public function _initialize()
	{
		parent::_initialize();

	}
	public function __construct()
	{
		$userconfig = get_addon_config('snslogin');
		$config['qq'] = ['app_key' => $userconfig['api_qq_key'], 'app_secret' => $userconfig['api_qq_secret'], 'scope' => 'get_user_info', 'callback' => ['default' => $userconfig['api_qq_callback'], 'mobile' => '']];
		$config['weibo'] = ['app_key' => $userconfig['api_weibo_key'], 'app_secret' => $userconfig['api_weibo_secret'], 'scope' => 'get_token_info', 'callback' => ['default' => $userconfig['api_weibo_callback'], 'mobile' => '']];
		$config['weiqrcode'] = ['app_key' => $userconfig['api_weixin_key'], 'app_secret' => $userconfig['api_weixin_secret'], 'scope' => 'snsapi_login', 'callback' => ['default' => $userconfig['api_weixin_callback'], 'mobile' => '']];
		$this->config = $config;
	}
	public function qq()
	{
		$OAuth = OAuth::getInstance($this->config['qq'], 'qq');
		$OAuth->setDisplay('default');
		cookie('channel', 'qq');
		cookie('close', input('t/d', 2));
		print_r(cookie('close'));
		return redirect($OAuth->getAuthorizeURL());
	}
	public function weibo()
	{
		$OAuth = OAuth::getInstance($this->config['weibo'], 'weibo');
		$OAuth->setDisplay('default');
		cookie('channel', 'weibo');
		cookie('close', input('t/d', 2));
		return redirect($OAuth->getAuthorizeURL());
	}
	public function callback($channel = "")
	{
		$userconfig = get_addon_config('snslogin');
		$channel = cookie('channel');
		$close = cookie('close');
		$channel = !empty($channel) ? $channel : "qq";
		$OAuth = OAuth::getInstance($this->config[$channel], $channel);
		$OAuth->getAccessToken();
		$sns_info = $OAuth->userinfo();
		if (is_array($sns_info) && $sns_info['openid']) {
			$rs = model('User');
			$user = $rs->GetApiUser($sns_info['openid'], $sns_info['channel']);
			if ($user) {
				if ($close == 1) {
					return view('/close');
				} else {
					return $this->redirect(xianyu_user_url('user/center/index'));
				}
			} else {
				if (user_islogin()) {
					$data['uid'] = user_islogin();
					$data['channel'] = $sns_info['channel'];
					$data['openid'] = $sns_info['openid'];
					$data['avatar'] = $sns_info['avatar'];
					$id = db('user_api')->insert($data);
					if ($id) {
						return $this->success('绑定成功！', xianyu_user_url('user/center/index'));
					}
				}
				session('user_auth_api', $sns_info);
				if ($userconfig['api_login_auth']) {
					return $this->redirect(xianyu_user_url('user/reg/index', array('api' => 1)));
				} else {
					$data['username'] = $sns_info['nickname'] . rand_string(3);
					$data['password'] = rand_string(8);
					$info = $rs->reg($data['username'], '', $data['password']);
					if ($info['userid']) {
						if ($close == 1) {
							return view('/close');
						} else {
							return $this->redirect(xianyu_user_url('user/center/index'));
						}
					}
				}
				return false;
			}
		}
	}
}