<?php

namespace app\user\controller;

use app\common\controller\All;
use \app\user\model\User;
use \think\Validate;

class Reg extends All
{
    public function index()
    {

        if (!$this->config['userconfig']['user_reg']) {
            return $this->error('对不起,暂未对外开放用户注册!', config('site_url'));
        }
        if ($this->request->isGet()) {
            $redirect = $this->request->post("redirect");
            if (empty($redirect)) {
                $redirect = $this->request->server('HTTP_REFERER');
            } else {
                $redirect = base64_decode($redirect);
            }
            session('login_http_referer', $redirect);
            if (cmf_is_user_login()) {
                return redirect($this->config['root']);
            } else {
                return view('/reg');
            }

        }
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $rules = [
                'username' => 'require|unique:user|max:25',
                'nickname' => 'require',
                'password' => 'require|max:20|min:6',
//                'rePassword' => 'confirm:password',
            ];
            $validate = new Validate($rules);
            $validate->message([
                'username.require' => '用户名不能为空',
                'username.unique' => '用户名已经存在',
                'username.max' => '用户名不能超过25个字符',
                'nickname' => '昵称不能为空',
//                'email.unique' => '邮箱已被占用了',
//                'email.require' => '邮箱不能为空',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能不大于于20位字符',
                'password.min' => '密码不能不少于6位字符',
//                'rePassword.confirm' => '确认密码和密码必须一致',
            ]);
            if ($this->config['userconfig']['user_code']) {
                $rules['validate'] = 'require';
                $message['validate.require'] = '验证码不能为空';
            }
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if ($this->config['userconfig']['user_code'] && !cmf_captcha_check($data['validate'])) {
                $this->error('验证码错误!');
            }
            $register = new User();
            $user['password'] = $data['password'];
            $user['username'] = $data['username'];
            $user['nickname'] = $data['nickname'];
            // 推荐人
            $user['userpid'] = session('userpid') > 0 ? session('userpid') : 0;
            $log = $register->register($user);
            $sessionLoginHttpReferer = session('login_http_referer');
            switch ($log) {
                case 0:
                    // 未完善nickname email认证
//                    session('needEditUserInfo',1);
                    //是否需要跳转
                    if($data['jump']){
                        return json(['msg'=>"注册成功",'rcode'=>1,'redir'=>xianyu_user_url('user/center/index'),'wantredir'=>1]);
                    }else{
                        return json(['msg'=>"注册成功",'rcode'=>1]);
                    }
                    break;
                case 1:
                    $this->error("您的账户已注册过");
                    break;
                case 2:
                    $this->error("您输入的账号格式错误");
                    break;
                default :
                    $this->error('未受理的请求');
            }
        }

    }
    public function ajax()
    {
        return view('reg/ajax');
    }
    public function agreement()
    {
        return view('/agreement');
    }
}