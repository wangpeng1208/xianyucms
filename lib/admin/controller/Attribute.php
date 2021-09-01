<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Attribute extends Admin
{
	protected $model;
	protected $attr;
	public function _initialize()
	{
		parent::_initialize();

		$this->getContentMenu();
		$this->model = model('Attribute');
		foreach (get_attribute_type() as $key => $value) {
			$this->attr[$key] = $value[0];
		}
		$this->validate_rule = array(0 => '请选择', 'regex' => '正则验证', 'function' => '函数验证', 'unique' => '唯一验证', 'length' => '长度验证', 'in' => '验证在范围内', 'notin' => '验证不在范围内', 'between' => '区间验证', 'notbetween' => '不在区间验证');
		$this->auto_type = array(0 => '请选择', 'function' => '函数', 'field' => '字段', 'string' => '字符串');
		$this->the_time = array(0 => '请选择', '3' => '始 终', '1' => '新 增', '2' => '编 辑');
		$this->field = $this->getField();
	}
	public function index()
	{
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['model'] = input('model/d', '');
		if ($admin['wd']) {
			$where['name|title|remark'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['model']) {
			$where['model_id'] = array('eq', $admin['model']);
		}
		$limit = config('admin_list_pages');
		$list = db('Attribute')->where($where)->order('id desc')->paginate($limit, false, ['page' => $admin['p']]);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/attribute/index', $admin);
		$pages = '<ul class="pagination">' . adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '') . '</ul>';
		Cookie('attribute_url_forward', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		$model_id = input('model/d', '', 'trim,intval');
		if (IS_POST) {
			$result = $this->model->change();
			if ($result) {
				action_log('admin/attribute/add', 'attribute', $result, 1);
				return $this->success("创建成功！", url('admin/attribute/index', array('model' => $model_id)));
			} else {
				action_log('admin/attribute/add', 'attribute', 0, 0);
				return $this->error($this->model->getError());
			}
		} else {
			if (!$model_id) {
				return $this->error('非法操作！');
			}
			$data = array('info' => array('model_id' => $model_id), 'fieldGroup' => $this->field);
			$this->assign($data);
			return view('public/edit');
		}
	}
	public function edit()
	{
		if (IS_POST) {
			$result = $this->model->change();
			if ($result) {
				action_log('admin/attribute/edit', 'attribute', $result, 1);
				return $this->success("修改成功！", url('admin/attribute/index', array('model' => $_POST['model_id'])));
			} else {
				action_log('admin/attribute/edit', 'attribute', 0, 0, $this->model->getError());
				return $this->error($this->model->getError());
			}
		} else {
			$id = input('id', '', 'trim,intval');
			$info = db('Attribute')->find($id);
			$data = array('info' => $info, 'fieldGroup' => $this->field);
			$this->assign($data);
			return view('public/edit');
		}
	}
	public function del()
	{
		$id = input('id', '', 'trim,intval');
		if (!$id) {
			action_log('admin/attribute/del', 'attribute', 0, 0);
			return $this->error("非法操作！");
		}
		$result = $this->model->del($id);
		if ($result) {
			action_log('admin/attribute/del', 'attribute', $result, 1);
			return $this->success("删除成功！", Cookie('attribute_url_forward'));
		} else {
			action_log('admin/attribute/del', 'attribute', 0, 0, $this->model->getError());
			return $this->error($this->model->getError());
		}
	}
	protected function getField()
	{
		return array('基础设置' => array(array('name' => 'id', 'title' => 'id', 'help' => '', 'type' => 'hidden'), array('name' => 'model_id', 'title' => 'model_id', 'help' => '', 'type' => 'hidden'), array('name' => 'name', 'title' => '字段名', 'help' => '英文字母开头，长度不超过30', 'type' => 'required'), array('name' => 'title', 'title' => '字段标题', 'help' => '请输入字段标题，用于表单显示', 'type' => 'required'), array('name' => 'type', 'title' => '字段类型', 'help' => '用于表单中的展示方式', 'type' => 'select', 'option' => $this->attr, 'help' => ''), array('name' => 'length', 'title' => '字段长度', 'help' => '字段的长度值', 'type' => 'text'), array('name' => 'extra', 'title' => '参数', 'help' => '布尔、枚举、多选字段类型的定义数据', 'type' => 'textarea'), array('name' => 'value', 'title' => '默认值', 'help' => '字段的默认值', 'type' => 'text'), array('name' => 'remark', 'title' => '字段备注', 'help' => '用于表单中的提示', 'type' => 'text'), array('name' => 'is_show', 'title' => '是否显示', 'help' => '是否显示在表单中', 'type' => 'select', 'option' => array('1' => '始终显示', '2' => '新增显示', '3' => '编辑显示', '0' => '不显示'), 'value' => 1), array('name' => 'is_must', 'title' => '是否必填', 'help' => '用于自动验证', 'type' => 'select', 'option' => array('0' => '是', '1' => '否'))), '高级设置' => array(array('name' => 'validate_type', 'title' => '验证方式', 'type' => 'select', 'option' => $this->validate_rule, 'help' => ''), array('name' => 'validate_rule', 'title' => '验证规则', 'help' => '根据验证方式定义相关验证规则', 'type' => 'text'), array('name' => 'error_info', 'title' => '出错提示', 'type' => 'text', 'help' => ''), array('name' => 'validate_time', 'title' => '验证时间', 'help' => '英文字母开头，长度不超过30', 'type' => 'select', 'option' => $this->the_time, 'help' => ''), array('name' => 'auto_type', 'title' => '自动完成方式', 'help' => '英文字母开头，长度不超过30', 'type' => 'select', 'option' => $this->auto_type, 'help' => ''), array('name' => 'auto_rule', 'title' => '自动完成规则', 'help' => '根据完成方式订阅相关规则', 'type' => 'text'), array('name' => 'auto_time', 'title' => '自动完成时间', 'help' => '英文字母开头，长度不超过30', 'type' => 'select', 'option' => $this->the_time)));
	}
	function get_attribute_type($type = '')
	{
		$type_array = config('config_type_list');
		static $type_list = array();
		foreach ($type_array as $key => $value) {
			$type_list[$key] = explode(',', $value);
		}
		return $type ? $type_list[$type][0] : $type_list;
	}
}