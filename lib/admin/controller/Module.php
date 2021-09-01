<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Module extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'module_id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		if ($admin['wd']) {
			$where['module_title|module_name|module_value'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Module')->where($where)->order($admin['type'] . ' ' . $admin['order'])->select();
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Module');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('Module')->save($data);
			if (false != $result) {
				action_log('admin/module/add', 'model', $data['module_name'], 1);
				return $this->success('添加成功！', url('admin/module/index'));
			} else {
				action_log('admin/module/add', 'model', 0, 0);
				return $this->success('添加失败！');
			}
		} else {
			return view('edit');
		}
	}
	public function edit($id = null)
	{
		$model = model('Module');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Module');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = $model->save($data, array('module_id' => $data['module_id']));
			if ($result !== false) {
				action_log('admin/module/edit', 'model', $data['module_id'], 1);
				return $this->success('编辑成功！', url('admin/module/index'));
			} else {
				action_log('admin/module/edit', 'model', 0, 0);
				return $this->error("编辑失败");
			}
		} else {
			$info = db('Module')->where(array('module_id' => $id))->find();
			if (!$info) {
				action_log('admin/module/edit', 'model', $id, 0);
				return $this->error("非法操作！");
			}
			$data = array('info' => $info, 'value' => (array) json_decode($info['module_value'], true));
			$this->assign($data);
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/module/del', 'model', 0, 0);
			return $this->error("非法操作！");
		}
		$map['module_id'] = array('IN', $id);
		$result = db('Module')->where($map)->delete();
		if ($result) {
			action_log('admin/module/del', 'model', $id, 1);
			return $this->success('删除成功！', Cookie('__forward__'));
		} else {
			action_log('admin/module/del', 'model', 0, 0);
			return $this->error('删除失败！');
		}
	}
}