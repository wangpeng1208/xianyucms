<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Email;
use mailer\lib\Mailer;
use mailer\lib\Config;
use think\Exception;
use app\user\model\User as UserModel;

class User extends Admin
{
    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
        $array = db('Module')->where('module_name', 'userconfig')->find();
        if ($array != null) {
            $value = json_decode($array["module_value"], true);
            F('_data/userconfig_cache', $value);
            $this->assign($value);
            $this->setNav();
            return view('index', [], ['</body>' => config('xianyucms.copyright')]);
        }
    }

    public function up()
    {
        if (IS_POST) {
            $post = input('post.');
            $data['module_value'] = json_encode($post['info']);
            $data['module_uptime'] = time();
            cache('user', $post['info']);
            $result = db('Module')->where('module_name', 'userconfig')->update($data);
            if ($result) {
                action_log('admin/user/up', 'user', $result, 1);
                return $this->success('配置更新成功！');
            } else {
                action_log('admin/user/up', 'user', 0, 0);
                return $this->error('配置未做修改！');
            }
        }
    }

    public function nav()
    {
        $admin['status'] = input('status/d', '');
        $admin['cid'] = input('cid/d', '');
        $admin['wd'] = urldecode(trim(input('wd', '')));
        $admin['type'] = input('type', 'id');
        $admin['order'] = input('order', 'desc');
        $admin['orders'] = $admin['type'] . ' ' . $admin['order'];
        if ($admin['wd']) {
            $where['name|url'] = array('like', '%' . $admin['wd'] . '%');
        }
        if ($admin['cid']) {
            $where['cid'] = array('eq', $admin['cid']);
        }
        if ($admin['status'] == 1) {
            $where['status'] = array('eq', $admin['status']);
        }
        if ($admin['status'] == 2) {
            $where['status'] = array('eq', 0);
        }
        $list = db('Usernav')->where($where)->order($admin['orders'])->select();
        $menu = array(1 => '网站前台', 2 => '会员中心');
        $data = array('list' => $list, 'menu' => $menu);
        $this->usernav_cache();
        $this->assign($admin);
        $this->assign($data);
        $this->setNav();
        return view('user/nav/index', [], ['</body>' => config('xianyucms.copyright')]);
    }

    public function navedit($id = null)
    {
        if (IS_POST) {
            $data = input('post.');
            $result = $this->validate($data, 'Usernav');
            if (true !== $result) {
                return $this->error($result);
            }
            $result = db('Usernav')->update($data);
            if ($result !== false) {
                action_log('admin/user/navedit', 'user', $result, 1);
                return $this->success('编辑成功！', url('admin/user/nav'));
            } else {
                action_log('admin/user/navedit', 'user', 0, 0);
                return $this->success('编辑失败！');
            }
        } else {
            $info = db('Usernav')->where(array('id' => $id))->find();
            if (!$info) {
                action_log('admin/user/navedit', 'user', 0, 0);
                return $this->error("ID操作失败！");
            }
            $data = array('info' => $info);
            $this->assign($data);
            return view('user/nav/edit');
        }
    }

    public function navadd()
    {
        if (IS_POST) {
            $data = input('post.');
            $result = $this->validate($data, 'Usernav');
            if (true !== $result) {
                action_log('admin/user/navadd', 'user', 0, 0, $result);
                return $this->error($result);
            }
            $result = db('Usernav')->insertGetId($data);
            if ($result) {
                action_log('admin/user/navadd', 'user', $result, 1);
                return $this->success('添加成功！', url('adminuser/nav'));
            } else {
                action_log('admin/user/navadd', 'user', 0, 0);
                return $this->success('添加失败！');
            }
        } else {
            $info['oid'] = db('Usernav')->max('oid') + 1;
            $data = array('info' => $info);
            $this->assign($data);
            return view('user/nav/edit');
        }
    }

    public function naveditable($name = null, $value = null, $pk = null)
    {
        if ($name && ($value != null || $value != '') && $pk) {
            db('Usernav')->where(array('id' => $pk))->setField($name, $value);
        }
    }

    public function navstatus($status = "")
    {
        $id = $this->getArrayParam('id');
        if (empty($id)) {
            action_log('admin/user/navstatus', 'user', 0, 0);
            return $this->error("非法操作！", '');
        }
        $message = !$status ? '隐藏' : '显示';
        $map['id'] = array('IN', $id);
        $result = db('Usernav')->where($map)->setField('status', $status);
        if ($result !== false) {
            action_log('admin/user/navstatus', 'user', $id, 1, '设置' . $message . '状态成功！');
            return $this->success('设置' . $message . '状态成功！');
        } else {
            action_log('admin/user/navstatus', 'user', $id, 0, '设置' . $message . '状态失败！');
            return $this->error('设置' . $message . '状态失败！');
        }
    }

    public function navdel()
    {
        $id = $this->getArrayParam('id');
        if (empty($id)) {
            action_log('admin/user/navdel', 'user', 0, 0);
            return $this->error("非法操作！", '');
        }
        $map['id'] = array('IN', $id);
        $result = db('Usernav')->where($map)->delete();
        if ($result) {
            action_log('admin/user/navdel', 'user', $id, 1);
            return $this->success('删除成功！');
        } else {
            action_log('admin/user/navdel', 'user', $id, 0);
            return $this->error('删除失败！');
        }
    }

    public function show()
    {
        $rs = new UserModel();

        $admin['user_status'] = input('user_status/d', '');
        $admin['start_time'] = strtotime(input('start_time'));
        $admin['end_time'] = strtotime(input('end_time'));
        $admin['wd'] = urldecode(trim(input('wd', '')));
        $admin['type'] = input('type', 'last_login_time');
        $admin['order'] = input('order', 'desc');
        $admin['orders'] = $admin['type'] . ' ' . $admin['order'];
        $limit = config('admin_list_pages');
        $admin['p'] = input('p/d', '');
        if ($admin['wd']) {
            $where['username|nickname|email'] = array('like', '%' . $admin['wd'] . '%');
        }
        if ($admin['start_time'] && $admin['end_time']) {
            $where['last_login_time'] = array('between', [$admin['start_time'], $admin['end_time']]);
        }
        if ($admin['user_status'] == 1) {
            $where['user_status'] = array('eq', $admin['user_status']);
        }
        if ($admin['user_status'] == 2) {
            $where['user_status'] = array('eq', 2);
        }
        $list = $rs->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);

        $admin['p'] = "xianyupage";
        $pageurl = url('admin/user/show', $admin);
        $pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
        Cookie('user_url_forward', $_SERVER['REQUEST_URI']);
        $data = array('list' => $list, 'page' => $pages);
        $this->assign($admin);
        $this->assign($data);
        $this->setNav();
        return view('show', [], ['</body>' => config('xianyucms.copyright')]);
    }

    public function add()
    {
        $rs = model('User');
        if (IS_POST) {
            $data = $this->request->post();
            $data['encrypt'] = rand_string(6);
            $result = $this->validate($data, 'User');
            if (true !== $result) {
                action_log('admin/user/add', 'user', 0, 0, $result);
                return $this->error($result);
            }
            $rs->save($data);
            if ($rs->userid) {
                $rs->detail()->save($data);
                action_log('admin/user/add', 'user', $rs->userid, 1);
                return $this->success('添加会员成功！', url('admin/user/show'));
            } else {
                action_log('admin/user/add', 'user', 0, 0);
                return $this->success('添加会员失败！');
            }
        } else {
            $info['regdate'] = time();
            $info['last_login_time'] = time();
            $info['regip'] = ip2long(get_client_ip());
            $info['lastip'] = ip2long(get_client_ip());
            $info['loginnum'] = 0;
            $info['islock'] = 0;
            $data = array('info' => $info);
            $this->assign($data);
            return view('edit');
        }
    }

    public function edit($id = null)
    {
        $rs = model('User');
        if (IS_POST) {
            $data = $this->request->post();
            $reuslt = $rs->editUser($data);
            if ($reuslt !== false) {
                action_log('admin/user/edit', 'user', $data['userid'], 1);
                return $this->success('编辑会员成功！', Cookie('user_url_forward'));
            } else {
                action_log('admin/user/edit', 'user', 0, 0);
                return $this->success('编辑会员失败！');
            }
        } else {
            $info = $rs->getuserinfo($id);
            if (!$info) {
                action_log('admin/user/edit', 'user', 0, 0);
                return $this->error("ID操作失败！");
            }
            if (config('admin_time_edit')) {
                $info['checktime'] = 'checked';
            }
            $data = array('info' => $info);
            $this->assign($data);
            return view('edit');
        }
    }

    public function islock($islock = "")
    {
        $id = $this->getArrayParam('id');
        if (empty($id)) {
            action_log('admin/user/islock', 'user', 0, 0);
            return $this->error("非法操作！", '');
        }
        $message = !$islock ? '锁定' : '解锁';
        $map['userid'] = array('IN', $id);
        $result = db('User')->where($map)->setField('islock', $islock);
        if ($result !== false) {
            action_log('admin/user/islock', 'user', $id, 1, '设置' . $message . '状态成功！');
            return $this->success('设置' . $message . '状态成功！', Cookie('user_url_forward'));
        } else {
            action_log('admin/user/islock', 'user', $id, 0, '设置' . $message . '状态失败！');
            return $this->error('设置' . $message . '状态失败！');
        }
    }

    public function userdel()
    {
        $id = $this->getArrayParam('id');
        if (empty($id)) {
            action_log('admin/user/userdel', 'user', 0, 0);
            return $this->error("非法操作！", '');
        }
        $this->delfile($id);
    }

    public function delfile($id)
    {
        db('User_detail')->where('userid', 'in', $id)->delete();
        db('Playlog')->where('log_uid', 'in', $id)->delete();
        db('Remind')->where('remind_uid', 'in', $id)->delete();
        db('Favorite')->where('favorite_uid', 'in', $id)->delete();
        db('Visitors')->where('visitors_userid', 'in', $id)->delete();
        db('User_api')->where('uid', 'in', $id)->delete();
        $result = db('User')->where('userid', 'in', $id)->delete();
        if ($result) {
            action_log('admin/user/userdel', 'user', $id, 1);
            return $this->success('删除成功', Cookie('user_url_forward'));
        } else {
            action_log('admin/user/userdel', 'user', 0, 0);
            return $this->error('删除失败！');
        }
    }

    public function testMail()
    {
        if (IS_POST) {
            $data = input('post.');
            $address = $data['receiver'];
            $result = cmf_send_email($address, '这是一封测试邮件', '这是一封测试邮件,用于测试邮件配置是否正常!');
            return $result;
        }
    }

    public function ajax()
    {
        $data = array();
        $where = array();
        $vid = intval(input('vid/d', ''));
        $cid = intval(input('cid/d', ''));
        $uid = intval(input('uid/d', ''));
        $sid = input('sid/s', '');
        $type = trim(input('type', ''));
        $lastdid = intval(input('lastdid'));
        if ($uid) {
            if ($sid == "remind") {
                $rs = db('remind');
                $db = "remind";
            } else {
                $rs = db('favorite');
                $db = "favorite";
            }
            if ($type == 'add') {
                $rsid = $rs->where([$db . '_uid' => $uid, $db . '_vid' => $vid])->find();
                if (!$rsid) {
                    $data[$db . '_vid'] = $vid;
                    $data[$db . '_uid'] = $uid;
                    $data[$db . '_cid'] = getlistpid($cid);
                    $data[$db . '_addtime'] = time();
                    $rs->insert($data);
                }
            } elseif ($type == 'del') {
                $where[$db . '_vid'] = $vid;
                $where[$db . '_uid'] = $uid;
                $rs->where($where)->delete();
            }
        }
        if ($sid == "remind") {
            return $this->showremind($uid);
        } elseif ($sid == "favorite") {
            return $this->showfavorite($uid);
        } else {
            return '请先添加会员！';
        }
    }

    public function showremind($uid)
    {
        $where = array();
        $where['remind_uid'] = $uid;
        $list = db('remind')->field('vod_id,vod_name,vod_cid,vod_letters,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl,remind_id')->alias('r')->join('vod v', 'v.vod_id=r.remind_vid', 'LEFT')->join('list l', 'l.list_id=v.vod_cid', 'LEFT')->where($where)->order('remind_addtime desc')->select();
        $this->assign('uid', $uid);
        $this->assign('list_vod', $list);
        $this->assign('count', count($list));
        return view('remind_vod_ids');
    }

    public function showfavorite($uid)
    {
        $where = array();
        $where['favorite_uid'] = $uid;
        $list = db('favorite')->field('vod_id,vod_name,vod_cid,vod_letters,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl,favorite_id')->alias('r')->join('vod v', 'v.vod_id=r.favorite_vid', 'LEFT')->join('list l', 'l.list_id=v.vod_cid', 'LEFT')->where($where)->order('favorite_addtime desc')->select();
        $this->assign('uid', $uid);
        $this->assign('list_vod', $list);
        $this->assign('count', count($list));
        return view('favorite_vod_ids');
    }

    public function addscore()
    {
        $data = input('post.');
        if ($data['userid'] && $data['score']) {
            $info = model('Score')->user_score($data['userid'], 5, intval($data['score']));
            if ($info) {
                $score = db('user')->where('userid', $data['userid'])->value('score');
                return $this->success('增加成功', '', $score);
            } else {
                return $this->error('增加失败');
            }
        } else {
            return $this->error('增加失败');
        }
    }

    public function addvip()
    {
        $data = input('post.');
        if ($data['userid'] && $data['day']) {
            $user = db('user')->field('userid,score,viptime')->find($data['userid']);
            $result = model('Score')->user_viptime($data['userid'], $user['viptime'], $user['score'], intval($data['day']));
            if ($result['rcode'] == 1) {
                $info = db('user')->field('userid,score,viptime')->find($data['userid']);
                $result['data'] = date('Y-m-d H:i:s', $info['viptime']);
                $result['score'] = $info['score'];
            }
            return json($result);
        }
    }
}