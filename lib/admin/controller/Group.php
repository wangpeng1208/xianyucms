<?php


namespace app\admin\controller;

use think\Db;
use com\Tree;
use app\common\controller\Admin;
class Group extends Admin
{
	protected $model;
	protected $rule;
	public function _initialize()
	{
		parent::_initialize();

		$this->group = model('AuthGroup');
		$this->rule = model('AuthRule');
	}
	public function index()
	{
		$list = db('AuthGroup')->order('id desc')->select();
		$data = array('list' => $list);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add($type = 'admin')
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'AuthGroup');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = $this->group->change();
			if ($result) {
				action_log('admin/group/add', 'group', $result, 1);
				return $this->success("添加成功！", url('admin/group/index'));
			} else {
				action_log('admin/group/add', 'group', 0, 0);
				return $this->error("添加失败！");
			}
		} else {
			$data = array('info' => array('module' => $type, 'status' => 1));
			$this->assign($data);
			return view('edit');
		}
	}
	public function edit($id)
	{
		if (!$id) {
			action_log('admin/group/edit', 'group', 0, 0);
			return $this->error("非法操作！");
		}
		if (IS_POST) {
			$result = $this->group->change();
			if ($result) {
				action_log('admin/group/edit', 'group', $id, 1);
				return $this->success("编辑成功！", url('admin/group/index'));
			} else {
				action_log('admin/group/edit', 'group', 0, 0);
				return $this->error("编辑失败！");
			}
		} else {
			$info = $this->group->where(array('id' => $id))->find();
			$data = array('info' => $info);
			$this->assign($data);
			return view('edit');
		}
	}
	public function roletree()
	{
		$id = input('id/d', '');
		$where['status'] = 1;
		if (!config('develop_mode')) {
			$where['isdev'] = 0;
		}
		$row = db('AuthRule')->where($where)->order('sort asc,id asc')->select();
		if ($id) {
			$info = db('AuthGroup')->find($id);
		}
		if (!empty($row)) {
			$tree = Tree::instance();
			$tree->init($row, 'id', 'pid');
			$ruleList = $tree->getTreeList($tree->getTreeArray(0));
		}
		$hasChildrens = [];
		foreach ($ruleList as $k => $v) {
			if ($v['haschild']) {
				$hasChildrens[] = $v['id'];
			}
		}
		$nodelist = [];
		foreach ($row as $key => $v) {
			$state = array('selected' => in_array($v['id'], explode(',', $info['rules'])) && !in_array($v['id'], $hasChildrens));
			$nodelist['data'][] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#', 'text' => $v['title'], 'type' => 'menu', 'state' => $state);
		}
		$nodelist['code'] = 1;
		return json($nodelist);
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			$result = $this->group->where(array('id' => $pk))->setField($name, $value);
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		$message = !$status ? '禁用' : '启用';
		if (empty($id)) {
			action_log('admin/group/status', 'group', 0, 0);
			return $this->error("非法操作！", '');
		}
		$where['group_id'] = array('IN', $id);
		$map['id'] = array('IN', $id);
		$result = db('AuthGroup')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/group/status', 'group', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/group/status', 'group', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/group/del', 'group', 0, 0);
			return $this->error("非法操作！", url('admin/group/index'));
		}
		$user = db('AuthGroupAccess')->where(array('group_id' => array('IN', $id)))->select();
		if ($user) {
			action_log('admin/group/del', 'group', $id, 1, "管理组存在用户无法删除");
			return $this->error("管理组存在用户无法删除");
		}
		$result = $this->group->where(array('id' => array('IN', $id)))->delete();
		if ($result) {
			action_log('admin/group/del', 'group', $id, 1);
			return $this->success("删除成功！", url('admin/group/index'));
		} else {
			action_log('admin/group/del', 'group', 0, 0);
			return $this->error("删除失败！", url('admin/group/index'));
		}
	}
	public function auth($id)
	{
		if (!$id) {
			action_log('admin/group/auth', 'group', 0, 0);
			return $this->error("非法操作！");
		}
		if (IS_POST) {
			$result = $this->group->change();
			if ($result) {
				action_log('admin/group/auth', 'group', $id, 1);
				return $this->success("编辑成功！", '');
			} else {
				action_log('admin/group/auth', 'group', 0, 0);
				return $this->error("编辑失败！");
			}
		} else {
			$info = $this->group->where(array('id' => $id))->find();
			$data = array('info' => $info);
			$this->assign($data);
			return view('auth', [], ['</body>' => config('xianyu_copyright]')]);
		}
	}
}