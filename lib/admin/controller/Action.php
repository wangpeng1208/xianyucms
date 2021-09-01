<?php


namespace app\admin\controller;

use think\Db;
use app\common\controller\Admin;
class Action extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['status'] = input('status/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'id');
		$admin['order'] = input('order', 'desc');
		$admin['p'] = input('p/d', '');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		if ($admin['wd']) {
			$where['name|title'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['status'] == 1) {
			$where['status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['status'] = array('eq', 0);
		}
		$list = Db::name('Action')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$type = array(0 => '用户', 1 => '系统');
		Cookie('action_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/action/index', $admin);
		$pages = '<ul class="pagination">' . adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '') . '</ul>';
		$data = array('list' => $list, 'page' => $pages, 'list_type' => $type);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$rs = model('Action');
			$data = input('post.');
			$result = $this->validate($data, 'Action');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->id) {
				action_log('admin/action/add', 'action', $rs->id, 1);
				return $this->success('添加成功！', url('admin/action/index'));
			} else {
				action_log('admin/action/add', 'action', 0, 0, $model->getError());
				return $this->error($model->getError());
			}
		} else {
			return view('edit');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			Db::name('Action')->where(array('id' => $pk))->setField($name, $value);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Action');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('Action')->save($data, array('id' => $data['id']));
			if ($result !== false) {
				action_log('admin/action/edit', 'action', $data['id'], 1);
				return $this->success('编辑成功！', url('admin/action/index'));
			} else {
				action_log('admin/action/edit', 'action', $data['id'], 1, $model->getError());
				return $this->error($model->getError());
			}
		} else {
			$info = Db::name('Action')->find($id);
			if (!$info) {
				action_log('admin/action/edit', 'action', $data['id'], 1, '行为ID不存在,非法操作！');
				return $this->error("非法操作！");
			}
			$data = array('info' => $info);
			$this->assign($data);
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/action/del', 'action', 0, 0);
			return $this->error("非法操作！");
		}
		$map['id'] = array('IN', $id);
		$result = Db::name('Action')->where($map)->delete();
		if ($result) {
			action_log('admin/action/del', 'action', $id, 1);
			return $this->success('删除成功！', Cookie('action_url_forward'));
		} else {
			action_log('admin/action/del', 'action', $id, 0);
			return $this->error('删除失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/action/status', 'action', 0, 0);
			return $this->error("非法操作！");
		}
		if (empty($status)) {
			$status = input('status');
		}
		$message = !$status ? '禁用' : '启用';
		$map['id'] = array('IN', $id);
		$result = Db::name('Action')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/action/status', 'action', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('action_url_forward'));
		} else {
			action_log('admin/action/status', 'action', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function logs()
	{
		$admin['status'] = input('status/d', '');
		$admin['start_time'] = strtotime(input('start_time'));
		$admin['end_time'] = strtotime(input('end_time'));
		$admin['type'] = input('type', 'create_time');
		$admin['uid'] = input('uid/d', '');
		$admin['order'] = input('order', 'desc');
		$admin['p'] = input('p/d', '');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = array();
		if ($admin['uid']) {
			$where['user_id'] = array('eq', $admin['uid']);
		}
		if ($admin['start_time'] && $admin['end_time']) {
			$where['create_time'] = array('between', [$admin['start_time'], $admin['end_time']]);
		}
		if ($admin['status'] == 1) {
			$where['m_ststus'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['m_ststus'] = array('eq', 0);
		}
		$list = Db::view('ActionLog', ['status' => 'm_ststus', '*'])->where($where)->view('member', 'nickname', 'ActionLog.user_id=member.uid', "LEFT")->view('action', ['title'], 'action.id=ActionLog.action_id', "LEFT")->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/action/logs', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		Cookie('logs_url_forward', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('logs', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function detail($id = 0)
	{
		$model = model('ActionLog');
		if (empty($id)) {
			return $this->error('参数错误！');
		}
		$info = Db::name('ActionLog')->field('a.*,a.id as a_id,a.remark as a_remark,a.status as a_status,m.nickname,m.username,ac.id as ac_id,ac.remark as ac_remark,ac.*')->alias('a')->join('member m', 'm.uid = a.user_id', 'LEFT')->join('action ac', 'ac.id = a.action_id', 'LEFT')->find($id);
		$info['action_ip'] = long2ip($info['action_ip']);
		$info['create_time'] = date('Y-m-d H:i:s', $info['create_time']);
		$data = array('info' => $info);
		$this->assign($data);
		return $this->fetch();
	}
	public function dellog()
	{
		$id = $this->getArrayParam('id');
		if (!$id) {
			action_log('admin/action/dellog', 'action', 0, 0);
			return $this->error("非法操作！");
		}
		$map['id'] = array('IN', $id);
		$res = Db::name('ActionLog')->where($map)->delete();
		if ($res !== false) {
			action_log('admin/action/dellog', 'action', $id, 1);
			return $this->success('删除成功！', Cookie('logs_url_forward'));
		} else {
			action_log('admin/action/dellog', 'action', $id, 0);
			return $this->error('删除失败！');
		}
	}
	public function clear($id = '')
	{
		$res = Db::name('ActionLog')->where('1=1')->delete();
		if ($res !== false) {
			action_log('admin/action/clear', 'action', $res, 1);
			return $this->success('日志清空成功！', url('admin/action/logs'));
		} else {
			action_log('admin/action/clear', 'action', $res, 0);
			return $this->error('日志清空失败！');
		}
	}
}