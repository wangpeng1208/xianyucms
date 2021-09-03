<?php

namespace app\admin\controller;

use think\Controller;

class login extends Controller
{
    //默认操作
    public function index($username = '', $password = '', $verify = '')
    {
        if (is_login()) {
            return $this->redirect('admin/index/index');
        }
        session_start();
        $logkey = md5(md5(config('xianyucms_login')) . 'hanju');
        // echo $logkey;
        if ($_SESSION['xianyucms_login'] != $logkey && session('xianyucms_login') != $logkey) {
            $_SESSION['xianyucms_login'] = "";
            session('xianyucms_login', null);
            return $this->display('请从后台登录入口登录');
        }
        if (\think\Request::instance()->Post()) {
            if (!$username || !$password) {
                return $this->error('用户名或者密码不能为空！');
            }
            if (config('admin_login_validate')) {
                $this->checkVerify($verify);
            }
            $user = model('Member');
            $uid = $user->login($username, $password);
            if ($uid > 0) {
                action_log('admin/login/index', 'login', session('member_auth.uid'), 1);
                return $this->success('登录成功！', url('admin/index/index'));
            } else {
                switch ($uid) {
                    case -1:
                        $error = '用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:
                        $error = '密码错误！';
                        break;
                    default:
                        $error = '未知错误！';
                        break; // 0-接口参数错误（调试阶段使用）
                }
                action_log('admin/login/index', 'login', 0, 0, $error);
                return $this->error($error);
            }
        } else {
            $apicss = config('site_path') . PUBLIC_PATH . 'tpl/';
            $this->assign('cdnurl', $apicss);
            $this->assign('validate', config('admin_login_validate'));
            return view('login', [], ['</body>' => config('xianyucms.copyright')]);
        }
    }

    /**
     * 验证码
     * @param integer $id 验证码ID
     */
    public function verify($id = 'admin')
    {
        $verify = new \org\Verify(array('length' => 4, 'imageH' => 32, 'imageW' => 100, 'fontSize' => 14));
        $verify->entry($id);
    }

    /**
     * 检测验证码
     * @param integer $id 验证码ID
     * @return boolean     检测结果
     */
    public function checkVerify($code, $id = 'admin')
    {
        if ($code) {
            $verify = new \org\Verify();
            $result = $verify->check($code, $id);
            if (!$result) {
                return $this->error("验证码错误！");
            }
        } else {
            return $this->error("验证码为空！");
        }
    }

    // 用户登出
    public function logout()
    {
        if (session('member_auth')) {
            action_log('admin/login/logout', 'login', session('member_auth.uid'), 1);
            session('member_auth', null);
            session('member_auth_sign', null);
            session('xianyucms_login', null);
            session_start();
            $_SESSION['xianyucms_login'] = "";
            return $this->success("退出成功", "admin/login/index");
        } else {
            return $this->error("退出失败");
        }
    }
}