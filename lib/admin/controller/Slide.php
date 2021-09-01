<?php


namespace app\admin\controller;

use think\validate;
use app\common\controller\Admin;
class Slide extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['status'] = input('status/d', '');
		$admin['cid'] = input('cid', '');
		$admin['type'] = input('type', 'slide_id');
		$admin['order'] = input('order', 'desc');
		$admin['p'] = input('p/d', '');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		if ($admin['cid']) {
			if ($admin['cid'] == "9999") {
				$where['slide_cid'] = array('eq', -1);
			} else {
				$where['slide_cid'] = array('eq', $admin['cid']);
			}
		}
		if ($admin['status'] == 1) {
			$where['slide_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['slide_status'] = array('eq', 0);
		}
		$list = db('slide')->alias('s')->field('s.*,vod_id,vod_name,vod_cid,vod_letters,vod_pic,vod_title,vod_addtime,vod_continu,vod_jumpurl,vod_diantai,vod_tvcont,vod_content,vod_actor,vod_year')->join('vod v', 'v.vod_id=s.slide_vid', 'LEFT')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/slide/index', $admin);
		$pages = '<ul class="pagination">' . adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '') . '</ul>';
		Cookie('slide_url_forward', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list, 'channel' => F('_data/listtree'), 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Slide');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('slide')->insert($data);
			if ($result) {
				action_log('admin/slide/add', 'slide', $result, 1);
				return $this->success('添加幻灯片成功！', url('admin/slide/index'));
			} else {
				action_log('admin/slide/add', 'slide', 0, 0);
				return $this->success('添加幻灯片失败！');
			}
		} else {
			$data = array('list' => F('_data/listtree'));
			$this->assign($data);
			return view('edit');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('slide')->where(array('slide_id' => $pk))->setField($name, $value);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Slide');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('slide')->update($data, array('slide_id' => $data['slide_id']));
			if ($result !== false) {
				action_log('admin/slide/edit', 'slide', $data['slide_id'], 1);
				return $this->success('编辑幻灯片成功！', Cookie('slide_url_forward'));
			} else {
				action_log('admin/slide/edit', 'slide', 0, 0);
				return $this->success('编辑幻灯片失败！');
			}
		} else {
			$info = db('slide')->where(array('slide_id' => $id))->find();
			if (!$info) {
				action_log('admin/slide/edit', 'slide', 0, 0);
				return $this->error("ID操作失败！");
			}
			$data = array('info' => $info, 'list' => F('_data/listtree'));
			$this->assign($data);
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/slide/del', 'slide', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['slide_id'] = array('IN', $id);
		$result = db('slide')->where($map)->delete();
		if ($result) {
			action_log('admin/slide/del', 'slide', $id, 1);
			return $this->success('删除幻灯片成功！', Cookie('slide_url_forward'));
		} else {
			action_log('admin/slide/del', 'slide', $id, 0);
			return $this->error('删除幻灯片失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/slide/status', 'slide', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['slide_id'] = array('IN', $id);
		$result = db('slide')->where($map)->setField('slide_status', $status);
		if ($result !== false) {
			action_log('admin/slide/status', 'slide', $id, 1);
			return $this->success('设置' . $message . '状态成功！', Cookie('slide_url_forward'));
		} else {
			action_log('admin/slide/status', 'slide', $id, 0);
			return $this->error('设置' . $message . '状态失败！');
		}
	}
}