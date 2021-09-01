<?php

namespace app\user\controller;

use app\common\controller\User;
use app\user\model\Score;
use think\Db;

/**
 * Class 金币购买
 * @package app\user\controller
 */
class Buy extends User
{
    public function index()
    {
        if ($this->request->isPost()) {
            $user = DB::name('user')->field('userid,score,viptime')->find($this->userInfo['userid']);
            if (!$user) {
                $result = ['msg' => '用户不存在', 'rcode' => -1];
            }else{
                $vip_day = intval(input('post.vip_day/d'));
                if ($vip_day >= 1) {
                    $rs = new Score();
                    $result = $rs->user_viptime($this->userInfo['userid'], $user['viptime'], $user['score'], $vip_day);
                } else {
                    $result = ['msg' => '购买天数必须大于1', 'rcode' => -1];
                }
            }
            return json($result);
        } else {
            return view('index');
        }
    }

}