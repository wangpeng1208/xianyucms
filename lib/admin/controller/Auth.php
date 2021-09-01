<?php


namespace app\admin\controller;

use com\Tree;
use app\common\controller\Admin;
class Auth extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$list = db('AuthRule')->order('sort asc,id asc')->select();
		if (!empty($list)) {
			$tree = Tree::instance();
			$tree->init($list, 'id', 'pid');
			$list = $tree->getTreeList($tree->getTreeArray(0), 'title');
		}
		F('_data/rules', $list);
		$data = array('list' => $list);
		$this->assign($data);
		$this->setNav();
		return view('index');
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/auth/status', 'auth', 0, 0);
			return $this->error("非法操作！", '');
		}
		if (empty($status)) {
			$status = input('status');
		}
		$message = !$status ? '禁用' : '启用';
		$map['id'] = array('IN', $id);
		$result = db('AuthRule')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/auth/status', 'auth', $result, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/auth/status', 'auth', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function type($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/auth/type', 'auth', 0, 0);
			return $this->error("非法操作！", '');
		}
		if (empty($status)) {
			$status = input('status');
		}
		$map['id'] = array('IN', $id);
		$result = db('AuthRule')->where($map)->setField('type', $status);
		if ($result !== false) {
			action_log('admin/auth/type', 'auth', $id, 1);
			return $this->success('设置成功！');
		} else {
			action_log('admin/auth/type', 'auth', 0, 0);
			return $this->error('设置失败！');
		}
	}
	public function up()
	{
		if (IS_POST) {
			$post = $this->request->param();
			if (empty($post['id'])) {
				action_log('admin/auth/up', 'auth', 0, 0);
				return $this->error('请选择要操作的数据!');
			}
			foreach ($post['id'] as $key => $value) {
				$data['id'] = $value;
				$data['title'] = $post['title'][$value];
				$data['name'] = $post['name'][$value];
				$data['sort'] = intval($post['sort'][$value]);
				$result = db('AuthRule')->update($data);
			}
			action_log('admin/auth/up', 'auth', 1, 1);
			return $this->success("更新成功！");
		}
	}
	public function add()
	{
		if (IS_POST) {
			$data = $this->request->param();
			$result = $this->validate($data, 'Auth');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('AuthRule')->insertGetId($data);
			if ($result) {
				action_log('admin/auth/add', 'auth', $result, 1);
				return $this->success("添加成功！", url('admin/auth/index'));
			} else {
				action_log('admin/auth/add', 'auth', 0, 0);
				return $this->error($this->rule->getError());
			}
		} else {
			$pid = input('pid/d', 0);
			$info['sort'] = db('AuthRule')->where('pid', $pid)->max('sort') + 1;
			$info['pid'] = input('pid/d');
			$this->assign('info', $info);
			$menus = F('_data/rules');
			if (!empty($menus)) {
				$menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
			} else {
				$menus = array(0 => array('id' => 0, 'title_show' => '顶级菜单'));
			}
			$this->assign('Menus', $menus);
			return view('edit');
		}
	}
	public function edit()
	{
		$id = input('id/d');
		if (IS_POST) {
			$data = $this->request->param();
			$result = $this->validate($data, 'Auth');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('AuthRule')->update($data);
			if (false !== $result) {
				action_log('admin/auth/edit', 'auth', $data['id'], 1);
				return $this->success("更新成功！", url('admin/auth/index'));
			} else {
				action_log('admin/auth/edit', 'auth', 0, 0);
				return $this->error("更新失败！");
			}
		} else {
			if (!$id) {
				action_log('admin/auth/edit', 'auth', 0, 0);
				return $this->error("非法操作！");
			}
			$info = db('AuthRule')->find($id);
			$menus = F('_data/rules');
			if (!empty($menus)) {
				$menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
			} else {
				$menus = array(0 => array('id' => 0, 'title_show' => '顶级菜单'));
			}
			$this->assign('Menus', $menus);
			$this->assign('info', $info);
			return view('edit');
		}
	}
	public function del($id = "")
	{
		$id = $this->getArrayParam('id');
		if (!$id) {
			action_log('admin/auth/del', 'auth', 0, 0);
			return $this->error("非法操作！");
		}
		$where['pid'] = array('IN', $id);
		$pid = db('AuthRule')->where($where)->select();
		if ($pid) {
			return $this->error("分类中存在多级菜单");
		}
		$map['id'] = array('IN', $id);
		$result = db('AuthRule')->where($map)->delete();
		if ($result) {
			action_log('admin/auth/del', 'auth', $id, 1);
			return $this->success("删除成功！");
		} else {
			action_log('admin/auth/del', 'auth', $id, 0);
			return $this->error("删除失败！");
		}
	}
}