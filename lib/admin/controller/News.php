<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class News extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['tid'] = input('tid/d', '');
		$admin['mid'] = input('mid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['p'] = input('p/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['tag'] = urldecode(trim(input('tag', '')));
		$admin['type'] = input('type', 'news_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['news_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['wd']) {
			$where['news_name|news_remark'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['stars']) {
			$where['news_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['status'] == 1) {
			$where['news_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['news_status'] = array('eq', 0);
		}
		if ($admin['tag']) {
			$where['tag_sid'] = 2;
			$where['tag_name'] = $admin['tag'];
			$list = db('tag')->alias('t')->join('news n', 'n.news_id=t.tag_id', 'RIGHT')->field('news_id,news_cid,news_name,news_pic,news_hits,news_addtime,news_stars,news_status,news_jumpurl')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		} else {
			$list = db('news')->field('news_id,news_cid,news_name,news_pic,news_hits,news_addtime,news_stars,news_status,news_jumpurl')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		}
		$list_topic = db('Newsrel')->select();
		foreach ($list_topic as $key => $value) {
			$array_topic[$value['newsrel_sid'] . '-' . $value['newsrel_nid']][$key] = $value['newsrel_nid'];
		}
		Cookie('news_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/news/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign('array_count', $array_topic);
		$this->assign($data);
		if ($admin['tid']) {
			return view('special/special_news');
		} elseif ($admin['vid']) {
			return view('vod/vod_news');
		} elseif ($admin['mid']) {
			return view('star/star_news');
		}
		$this->setNav();
		$this->assign(F('_data/url_html_config'));
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		$news = model('News');
		$newsrel = model('Newsrel');
		$tag = model('Tag');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'News');
			if (true !== $result) {
				return $this->error($result);
			}
			if (empty($data["news_keywords"]) && config('auto_tag')) {
				$data["news_keywords"] = xianyu_tag_auto($data["news_name"], $data["news_content"]);
			}
			$news->save($data);
			if ($news->news_id) {
				$this->_after_insert($news->news_id, $data);
				action_log('admin/news/add', 'news', $news->news_id, 1);
				$readurl = xianyu_data_url('home/news/read', array('id' => $news->news_id, 'cid' => $news->news_cid, 'dir' => getlistname($news->news_cid, 'list_dir'), 'jumpurl' => $data['news_jumpurl']), true, true);
				return $this->success('添加新闻成功！' . baidutui($readurl, 'add', 1), url('admin/news/index'), admin_url('admin/html/news_detail_id', ['ids' => $news->news_id]));
			} else {
				action_log('admin/news/add', 'news', 0, 0);
				return $this->success('添加新闻失败！');
			}
		} else {
			$info['news_inputer'] = session('member_auth.username');
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$rs = model('News');
			$data = input('post.');
			$result = $this->validate($data, 'News');
			if (true !== $result) {
				return $this->error($result);
			}
			if (empty($data["news_keywords"]) && config('auto_tag')) {
				$data["news_keywords"] = xianyu_tag_auto($data["news_name"], $data["news_content"]);
			}
			$rs->save($data, array('news_id' => $data['news_id']));
			if ($rs->news_id) {
				$this->_after_insert($data['news_id'], $data);
				action_log('admin/news/edit', 'news', $rs->news_id, 1);
				$readurl = xianyu_data_url('home/news/read', array('id' => $rs->news_id, 'cid' => $rs->news_cid, 'dir' => getlistname($rs->news_cid, 'list_dir'), 'jumpurl' => $data['news_jumpurl']), true, true);
				return $this->success('编辑新闻成功！' . baidutui($readurl, 'update', 1), url('admin/news/index'), admin_url('admin/html/news_detail_id', ['ids' => $data['news_id']]));
			} else {
				action_log('admin/news/edit', 'news', 0, 0);
				return $this->success('编辑新闻失败！');
			}
		} else {
			$info = db('news')->find($id);
			if (!$info) {
				action_log('admin/news/edit', 'news', $id, 0);
				return $this->error("ID操作失败！");
			}
			$info['news_prty_array'] = model('Prty')->prty_data($id, 2);
			$where['newsrel_sid'] = array('neq', 0);
			$where['newsrel_nid'] = array('eq', $info['news_id']);
			$newsarray = db('newsrel')->where($where)->select();
			foreach ($newsarray as $key => $value) {
				$array_newsrel[$value['newsrel_sid']][$key] = $value;
			}
			$info['countvod'] = count($array_newsrel[1]);
			$info['countstar'] = count($array_newsrel[3]);
			if ($array_newsrel[3]) {
				foreach ($array_newsrel[3] as $key => $value) {
					$star[$key] = $value['newsrel_name'] . "#" . $value['newsrel_did'];
				}
				$info['news_star'] = implode(',', $star);
			}
			if ($array_newsrel[1]) {
				foreach ($array_newsrel[1] as $key => $value) {
					$vod[$key] = $value['newsrel_name'] . "#" . $value['newsrel_did'];
				}
				$info['news_vod'] = implode(',', $vod);
			}
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info, 'list' => $list, 'newsarray' => $newsarray);
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function _after_insert($id = "", $data = "")
	{
		if (!empty($id) && !empty($data)) {
			if ($data['news_vod']) {
				model('Newsrel')->newsrel_update($id, $data['news_vod'], 1);
			}
			if ($data['news_star']) {
				model('Newsrel')->newsrel_update($id, $data['news_star'], 3);
			}
			if ($data["news_keywords"]) {
				model('Tag')->tag_update($id, $data["news_keywords"], 2);
			}
			if ($data["news_prty"]) {
				model('Prty')->prty_update($id, $data["news_prty"], 2);
			}
		}
		$this->_after_update($id, $data);
	}
	public function _after_update($id = "", $data = "")
	{
		if (config('data_cache_news')) {
			Cache::rm('data_cache_news_' . $id);
		}
		Cache::clear('foreach_news');
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_news_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['news_cid'], 'list_id_big') . '/' . md5(getlistname($data['news_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['news_cid'] . '/' . md5(getlistname($data['news_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['news_cid'], 'list_id_big') . '/' . md5(getlistname($data['news_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['news_cid'] . '/' . md5(getlistname($data['news_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_news_read')) {
				deldir(RUNTIME_PATH . 'html/pc/news/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/news/' . get_small_id($id) . '/' . $id . '/');
			}
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/news/del', 'news', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$this->_after_del_file($id);
		$this->delfile($id);
	}
	public function _after_del_file($id = "")
	{
		$array = db('news')->field('news_id,news_cid,news_pic,news_content')->where('news_id', 'in', $id)->select();
		foreach ($array as $val) {
			if ($this->config_url['url_html']) {
				@unlink('./' . xianyu_url_html('home/news/read', array('id' => $val['news_id'], 'cid' => $val['news_cid'], 'dir' => getlistname($val['news_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
			}
			$this->_after_update($val['news_id'], $val);
			@unlink('./' . config('upload_path') . '/' . $val['news_pic']);
			xianyu_del_img_file($val['news_content']);
		}
	}
	public function delfile($id)
	{
		db('Prty')->where('prty_id', 'in', $id, 'and', 'prty_sid', 2)->delete();
		db('Topic')->where('topic_did', 'in', $id, 'and', 'topic_sid', 2)->delete();
		db('Newsrel')->where('newsrel_nid', 'in', $id)->delete();
		db('Tag')->where('tag_id', 'in', $id, 'and', 'tag_sid', 2)->delete();
		$result = db('News')->where('news_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/news/del', 'news', $id, 1);
			return $this->success('删除新闻成功！');
		} else {
			action_log('admin/news/del', 'news', $id, 0);
			return $this->error('删除新闻失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/news/status', 'news', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['news_id'] = array('IN', $id);
		$result = db('News')->where($map)->setField('news_status', $status);
		if ($result !== false) {
			action_log('admin/news/status', 'news', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('news_url_forward'));
		} else {
			action_log('admin/news/status', 'news', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/news/pestcid', 'news', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		if (empty($cid)) {
			action_log('admin/news/pestcid', 'news', 0, 0);
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['news_id'] = array('IN', $id);
			$result = db('News')->where($map)->setField('news_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/news/pestcid', 'news', $id, 1);
			return $this->success('转移分类成功', Cookie('news_url_forward'));
		} else {
			action_log('admin/news/pestcid', 'news', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('news')->where(array('news_id' => $pk))->setField($name, $value);
		}
	}
	public function stars()
	{
		$where['news_id'] = input('id/d', '');
		$data['news_stars'] = input('stars/d', '');
		$result = db('News')->where($where)->update($data);
		if ($result) {
			return $this->success('设置星级成功');
		} else {
			return $this->error('设置星级失败');
		}
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
					$data['newsrel_name'] = $name;
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
		if ($nid && $sid == 1) {
			return $this->showvod($did, $nid);
		} elseif ($nid && $sid == 3) {
			return $this->showstar($did, $nid);
		} else {
			return '请先添加明星！';
		}
	}
	public function showstar($did, $nid)
	{
		$where = array();
		$where['newsrel_sid'] = 3;
		$where['newsrel_nid'] = $nid;
		$rs = db('Newsrel');
		$maxoid = $rs->where($where)->max('newsrel_oid');
		$minoid = $rs->where($where)->min('newsrel_oid');
		$list = $rs->alias('ns')->field('newsrel_oid,star_id,star_name,star_cid,star_letters,star_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('star s', 's.star_id=ns.newsrel_did', 'LEFT')->join('list l', 'l.list_id=s.star_cid', 'LEFT')->where($where)->order('newsrel_oid desc')->select();
		$this->assign('nid', $nid);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_star', $list);
		$this->assign('count', count($list));
		return view('news_star_ids');
	}
	public function showvod($did, $nid)
	{
		$where = array();
		$where['newsrel_sid'] = 1;
		$where['newsrel_nid'] = $nid;
		$rs = db('Newsrel');
		$maxoid = $rs->where($where)->max('newsrel_oid');
		$minoid = $rs->where($where)->min('newsrel_oid');
		$list = $rs->alias('ns')->field('newsrel_oid,vod_id,vod_name,vod_cid,vod_letters,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('Vod v', 'v.vod_id=ns.newsrel_did', 'LEFT')->join('list l', 'l.list_id=v.vod_cid', 'LEFT')->where($where)->order('newsrel_oid desc')->select();
		$this->assign('nid', $nid);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_vod', $list);
		$this->assign('count', count($list));
		return view('news_vod_ids');
	}
}