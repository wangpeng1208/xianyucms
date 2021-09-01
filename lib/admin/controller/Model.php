<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Model extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

		$this->getContentMenu();
	}
	public function index()
	{
		$admin['type'] = input('type', 'id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$list = db('Model')->order($admin['orders'])->select();
		$data = array('list' => $list);
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		$models = db('Model')->where(array('extend' => 0))->field('id,title')->select();
		$this->assign('models', $models);
		return view('add');
	}
	public function edit()
	{
		$id = input('id', '', 'trim,intval');
		if (empty($id)) {
			action_log('admin/model/edit', 'model', 0, 0);
			return $this->error('参数不能为空！');
		}
		$model = model('Model');
		$data = $model::find($id);
		if (!$data) {
			action_log('admin/model/edit', 'model', 0, 0, $Model->getError());
			return $this->error($Model->getError());
		}
		$data['attribute_list'] = empty($data['attribute_list']) ? '' : explode(",", $data['attribute_list']);
		if ($data['extend'] == 1) {
			$map['model_id'] = array('IN', array($data['id'], $data['extend']));
		} else {
			$map['model_id'] = $data['id'];
		}
		$map['is_show'] = 1;
		$fields = db('Attribute')->where($map)->select();
		foreach ($fields as $key => $field) {
			if (!empty($data['attribute_list']) && !in_array($field['id'], $data['attribute_list'])) {
				$field['is_show'] = 0;
			}
			$field['group'] = -1;
			$field['sort'] = 0;
			$fields_tem[$field['id']] = $field;
		}
		$field_sort = json_decode(json_decode($data['field_sort']));
		if (!empty($field_sort)) {
			foreach ($field_sort as $group => $ids) {
				foreach ($ids as $key => $value) {
					if (!empty($fields_tem[$value])) {
						$fields_tem[$value]['group'] = $group;
						$fields_tem[$value]['sort'] = $key;
					}
				}
			}
		}
		if (isset($fields_tem) && $fields_tem) {
			$fields = list_sort_by($fields_tem, "sort");
		}
		$this->assign('fields', $fields);
		$this->assign('info', $data);
		$this->setNav();
		return view('edit');
	}
	public function del()
	{
		$result = model('Model')->del();
		if ($result) {
			action_log('admin/model/del', 'model', 1, 1);
			return $this->success('删除模型成功！');
		} else {
			action_log('admin/model/del', 'model', 0, 0);
			return $this->error($mdoel->getError());
		}
	}
	public function update()
	{
		$res = model('Model')->change();
		if ($res['status']) {
			action_log('admin/model/update', 'model', 1, 1, $res['info']);
			return $this->success($res['info'], url('index'));
		} else {
			action_log('admin/model/update', 'model', 0, 0, $res['info']);
			return $this->error($res['info']);
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/model/status', 'model', 0, 0);
			return $this->error("非法操作！", '');
		}
		$message = !$status ? '禁用' : '启用';
		$map['id'] = array('IN', $id);
		$result = db('Model')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/model/status', 'model', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/model/status', 'model', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}
function list_sort_by($list, $field, $sortby = 'asc')
{
	if (is_array($list)) {
		$refer = $resultSet = array();
		foreach ($list as $i => $data) {
			$refer[$i] =& $data[$field];
		}
		switch ($sortby) {
			case 'asc':
				asort($refer);
				break;
			case 'desc':
				arsort($refer);
				break;
			case 'nat':
				natcasesort($refer);
				break;
		}
		foreach ($refer as $key => $val) {
			$resultSet[] =& $list[$key];
		}
		return $resultSet;
	}
	return false;
}