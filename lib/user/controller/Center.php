<?php


namespace app\user\controller;

use app\common\controller\User;
use app\common\model\Vod;
use app\user\model\Favorite;
use app\user\model\Remind;
use app\user\model\Score;
use app\user\model\User as UserModel;
use app\user\model\UserDetail;
use cmf\lib\Storage;
use think\Db;
use think\Image;
use think\Validate;

class Center extends User
{
    protected $userId = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->userId = $this->userInfo['userid'];

    }

    /**
     * 用户首页
     */
    public function index()
    {
        return view('/center_index');
    }

    /**
     * 用户资料修改页
     */
    public function info()
    {
//	    halt($this->userInfo);
        return view('/center_info');
    }

    /**
     * 用户资料 修改
     * @return \think\response\Json
     */
    public function infoSetting()
    {
        if ($this->request->isPost()) {
            $data = input('post.info/a');
            // 数据校验
            // 数据更新
            $rs = new UserDetail();
            $result = $rs->editUser($data);
            if ($result !== false) {
                return json(["msg" => "资料修改成功", "rcode" => "1"]);
            } else {
                return json(['msg' => '资料修改失败', 'rcode' => '-1']);
            }
        }
    }

    /**
     * 订阅设置 subscriptionSetting
     */
    public function subscriptionSetting()
    {
        if ($this->request->isPost()) {
            $data = input('post.info/a');
            if ($data['isRemind'] == 1) {
                if (empty($data['iemail'])) {
                    return json(["msg" => "订阅设置失败,订阅邮箱必须填写", "rcode" => "-1"]);
                }
                // 数据校验
                $rules = [
                    'iemail' => 'require|email',
                ];
                $message = [
                    'iemail' => '邮箱格式不正确',
                ];
                $validate = new Validate($rules);
                $validate->message($message);
                if (!$validate->check($data)) {
                    $this->error($validate->getError());
                }
            }
            // 强制1 或 0
            $data['isRemind'] = !empty($data['isRemind']) ? 1 : 0;
            $data['isstation'] = !empty($data['isstation']) ? 1 : 0;
            // 数据更新
            $rs = new UserModel();
            $result = $rs->editData($data, 'isstation,isRemind,iemail');
            if ($result !== false) {
                return json(["msg" => "订阅修改成功", "rcode" => "1"]);
            } else {
                return json(['msg' => '订阅修改失败', 'rcode' => '-1']);
            }
        }
    }

    /**
     * 个人中心修改密码提交
     */
    public function passwordSetting()
    {
        if ($this->request->isPost()) {
            $validate = new Validate([
                'old_password' => 'require|min:6|max:32',
                'password' => 'require|min:6|max:32',
                'repassword' => 'require|min:6|max:32',
            ]);
            $validate->message([
                'old_password.require' => '旧密码不得为空！',
                'old_password.max' => '旧密码不能超过32个字符！',
                'old_password.min' => '旧密码不能小于6个字符！',
                'password.require' => '新密码不能为空！',
                'password.max' => '新密码不能超过32个字符！',
                'password.min' => '新密码不能超过32个字符！',
                'repassword.require' => '重复密码不能为空！',
                'repassword.max' => '重复密码不能超过32个字符！',
                'repassword.min' => '重复密码不能小于6个字符！',
            ]);

            $data = input('post.info/a');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $login = new UserModel();
            $log = $login->editPassword($data);
            switch ($log) {
                case 0:
                    $this->success('修改成功');
                    break;
                case 1:
                    $this->error('密码输入不一致!');
                    break;
                case 2:
                    $this->error('原始密码不正确');
                    break;
                default :
                    $this->error('请求错误');
            }
        } else {
            $this->error('请求错误');
        }

    }

    // 用户头像上传
    public function avatarUpload()
    {

            $file = $this->request->file('file');
            $result = $file->validate([
                'ext' => 'jpg,jpeg,png',
                'size' => 1024 * 1024
            ])->move('uploads' . DIRECTORY_SEPARATOR . 'avatar' . DIRECTORY_SEPARATOR);

            if ($result) {
                $avatarSaveName = str_replace('//', '/', str_replace('\\', '/', $result->getSaveName()));
                $avatar = '/uploads/avatar/' . $avatarSaveName;
                Db::name('user')->where('userid', $this->userId)->update([
                    'avatar' => $avatar
                ]);
                session('user.avatar', $avatar);
                return json(["msg" => "修改成功", "code" => "1"]);
            } else {
                return json(["msg" => "修改失败", "code" => "-1"]);
            }

    }

    public function sns()
    {
        $snsconfig = get_addon_config('snslogin');
        $del = htmlspecialchars(input('del/s', ''));
        if ($del) {
            $where['uid'] = $this->userinfo['userid'];
            $where['channel'] = $del;
            $id = db('user_api')->where($where)->delete();
            if ($id) {
                return $this->success('删除成功！', url('user/center/sns'));
            } else {
                return $this->error('删除失败！', url('user/center/sns'));
            }
        } else {
            $this->assign($snsconfig);
            return view('/center_sns');
        }
    }

    public function iemail()
    {

        if ($this->request->isPost()) {
            $data = input('post.info/a');
            $data['password'] = htmlspecialchars($data['password']);
            if (!cmf_compare_password($data['password'], $this->userinfo['password'])) {
                return json(["msg" => "原密码错误请重新输入", "rcode" => "-1"]);
            }
            $result = $this->validate($data, 'user.email');
            if ($result != 1) {
                return json(['msg' => $result, 'rcode' => -1]);
            }
            $data['userid'] = $this->userinfo['userid'];
            $rs = new UserModel();
            $rs->editData($data, 'email');
            if ($rs !== false) {
                return json(['msg' => "修改邮箱成功", 'rcode' => 1]);
            } else {
                return json(['msg' => '修改邮箱失败', 'rcode' => '-1']);
            }
        }
        return view('/center/iemail');
    }

    public function getall()
    {
        $Url = param_url();
        $Url['limit'] = input('t/d', 5);
//		config('model', $this->url);
        $JumpUrl = param_jump($Url);
        $JumpUrl['p'] = 'xianyupage';
        config('params', $JumpUrl);
        $where = array('cm_uid' => $this->userId);
        $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
        $rs = model("Cm");
        $list = $rs->getcomments($where, $page);
        $page = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
        $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
        $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
        $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
        $this->assign('list', $list);
        $ajax['total'] = $list->total();
        $ajax['pagetop'] = $pagebox;
        $ajax['pages'] = $page;
        $ajax['ajaxtxt'] = $this->view->fetch('/cm/getall');
        return json($ajax);
    }

    /**
     * 订阅页面
     */
    public function remind()
    {
        $rs = new Remind();
        $catalogs = $rs->getRemindCatalogs($this->userId);
        $this->assign('catalogs', $catalogs);
        if ($this->request->isAjax()) {
            $Url = param_url();
            $Url['limit'] = input('t/d', 10);
            config('model', $this->url);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $list = $rs->getRemindList($this->userId, $Url['pid'], $page);
            $page = "<span class='total'>共" . $list->total() . "条记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/remind/ajax');
            $ajax['ajaxnav'] = $this->view->fetch('/remind/nav');
            return json($ajax);
        }
        return view('/remind/index');
    }

    /**
     * 热门订阅
     */
    public function getremind()
    {
        $rs = new Remind();
        $list = $rs->getRemindDatas($this->userId);
        $this->assign('list', $list);
        $ajaxtxt = $this->view->fetch('/remind/hot');
        return json($ajaxtxt);
    }

    /**
     * 点击订阅
     */
    public function saveRemind()
    {
        $Url = param_url();
        $result = array();
        if (isset($Url['id'])) {
            $rs = model("Vod");
            $info = $rs->where('vod_id', $Url['id'])->field("vod_cid,vod_addtime")->find();
            $remind = new Remind();
            $data['remind_vid'] = $Url['id'];
            $data['remind_uid'] = $this->userId;
            $data['remind_cid'] = getlistpid($info['vod_cid']);
            $data['remind_addtime'] = time();
            $data['remind_uptime'] = $info['vod_addtime'];
            $remind->save($data);
            if ($remind->remind_id > 0) {
                $catalogs = $remind->getRemindCatalogs($this->userId);
                $this->assign('catalogs', $catalogs);
                $result = array("msg" => "订阅成功", "rcode" => 1, "pids" => $this->view->fetch('/remind/nav'));
            } else {
                $result = array("msg" => "订阅失败", "rcode" => -1);
            }
        } else {
            $result = array("msg" => "订阅失败", "rcode" => -1);
        }
        return json($result);
    }

    public function getplayurl()
    {
        $Url = param_url();
        if ($Url['id']) {
            $home = new Home();
            $array = get_vod_find($Url['id'], 'vod_id', 'vod_id,vod_cid,vod_letters,vod_name,vod_url,vod_play,vod_copyright');
            $playlist = $home->playlist_all($array, getlistname($array['vod_cid'], 'list_dir'));
            if (empty($array['vod_playoid'])) {
                ksort($playlist[0]);
            }
            $arrays['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => getlistname($array['vod_cid'], 'list_dir'), 'jumpurl' => $array['vod_jumpurl']));
            $arrays['vod_playlist'] = $playlist[0];
        }
        $this->assign($arrays);
        $ajaxtxt = $this->view->fetch('/remind/playurl');
        return json($ajaxtxt);
    }

    public function delremind()
    {
        $Url = param_url();
        $rs = new Remind();
        if ($this->request->isPost()) {
            $Url['id'] = input('post.ids/a');
        }
        $where['remind_id'] = array('IN', $Url['id']);
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            $catalogs = $rs->getRemindCatalogs($this->userId);
            $this->assign('catalogs', $catalogs);
            return json(['msg' => '删除成功', 'rcode' => "1", 'pids' => $this->view->fetch('/remind/nav')]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }

    public function love()
    {
        $rs = new Favorite();
        if ($this->request->isAjax()) {
            $Url = param_url();
            $Url['limit'] = input('t/d', 10);
            config('model', $this->url);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $list = $rs->getFavoriteList($this->userId, $Url['pid'], $page);
            $page = "<span class='total'>共" . $list->total() . "条记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/love/ajax');
            return json($ajax);
        }
        $catalogs = $rs->getFavoriteCatalogs($this->userId);
        $this->assign('catalogs', $catalogs);
        return view('/love/index');
    }

    public function getlove()
    {
        $rs = new Favorite();
        $list = $rs->getFavoriteDatas($this->userId);
        $this->assign('list', $list);
        $ajaxtxt = $this->view->fetch('/love/hot');
        return json($ajaxtxt);
    }

    public function savelove()
    {
        $Url = param_url();
        $result = array();
        if (isset($Url['id'])) {
            $rs = new Vod();
            $info = $rs->where('vod_id', $Url['id'])->field("vod_cid,vod_addtime")->find();
            $favorite = new Favorite();
            $data['favorite_vid'] = $Url['id'];
            $data['favorite_uid'] = $this->userId;
            $data['favorite_cid'] = getlistpid($info['vod_cid']);
            $data['favorite_addtime'] = time();
            $favorite->save($data);
            if ($favorite->favorite_id > 0) {
                $catalogs = $favorite->getFavoriteCatalogs($this->userId);
                $this->assign('catalogs', $catalogs);
                $result = array("msg" => "收藏成功", "rcode" => 1, "pids" => $this->view->fetch('/love/nav'));
            } else {
                $result = array("msg" => "收藏失败", "rcode" => -1);
            }
        } else {
            $result = array("msg" => "收藏失败", "rcode" => -1);
        }
        return json($result);
    }

    public function dellove()
    {
        $Url = param_url();
        $rs = model("Favorite");
        if ($this->request->isPost()) {
            $Url['id'] = input('post.ids/a');
        }
        $where['favorite_id'] = array('IN', $Url['id']);
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            $catalogs = $rs->getFavoriteCatalogs($this->userId);
            $this->assign('catalogs', $catalogs);
            return json(['msg' => '删除成功', 'rcode' => "1", 'pids' => $this->view->fetch('/love/nav')]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }

    public function cm()
    {
        if ($this->request->isAjax()) {
            $Url = param_url();
            $Url['limit'] = input('t/d', 10);
            config('model', $this->url);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $where = array('cm_uid' => $this->userId);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $rs = model("Cm");
            $list = $rs->getcomments($where, $page);
            $page = "<span class='total'>共" . $list->total() . "条评论&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/cm/ajax');
            return json($ajax);
        }
        return view('/cm/index');
    }

    public function delcm()
    {
        $Url = param_url();
        $rs = model("Cm");
        if ($this->request->isPost()) {
            $Url['id'] = input('post.ids/a');
        }
        $where['cm_id'] = array('IN', $Url['id']);
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            return json(["msg" => "删除成功", "rcode" => "1"]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }

    public function msg()
    {
        if ($this->request->isAjax()) {
            $Url = param_url();
            config('model', $this->url);
            $Url['limit'] = input('t/d', 10);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $userconfig = F('_data/userconfig_cache');
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $rs = model("Msg");
            $list = $rs->getmsgs($where, $page);
            $page = "<span class='total'>共" . $list->total() . "条记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/msg/ajax');
            return json($ajax);
        }
        return view('/msg/index');
    }

    public function delmsg()
    {
        $Url = param_url();
        $rs = model("Msg");
        if ($this->request->isPost()) {
            $Url['id'] = input('post.ids/a');
        }
        $where['msg_id'] = array('IN', $Url['id']);
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            return json(["msg" => "删除成功", "rcode" => "1"]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }



    public function playlog()
    {
        if ($this->request->isAjax()) {
            $Url = param_url();
            config('model', $this->url);
            $Url['limit'] = input('t/d', 10);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $userconfig = F('_data/userconfig_cache');
            $where = array('log_uid' => $this->userId);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $rs = new \app\user\model\Playlog();
            $list = $rs->getplaylog($where, $page);
            $page = "<span class='total'>共" . $list->total() . "条播放记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/playlog/ajax');
            return json($ajax);
        }
        return view('/playlog/index');
    }

    public function dellog()
    {
        $Url = param_url();
        $rs = model("Playlog");
        if ($Url['uid']) {
            $where['log_uid'] = $Url['uid'];
        } else {
            if ($this->request->isPost()) {
                $Url['id'] = input('post.ids/a');
            }
            $where['log_id'] = array('IN', $Url['id']);
        }
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            return json(["msg" => "删除成功", "rcode" => "1"]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }

    public function gb()
    {
        $cachename = 'gb-' . ip2long(get_client_ip()) . '-' . intval(1);
        $cahca = cache($cachename);
        $rs = model("Gb");
        $catalogs = $rs->getGbCatalogs($this->userId);
        $gbcid = array(1 => '其他留言', 2 => '影片报错', 3 => '网站建议', 4 => '访问故障');
        $this->assign("list_name", $gbcid);
        $this->assign("catalogs", $catalogs);
        if ($this->request->isAjax()) {
            $Url = param_url();
            config('model', $this->url);
            $Url['limit'] = input('t/d', 10);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $list = $rs->getGbList($this->userId, $Url['pid'], $page);
            $page = "<span class='total'>共" . $list->total() . "条留言&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/gb/ajax');
            return json($ajax);
        }
        return view('/gb/index');
    }

    public function addgb()
    {
        $userconfig = F('_data/userconfig_cache');
        $rs = model("Gb");
        if ($this->request->isPost()) {
            $post = input('post.gb/a');
            if ($userconfig['user_code']) {
                $result = $this->checkcode(input('post.validate/s'));
                if ($result != 1) {
                    return json(['msg' => "验证码错误", 'rcode' => -1]);
                }
            }
            $data['gb_uid'] = $this->userId;
            $data['gb_cid'] = input('post.gb_cid/d');
            $data['gb_title'] = htmlspecialchars(strip_tags(remove_xss(input('post.gb_title/s'))));
            $data['gb_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.gb_content/s'))));
            $id = $rs->add($data);
            if ($id == 1) {
                if ($userconfig['user_check'] == 1) {
                    return json(['msg' => "添加留言成功,我们会尽快审核", 'rcode' => 1]);
                }
                return json(['msg' => '添加留言成功', 'rcode' => 1]);
            } else {
                return json(['msg' => '添加留言失败' . $id, 'rcode' => -1]);
            }
        } elseif ($this->request->isAjax()) {
            return view('gb/add');
        }
    }

    public function delgb()
    {
        $Url = param_url();
        $rs = model("Gb");
        if ($this->request->isPost()) {
            $Url['id'] = input('post.ids/a');
        }
        $where['gb_id'] = array('IN', $Url['id']);
        $result = $rs->where($where)->delete();
        if ($result !== false) {
            $catalogs = $rs->getGbCatalogs($this->userId);
            $this->assign("catalogs", $catalogs);
            return json(['msg' => '删除成功', 'rcode' => "1", 'pids' => $this->view->fetch('/gb/nav')]);
        } else {
            return json(['msg' => '删除失败', 'rcode' => '-1']);
        }
    }

    public function addcomm()
    {
        if ($this->request->isPost()) {
            $userconfig = F('_data/userconfig_cache');
            $rs = model('Cm');
            $data = array();
            $data['cm_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.comm_txt/s'))));
            $data['cm_vid'] = input('post.comm_vid/d');
            $data['cm_uid'] = $this->userId;
            $data['cm_username'] = $this->userinfo['nickname'];
            $data['cm_sid'] = input('post.comm_sid/d', 1);
            $data['cm_pid'] = input('post.comm_pid/d', 0);
            $data['cm_addtime'] = time();
            $data['checktime'] = 1;
            if (empty($data['cm_content']) || $data['cm_content'] == '请在这里发表您的个人看法，最多1000个字。') {
                return json(["msg" => "内容不能为空或带非法字符,请填写提交内容!", "rcode" => "-1"]);
            }
            $cookiename = md5('cm-' . ip2long(get_client_ip()) . '-' . intval($data['cm_vid']) . '-' . intval($data['cm_uid']) . '-' . intval($data['cm_pid']) . '-' . intval($data['cm_sid']));
            $cookie = cookie($cookiename);
            $cache = cache($cachename);
            if ($cookie) {
                return json(["msg" => "亲您评论速度太快了,请休息一会，喝杯咖啡！", "rcode" => "-1"]);
            }
            $rs->validate('cm')->save($data);
            if ($rs->getError()) {
                return json(["msg" => "评论提交失败," . $rs->getError(), "rcode" => "-1"]);
            } else {
                if ($userconfig['user_cm_score']) {
                    model('Score')->user_score($this->userId, 11, intval($userconfig['user_cm_score']));
                }
                cookie($cookiename, true, intval($userconfig['user_second']));
                if ($userconfig['user_check'] == 1) {
                    return json(["msg" => "评论提交成功,我们会尽快审核你的留言！", "rcode" => "1"]);
                } else {
                    return json(['msg' => '评论提交成功', 'rcode' => "1"]);
                }
            }
        }
    }

    public function addrecomm()
    {
        if ($this->request->isPost()) {
            $cm_pid = input('post.comm_pid/d', 0);
            $cm_id = input('post.comm_id/d', 0);
            $cm_tuid = input('post.comm_tuid/d', 0);
            $userconfig = F('_data/userconfig_cache');
            $rs = model('Cm');
            $data = array();
            $data['cm_content'] = htmlspecialchars(strip_tags(remove_xss(input('post.recomm_txt/s', ''))));
            $data['cm_vid'] = input('post.comm_vid/d');
            $data['cm_uid'] = $this->userinfo['userid'];
            $data['cm_username'] = $this->userinfo['nickname'];
            $data['cm_sid'] = input('post.comm_sid/d', 1);
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
            $cache = cache($cachename);
            if ($cookie) {
                return json(["msg" => "亲您评论速度太快了,请休息一会，喝杯咖啡！", "rcode" => "-1"]);
            }
            $rs->validate('cm')->save($data);
            if ($rs->getError()) {
                return json(["msg" => "评论提交失败," . $rs->getError(), "rcode" => "-1"]);
            } else {
                if ($userconfig['user_cm_score']) {
                    model('Score')->user_score($this->userId, 11, intval($userconfig['user_cm_score']));
                }
                cookie($cookiename, true, intval($userconfig['user_second']));
                if ($userconfig['user_check'] == 1) {
                    return json(["msg" => "评论提交成功,我们会尽快审核你的留言！", "rcode" => "1"]);
                } else {
                    return json(['msg' => '评论提交成功', 'rcode' => "1"]);
                }
            }
        }
    }

    public function score()
    {
        if ($this->request->isAjax()) {
            $Url = param_url();
            $Url['limit'] = input('t/d', 20);
            config('model', $this->url);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $where = array('score_uid' => $this->userId);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $rs = new Score();
            $list = $rs->getlist($where, $page);
            $page = "<span class='total'>共" . $list->total() . "条记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/score/ajax');
            return json($ajax);
        }
        return view('/score/index');
    }

    public function order()
    {
        if ($this->request->isAjax()) {
            $Url = param_url();
            $Url['limit'] = input('t/d', 10);
            config('model', $this->url);
            $JumpUrl = param_jump($Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $where = array('order_uid' => $this->userId);
            $page = array('limit' => $Url['limit'], 'currentpage' => $Url['p']);
            $rs = model("orders");
            $list = $rs->getlist($where, $page);
            $page = "<span class='total'>共" . $list->total() . "条记录&nbsp;" . $list->currentPage() . "/" . $list->lastPage() . "页</span>";
            $page .= getpage(config('model'), config('params'), $list->currentPage(), $list->total(), $Url['limit'], config('home_pagenum'));
            $pagebox = "<span class='total'>" . $list->currentPage() . "/" . $list->lastPage() . "</span>";
            $pagebox .= gettoppage(config('model'), config('params'), $list->currentPage(), $list->lastPage(), config('home_pagenum'), $JumpUrl['url'], 'pagego(\'' . $JumpUrl['url'] . '\',' . $list->lastPage() . ')');
            $this->assign('list', $list);
            $ajax['total'] = $list->total();
            $ajax['pagetop'] = $pagebox;
            $ajax['pages'] = $page;
            $ajax['ajaxtxt'] = $this->view->fetch('/order/ajax');
            return json($ajax);
        }
        return view('/order/index');
    }
}