<?php


namespace app\admin\controller;

use app\common\model\Member as MemberModel;
use think\Db;
use app\common\controller\Admin;
class Member extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['nickname'] = input('nickname');
		$admin['type'] = input('type', 'm_uid');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$list = Db::view('member', ['status' => 'm_ststus', 'login' => 'm_login', 'last_login_time' => 'm_last_login_time', 'uid' => 'm_uid', '*'])->where($where)->view('auth_group_access', 'group_id', 'auth_group_access.uid=member.uid', "LEFT")->view('auth_group', ['title'], 'auth_group.id=auth_group_access.group_id', "LEFT")->order($admin['orders'])->select();
		$data = array('list' => $list);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$member = new MemberModel();
			$data = $this->request->param();
			$uid = $member->register($data, false);
			if ($uid > 0) {
				$member = MemberModel::get($member->uid);
				$member->access()->save(['group_id' => $data['group_id']]);
				action_log('admin/member/add', 'member', $member->uid, 1);
				return $this->success('用户添加成功！', url('admin/member/index'));
			} else {
				action_log('admin/member/add', 'member', 0, 0, $member->getError());
				return $this->error($member->getError());
			}
		} else {
			$group = db('AuthGroup')->where('status', 1)->select();
			$data = array('group' => $group);
			$this->assign($data);
			return view('edit');
		}
	}
	public function edit($id = "")
	{
		if (IS_POST) {
			$data = $this->request->param();
			$member = new MemberModel();
			$reuslt = $member->editUser($data, true);
			if (false !== $reuslt) {
				$member = MemberModel::get($id);
				$member->access->save(['group_id' => $data['group_id']]);
				action_log('admin/member/edit', 'member', $id, 1);
				return $this->success('修改管理员信息成功！', url('admin/member/index'));
			} else {
				action_log('admin/member/edit', 'member', $data['uid'], 0);
				return $this->error('修改管理员信息失败');
			}
		} else {
			$group = db('AuthGroup')->where('status', 1)->select();
			$info = db('Member')->alias('m')->join('AuthGroupAccess a', 'a.uid = m.uid', 'LEFT')->find($id);
			$data = array('info' => $info, 'group' => $group);
			$this->assign($data);
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (!$id) {
			action_log('admin/member/del', 'member', 0, 0);
			return $this->error("请选择要删除的用户");
		}
		if (in_array(config('user_administrator'), $id)) {
			action_log('admin/member/del', 'member', $id, 0, "不允许对超级管理员执行该操作!");
			return $this->error("不允许对超级管理员执行该操作!");
		}
		$map['uid'] = array('IN', $id);
		$result = db('Member')->where($map)->delete();
		$result = db('AuthGroupAccess')->where($map)->delete();
		action_log('admin/member/del', 'member', $id, 1);
		return $this->success('删除用户成功！');
	}
	public function auth()
	{
		$access = model('AuthGroupAccess');
		$group = model('AuthGroup');
		if (IS_POST) {
			$uid = input('uid', '', 'trim,intval');
			$access->where(array('uid' => $uid))->delete();
			$group_type = config('user_group_type');
			print_r($group_type);
			foreach ($group_type as $key => $value) {
				$group_id = input($key, '', 'trim,intval');
				if ($group_id) {
					$add = array('uid' => $uid, 'group_id' => $group_id);
					$access->save($add);
				}
			}
			action_log('admin/member/auth', 'member', $uid, 1);
			return $this->success("设置成功！");
		} else {
			$uid = input('id', '', 'trim,intval');
			$row = $group::select();
			$auth = $access::where(array('uid' => $uid))->select();
			$auth_list = array();
			foreach ($auth as $key => $value) {
				$auth_list[] = $value['group_id'];
			}
			foreach ($row as $key => $value) {
				$list[$value['module']][] = $value;
			}
			$data = array('uid' => $uid, 'auth_list' => $auth_list, 'list' => $list);
			$this->assign($data);
			$this->setMeta("用户分组");
			return $this->fetch();
		}
	}
	private function getUserinfo($uid = null, $pass = null, $errormsg = null)
	{
		$user = model('Member');
		$uid = $uid ? $uid : input('id');
		$uid = $uid ? $uid : session('member_auth.uid');
		$map['uid'] = $uid;
		if ($pass != null) {
			unset($map);
			$map['password'] = $pass;
		}
		$list = $user::where($map)->field('uid,username,nickname,email,qq,status,salt')->find();
		if (!$list) {
			return $this->error($errormsg ? $errormsg : '不存在此用户！');
		}
		return $list;
	}
	public function submitNickname()
	{
		$nickname = input('post.nickname');
		$password = input('post.password');
		if (empty($nickname)) {
			return $this->error('请输入昵称');
		}
		if (empty($password)) {
			return $this->error('请输入密码');
		}
		$User = new UserApi();
		$uid = $User->login(UID, $password, 4);
		if ($uid == -2) {
			return $this->error('密码不正确');
		}
		$Member = model('Member');
		$data = $Member->create(array('nickname' => $nickname));
		if (!$data) {
			return $this->error($Member->getError());
		}
		$res = $Member->where(array('uid' => $uid))->save($data);
		if ($res) {
			$user = session('member_auth');
			$user['username'] = $data['nickname'];
			session('member_auth', $user);
			session('member_auth_sign', data_auth_sign($user));
			return $this->success('修改昵称成功！');
		} else {
			return $this->error('修改昵称失败！');
		}
	}
	public function editpwd()
	{
		if (IS_POST) {
			$Member = new MemberModel();
			$data = $this->request->post();
			$res = $Member->editpw($data);
			if ($res) {
				action_log('admin/member/editpwd', 'member', session('member_auth.uid'), 1);
				return $this->success('修改密码成功,请退出重新登录！');
			} else {
				action_log('admin/member/editpwd', 'member', 0, 0, $Member->getError());
				return $this->error($Member->getError());
			}
		} else {
			return view('editpwd');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/member/status', 'member', 0, 0, "请选择要操作的数据!");
			return $this->error('请选择要操作的数据!');
		}
		if (in_array(config('user_administrator'), $id)) {
			action_log('admin/member/status', 'member', $id, 0, "不允许对超级管理员执行该操作!");
			return $this->error("不允许对超级管理员执行该操作!");
		}
		$message = !$status ? '禁用' : '启用';
		$map['uid'] = array('in', $id);
		$result = db('Member')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/member/status', 'member', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/member/status', 'member', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}