<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use app\common\controller\Admin;
class Cm extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['sid'] = input('sid/d', '');
		$admin['status'] = input('status/d', '');
		$admin['view'] = input('view/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'cm_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['sid']) {
			$where['cm_sid'] = $admin['sid'];
		}
		if ($admin['uid']) {
			$where['cm_uid'] = $admin['uid'];
		}
		if ($admin['vid']) {
			$where['cm_vid'] = $admin['vid'];
		}
		if ($admin['status'] == 1) {
			$where['cm_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['cm_status'] = array('eq', 0);
		}
		if ($admin['wd']) {
			$where['username|nickname|cm_content|cm_username'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Cm')->alias('c')->join('User u', 'u.userid=c.cm_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		$datalist = $list->all();
		foreach ($datalist as $key => $val) {
			$sidname = getmodeid($datalist[$key]['cm_sid'], 'name');
			$info = getinfo($datalist[$key]['cm_vid'], $datalist[$key]['cm_sid'], $sidname . '_name');
			$datalist[$key]['cm_name'] = !empty($info) ? $info[$sidname . '_name'] : "";
			$datalist[$key]['cm_sidname'] = getmodeid($datalist[$key]['cm_sid'], 'title');
		}
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/cm/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$data = array('list' => $datalist, 'page' => $pages, 'data' => $list);
		$this->assign($admin);
		$this->assign('list_model', F('_data/modellist'));
		$this->assign($data);
		if ($admin['uid'] && $admin['view'] == 1) {
			return view('user_show');
		} else {
			$this->setNav();
			return view('index', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Cm');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('Cm')->update($data);
			if ($result !== false) {
				action_log('admin/cm/edit', 'cm', $id, 1);
				return $this->success('编辑评论成功！', url('admin/cm/index'));
			} else {
				action_log('admin/cm/edit', 'cm', $id, 0);
				return $this->success('编辑评论失败！');
			}
		} else {
			$info = db('Cm')->alias('c')->join('User u', 'u.userid=c.cm_uid', 'LEFT')->where(array('cm_id' => $id))->find();
			$infos = getinfo($info['cm_vid'], $info['cm_sid']);
			$info['cm_name'] = $infos[getmodeid($info['cm_sid'], 'name') . '_name'];
			$info['cm_model'] = getmodeid($info['cm_sid'], 'title');
			if (!$info) {
				action_log('admin/cm/edit', 'cm', $id, 0);
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
			action_log('admin/cm/del', 'cm', 0, 0);
			return $this->error('必须选择数据才能操作！', '');
		}
		$result = db('Cm')->where('cm_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/cm/del', 'cm', $id, 1);
			return $this->success('删除评论成功！', Cookie('__forward__'));
		} else {
			action_log('admin/cm/del', 'cm', $id, 0);
			return $this->error('删除评论失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/cm/status', 'cm', 0, 0);
			return $this->error("非法操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['cm_id'] = array('IN', $id);
		$result = db('Cm')->where($map)->setField('cm_status', $status);
		if ($result !== false) {
			action_log('admin/cm/status', 'cm', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('__forward__'));
		} else {
			action_log('admin/cm/status', 'cm', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}