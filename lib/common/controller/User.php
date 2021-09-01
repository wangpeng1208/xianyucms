<?php

namespace app\common\controller;

use app\common\library\Redis;
use \app\user\model\User as UserModel;

class User extends All
{
    protected $userInfo = null;

    public function _initialize()
    {
        parent::_initialize();
        $user_info = cmf_get_current_user();
        if (!$user_info) {
            $this->error("您尚未登录", xianyu_user_url("user/login/index"));
        }
        // 用户扩展资料
        $rs = new UserModel();
        $user_detail = $rs->getUserInfo(cmf_get_current_user_id());
        $this->userInfo = array_merge($user_info, $user_detail);
        if(!$this->userInfo['nickname']){
            // 未完善资料
           return $this->redirect(xianyu_user_url("user/login/edit"));
        }
        $this->assign('userinfo', $this->userInfo);
        $this->assign('user_nav', F('_data/usernavcenter'));
    }

    public function getToken()
    {
        return $this->request->header('access-token');
    }

    public function getUser()
    {
        return $this->redis->get(config('data_cache_prefix') . $this->getToken());
    }

    public function getUid()
    {
        return $this->getUser()['userid'];
    }
}
