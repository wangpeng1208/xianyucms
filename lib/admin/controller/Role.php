<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class Role extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['vcid'] = input('vcid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['pic'] = input('pic/d', '');
		$admin['content'] = input('content/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'role_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['p'] = input('p/d', '');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['role_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['vid']) {
			$where['role_vid'] = array('eq', $admin['vid']);
		}
		if ($admin['vcid']) {
			$where['vod_cid'] = getlistsqlin($admin['vcid']);
		}
		if ($admin['wd']) {
			$where['role_name|role_star|vod_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['stars']) {
			$where['role_stars'] = array('eq', $admin['stars']);
		}
		if ($admin["pic"] == 1) {
			$where['role_pic'] = array('eq', "");
		}
		if ($admin["pic"] == 2) {
			$where['role_pic'] = array('neq', "");
		}
		if ($admin["content"] == 1) {
			$where['role_content'] = array('eq', "");
		}
		if ($admin["content"] == 2) {
			$where['role_content'] = array('neq', "");
		}
		if ($admin['status'] == 1) {
			$where['role_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['role_status'] = array('eq', 0);
		}
		$list = db('role')->alias('r')->join('vod v', 'v.vod_id=r.role_vid', 'LEFT')->field('vod_id,vod_cid,vod_play,vod_name,vod_pic,vod_stars,vod_mcid,vod_hits,vod_gold,vod_status,vod_letters,vod_addtime,vod_jumpurl,r.*')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('role_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/role/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('listrole', F('_data/listrole'));
		$this->assign($data);
		$this->setNav();
		$this->assign(F('_data/url_html_config'));
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function actor()
	{
		$admin['vid'] = input('vid/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['hidden'] = input('hidden/d', '');
		$admin['type'] = input('type', 'oid');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'role_' . $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		if ($admin['vid']) {
			$where['role_vid'] = array('eq', $admin['vid']);
		}
		if ($admin['stars']) {
			$where['role_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['status'] == 1) {
			$where['role_status'] = array('eq', $admin['status']);
		} elseif ($admin['status'] == 2) {
			$where['role_status'] = array('eq', 0);
		} else {
			$where['role_status'] = array('gt', -1);
		}
		$list = db('role')->alias('r')->field('r.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->join('vod v', 'v.vod_id=r.role_vid', 'LEFT')->where($where)->order($admin['orders'])->select();
		Cookie('actor_role_url_forward', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list);
		$this->assign($admin);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('listrole', F('_data/listrole'));
		$this->assign($data);
		return view('ajax');
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Role');
			$actorid = db('actor')->where(array('actor_vid' => $data['role_vid']))->value('actor_id');
			$role = model('role');
			$role->save($data);
			if ($role->role_id) {
				$actor = model('actor');
				$actordata['actor_vid'] = $data['role_vid'];
				$actordata['actor_cid'] = gettypelistcid($data['role_vid'], 6);
				$actor->save($actordata);
				$this->_after_update($role->role_id, $data);
				$vod_letters = get_vod_info($data['role_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/role/read', array('id' => $role->role_id, 'pinyin' => $vod_letters, 'cid' => $data['role_cid'], 'dir' => getlistname($data['role_cid'], 'list_dir'), 'jumpurl' => $data['role_jumpurl']), true, true);
				action_log('admin/role/add', 'role', $role->role_id, 1);
				return $this->success('添加角色成功！' . baidutui($readurl, 'add', 1), url('admin/role/index'), admin_url('admin/html/role_detail_id', ['ids' => $role->role_id]));
			} else {
				action_log('admin/role/add', 'role', 0, 0);
				return $this->success('添加角色失败！');
			}
		} else {
			$vid = input('vid/d', '');
			if (!empty($vid)) {
				$info = db('vod')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->where(array('vod_id' => $vid))->find();
			}
			$info['role_oid'] = db('role')->where('role_vid', $vid)->max('role_oid') + 1;
			$info['role_vid'] = $vid;
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign('listrole', F('_data/listrole'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Role');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('role')->save($data, array('role_id' => $data['role_id']));
			if ($result !== false) {
				$this->_after_update($data['role_id'], $data);
				$vod_letters = get_vod_info($data['role_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/role/read', array('id' => $data['role_id'], 'pinyin' => $vod_letters, 'cid' => $data['role_cid'], 'dir' => getlistname($data['role_cid'], 'list_dir'), 'jumpurl' => $data['role_jumpurl']), true, true);
				action_log('admin/role/edit', 'role', $data['role_id'], 1);
				return $this->success('编辑角色成功！' . baidutui($readurl, 'update', 1), url('admin/role/index'), admin_url('admin/html/role_detail_id', ['ids' => $data['role_id']]));
			} else {
				action_log('admin/role/edit', 'role', 0, 0);
				return $this->success('编辑角色失败！');
			}
		} else {
			$info = db('role')->alias('r')->field('r.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('vod v', 'v.vod_id=r.role_vid', 'LEFT')->join('list l', 'l.list_id=r.role_cid', 'LEFT')->where(array('role_id' => $id))->find();
			if (!$info) {
				action_log('edit_role', 'Role', $id, session('member_auth.uid'), 0);
				action_log('admin/role/edit', 'role', $id, 0);
			}
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listrole', F('_data/listrole'));
			$this->assign('listvod', F('_data/listvod'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/role/del', 'role', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$rolearray = db('role')->field('role_content,role_cid,role_vid,role_id')->where('role_id', 'in', $id)->select();
		if (!empty($rolearray)) {
			foreach ($rolearray as $val) {
				$this->_after_update($val['role_id'], $val);
				@unlink('./' . config('upload_path') . '/' . $val['role_pic']);
				xianyu_del_img_file($val['role_content']);
			}
		}
		$map['role_id'] = array('IN', $id);
		$result = db('role')->where($map)->delete();
		if ($result) {
			action_log('admin/role/del', 'role', $id, 1);
			return $this->success('删除角色成功！', Cookie('role_url_forward'));
		} else {
			action_log('admin/role/del', 'role', 0, 0);
			return $this->error('删除角色失败！');
		}
	}
	public function ajaxdel()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/role/ajaxdel', 'role', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$rolearray = db('role')->field('role_content,role_cid,role_vid,role_id')->where('role_id', 'in', $id)->select();
		if (!empty($rolearray)) {
			foreach ($rolearray as $val) {
				$this->_after_update($val['role_id'], $val);
				@unlink('./' . config('upload_path') . '/' . $val['role_pic']);
				xianyu_del_img_file($val['role_content']);
			}
		}
		$map['role_id'] = array('IN', $id);
		$result = db('role')->where($map)->delete();
		if ($result) {
			action_log('admin/role/ajaxdel', 'role', $id, 1);
			return $this->success('删除角色成功！', Cookie('actor_role_url_forward'));
		} else {
			action_log('admin/role/ajaxdel', 'role', 0, 0);
			return $this->error('删除角色失败！');
		}
	}
	public function ajaxstatus($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/role/ajaxstatus', 'role', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['role_id'] = array('IN', $id);
		$result = db('role')->where($map)->setField('role_status', $status);
		if ($result !== false) {
			action_log('admin/role/ajaxstatus', 'role', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('actor_role_url_forward'));
		} else {
			action_log('admin/role/ajaxstatus', 'role', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function _after_update($id = "", $data = "")
	{
		if (empty($data)) {
			$data = get_role_find($id, 'role_id', 'role_vid,role_cid');
		}
		$actor = get_actor_find($data['role_vid'], 'actor_vid', 'actor_vid,actor_id,actor_cid');
		Cache::clear('foreach_role');
		if (config('data_cache_role')) {
			Cache::rm('data_cache_role_' . $id);
		}
		if (config('data_cache_vod')) {
			Cache::rm('data_cache_vod_' . $data['role_vid']);
		}
		if (config('data_cache_actor')) {
			Cache::rm('data_cache_actor_' . $actor['actor_id']);
		}
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_role_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['role_cid'], 'list_id_big') . '/' . md5(getlistname($data['role_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['role_cid'] . '/' . md5(getlistname($data['role_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['role_cid'], 'list_id_big') . '/' . md5(getlistname($data['role_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['role_cid'] . '/' . md5(getlistname($data['role_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_actor_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($actor['actor_cid'], 'list_id_big') . '/' . md5(getlistname($actor['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $actor['actor_cid'] . '/' . md5(getlistname($actor['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($actor['actor_cid'], 'list_id_big') . '/' . md5(getlistname($actor['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $actor['actor_cid'] . '/' . md5(getlistname($actor['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_actor_read') || config('html_cache_role_read')) {
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/actor/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/story/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/actor/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/story/' . get_small_id($data['role_vid']) . '/' . $data['role_vid'] . '/');
			}
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid/d');
		if (empty($id)) {
			action_log('admin/role/pestcid', 'role', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		if (empty($cid)) {
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['role_id'] = array('IN', $id);
			$result = db('role')->where($map)->setField('role_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/role/pestcid', 'role', $id, 1);
			return $this->success('转移分类成功', Cookie('role_url_forward'));
		} else {
			action_log('admin/role/pestcid', 'role', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/role/status', 'role', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['role_id'] = array('IN', $id);
		$result = db('role')->where($map)->setField('role_status', $status);
		if ($result !== false) {
			action_log('admin/role/status', 'role', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('role_url_forward'));
		} else {
			action_log('admin/role/status', 'role', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('role')->where(array('role_id' => $pk))->setField($name, $value);
		}
	}
	public function stars()
	{
		$where['role_id'] = input('id/d', '');
		$data['role_stars'] = input('stars/d', '');
		$result = db('role')->where($where)->update($data);
		if ($result) {
			return $this->success('设置星级成功');
		} else {
			return $this->error('设置星级失败');
		}
	}
	public function transfer()
	{
		$admin['p'] = input('p', 1);
		$admin['type'] = input('type', config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'role_' . $admin['type'] . ' ' . $admin['order'];
		$where['role_vid'] = array('neq', "");
		$limit = 1000;
		$count = db('role')->where($where)->count('role_id');
		$totalpages = ceil($count / 1000);
		$list = db('role')->field('role_id,role_vid')->where($where)->order('role_id')->limit(1000)->page($admin['p'])->select();
		foreach ($list as $key => $value) {
			$role_cid = gettypelistcid($value['role_vid'], 5);
			$result = db('role')->where(array('role_id' => $value['role_id']))->setField('role_cid', $role_cid);
			echo '<p>角色ID：' . $value['role_id'] . '分类' . $role_cid . ' 更新成功</p>';
			ob_flush();
			flush();
		}
		if ($totalpages > $admin['p']) {
			$url = url('admin/role/transfer', array('p' => $admin['p'] + 1));
			return $this->success('转移成功', $url);
		} else {
			return $this->success('转移完成', 'index');
		}
	}
}