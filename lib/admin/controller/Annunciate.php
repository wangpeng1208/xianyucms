<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Annunciate extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['type'] = input('type', 'ads_id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['status'] = input('status/d', '');
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		if ($admin['status'] == 1) {
			$where['ads_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['ads_status'] = array('eq', 0);
		}
		$list = db('Ads')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('ad_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/annunciate/index', $admin);
		$pages = '<ul class="pagination">' . adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '') . '</ul>';
		$data = array('list' => $list, 'page' => $pages);
		$this->ad_cache();
		$this->assign($data);
		$this->assign($admin);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$rs = model('Ads');
			$data = input('post.');
			$result = $this->validate($data, 'Ads');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->ads_id) {
				action_log('admin/annunciate/add', 'annunciate', $rs->ads_id, 1);
				write_file('./' . config('admin_ads_file') . '/' . $data['ads_name'] . '.js', t2js(stripslashes(trim($data['ads_content']))));
				return $this->success('添加广告成功！', url('admin/annunciate/index'));
			} else {
				action_log('admin/annunciate/add', 'annunciate', 0, 0);
				return $this->success('添加广告失败！');
			}
		} else {
			return view('edit');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('Ads')->where(array('ads_id' => $pk))->setField($name, $value);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Ads');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('Ads')->save($data, array('ads_id' => $data['ads_id']));
			if ($result !== false) {
				action_log('admin/annunciate/edit', 'annunciate', $data['ads_id'], 1);
				write_file('./' . config('admin_ads_file') . '/' . $data['ads_name'] . '.js', t2js(stripslashes(trim($data['ads_content']))));
				return $this->success('编辑成功！', Cookie('ad_url_forward'));
			} else {
				action_log('admin/annunciate/edit', 'annunciate', 0, 0);
				return $this->success('编辑失败！');
			}
		} else {
			$info = db('Ads')->where('ads_id', $id)->find();
			if (!$info) {
				return $this->error("ID操作失败！");
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
			action_log('admin/annunciate/del', 'annunciate', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['ads_id'] = array('IN', $id);
		$info = db('Ads')->where($map)->select();
		foreach ($info as $key => $value) {
			@unlink('./' . config('admin_ads_file') . '/' . $value['ads_name'] . '.js');
		}
		$result = db('Ads')->where($map)->delete();
		if ($result) {
			action_log('admin/annunciate/del', 'annunciate', $id, 1);
			return $this->success('删除广告成功！', Cookie('ad_url_forward'));
		} else {
			action_log('admin/annunciate/del', 'annunciate', $id, 0);
			return $this->error('删除广告失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/annunciate/status', 'annunciate', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['ads_id'] = array('IN', $id);
		$result = db('Ads')->where($map)->setField('ads_status', $status);
		if ($result !== false) {
			action_log('admin/annunciate/status', 'annunciate', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('ad_url_forward'));
		} else {
			action_log('admin/annunciate/status', 'annunciate', $id, 0, '设置' . $message . '状态失败');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function view()
	{
		$id = input('id', '');
		if (empty($id)) {
			return "选择ID才能预览广告";
		}
		$list = db('Ads')->field('ads_name')->where(array('ads_id' => $id))->find();
		if (!empty($list)) {
			$this->assign($list);
			return view('view');
			return getadsurl($list['ads_name']);
		} else {
			return '没有相关的广告';
		}
	}
}