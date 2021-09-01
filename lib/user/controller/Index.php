<?php


namespace app\user\controller;


use app\common\controller\All;
use app\user\model\User;

class Index extends All
{
    // 前台用户首页 （公开）
    public function index(){
        $id        = $this->request->param("id", 0, "intval");
        $userModel = new User();
        $user      = $userModel->where('id', $id)->find();
        if (empty($user)) {
            $this->error("查无此人！");
        }
        $this->assign('user',$user);
        return $this->fetch(":index");
    }
    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (cmf_is_user_login()) {
            $this->success("用户已登录",null,['user'=>cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session("user", null);//只有前台用户退出
        return redirect($this->request->root() . "/");
    }

}