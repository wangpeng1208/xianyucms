<?php

namespace app\home\controller;

use think\Controller;

class User extends Controller
{
    public function loginout()
    {
        $jump = input('jump/d', 0);
        session('user', null);
        session('token', null);
        cookie('token', null);
        if ($jump) {
            return json(["msg" => "退出成功", "redir" => '/', "rcode" => "1", 'wantredir' => 1]);
        } else {
            return json(["msg" => "退出成功", "rcode" => 1]);
        }
    }
}