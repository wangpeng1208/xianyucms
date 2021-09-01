<?php


namespace app\admin\controller;

use think\validate;
use app\common\library\Insert;
use app\common\controller\Admin;
class Timming extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

		$this->assign($this->config_url);
	}
	public function index()
	{
		$list = db('timming')->order('timming_uptime desc')->select();
		$this->assign('list', $list);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'timming');
			if (true !== $result) {
				action_log('add_timming', 'timming', 1, session('member_auth.uid'), 0);
				return $this->error($result);
			}
			$result = model('timming')->save($data);
			if ($result) {
				action_log('admin/timming/add', 'timming', $result, 1);
				return $this->success('添加采集资源成功！', url('admin/timming/index', array('cid' => $data['timming_cid'])));
			} else {
				action_log('admin/timming/add', 'timming', 0, 0);
				return $this->error('添加采集资源失败！');
			}
		} else {
			$collect_list = F('_collect/list');
			$data = array('collect_list' => $collect_list);
			$this->assign($data);
			$this->setNav();
			return view('edit');
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'timming');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('timming')->save($data, ['timming_id' => $data['timming_id']]);
			if ($result) {
				action_log('admin/timming/edit', 'timming', $id, 1);
				return $this->success('编辑采集资源成功！', url('admin/timming/index', array('cid' => $data['timming_cid'])));
			} else {
				action_log('admin/timming/edit', 'timming', 0, 0);
				return $this->error('编辑采集资源失败！');
			}
		} else {
			$collect_list = F('_collect/list');
			$info = model('timming')->where('timming_id', $id)->find();
			if ($info['timming_apiid']) {
				$info['timming_api'] = json_decode($info['timming_apiid'], true);
			}
			$data = array('info' => $info, 'collect_list' => $collect_list);
			$this->assign($data);
			$this->setNav();
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/timming/del', 'timming', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['timming_id'] = array('IN', $id);
		$result = db('timming')->where($map)->delete();
		if ($result) {
			action_log('admin/timming/del', 'timming', $id, 1);
			return $this->success('删除成功！');
		} else {
			action_log('admin/timming/del', 'timming', 0, 0);
			return $this->error('删除失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/timming/status', 'timming', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '正常' : '锁定';
		$map['timming_id'] = array('IN', $id);
		$result = db('timming')->where($map)->setField('timming_status', $status);
		if ($result !== false) {
			action_log('admin/timming/status', 'timming', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/timming/status', 'timming', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}