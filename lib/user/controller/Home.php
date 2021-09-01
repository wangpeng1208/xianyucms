<?php

namespace app\user\controller;
use app\common\controller\All;
use app\user\model\User as UserModel;

class Home extends All
{
    //会员主页
    public function index()
    {
        $rs = new UserModel();
        $userid = input('id/d', 0);
        $userinfo = $rs->getUserInfo($userid);
        if (empty($userinfo['userid'])) {
            return $this->error('对不起,没有找到该用户!');
        }
        if (cmf_is_user_login() && $userid != cmf_get_current_user_id()) {
            model("Visitors")->add($userid, cmf_get_current_user_id());
        }
//        halt($userinfo);
        $this->assign("userinfo", $userinfo);
        return view('/home/index');

    }

    //登陆后信息
    public function flushinfo()
    {
        if (cmf_is_user_login() && $this->request->isAjax()) {
            $user_info = cmf_get_current_user();
            // 用户扩展资料
            $rs = new UserModel();
            $user_detail = $rs->getUserInfo(cmf_get_current_user_id());
            $userinfo = array_merge($user_info, $user_detail);
            $menus = F('_data/usernavindex');
            $result = [];
            $result['rcode'] = 1;
            $result['uid'] = $userinfo['userid'];
            $result['username'] = $userinfo['username'];
            $result['nickname'] = $userinfo['nickname'];
            $result['avatar'] = $userinfo['avatar'];
            $result['history'] = $userinfo['playlog_count'];
            $result['favorite'] = $userinfo['favorite_count'];
            $result['remind'] = $userinfo['remind_count'];
            $result['comment'] = $userinfo['comment_count'];
            $result['gb'] = $userinfo['gb_count'];
            $result['msg'] = $userinfo['msg_count'];

            $this->assign('userinfo', $userinfo);
            $this->assign('list', $menus);
            $result['html'] = $this->view->fetch('/home/flushinfo');
        } else {
            session('user', null);
            cookie('token', null);
            $result['rcode'] = -1;
        }
        return json($result);
    }

    public function addcomm()
    {
        if (!cmf_is_user_login()) {
            return json(["msg" => "请先登录", "rcode" => "-1"]);
        }
        if ($this->request->isPost()) {
            $userconfig = F('_data/userconfig_cache');
            $rs = model('Cm');
            $data = array();
            $data['cm_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.comm_txt/s'))));
            $data['cm_vid'] = input('post.comm_vid/d');
            $data['cm_uid'] = $this->userinfo['userid'];
            $data['cm_username'] = $this->userinfo['nickname'];
            $data['cm_sid'] = input('post.comm_sid/d', 1);
            $data['cm_pid'] = input('post.comm_pid/d', 0);
            $data['cm_addtime'] = time();
            $data['checktime'] = 1;
            if (empty($data['cm_content']) || $data['cm_content'] == '请在这里发表您的个人看法，最多1000个字。') {
                return json(["msg" => "弹幕内容不能为空或带非法字符,请填写提交内容!", "rcode" => "-1"]);
            }
            $cachename = 'cm-' . ip2long(get_client_ip()) . '-' . intval($data['cm_vid']) . '-' . intval($data['cm_uid']) . '-' . intval($data['cm_pid']) . '-' . intval($data['cm_sid']);
            $cache = cache($cachename);
            if ($cache) {
                return json(["msg" => "亲您发送弹幕太快,请休息一会，喝杯咖啡！", "rcode" => "-1"]);
            }
            $rs->validate('cm')->save($data);
            if ($rs->getError()) {
                return json(["msg" => "弹幕发送失败," . $rs->getError(), "rcode" => "-1"]);
            } else {
                if ($userconfig['user_cm_score']) {
                    model('Score')->user_score($this->userinfo['userid'], 11, intval($userconfig['user_cm_score']));
                }
                cache($cachename, 'ture', intval($userconfig['user_second']));
                if ($userconfig['user_check'] == 1) {
                    return json(["msg" => "弹幕发送成功,我们会尽快审核你的弹幕！", "rcode" => "1"]);
                } else {
                    return json(["msg" => "弹幕发送成功", "rcode" => "1"]);
                }
            }
        }

    }

    public function love()
    {
        $Url = param_url();
        $Url['limit'] = input('t/d', 10);
        config('model', $this->url);
        $JumpUrl = param_jump($Url);
        $JumpUrl['p'] = 'xianyupage';
        config('params', $JumpUrl);
        $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
        $rs = model("Favorite");
        $list = $rs->getFavoriteList($Url['id'], $Url['pid'], $page);//查询所有结果集
        if ($list->total() > $Url['limit']) {
            $pages = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";//数字分页
            $pages .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));//数字分页
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');//数字分页
        }
        $this->assign('list', $list);
        $ajax['total'] = $list->total();
        $ajax['pagetop'] = $pagebox;
        $ajax['pages'] = $pages;
        $ajax['ajaxtxt'] = $this->view->fetch('/home/love');
        return json($ajax);
    }

    public function remind()
    {
        $Url = param_url();
        $Url['limit'] = input('t/d', 10);
        config('model', $this->url);
        $JumpUrl = param_jump($Url);
        $JumpUrl['p'] = 'xianyupage';
        config('params', $JumpUrl);
        $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
        $rs = model("Remind");
        $list = $rs->getRemindList($Url['id'], $Url['pid'], $page);//查询所有结果集
        if ($list->total() > $Url['limit']) {
            $pages = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";//数字分页
            $pages .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));//数字分页
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');//数字分页
        }
        $this->assign('list', $list);
        $ajax['total'] = $list->total();
        $ajax['pagetop'] = $pagebox;
        $ajax['pages'] = $pages;
        $ajax['ajaxtxt'] = $this->view->fetch('/home/remind');
        return json($ajax);
    }

    public function Comm()
    {
        $Url = param_url();
        $Url['limit'] = input('t/d', 10);
        config('model', $this->url);
        $JumpUrl = param_jump($Url);
        $JumpUrl['p'] = 'xianyupage';
        config('params', $JumpUrl);
        $userconfig = F('_data/userconfig_cache');
        $where = array('cm_uid' => $Url['id']);
        $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
        $rs = model("Cm");
        $list = $rs->getcomments($where, $page);//查询所有结果集
        $data = $list->all();
        foreach ($list as $k => $v) {
            $data[$k]['cm_sub'] = $rs->getpidcomments(array('cm_pid' => $v['cm_id'], 'cm_sid' => $v['cm_sid'], 'cm_status' => 1));
        }
        //print_r($data) ;
        $page = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";//数字分页
        $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));//数字分页
        $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
        $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');//数字分页
        $this->assign('list', $data);
        $ajax['total'] = $list->total();
        $ajax['pagetop'] = $pagebox;
        $ajax['pages'] = $page;
        $ajax['ajaxtxt'] = $this->view->fetch('/home/cm');
        return json($ajax);
    }

    public function msg()
    {
        if ($this->request->isAjax()) {
            $uid = input('uid/d', '');
            $this->assign('userid', $uid);
            return view('/msg/add');
        }
    }


}