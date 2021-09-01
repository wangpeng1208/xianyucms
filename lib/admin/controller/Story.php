<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class Story extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['cid'] = input('cid/d', '');
		$admin['vcid'] = input('vcid/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['url'] = trim(input('url', ''));
		$admin['cont'] = input('cont/d', '');
		$admin['status'] = input('status/d', '');
		$admin['content'] = input('content/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'story_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['story_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['vcid']) {
			$where['vod_cid'] = getlistsqlin($admin['vcid']);
		}
		if ($admin['wd']) {
			$where['vod_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['url']) {
			$where['story_url'] = array('like', '%' . $admin['url'] . '%');
		}
		if ($admin['stars']) {
			$where['story_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['cont'] == 1) {
			$where['story_cont'] = array('eq', $admin['cont']);
		}
		if ($admin['cont'] == 2) {
			$where['story_cont'] = array('eq', 0);
		}
		if ($admin['status'] == 1) {
			$where['story_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['story_status'] = array('eq', 0);
		}
		$list = db('story')->alias('s')->join('vod v', 'v.vod_id=s.story_vid', 'LEFT')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,story_id,story_addtime,story_cid,story_vid,story_vcid,story_stitle,story_hits,story_continu,story_cont,story_url,story_title,story_page,story_status,story_stars')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$type = array(0 => '连载中', 1 => '完结');
		Cookie('story_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/story/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages, 'list_status' => $type);
		$this->assign($admin);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('liststory', F('_data/liststory'));
		$this->assign($data);
		$this->setNav();
		$this->assign(F('_data/url_html_config'));
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		$rs = model('Story');
		if (IS_POST) {
			$data = $this->request->post();
			$result = $this->validate($data, 'Story');
			if (true !== $result) {
				action_log('add_story', 'Story', 1, session('member_auth.uid'), 0);
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->story_id) {
				action_log('admin/story/add', 'story', $rs->story_id, 1);
				$this->_after_update($rs->story_id, $data);
				$vod_letters = get_vod_info($data['story_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/story/read', array('id' => $rs->story_id, 'pinyin' => $vod_letters, 'cid' => $data['story_cid'], 'dir' => getlistname($data['story_cid'], 'list_dir'), 'jumpurl' => $data['story_jumpurl']), true, true);
				return $this->success('添加剧情成功！' . baidutui($readurl, 'add', 1), url('index'), admin_url('admin/html/story_detail_id', ['ids' => $rs->story_id]));
			} else {
				action_log('admin/story/add', 'story', 0, 0);
				return $this->success('添加剧情失败！', url('index'));
			}
		} else {
			$vid = input('vid/d', '');
			if (!empty($vid)) {
				$info = db('vod')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->where(array('vod_id' => $vid))->find();
			}
			$info['story_vid'] = input('vid/d', '');
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign('liststory', F('_data/liststory'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		$rs = model('Story');
		if (IS_POST) {
			$data = $this->request->post();
			$result = $this->validate($data, 'Story');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data, array('story_id' => $data['story_id']));
			if ($rs->story_id) {
				$this->_after_update($rs->story_id, $data);
				action_log('admin/story/edit', 'story', $rs->story_id, 1);
				$vod_letters = get_vod_info($data['story_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/story/read', array('id' => $rs->story_id, 'pinyin' => $vod_letters, 'cid' => $data['story_cid'], 'dir' => getlistname($data['story_cid'], 'list_dir'), 'jumpurl' => $data['story_jumpurl']), true, true);
				return $this->success('编辑剧情成功！' . baidutui($readurl, 'update', 1), Cookie('story_url_forward'), admin_url('admin/html/story_detail_id', ['ids' => $rs->story_id]));
			} else {
				action_log('admin/story/edit', 'story', 0, 0);
				return $this->success('编辑剧情失败！');
			}
		} else {
			$info = db('story')->alias('s')->field('s.*,vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('vod v', 'v.vod_id=s.story_vid')->join('list l', 'l.list_id=s.story_cid')->find($id);
			if (!$info) {
				action_log('admin/story/edit', 'story', 0, 0);
				return $this->error("ID操作失败！");
			}
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('liststory', F('_data/liststory'));
			$this->assign('listvod', F('_data/listvod'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/story/del', 'story', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$storyarray = db('Story')->alias('s')->join('vod v', 'v.vod_id = s.story_vid', 'LEFT')->field('story_content,story_id,story_cid,story_page,story_vid,vod_letters')->where('story_id', 'in', $id)->select();
		if (!empty($storyarray)) {
			foreach ($storyarray as $val) {
				if ($this->config_url['url_html']) {
					for ($i = 0; $i < $val['story_page']; $i++) {
						@unlink('./' . xianyu_url_html('home/story/read', array('id' => $val['story_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['story_cid'], 'dir' => getlistname($val['story_cid'], 'list_dir'), 'p' => $i + 1)) . '.' . config('url_html_suffix'));
					}
				}
				$this->_after_update($val['story_id'], $val);
				xianyu_del_img_file($val['story_content']);
			}
		}
		$result = db('Story')->where('story_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/story/del', 'story', $id, 1);
			return $this->success('删除剧情成功！', Cookie('story_url_forward'));
		} else {
			action_log('admin/story/del', 'story', $id, 0);
			return $this->error('删除剧情失败！');
		}
	}
	public function _after_update($id = "", $data = "")
	{
		if (empty($data)) {
			$data['story_vid'] = get_story_info($id, 'story_id', 'story_vid');
		}
		if (config('data_cache_story')) {
			Cache::rm('data_cache_story_' . $id);
		}
		if (config('data_cache_vod')) {
			Cache::rm('data_cache_vod_' . $data['story_vid']);
		}
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_story_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['story_cid'], 'list_id_big') . '/' . md5(getlistname($data['story_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['story_cid'] . '/' . md5(getlistname($data['story_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['story_cid'], 'list_id_big') . '/' . md5(getlistname($data['story_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['story_cid'] . '/' . md5(getlistname($data['story_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_actor_read')) {
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/actor/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/story/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/actor/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/story/' . get_small_id($data['story_vid']) . '/' . $data['story_vid'] . '/');
			}
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('story')->where(array('story_id' => $pk))->setField($name, $value);
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/story/pestcid', 'story', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		if (empty($cid)) {
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['story_id'] = array('IN', $id);
			$result = db('story')->where($map)->setField('story_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/story/pestcid', 'story', $id, 1);
			return $this->success('转移分类成功', Cookie('story_url_forward'));
		} else {
			action_log('admin/story/pestcid', 'story', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/story/status', 'story', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['story_id'] = array('IN', $id);
		$result = db('story')->where($map)->setField('story_status', $status);
		if ($result !== false) {
			action_log('admin/story/status', 'story', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('story_url_forward'));
		} else {
			action_log('admin/story/status', 'story', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function stars()
	{
		$where['story_id'] = input('id/d', '');
		$data['story_stars'] = input('stars/d', '');
		$result = db('story')->where($where)->update($data);
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
		$admin['orders'] = 'story_' . $admin['type'] . ' ' . $admin['order'];
		$where['story_vid'] = array('neq', "");
		$limit = 1000;
		$count = db('story')->where($where)->count('story_id');
		$totalpages = ceil($count / 1000);
		$list = db('story')->field('story_id,story_vid')->where($where)->order('story_id')->limit(1000)->page($admin['p'])->select();
		foreach ($list as $key => $value) {
			$story_cid = gettypelistcid($value['story_vid'], 4);
			$result = db('story')->where(array('story_id' => $value['story_id']))->setField('story_cid', $story_cid);
			echo '<p>剧情ID：' . $value['story_id'] . '分类' . $story_cid . ' 更新成功</p>';
			ob_flush();
			flush();
		}
		if ($totalpages > $admin['p']) {
			$url = url('admin/story/transfer', array('p' => $admin['p'] + 1));
			return $this->success('转移成功', $url);
		} else {
			return $this->success('转移完成', 'index');
		}
	}
}