<?php


namespace app\admin\controller;

use think\validate;
use think\Cache;
use app\common\library\Email;
use app\common\controller\Admin;
class Vod extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

		$this->assign($this->config_url);
	}
	public function index()
	{
		$admin['nid'] = input('nid/d', '');
		$admin['tid'] = input('tid/d', '');
		$admin['uid'] = input('uid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['sid'] = input('sid/d', '');
		$admin['usid'] = input('usid/s', '');
		$admin['view'] = input('view/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['continu'] = input('continu/d', '');
		$admin['isfilm'] = input('isfilm/d', '');
		$admin['player'] = input('player', '');
		$admin['url'] = input('url', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['tag'] = urldecode(trim(input('tag', '')));
		$admin['type'] = input('type', 'vod_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['vod_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin["continu"] == 1) {
			$where['vod_continu'] = array('neq', '0');
		}
		if ($admin["continu"] == 2) {
			$where['vod_continu'] = array('eq', '0');
		}
		if ($admin['status'] == 2) {
			$where['vod_status'] = array('eq', 0);
		} elseif ($admin['status'] == 1) {
			$where['vod_status'] = array('eq', 1);
		} elseif ($admin['status'] == 3) {
			$where['vod_status'] = array('eq', -1);
		} elseif ($admin['status'] == 4) {
			$where['vod_status'] = array('neq', 1);
		}
		if ($admin["isfilm"]) {
			$where['vod_isfilm'] = array('eq', $admin["isfilm"]);
		}
		if ($admin['player']) {
			$where['vod_play'] = array('like', '%' . trim($admin['player']) . '%');
		}
		if ($admin['stars']) {
			$where['vod_stars'] = $admin['stars'];
		}
		if ($admin["url"]) {
			$where['vod_url'] = array('eq', '');
		}
		if ($admin['wd']) {
			$where['vod_name|vod_actor|vod_director'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['tag']) {
			$where['tag_sid'] = 1;
			$where['tag_name'] = $admin['tag'];
			$list = db('tag')->alias('t')->join('vod v', 'v.vod_id=t.tag_id', 'LEFT')->join('actor a', 'a.actor_vid=v.vod_id', 'LEFT')->join('story s', 's.story_vid=v.vod_id', 'LEFT')->field('vod_id,vod_cid,vod_play,vod_name,vod_pic,vod_stars,vod_mcid,vod_hits,vod_gold,vod_status,vod_letters,vod_addtime,vod_jumpurl,actor_id,story_id')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		} else {
			$count = db('vod')->where($where)->count('vod_id');
			$list = db('vod')->alias('v')->join('actor a', 'a.actor_vid=v.vod_id', 'LEFT')->join('story s', 's.story_vid=v.vod_id', 'LEFT')->field('vod_id,vod_cid,vod_play,vod_name,vod_pic,vod_stars,vod_mcid,vod_hits,vod_gold,vod_status,vod_letters,vod_addtime,vod_jumpurl,actor_id,story_id')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, $count, ['page' => $admin['p']]);
		}
        // 文章数量统计
        foreach ($list as $key => $value) {
            $news_count = db('Newsrel')->where('newsrel_did',$value['vod_id'])->count('newsrel_nid');
            if($news_count){
                $array_topic['1-' . $value['vod_id']] = $news_count;
            }else{
                $array_topic['1-' . $value['vod_id']] = 0;
            }
        }
		Cookie('vod_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/vod/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');

		$data = array('list' => $list, 'page' => $pages, 'array_count' => $array_topic);
//        halt($data);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign($admin);
		$this->assign($data);
		if ($admin['tid']) {
			return view('special/special_vod');
		} elseif ($admin['nid']) {
			return view('news/news_vod');
		} elseif ($admin['uid'] && $admin['usid'] == "remind") {
			return view('user/remind_vod');
		} elseif ($admin['uid'] && $admin['usid'] == "favorite") {
			return view('user/favorite_vod');
		} elseif ($admin['view']) {
			return view('view');
		} else {
			$this->setNav();
			return view('index', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function add()
	{
		$rs = model('Vod');
		if (IS_POST) {
			$data = $this->_before_insert(input('post.'));
			$result = $this->validate($data, 'Vod');
			if (true !== $result) {
				action_log('admin/vod/add', 'vod', 0, 0, $result);
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->vod_id) {
				if (!empty($data['story_stitle']) || !empty($data['story_keywords']) || !empty($data['story_description']) || !empty($data['story_url']) || !empty($data['story_content'])) {
					$rs->story()->save($data);
				}
				if (!empty($data['actor_title']) || !empty($data['actor_keywords']) || !empty($data['actor_description']) || !empty($data['role'][1]['role_name']) || !empty($data['role'][1]['role_star']) || !empty($data['role'][1]['role_content'])) {
					$rs->actor()->save($data);
				}
				$this->_after_insert($rs->vod_id, $data);
				action_log('admin/vod/add', 'vod', $rs->vod_id, 1);
				$readurl = xianyu_data_url('home/vod/read', array('id' => $rs->vod_id, 'pinyin' => $rs->vod_letters, 'cid' => $data['vod_cid'], 'dir' => getlistname($data['vod_cid'], 'list_dir'), 'jumpurl' => $data['vod_jumpurl']), true, true);
				return $this->success('添加视频成功！' . baidutui($readurl, 'add', 1), url('admin/vod/index'), admin_url('admin/html/vod_detail_id', ['ids' => $rs->vod_id]));
			} else {
				action_log('admin/vod/add', 'vod', 0, 0);
				return $this->error('添加视频失败！');
			}
		} else {
			$info['vod_url'] = array(0 => '');
			$info['vod_play'] = array(0 => '');
			$info['vod_inputer'] = session('member_auth.username');
			$info['vod_language_list'] = explode(',', config("play_language"));
			$info['vod_area_list'] = explode(',', config("play_area"));
			$info['vod_year_list'] = explode(',', config("play_year"));
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listvod', F('_data/listvod'));
			$this->assign('liststory', F('_data/liststory'));
			$this->assign('listrole', F('_data/listrole'));
			$this->assign('listactor', F('_data/listactor'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = $this->_before_insert(input('post.'));
			$result = $this->validate($data, 'Vod');
			if (true !== $result) {
				action_log('admin/vod/edit', 'vod', 0, 0, $result);
				return $this->error($result);
			}
			$result = model('Vod')->save($data, array('vod_id' => $data['vod_id']));
			model('Story')->story_data($data);
			model('Actor')->actor_data($data);
			if ($result !== false) {
				$this->_after_insert($data['vod_id'], $data);
				action_log('admin/vod/edit', 'vod', $data['vod_id'], 1);
				$readurl = xianyu_data_url('home/vod/read', array('id' => $id, 'pinyin' => $data['vod_letters'], 'cid' => $data['vod_cid'], 'dir' => getlistname($data['vod_cid'], 'list_dir'), 'jumpurl' => $data['vod_jumpurl']), true, true);
				$userconfig = F('_data/userconfig_cache');
				if ($userconfig['remindset_auth']) {
					$email = new Email();
					$email->remindset($data);
				}
				return $this->success('编辑视频成功！' . baidutui($readurl, 'update', 1), Cookie('vod_url_forward'), admin_url('admin/html/vod_detail_id', ['ids' => $data['vod_id']]));
			} else {
				action_log('admin/vod/edit', 'vod', 0, 0);
				return $this->error('编辑视频失败！');
			}
		} else {
			$info = db('vod')->alias('v')->join('actor a', 'a.actor_vid=v.vod_id', 'LEFT')->join('story s', 's.story_vid=v.vod_id', 'LEFT')->find($id);
			if (!$info) {
				action_log('admin/vod/edit', 'vod', 0, 0);
				return $this->error("不存在数据！");
			}
			$info['vod_language_list'] = explode(',', config("play_language"));
			$info['vod_area_list'] = explode(',', config("play_area"));
			$info['vod_year_list'] = explode(',', config("play_year"));
			$info['vod_url'] = explode('$$$', $info['vod_url']);
			$info['vod_play'] = explode('$$$', $info['vod_play']);
			$info['vod_mcid_array'] = explode(',', $info['vod_mcid']);
			$info['vod_prty_array'] = model('Prty')->prty_data($id, 1);
			$info['vod_week_array'] = model('Weekday')->weekday_data($id, 1);
			$info['vod_diantai'] = implode(',', model('Vodtv')->tv_data($id, 1));
			$info['vod_reurl'] = implode(',', model('Urls')->urls_data($id));
			if ($info['vod_cid']) {
				$info['vod_cat_list'] = getlistmcat($info['vod_cid']);
			}
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listvod', F('_data/listvod'));
			$this->assign('liststory', F('_data/liststory'));
			$this->assign('listrole', F('_data/listrole'));
			$this->assign('listactor', F('_data/listactor'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function _before_insert($data = "")
	{
		if (!empty($data)) {
			if (empty($data["vod_keywords"]) && config('auto_tag')) {
				$data["vod_keywords"] = xianyu_tag_auto($data["vod_name"], $data["vod_content"]);
			}
			if (!empty($data["vod_keywords"]) && empty($data["vod_mcids"])) {
				$data["vod_mcids"] = explode(',', model('Mcat')->get_mcid($data['vod_cid'], $data["vod_keywords"]));
			}
			$play = $data["vod_play"];
			if (!empty($data["vod_url"])) {
				foreach ($data["vod_url"] as $key => $val) {
					$val = trim($val);
					if ($val) {
						$vod_play[] = $play[$key];
						$vod_url[] = $val;
					}
				}
				$data["vod_play"] = strval(@implode('$$$', $vod_play));
				$data["vod_url"] = strval(@implode('$$$', $vod_url));
			}
			$data['vod_prty'] = empty($data['vod_prty']) ? 0 : implode(',', $data['vod_prty']);
			$data['vod_weekday'] = empty($data['vod_weekday']) ? 0 : implode(',', $data['vod_weekday']);
			$data['vod_mcid'] = empty($data['vod_mcids']) ? 0 : implode(',', $data['vod_mcids']);
			return $data;
		}
	}
	public function _after_insert($id = "", $data = "")
	{
		if (!empty($id) && !empty($data)) {
			if ($data["vod_keywords"]) {
				model('Tag')->tag_update($id, $data["vod_keywords"], 1);
			}
			if ($data["vod_mcids"][0]) {
				model('Mcid')->mcid_update($id, $data["vod_mcids"], 1);
			}
			model('Prty')->prty_update($id, $data["vod_prty"], 1);
			model('Weekday')->weekday_update($id, $data["vod_weekday"], 1);
			model('Vodtv')->tv_update($id, $data["vod_diantai"], 1);
			if ($data['vod_actor']) {
				model('Actors')->actors_update($id, $data['vod_actor'], 1);
			}
			if ($data['vod_director']) {
				model('Actors')->actors_update($id, $data['vod_director'], 2);
			}
			if ($data['vod_reurl']) {
				model('Urls')->urls_update($id, $data['vod_reurl']);
			}
		}
		$this->_after_update($id, $data);
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/vod/del', 'vod', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$this->_after_del_file($id);
		$this->delfile($id);
	}
	public function _after_del_file($id = "")
	{
		$array = db('vod')->field('vod_id,vod_cid,vod_pic,vod_bigpic,vod_letters,vod_content')->where('vod_id', 'in', $id)->select();
		if (config('html_cache_on')) {
			@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
			@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
		}
		foreach ($array as $val) {
			if ($this->config_url['url_html']) {
				@unlink('./' . xianyu_url_html('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
				@unlink('./' . xianyu_url_html('home/vod/filmtime', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
			}
			if (config('data_cache_vod')) {
				Cache::rm('data_cache_vod_' . $val['vod_id']);
			}
			if (config('html_cache_on')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val['vod_cid'], 'list_id_big') . '/' . md5(getlistname($val['vod_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $val['vod_cid'] . '/' . md5(getlistname($val['vod_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/actor/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
				deldir(RUNTIME_PATH . 'html/pc/story/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val['vod_cid'], 'list_id_big') . '/' . md5(getlistname($val['vod_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val['vod_cid'] . '/' . md5(getlistname($val['vod_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/actor/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
				deldir(RUNTIME_PATH . 'html/mobile/story/' . get_small_id($val['vod_id']) . '/' . $val['vod_id'] . '/');
			}
			@unlink('./' . config('upload_path') . '/' . $val['vod_pic']);
			@unlink('./' . config('upload_path') . '/' . $val['vod_bigpic']);
			xianyu_del_img_file($val['vod_content']);
		}
		Cache::clear('foreach_vod');
	}
	public function _after_update($id = "", $data = "")
	{
		if (config('data_cache_vod')) {
			Cache::rm('data_cache_vod_' . $id);
		}
		Cache::clear('foreach_vod');
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_vod_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['vod_cid'], 'list_id_big') . '/' . md5(getlistname($data['vod_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['vod_cid'] . '/' . md5(getlistname($data['vod_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['vod_cid'], 'list_id_big') . '/' . md5(getlistname($data['vod_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['vod_cid'] . '/' . md5(getlistname($data['vod_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_vod_read')) {
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($id) . '/' . $id . '/');
			}
		}
	}
	public function delfile($id)
	{
		$rolearray = db('role')->alias('r')->join('vod v', 'v.vod_id = r.role_vid', 'LEFT')->field('role_id,role_cid,role_pic,role_content,vod_letters')->where('role_vid', 'in', $id)->select();
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
			db('Role')->where('role_vid', 'in', $id)->delete();
			Cache::clear('foreach_role');
		}
		$storyarray = db('Story')->alias('s')->join('vod v', 'v.vod_id = s.story_vid', 'LEFT')->field('story_id,story_cid,story_page,story_content,vod_letters')->where('story_vid', 'in', $id)->select();
		if (!empty($storyarray)) {
			foreach ($storyarray as $val) {
				if ($this->config_url['url_html']) {
					for ($i = 0; $i < $val['story_page']; $i++) {
						@unlink('./' . xianyu_url_html('home/story/read', array('id' => $val['story_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['story_cid'], 'dir' => getlistname($val['story_cid'], 'list_dir'), 'p' => $i + 1)) . '.' . config('url_html_suffix'));
					}
				}
				if (config('data_cache_role')) {
					Cache::rm('data_cache_story_' . $val['story_id']);
				}
				if (config('html_cache_on') && config('html_cache_story_list')) {
					@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val['story_cid'], 'list_id_big') . '/' . md5(getlistname($val['story_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/pc/show/' . $val['story_cid'] . '/' . md5(getlistname($val['story_cid'], 'list_url')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val['story_cid'], 'list_id_big') . '/' . md5(getlistname($val['story_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val['story_cid'] . '/' . md5(getlistname($val['story_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				}
				xianyu_del_img_file($val['story_content']);
			}
			db('Story')->where('story_vid', 'in', $id)->delete();
			Cache::clear('foreach_story');
		}
		$actorarray = db('Actor')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'LEFT')->field('actor_id,actor_cid,vod_letters')->where('actor_vid', 'in', $id)->select();
		if (!empty($actorarray)) {
			foreach ($actorarray as $val) {
				if ($this->config_url['url_html']) {
					@unlink('./' . xianyu_url_html('home/actor/read', array('id' => $val['actor_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['actor_cid'], 'dir' => getlistname($val['actor_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
				}
				if (config('data_cache_actor')) {
					Cache::rm('data_cache_actor_' . $val['actor_id']);
				}
				if (config('html_cache_on') && config('html_cache_actor_list')) {
					@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val['actor_cid'], 'list_id_big') . '/' . md5(getlistname($val['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/pc/show/' . $val['actor_cid'] . '/' . md5(getlistname($val['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val['actor_cid'], 'list_id_big') . '/' . md5(getlistname($val['actor_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val['actor_cid'] . '/' . md5(getlistname($val['actor_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				}
				xianyu_del_img_file($val['story_content']);
			}
			db('Actor')->where('actor_vid', 'in', $id)->delete();
			Cache::clear('foreach_actor');
		}
		db('Mcid')->where('mcid_id', 'in', $id, 'and', 'mcid_sid', 1)->delete();
		db('Actors')->where('actors_id', 'in', $id)->delete();
		db('Urls')->where('urls_id', 'in', $id)->delete();
		db('Vodtv')->where('vodtv_id', 'in', $id)->delete();
		db('Weekday')->where('weekday_id', 'in', $id, 'and', 'weekday_sid', 1)->delete();
		db('Prty')->where('prty_id', 'in', $id, 'and', 'prty_sid', 1)->delete();
		db('Topic')->where('topic_did', 'in', $id, 'and', 'topic_sid', 1)->delete();
		db('Newsrel')->where('newsrel_did', 'in', $id, 'and', 'newsrel_sid', 1)->delete();
		db('Tag')->where('tag_id', 'in', $id, 'and', 'tag_sid', 1)->delete();
		db('playlog')->where('log_vid', 'in', $id)->delete();
		db('remind')->where('remind_vid', 'in', $id)->delete();
		db('favorite')->where('favorite_vid', 'in', $id)->delete();
		$result = db('vod')->where('vod_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/vod/del', 'vod', $id, 1);
			return $this->success('删除视频成功！', Cookie('vod_url_forward'));
		} else {
			action_log('admin/vod/del', 'vod', $id, 0);
			return $this->error('删除视频失败！');
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
			$actorid = db('actor')->where(array('actor_vid' => $data['role_vid']))->value('actor_id');
			if (empty($actorid)) {
				$actor = model('actor');
				$actordata['actor_vid'] = $data['role_vid'];
				$actordata['actor_cid'] = gettypelistcid($data['role_vid'], 6);
				$actor->save($actordata);
			}
			$role = model('role');
			$role->save($data);
			if ($role->role_id) {
				$vod_letters = get_vod_info($data['role_vid'], 'vod_id', 'vod_letters');
				$readurl = xianyu_data_url('home/role/read', array('id' => $role->role_id, 'pinyin' => $vod_letters, 'cid' => $data['role_cid'], 'dir' => getlistname($data['role_cid'], 'list_dir'), 'jumpurl' => $data['role_jumpurl']), true, true);
				action_log('admin/vod/addrole', 'vod', $role->role_id, 1);
				return $this->success('添加角色成功！' . baidutui($readurl, 'add', 1), Cookie('vod_url_forward'));
			} else {
				action_log('admin/vod/addrole', 'vod', 0, 0);
				return $this->success('添加角色失败！');
			}
		} else {
			$vid = input('vid/d', '');
			if (!empty($vid)) {
				$info = db('vod')->field('vod_id,vod_cid,vod_name,vod_pic,vod_letters,vod_addtime,vod_jumpurl')->where(array('vod_id' => $vid))->find();
			}
			$info['role_oid'] = db('role')->where('role_vid', $vid)->max('role_oid') + 1;
			$info['role_vid'] = $vid;
			$data = array('info' => $info);
			$this->assign('listrole', F('_data/listrole'));
			$this->assign($data);
			return view('actor/role');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('vod')->where(array('vod_id' => $pk))->setField($name, $value);
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/vod/pestcid', 'vod', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		if (empty($cid)) {
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['vod_id'] = array('IN', $id);
			$result = db('vod')->where($map)->setField('vod_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/vod/pestcid', 'vod', $id, 1);
			return $this->success('转移分类成功', Cookie('vod_url_forward'));
		} else {
			action_log('admin/vod/pestcid', 'vod', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/vod/status', 'vod', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$message = !$status ? '隐藏' : '显示';
		$map['vod_id'] = array('IN', $id);
		$result = db('vod')->where($map)->setField('vod_status', $status);
		db('Actor')->where('actor_vid', 'in', $id)->setField('actor_status', $status);
		db('Role')->where('role_vid', 'in', $id)->setField('role_status', $status);
		db('Story')->where('story_vid', 'in', $id)->setField('story_status', $status);
		if ($result !== false) {
			action_log('admin/vod/status', 'vod', $id, 1);
			return $this->success('设置' . $message . '状态成功！', Cookie('vod_url_forward'));
		} else {
			action_log('admin/vod/status', 'vod', $id, 0);
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function stars()
	{
		$where['vod_id'] = input('id/d', '');
		$data['vod_stars'] = input('stars/d', '');
		$result = db('vod')->where($where)->update($data);
		if ($result) {
			return $this->success('设置星级成功');
		} else {
			return $this->error('设置星级失败');
		}
	}
	public function ajaxcat()
	{
		$list_id = intval(input('id/d', ''));
		$cat_data = getlistmcat($list_id);
		$this->assign('cat_list', $cat_data);
		return view('vod_ajaxcat');
	}
	public function ajaxmcid()
	{
		$id = intval(input('id/d', ''));
		$voddata = db('vod')->field('vod_id,vod_cid,vod_mcid')->where(array('vod_id' => $id))->find();
		$data['listmcid'] = getlistmcat($voddata['vod_cid']);
		$data['voddata'] = $voddata;
		$data['mcids'] = explode(',', $voddata['vod_mcid']);
		$this->assign('data', $data);
		return view('vod_ajaxmcid');
	}
	public function mcid_post()
	{
		$data = input('post.');
		$vod_id = intval($data['vod_id']);
		if (isset($data['vod_mcids'])) {
			$mcids = $data['vod_mcids'];
			$mcid = implode(',', $data['vod_mcids']);
		} else {
			$mcids = '';
			$mcid = '';
		}
		db('vod')->where(array('vod_id' => $vod_id))->setField('vod_mcid', $mcid);
		model('Mcid')->mcid_update($vod_id, $data['vod_mcids'], 1);
		return $this->success('编辑类型成功', Cookie('vod_url_forward'));
	}
	public function ajax()
	{
		$data = array();
		$where = array();
		$did = intval(input('did/d', ''));
		$nid = intval(input('nid/d', ''));
		$sid = intval(input('sid/d', ''));
		$type = trim(input('type', ''));
		$lastdid = intval(input('lastdid'));
		if ($sid == 1) {
			$name = get_vod_info($did, 'vod_id', 'vod_name');
		}
		if ($sid == 3) {
			$name = get_star_info($did, 'star_id', 'star_name');
		}
		if ($did && $nid) {
			$rs = db('Newsrel');
			if ($type == 'add') {
				$rsid = $rs->where(["newsrel_sid" => $sid, 'newsrel_did' => $did, 'newsrel_nid' => $nid])->value('newsrel_did');
				if (!$rsid) {
					$count = $rs->where(["newsrel_sid" => $sid, 'newsrel_nid' => $nid])->max('newsrel_oid');
					$data['newsrel_did'] = $did;
					$data['newsrel_nid'] = $nid;
					$data['newsrel_sid'] = $sid;
					$data['newsrel_oid'] = $count + 1;
					$rs->insert($data);
				}
			} elseif ($type == 'del') {
				$where['newsrel_did'] = $did;
				$where['newsrel_nid'] = $nid;
				$where['newsrel_sid'] = $sid;
				$rs->where($where)->delete();
			} elseif ($type == 'up') {
				$where['newsrel_did'] = $did;
				$where['newsrel_nid'] = $nid;
				$where['newsrel_sid'] = $sid;
				$rs->where($where)->setInc('newsrel_oid');
				$where['newsrel_did'] = $lastdid;
				$rs->where($where)->setDec('newsrel_oid');
			} elseif ($type == 'down') {
				$where['newsrel_did'] = $did;
				$where['newsrel_nid'] = $nid;
				$where['newsrel_sid'] = $sid;
				$rs->where($where)->setDec('newsrel_oid');
				$where['newsrel_did'] = $lastdid;
				$rs->where($where)->setInc('newsrel_oid');
			}
		}
		if ($did && $sid == 1) {
			return $this->shownews($did, $nid);
		} else {
			return '请先添加视频！';
		}
	}
	public function shownews($did, $nid)
	{
		$where = array();
		$where['newsrel_sid'] = 1;
		$where['newsrel_did'] = $did;
		$rs = db('Newsrel');
		$maxoid = $rs->where($where)->max('newsrel_oid');
		$minoid = $rs->where($where)->min('newsrel_oid');
		$list = $rs->alias('ns')->field('newsrel_oid,news_id,news_name,news_cid,news_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('news n', 'n.news_id=ns.newsrel_nid', 'LEFT')->join('list l', 'l.list_id=n.news_cid', 'LEFT')->where($where)->order('newsrel_oid desc')->select();
		$this->assign('did', $did);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_news', $list);
		$this->assign('count', count($list));
		return view('vod_news_ids');
	}
}