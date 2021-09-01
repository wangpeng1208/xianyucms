<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Request;

class Gb extends Home
{
    private $userConfig = null;
    public function __construct(Request $request = null)
    {
        $this->userConfig = F('_data/userconfig_cache');
        parent::__construct($request);
    }

    public function show()
    {
        $JumpUrl = param_jump($this->Url);
        $JumpUrl['p'] = 'xianyupage';
        config('params', $JumpUrl);
        if ($this->Url['id']) {
            $info = get_vod_find($this->Url['id'], 'vod_id', 'vod_id,vod_name,vod_cid,vod_actor');
            if ($info) {
                $this->assign($info);
                $this->assign('gb_content', '影片ID' . $info['vod_id'] . '点播出现错误！名称：' . $info['vod_name'] . ' 主演：' . $info['vod_actor']);
            }
        }
        if (cmf_is_user_login()) {
            $session = session('user');
            $this->assign($session);
        }
        $this->assign('param', $this->Url);
        $this->assign('page', $this->Url['p']);
        if ($this->request->isAjax()) {
            return view('/guestbook_ajax');
        } else {
            return view('/guestbook');
        }
    }


    public function add()
    {
        $rs = new Gb();
        if ($this->request->isPost()) {
            if ($this->userConfig['user_code']) {
                //验证码验证
                $result = cmf_captcha_check(input('post.validate/s'));
                if ($result != 1) {
                    return json(['msg' => "验证码错误", 'rcode' => -1]);
                }
            }
            $data['gb_uid'] = cmf_is_user_login();
            $data['gb_cid'] = input('post.gb_cid/d');
            $data['gb_vid'] = input('post.gb_vid/d', 0);
            $data['gb_nickname'] = htmlspecialchars(strip_tags(remove_xss(input('post.gb_nickname/s'))));
            $data['gb_title'] = htmlspecialchars(strip_tags(remove_xss(input('post.gb_title/s'))));
            $data['gb_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.gb_content/s'))));
            $id = $rs->add($data);
            if ($id == 1) {
                if ($this->userConfig['user_check'] == 1) {
                    return json(['msg' => "添加留言成功,我们会尽快审核", 'rcode' => 1]);
                }
                return json(['msg' => "添加留言成功", 'rcode' => 1]);
            } else {
                return json(['msg' => "添加留言失败" . $id, 'rcode' => -1]);
            }
        }

    }

    function checkcode($code, $id = 1)
    {
        if ($code) {
            $verify = new \org\Verify(array('reset' => false));
            $result = $verify->check($code, $id);
            if (!$result) {
                return "验证码错误";
            } else {
                return true;
            }
        } else {
            return "验证码为空";
        }
    }
}


