<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class Tv extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['cid'] = input('cid/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'tv_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['tv_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['wd']) {
			$where['tv_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['stars']) {
			$where['tv_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['status'] == 1) {
			$where['tv_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['tv_status'] = array('eq', 0);
		}
		$list = db('tv')->where($where)->field('tv_content,tv_data', true)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('tv_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/tv/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign('listtv', F('_data/listtv'));
		$this->assign($data);
		$this->assign($admin);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$rs = model('Tv');
			$data = input('post.');
			$result = $this->validate($data, 'Tv');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->tv_id) {
				$this->_after_insert($rs->tv_id, $data);
				action_log('admin/tv/add', 'tv', $rs->tv_id, 1);
				$readurl = xianyu_data_url('home/tv/read', array('id' => $rs->tv_id, 'pinyin' => $rs->tv_letters, 'cid' => $rs->tv_cid, 'dir' => getlistname($rs->tv_cid, 'list_dir'), 'jumpurl' => $data['tv_jumpurl']), true, true);
				return $this->success('添加电台成功！' . baidutui($readurl, 'add', 1), url('admin/tv/index'));
			} else {
				action_log('admin/tv/add', 'tv', 0, 0);
				return $this->success('添加电台失败！', url('admin/tv/index'));
			}
		} else {
			$info['tv_data'][0]['title'] = "";
			$info['tv_data'][0]['info'] = "";
			$info['tv_week_list'] = config('week_list');
			$info['tv_area_list'] = explode(',', config('play_area'));
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign('listtv', F('_data/listtv'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		$rs = model('Tv');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Tv');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data, array('tv_id' => $data['tv_id']));
			if ($rs->tv_id) {
				$this->_after_insert($rs->tv_id, $data);
				action_log('admin/tv/edit', 'tv', $rs->tv_id, 1);
				$readurl = xianyu_data_url('home/tv/read', array('id' => $rs->tv_id, 'pinyin' => $rs->tv_letters, 'cid' => $rs->tv_cid, 'dir' => getlistname($rs->tv_cid, 'list_dir'), 'jumpurl' => $data['tv_jumpurl']), true, true);
				return $this->success('编辑电台成功！' . baidutui($readurl, 'add', 1), url('admin/tv/index'));
			} else {
				action_log('admin/tv/edit', 'tv', 0, 0);
				return $this->success('编辑电台失败！', url('admin/tv/index'));
			}
		} else {
			$info = db('tv')->find($id);
			if (!$info) {
				action_log('admin/tv/edit', 'tv', 0, 0);
				return $this->error("ID操作失败！");
			}
			$info['tv_week_list'] = config('week_list');
			$info['tv_area_list'] = explode(',', config('play_area'));
			if ($info['tv_data']) {
				$info['tv_data'] = json_decode($info['tv_data'], true);
			} else {
				$info['tv_data'][0]['title'] = "";
				$info['tv_data'][0]['info'] = "";
			}
			$info['tv_prty_array'] = model('Prty')->prty_data($id, 7);
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listtv', F('_data/listtv'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function _after_insert($id = "", $data = "")
	{
		if (!empty($id) && !empty($data)) {
			if ($data["tv_prty"]) {
				model('Prty')->prty_update($id, $data["tv_prty"], 7);
			}
		}
		$this->_after_update($id, $data);
	}
	public function _after_update($id = "", $data = "")
	{
		if (config('data_cache_tv')) {
			Cache::rm('data_cache_tv_' . $id);
		}
		Cache::clear('foreach_tv');
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_tv_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['tv_cid'], 'list_id_big') . '/' . md5(getlistname($data['tv_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['tv_cid'] . '/' . md5(getlistname($data['tv_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['tv_cid'], 'list_id_big') . '/' . md5(getlistname($data['tv_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['tv_cid'] . '/' . md5(getlistname($data['tv_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_news_read')) {
				deldir(RUNTIME_PATH . 'html/pc/tv/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/tv/' . get_small_id($id) . '/' . $id . '/');
			}
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/tv/del', 'tv', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$this->_after_del_file($id);
		$this->delfile($id);
	}
	public function _after_del_file($id = "")
	{
		$array = db('tv')->field('tv_id,tv_cid,tv_pic,tv_content')->where('tv_id', 'in', $id)->select();
		foreach ($array as $val) {
			$this->_after_update($val['tv_id'], $val);
			@unlink('./' . config('upload_path') . '/' . $val['tv_pic']);
			xianyu_del_img_file($val['tv_content']);
		}
	}
	public function delfile($id)
	{
		db('Prty')->where('prty_id', 'in', $id, 'and', 'prty_sid', 7)->delete();
		$result = db('tv')->where('tv_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/tv/del', 'tv', $id, 1);
			return $this->success('删除电台成功！', Cookie('tv_url_forward'));
		} else {
			action_log('admin/tv/del', 'tv', 0, 0);
			return $this->error('删除电台失败！');
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/tv/pestcid', 'tv', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		if (empty($cid)) {
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['tv_id'] = array('IN', $id);
			$result = db('tv')->where($map)->setField('tv_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/tv/pestcid', 'tv', $id, 1);
			return $this->success('转移分类成功');
		} else {
			action_log('admin/tv/pestcid', 'tv', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/tv/status', 'tv', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['tv_id'] = array('IN', $id);
		$result = db('tv')->where($map)->setField('tv_status', $status);
		if ($result !== false) {
			action_log('admin/tv/status', 'tv', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/tv/status', 'tv', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('tv')->where(array('tv_id' => $pk))->setField($name, $value);
		}
	}
	public function stars()
	{
		$where['tv_id'] = input('id/d', '');
		$data['tv_stars'] = input('stars/d', '');
		$result = db('tv')->where($where)->update($data);
		if ($result) {
			return $this->success('设置星级成功');
		} else {
			return $this->error('设置星级失败');
		}
	}
}