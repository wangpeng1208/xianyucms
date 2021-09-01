<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use app\common\controller\Admin;
class Gb extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['intro'] = input('intro/d', '');
		$admin['status'] = input('status/d', '');
		$admin['view'] = input('view/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'gb_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['gb_cid'] = $admin['cid'];
		}
		if ($admin['intro'] == 1) {
			$where['gb_intro'] = array("eq", "");
		}
		if ($admin['intro'] == 2) {
			$where['gb_intro'] = array("neq", "");
		}
		if ($admin['uid']) {
			$where['gb_uid'] = $admin['uid'];
		}
		if ($admin['vid']) {
			$where['gb_vid'] = $admin['vid'];
		}
		if ($admin['status'] == 1) {
			$where['gb_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['gb_status'] = array('eq', 0);
		}
		if ($admin['wd']) {
			$where['nickname|username|gb_nickname|gb_content'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Gb')->alias('g')->join('User u', 'u.userid=g.gb_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('gb_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/gb/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$gb_type = array(1 => '其他留言', 2 => '影片报错', 3 => '网站建议', 4 => '访问故障');
		$data = array('list' => $list, 'page' => $pages, 'gb_type' => $gb_type);
		$this->assign($admin);
		$this->assign($data);
		if ($admin['uid'] && $admin['view'] == 1) {
			return view('user_show');
		} else {
			$this->setNav();
			return view('index', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function errors()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['intro'] = input('intro/d', '');
		$admin['status'] = input('status/d', '');
		$admin['cid'] = 2;
		$admin['view'] = input('view/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'gb_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where['gb_cid'] = $admin['cid'];
		$where['gb_vid'] = array("neq", 0);
		if ($admin['intro'] == 1) {
			$where['gb_intro'] = array("eq", "");
		}
		if ($admin['intro'] == 2) {
			$where['gb_intro'] = array("neq", "");
		}
		if ($admin['uid']) {
			$where['gb_uid'] = $admin['uid'];
		}
		if ($admin['status'] == 1) {
			$where['gb_status'] = array('eq', $admin['status']);
		} elseif ($admin['status'] == 2) {
			$where['gb_status'] = array('eq', 0);
		}
		if ($admin['wd']) {
			$where['nickname|username|gb_nickname|gb_content|vod_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Gb')->alias('g')->join('User u', 'u.userid=g.gb_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('gb_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/gb/errors', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('error', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function edit($id = null)
	{
		$rs = model('Gb');
		if (IS_POST) {
			$data = input('post.');
			$result = $rs->update($data);
			if ($result !== false) {
				action_log('admin/gb/edit', 'gb', $data['gb_id'], 1);
				return $this->success('编辑留言成功！', Cookie('gb_url_forward'));
			} else {
				action_log('admin/gb/edit', 'gb', 0, 0);
				return $this->success('编辑留言失败！');
			}
		} else {
			$info = db('Gb')->alias('g')->join('User u', 'u.userid=g.gb_uid', 'LEFT')->where(array('gb_id' => $id))->find();
			if (!$info) {
				action_log('admin/gb/edit', 'gb', 0, 0);
				return $this->error("ID操作失败！");
			}
			if (config('admin_time_edit')) {
				$info['checktime'] = 'checked';
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
			action_log('admin/gb/del', 'gb', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$result = db('Gb')->where('gb_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/gb/del', 'gb', $id, 1);
			return $this->success('删除评论成功！', Cookie('gb_url_forward'));
		} else {
			action_log('admin/gb/del', 'gb', 0, 0);
			return $this->error('删除评论失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/gb/status', 'gb', 0, 0);
			return $this->error("非法操作！");
		}
		$message = !$status ? '隐藏' : '显示';
		$map['gb_id'] = array('IN', $id);
		$result = db('Gb')->where($map)->setField('gb_status', $status);
		if ($result !== false) {
			action_log('admin/gb/status', 'gb', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('gb_url_forward'));
		} else {
			action_log('admin/gb/status', 'gb', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function order($oid = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('order_gb', 'Gb', $id, session('member_auth.uid'), 0);
			return $this->error("非法操作！", '');
		}
		$message = !$oid ? '置顶' : '取消置顶';
		$map['gb_id'] = array('IN', $id);
		$result = db('Gb')->where($map)->setField('gb_oid', $oid);
		if ($result !== false) {
			action_log('admin/gb/del', 'gb', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('gb_url_forward'));
		} else {
			action_log('admin/gb/del', 'gb', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}