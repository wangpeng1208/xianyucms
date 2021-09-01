<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class Actor extends Admin
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
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'actor_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['actor_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['vid']) {
			$where['actor_vid'] = array('eq', $admin['vid']);
		}
		if ($admin['vcid']) {
			$where['vod_cid'] = getlistsqlin($admin['vcid']);
		}
		if ($admin['wd']) {
			$where['vod_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['stars']) {
			$where['actor_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['status'] == 1) {
			$where['actor_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['actor_status'] = array('eq', 0);
		}
		$list = db('actor')->alias('a')->join('vod v', 'v.vod_id=a.actor_vid', 'LEFT')->field('vod_id,vod_cid,vod_play,vod_name,vod_pic,vod_stars,vod_mcid,vod_hits,vod_gold,vod_status,vod_letters,vod_addtime,vod_jumpurl,actor_id,actor_cid,actor_vid,actor_addtime,actor_hits,actor_status,actor_stars')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('actor_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/actor/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('listactor', F('_data/listactor'));
		$this->assign($data);
		$this->setNav();
		$this->assign(F('_data/url_html_config'));
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		$actor = model('Actor');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Actor');
			if (true !== $result) {
				return $this->error($result);
			}
			$info = $actor->where(array('actor_vid' => $data['actor_vid']))->find();
			if ($info) {
				action_log('admin/actor/add', 'actor', $data['actor_vid'], 0);
				return $this->error("该视频已存在演员表");
			}
			$actor->save($data);
			if ($actor->actor_id) {
				$this->_after_update($actor->actor_id, $data);
				$vod_letters = get_vod_info($data['actor_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/actor/read', array('id' => $actor->actor_id, 'pinyin' => $vod_letters, 'cid' => $data['actor_cid'], 'dir' => getlistname($data['actor_cid'], 'list_dir'), 'jumpurl' => $data['actor_jumpurl']), true, true);
				action_log('admin/actor/add', 'actor', $actor->actor_id, 1);
				return $this->success('添加演员表成功！' . baidutui($readurl, 'add', 1), url('admin/actor/index'), admin_url('admin/html/actor_detail_id', ['ids' => $actor->actor_id]));
			} else {
				action_log('admin/actor/add', 'actor', 0, 0);
				return $this->success('添加演员表失败！');
			}
		} else {
			$vid = input('vid/d', '');
			if (!empty($vid)) {
				$info = db('vod')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->where(array('vod_id' => $vid))->find();
			}
			$info['actor_vid'] = $vid;
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info, 'data' => $datass);
			$this->assign('listactor', F('_data/listactor'));
			$this->assign('listrole', F('_data/listrole'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		$actor = model('Actor');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Actor');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = $actor->save($data, array('actor_id' => $data['actor_id']));
			if ($result !== false) {
				$this->_after_update($data['actor_id'], $data);
				action_log('admin/actor/edit', 'actor', $data['actor_id'], 1);
				$vod_letters = get_vod_info($data['actor_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/actor/read', array('id' => $data['actor_id'], 'pinyin' => $vod_letters, 'cid' => $data['actor_cid'], 'dir' => getlistname($data['actor_cid'], 'list_dir'), 'jumpurl' => $data['actor_jumpurl']), true, true);
				return $this->success('编辑演员表成功！' . baidutui($readurl, 'update', 1), url('admin/actor/index'), admin_url('admin/html/actor_detail_id', ['ids' => $data['actor_id']]));
			} else {
				action_log('admin/actor/edit', 'actor', $data['actor_id'], 0);
				return $this->success('编辑演员表失败！');
			}
		} else {
			$info = db('actor')->alias('a')->field('a.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('vod v', 'v.vod_id=a.actor_vid', 'LEFT')->join('list l', 'l.list_id=a.actor_cid', 'LEFT')->find($id);
			$actor = $actor::get($id);
			$roles = $actor->role;
			if (!$info) {
				action_log('admin/actor/edit', 'actor', $id, 0, '数据不存在');
				return $this->error("ID操作失败！");
			}
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info, 'roles' => $roles);
			$this->assign($data);
			$this->assign('listactor', F('_data/listactor'));
			$this->assign('listrole', F('_data/listrole'));
			$this->assign('listvod', F('_data/listvod'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function addrole()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Role');
			if (true !== $result) {
				return $this->error($result);
			}
			$role = model('role');
			$role->save($data);
			if ($role->role_id) {
				$vod_letters = get_vod_info($data['role_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/role/read', array('id' => $role->role_id, 'pinyin' => $vod_letters, 'cid' => $data['role_cid'], 'dir' => getlistname($data['role_cid'], 'list_dir'), 'jumpurl' => $data['role_jumpurl']), true, true);
				action_log('admin/actor/addrole', 'actor', $role->role_id, 1);
				return $this->success('添加角色成功！' . baidutui($readurl, 'add', 1), Cookie('actor_role_url_forward'));
			} else {
				action_log('admin/actor/addrole', 'actor', 0, 0);
				return $this->success('添加角色失败！');
			}
		} else {
			$vid = input('vid/d', '');
			if (!empty($vid)) {
				$info = db('vod')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->where(array('vod_id' => $vid))->find();
			}
			$info['role_oid'] = db('role')->where('role_vid', $vid)->max('role_oid') + 1;
			$info['role_vid'] = $vid;
			$info['role_starsarr'] = admin_star_arr(1);
			$info['role_addtime'] = time();
			$info['admin_time_edit'] = config("admin_time_edit");
			if (config('admin_time_edit')) {
				$info['checktime'] = 'checked';
			}
			$data = array('info' => $info, 'data' => $datass);
			$this->assign('listrole', F('_data/listrole'));
			$this->assign($data);
			return view('role');
		}
	}
	public function editrole($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Role');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('role')->save($data, array('role_id' => $data['role_id']));
			if ($result !== false) {
				$vod_letters = get_vod_info($data['role_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/role/read', array('id' => $data['role_id'], 'pinyin' => $vod_letters, 'cid' => $data['role_cid'], 'dir' => getlistname($data['role_cid'], 'list_dir'), 'jumpurl' => $data['role_jumpurl']), true, true);
				$actor = get_actor_find($data['role_vid'], 'actor_vid', 'actor_id,actor_cid');
				if ($actor) {
					$actordata['actor_vid'] = $data['role_vid'];
					$actordata['actor_cid'] = $actor['actor_cid'];
					$this->_after_update($actor['actor_id'], $actordata);
				}
				action_log('admin/actor/editrole', 'actor', $data['role_id'], 1);
				return $this->success('编辑角色成功！' . baidutui($readurl, 'update', 1), Cookie('actor_role_url_forward'));
			} else {
				action_log('admin/actor/editrole', 'actor', 0, 0);
				return $this->success('编辑角色失败！');
			}
		} else {
			$info = db('role')->alias('r')->field('r.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('vod v', 'v.vod_id=r.role_vid', 'LEFT')->join('list l', 'l.list_id=r.role_cid', 'LEFT')->where(array('role_id' => $id))->find();
			if (!$info) {
				action_log('edit_role', 'Role', $id, session('member_auth.uid'), 0);
				return $this->error("ID操作失败！");
			}
			$info['role_starsarr'] = admin_star_arr($info['role_stars']);
			if (config('admin_time_edit')) {
				$info['checktime'] = 'checked';
			}
			$info['role_content'] = xianyu_news_img_array($info['role_content']);
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listrole', F('_data/listrole'));
			$this->assign('listvod', F('_data/listvod'));
			return view('role');
		}
	}
	public function del()
	{
		$actor = model('Actor');
		$role = model('Role');
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/actor/del', 'actor', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$this->delfile($id);
	}
	public function delfile($id)
	{
		$vid = db('actor')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'LEFT')->where('actor_id', 'in', $id)->field('actor_id,actor_vid,actor_cid,vod_letters')->select();
		foreach ($vid as $key => $val) {
			if ($this->config_url['url_html']) {
				@unlink('./' . xianyu_url_html('home/actor/read', array('id' => $val['actor_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['actor_cid'], 'dir' => getlistname($val['actor_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
			}
			$vidarray[] = $val['actor_vid'];
			$this->_after_update($val['actor_id'], $val);
		}
		$rolearray = db('role')->field('role_id,role_cid,role_pic,role_content,vod_letters')->alias('r')->join('vod v', 'v.vod_id = r.role_vid', 'LEFT')->where('role_vid', 'in', $vidarray)->select();
		if (!empty($rolearray)) {
			foreach ($rolearray as $val) {
				if ($this->config_url['url_html']) {
					@unlink('./' . xianyu_url_html('home/role/read', array('id' => $val['role_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['role_cid'], 'dir' => getlistname($val['role_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
				}
				if (config('data_cache_role')) {
					Cache::rm('data_cache_role_' . $val['role_id']);
				}
				if (config('html_cache_on') && config('html_cache_story_list')) {
					@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val['role_cid'], 'list_id_big') . '/' . md5(getlistname($val['role_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/pc/show/' . $val['role_cid'] . '/' . md5(getlistname($val['role_cid'], 'list_url')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val['role_cid'], 'list_id_big') . '/' . md5(getlistname($val['role_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val['role_cid'] . '/' . md5(getlistname($val['role_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				}
				@unlink('./' . config('upload_path') . '/' . $val['role_pic']);
				xianyu_del_img_file($val['role_content']);
			}
			db('role')->where('role_vid', 'in', $vidarray)->delete();
			Cache::clear('foreach_role');
		}
		$result = db('actor')->where('actor_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/actor/del', 'actor', $id, 1);
			return $this->success('删除演员表成功！', Cookie('actor_url_forward'));
		} else {
			action_log('admin/actor/del', 'actor', $id, 0);
			return $this->error('删除演员表失败！');
		}
	}
	public function _after_update($id = "", $data = "")
	{
		if (empty($data)) {
			$data['actor_vid'] = get_actor_info($id, 'actor_id', 'actor_vid');
		}
		Cache::clear('foreach_actor');
		if (config('data_cache_actor')) {
			Cache::rm('data_cache_actor_' . $id);
		}
		if (config('data_cache_vod')) {
			Cache::rm('data_cache_vod_' . $data['actor_vid']);
		}
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_actor_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['actor_cid'], 'list_id_big') . '/' . md5(getlistname($data['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['actor_cid'] . '/' . md5(getlistname($data['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['actor_cid'], 'list_id_big') . '/' . md5(getlistname($data['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['actor_cid'] . '/' . md5(getlistname($data['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_actor_read')) {
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/actor/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/story/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/actor/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/story/' . get_small_id($data['actor_vid']) . '/' . $data['actor_vid'] . '/');
			}
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid/d');
		if (empty($id)) {
			action_log('admin/actor/pestcid', 'actor', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		if (empty($cid)) {
			action_log('admin/actor/pestcid', 'actor', 0, 0);
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['actor_id'] = array('IN', $id);
			$result = db('actor')->where($map)->setField('actor_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/actor/pestcid', 'actor', $id, 1);
			return $this->success('转移分类成功', Cookie('actor_url_forward'));
		} else {
			action_log('admin/actor/pestcid', 'actor', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$actor = model('Actor');
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/actor/status', 'actor', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['actor_id'] = array('IN', $id);
		$vid = $actor->where($map)->field('actor_vid')->select();
		foreach ($vid as $key => $val) {
			$vidarray[] = $val['actor_vid'];
		}
		db('role')->where('role_vid', 'in', $vidarray)->setField('role_status', $status);
		$result = $actor->where($map)->setField('actor_status', $status);
		if ($result !== false) {
			action_log('admin/actor/status', 'actor', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('actor_url_forward'));
		} else {
			action_log('admin/actor/status', 'actor', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('actor')->where(array('actor_id' => $pk))->setField($name, $value);
		}
	}
	public function stars()
	{
		$id = input('id/d', '');
		$stars = input('stars/d', '');
		$result = db('actor')->where('actor_id', $id)->setField('actor_stars', $stars);
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
		$where['vod_id'] = array('neq', "");
		$limit = 1000;
		$actor_cid = db('list')->where('list_sid', 6)->value('list_id');
		$count = db('role')->group('role_vid')->select();
		$totalpages = ceil(count($count) / 1000);
		$list = db('role')->alias('r')->join('vod v', 'v.vod_id=r.role_vid', 'LEFT')->join('list l', 'l.list_id=r.role_cid', 'LEFT')->field('r.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,vod_actortitle,vod_actorkeywords,vod_actordescription,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_hits_lasttime,vod_stars,list_id,list_name,list_dir,list_pid,list_jumpurl')->where($where)->group('role_vid')->order('role_id')->limit(1000)->page($admin['p'])->select();
		foreach ($list as $key => $value) {
			$actorid = db('actor')->where('actor_vid = ' . $value['vod_id'])->find();
			if ($actorid) {
				echo "<p>视频ID：" . $value['vod_id'] . '已经添加到了演员表</p>';
			} else {
				$data['actor_cid'] = gettypelistcid($value['vod_id'], 6);
				$data['actor_vid'] = $value['vod_id'];
				$data['actor_title'] = $value['vod_actortitle'];
				$data['actor_keywords'] = $value['vod_actorkeywords'];
				$data['actor_description'] = $value['vod_actordescription'];
				$data['actor_addtime'] = $value['vod_addtime'];
				$data['actor_hits'] = $value['vod_hits'];
				$data['actor_hits_day'] = $value['vod_hits_day'];
				$data['actor_hits_week'] = $value['vod_hits_week'];
				$data['actor_hits_month'] = $value['vod_hits_month'];
				$data['actor_hits_lasttime'] = $value['vod_hits_lasttime'];
				$data['actor_stars'] = $value['vod_stars'];
				$result = db('actor')->insert($data);
				if ($result) {
					echo "<p>视频ID：" . $value['vod_id'] . '视频名称' . $value['vod_name'] . ' 添加到演员表成功</p>';
				}
			}
			ob_flush();
			flush();
		}
		if ($totalpages > $admin['p']) {
			$url = url('admin/actor/transfer', array('p' => $admin['p'] + 1));
			return $this->success('转移成功', $url);
		} else {
			return $this->success('转移完成', 'index');
		}
	}
}