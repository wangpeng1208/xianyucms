<?php


namespace app\admin\controller;

use think\Cache;
use app\common\controller\Admin;
class Link extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['status'] = input('status/d', '');
		$admin['p'] = input('p/d', '');
		$admin['t'] = input('t/d', '');
		$admin['type'] = input('type', 'link_id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		if ($admin['status'] == 1) {
			$where['link_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['link_status'] = array('eq', 0);
		}
		if ($admin['t']) {
			$where['link_type'] = array('eq', $admin['t']);
		}
		$list = db('Link')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$type = array(1 => '文字连接', 2 => '图片连接');
		Cookie('link_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/link/index', $admin);
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
			$data = input('post.');
			$result = $this->validate($data, 'Link');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('Link')->insertGetId($data);
			if ($result) {
				action_log('admin/link/add', 'link', $result, 1);
				return $this->success('添加友情连接成功！', url('admin/link/index'));
			} else {
				action_log('admin/link/add', 'link', 0, 0);
				return $this->success('添加友情连接失败！');
			}
		} else {
			return view('edit');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('Link')->where(array('link_id' => $pk))->setField($name, $value);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Link');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('Link')->update($data);
			if ($result !== false) {
				action_log('admin/link/edit', 'link', $id, 1);
				return $this->success('编辑成功！', Cookie('link_url_forward'));
			} else {
				action_log('admin/link/edit', 'link', $id, 0);
				return $this->success('编辑失败！');
			}
		} else {
			$info = db('Link')->find($id);
			if (!$info) {
				action_log('edit_link', 'Link', $id, session('member_auth.uid'), 0, "没有相关数据");
				return $this->error("没有相关数据");
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
			action_log('admin/link/del', 'link', 0, 0, '必须选择ID才能操作');
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['link_id'] = array('IN', $id);
		$result = db('Link')->where($map)->delete();
		if ($result) {
			action_log('admin/link/del', 'link', $id, 1);
			return $this->success('删除友情连接成功！', Cookie('link_url_forward'));
		} else {
			action_log('admin/link/del', 'link', $id, 0);
			return $this->error('删除友情连接失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/link/status', 'link', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['link_id'] = array('IN', $id);
		$result = db('Link')->where($map)->setField('link_status', $status);
		if ($result !== false) {
			action_log('admin/link/status', 'link', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('link_url_forward'));
		} else {
			action_log('admin/link/status', 'link', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}