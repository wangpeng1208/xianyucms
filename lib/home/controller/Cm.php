<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\library\Redis;
use app\user\model\Score;
use think\Controller;
use think\Db;

class Cm extends Home
{
    //构造函数
    public function _initialize()
    {
        parent::_initialize();
        $this->redis = new Redis();
        $this->userconfig = F('_data/userconfig_cache');
        $path = config('template.view_path', ROOT_PATH . 'tpl' . DS . 'user' . DS) . config('user_theme') . DS;
        $this->view->config('view_path', $path);
    }

    public function index()
    {
        $data = $this->redis->get('qqqqe993aa091a91d681c663ca47a44ba5d80c61a369');
        print_r($data);
    }

    public function addChat()
    {
        if ($this->request->instance()->isAjax()) {
            $data = input('data');
            $data = json_decode($data, true);
            //            需要验证数据是否合法
            $token = $data['sign'];
            // 查询用户表里的sign
            $user_token = Db::name('user')->where('userid', $data['cm_uid'])->find('token');
            if ($token !== $user_token) {
                return false;
            }
            $data['cm_status'] = 1;
            $data['cm_ip'] = ip2long(get_client_ip());
            $data['cm_addtime'] = time();
//            $rs=model('Cm');
//            halt($rs);
            Db::name('cm')->insert($data);
//            静默不返回任何数据

        }
    }

    public function get()
    {
        if ($this->request->isAjax()) {
            $p = input('p/d', 1);
            config('model', $this->url);
            $JumpUrl = param_jump($this->Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $result = array("ajaxtxt" => '');
            $total = 0;
            if (!empty($this->Url['id']) && !empty($this->Url['sid'])) {
                $rs = model("Cm");
                $where = array('cm_vid' => $this->Url['id'], 'cm_sid' => $this->Url['sid'], 'cm_pid' => 0, 'cm_status' => 1);
                $total = $rs->getcmcount($where);
                if ($total > 0) {
                    $limit = $this->userconfig['user_cmnum'];
                    $page = array('limit' => $limit, 'currentpage' => $this->Url['p']);
                    $list = $rs->getcomments($where, $page);  //查询所有结果集
                    $data = $list->all();
                    foreach ($list as $k => $v) {
                        $data[$k]['cm_sub'] = $rs->getpidcomments(array('cm_pid' => $v['cm_id'], 'cm_sid' => $v['cm_sid'], 'cm_status' => 1));
                        if ($data[$k]['cm_sub']) {
                            foreach ($data[$k]['cm_sub'] as $kk => $var) {
                                if ($var['cm_tid']) {
                                    $data[$k]['cm_sub'][$kk]['cm_tub'] = Db::name('Cm')->alias('c')->join('user u', 'u.userid=c.cm_uid', 'LEFT')->where("cm_id = " . $var['cm_tid'] . "")->find();
                                }
                            }
                        }
                    }
                    //print_r($data) ;
                    // $result['ajaxtxt'] = $this->outputPublicCommns($data,$list->total(),$limit,$p);
                    $pages = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";//数字分页
                    $pages .= getpage('user/cm/ajax', config('params'), $list->currentPage(), $list->total(), $limit, config('home_pagenum'));//数字分页
                    $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
                    $pagebox .= gettoppage('user/cm/ajax', config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');//数字分页
                    $this->assign('list', $data);
                    $this->assign('limit', $limit);
                    $this->assign('page', $p);
                    $this->assign('total', $list->total());
                    $result['pagetop'] = $pagebox;
                    $result['pages'] = $pages;
                    $result['count'] = $list->total();
                    $result['total'] = $list->total();
                    $result['ajaxtxt'] = $this->view->fetch('/cm/all');
                }
                //获取其它数据信息
                $rs = model('VodMark');
                $start = array();
                $markdata = $rs->getMark($this->Url['id']);
                $value = $rs->getMarkValue($this->Url['id'], get_client_ip());
                if ($value > 0) {
                    $start['hadpingfen'] = 1;
                    $start['mystars'] = $value;
                } else {
                    $start['mystars'] = 0;
                }
                $start['curpingfen'] = array();
                $vod_gold = Db::name('vod')->where(array('vod_id' => $this->Url['id'], 'vod_status' => 1))->value('vod_gold');
                if ($markdata != null) {
                    $start['curpingfen']['a'] = $markdata['F5'];
                    $start['curpingfen']['b'] = $markdata['F4'];
                    $start['curpingfen']['c'] = $markdata['F3'];
                    $start['curpingfen']['d'] = $markdata['F2'];
                    $start['curpingfen']['e'] = $markdata['F1'];
                    $rate = $rs->getRating($markdata);
                    if (count($rate) > 0) {
                        $start['curpingfen']['pinfenb'] = $rate['R2'];
                        $start['curpingfen']['pinfen'] = $rate['R1'];
                    } else {
                        $start['curpingfen']['a'] = 0;
                        $start['curpingfen']['b'] = 0;
                        $start['curpingfen']['c'] = 0;
                        $start['curpingfen']['d'] = 0;
                        $start['curpingfen']['e'] = 0;
                        $start['curpingfen']['pinfenb'] = $vod_gold * 10;
                        $start['curpingfen']['pinfen'] = $vod_gold;
                    }
                }
                $start['curpingfen']['num'] = $total;
                //是否收藏
                $userinfo = session('user');

                if ($userinfo['userid']) {
                    $favorite = Db::name('favorite')->where(array('favorite_vid' => $this->Url['id'], 'favorite_uid' => $userinfo['userid']))->value('favorite_id');

                    if ($favorite) {
                        $start['loveid'] = $this->Url['id'];
                    }
                    $remin = Db::name('remind')->where(array('remind_vid' => $this->Url['id'], 'remind_uid' => $userinfo['userid']))->value('remind_id');

                    if ($remin) {
                        $start['remindid'] = $this->Url['id'];
                    }
                }
                $result['star'] = $start;
            }
            return json($result);
        }
    }

    public function ajax()
    {
        if ($this->request->isAjax()) {
            $p = input('p/d', 1);
            $JumpUrl = param_jump($this->Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $result = array("ajaxtxt" => '');
            $total = 0;
            if (!empty($this->Url['id']) && !empty($this->Url['sid'])) {
                $rs = model("Cm");
                $where = array('cm_vid' => $this->Url['id'], 'cm_sid' => $this->Url['sid'], 'cm_pid' => 0, 'cm_status' => 1);
                $total = $rs->getcmcount($where);
                if ($total > 0) {
                    $limit = $this->userconfig['user_cmnum'];
                    $page = array('limit' => $limit, 'currentpage' => $this->Url['p']);
                    $list = $rs->getcomments($where, $page);  //查询所有结果集
                    $data = $list->all();
                    foreach ($list as $k => $v) {
                        $data[$k]['cm_sub'] = $rs->getpidcomments(array('cm_pid' => $v['cm_id'], 'cm_sid' => $v['cm_sid'], 'cm_status' => 1));
                        if ($data[$k]['cm_sub']) {
                            foreach ($data[$k]['cm_sub'] as $kk => $var) {
                                if ($var['cm_tid']) {
                                    $data[$k]['cm_sub'][$kk]['cm_tub'] = Db::name('Cm')->alias('c')->join('user u', 'u.userid=c.cm_uid', 'LEFT')->where("cm_id = " . $var['cm_tid'] . "")->find();
                                }
                            }
                        }
                    }
                    $pages = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";//数字分页
                    $pages .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $limit, config('home_pagenum'));//数字分页
                    $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
                    $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');//数字分页
                    $this->assign('list', $data);
                    $this->assign('limit', $limit);
                    $this->assign('page', $p);
                    $this->assign('total', $list->total());
                    $result['pagetop'] = $pagebox;
                    $result['pages'] = $pages;
                    $result['count'] = $list->total();
                    $result['total'] = $list->total();
                    $result['ajaxtxt'] = $this->view->fetch('/cm/all');
                }
            }
            return json($result);
        }
    }

    private function gettan($id)
    {
        if ($id > 0) {
            $comment = Db::name('Cm')->alias('c')->join('user u', 'u.userid=c.cm_uid', 'LEFT')->where("cm_id = " . $id . "")->find();
            return "回复 <a href='" . xianyu_user_url('user/home/index', array('id' => $comment['cm_uid']), true, true) . "' target='_blank'>" . $comment['nickname'] . "</a>";
        }
    }

    public function addcm()
    {
        if ($this->request->isPost()) {
            $userinfo = cmf_get_current_user();
            if (!cmf_is_user_login()) {
                return json(["msg" => "请先登录", "rcode" => "-2"]);
            }
            $rs = model('Cm');
            $data = array();
            $data['cm_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.comm_txt/s'))));
            $data['cm_vid'] = input('id/d');
            $data['cm_uid'] = $userinfo['userid'];
            $data['cm_username'] = $userinfo['nickname'];
            $data['cm_sid'] = input('sid/d', 1);
            $data['cm_pid'] = input('post.comm_pid/d', 0);
            $data['cm_addtime'] = time();
            $data['checktime'] = 1;
            if (empty($data['cm_content']) || $data['cm_content'] == '请在这里发表您的个人看法，最多1000个字。') {
                return json(["msg" => "内容不能为空或带非法字符,请填写提交内容!", "rcode" => "-1"]);
            }
            $cookiename = md5('cm-' . ip2long(get_client_ip()) . '-' . intval($data['cm_vid']) . '-' . intval($data['cm_uid']) . '-' . intval($data['cm_pid']) . '-' . intval($data['cm_sid']));
            $cookie = cookie($cookiename);
            if ($cookie) {
                return json(["msg" => "亲您评论速度太快了,请休息一会，喝杯咖啡！", "rcode" => "-1"]);
            }
            $rs->validate('cm')->save($data);
            if ($rs->getError()) {
                return json(["msg" => "评论提交失败," . $rs->getError(), "rcode" => "-1"]);
            } else {
                if ($this->userconfig['user_cm_score']) {
                    // 积分
                    $score = new Score();
                    $score->user_score($userinfo['userid'], 11, intval($this->userconfig['user_cm_score']));
                }
                cookie($cookiename, true, intval($this->userconfig['user_second']));
                if ($this->userconfig['user_check'] == 1) {
                    return json(["msg" => "评论提交成功,我们会尽快审核你的留言！", "rcode" => "1"]);
                } else {
                    return json(["msg" => "评论提交成功", "rcode" => "1"]);
                }
            }
        }
    }

    public function addrecm()
    {
        if ($this->request->isPost()) {
            $userinfo = session('user_auth');
            if (!$userinfo['userid']) {
                return json(["msg" => "请先登录", "rcode" => "-2"]);
            }
            $cm_pid = input('post.comm_pid/d', 0);
            $cm_id = input('post.comm_id/d', 0);
            $cm_tuid = input('post.comm_tuid/d', 0);
            $cm_vid = input('post.comm_vid/d', 0);
            $cm_sid = input('post.comm_sid/d', 1);
            $rs = model('Cm');
            $data = array();
            $data['cm_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.recomm_txt/s', ''))));
            $data['cm_vid'] = $cm_vid;
            $data['cm_uid'] = $userinfo['userid'];
            $data['cm_username'] = $userinfo['nickname'];
            $data['cm_sid'] = $cm_sid;
            $data['cm_pid'] = empty($cm_pid) ? $cm_id : $cm_pid;
            $data['cm_tid'] = !empty($cm_pid) ? $cm_id : 0;
            $data['cm_tuid'] = !empty($cm_tuid) ? $cm_tuid : 0;
            $data['cm_addtime'] = time();
            $data['checktime'] = 1;
            if (empty($data['cm_content']) || $data['cm_content'] == '请在这里发表您的个人看法，最多1000个字。') {
                return json(["msg" => "内容不能为空或带非法字符,请填写提交内容!", "rcode" => "-1"]);
            }
            $cookiename = md5('cm-' . ip2long(get_client_ip()) . '-' . intval($data['cm_vid']) . '-' . intval($data['cm_uid']) . '-' . intval($data['cm_pid']) . '-' . intval($data['cm_sid']));
            $cookie = cookie($cookiename);
            if ($cookie) {
                return json(["msg" => "亲您评论速度太快了,请休息一会，喝杯咖啡！", "rcode" => "-1"]);
            }
            $rs->validate('cm')->save($data);
            if ($rs->getError()) {
                return json(["msg" => "评论提交失败," . $rs->getError(), "rcode" => "-1"]);
            } else {
                if ($this->userconfig['user_cm_score']) {
                    model('Score')->user_score($userinfo['userid'], 11, intval($this->userconfig['user_cm_score']));
                }
                cookie($cookiename, true, intval($this->userconfig['user_second']));
                if ($this->userconfig['user_check'] == 1) {
                    return json(["msg" => "评论提交成功,我们会尽快审核你的留言！", "rcode" => "1"]);
                } else {
                    return json(["msg" => "评论提交成功", "rcode" => "1"]);
                }
            }
        }
    }

    //评论顶踩
    public function digg()
    {
        $comment_id = $this->Url['id'];
        $type = $this->Url['type'];
        $success = false;
        //不需要登录
        if (!empty($comment_id) && !empty($type)) {
            $ip = ip2long(get_client_ip());
            $count = Db::name('cm_opinion')->where(array('opinion_cmid' => $comment_id, 'opinion_ip' => $ip))->count();
            if ($count > 0) { //已经
                $result = array("msg" => "已经点评", "rcode" => "-1");
                $success = true;
            } else {
                $opinion = -1;
                $key = '';
                if ($type == 'sup') {
                    $opinion = 1;
                    $key = 'cm_support';
                } else if ($type == 'opp') {
                    $opinion = 0;
                    $key = 'cm_oppose';
                }
                if ($opinion >= 0) {
                    $data['opinion_cmid'] = $comment_id;
                    $data['opinion_opinion'] = $opinion;
                    $data['opinion_addtime'] = time();
                    $data['opinion_ip'] = $ip;
                    $id = Db::name('cm_opinion')->insertGetId($data);
                    if ($id > 0) {
                        //修改值
                        $addValue = Db::name('Cm')->where('cm_id', $comment_id)->setInc($key);
                        $result = array("msg" => "成功点评", "rcode" => "1");
                        $success = true;
                    }
                }
            }
        }
        if (!$success) {
            $result = array("msg" => "点评失败", "rcode" => "-1");
        }
        return json($result);
    }

    public function emots()
    {
        $this->assign('list_emots', F('_data/list_emots'));
        return view('/cm/emots');

    }
}