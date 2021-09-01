<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Email;
use think\Exception;
class Pay extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$array = db('Module')->where('module_name', 'payconfig')->find();
		if ($array != null) {
			$value = json_decode($array["module_value"], true);
			F('_data/payconfig_cache', $value);
			$this->assign($value);
		}
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function up()
	{
		if (IS_POST) {
			$post = input('post.');
			$data['module_value'] = json_encode($post['info']);
			$data['module_uptime'] = time();
			if (db('Module')->where('module_name', 'payconfig')->find()) {
				$result = db('Module')->where('module_name', 'payconfig')->update($data);
			} else {
				$data['module_name'] = 'payconfig';
				$result = db('Module')->insert($data);
			}
			if ($result) {
				action_log('admin/pay/up', 'user', $result, 1);
				return $this->success('配置更新成功！');
			} else {
				action_log('admin/pay/up', 'user', 0, 0);
				return $this->error('配置未做修改！');
			}
		}
	}
}