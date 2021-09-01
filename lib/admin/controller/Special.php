<?php


namespace app\admin\controller;

use think\validate;
use think\Cache;
use app\common\controller\Admin;
class Special extends Admin
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
		$admin['type'] = input('type', 'special_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['special_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['stars']) {
			$where['special_stars'] = array('eq', $admin['stars']);
		}
		if ($admin['wd']) {
			$where['special_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['status'] == 1) {
			$where['special_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['special_status'] = array('eq', 0);
		}
		$list = db('special')->where($where)->field('special_content,special_description,special_keywords', true)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$list_topic = db('Topic')->select();
		foreach ($list_topic as $key => $value) {
			$array_topic[$value['topic_sid'] . '-' . $value['topic_tid']][$key] = $value['topic_tid'];
		}
		Cookie('special_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/special/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign('listspecial', F('_data/listspecial'));
		$this->assign('array_count', $array_topic);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		$this->assign(F('_data/url_html_config'));
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('special')->where(array('special_id' => $pk))->setField($name, $value);
		}
	}
	public function add()
	{
		if (IS_POST) {
			$rs = model('Special');
			$data = input('post.');
			$result = $this->validate($data, 'Special');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->special_id) {
				$this->_after_insert($rs->special_id, $data);
				$readurl = xianyu_data_url('home/special/read', array('id' => $rs->special_id, 'pinyin' => $rs->special_letters, 'cid' => $rs->special_cid, 'dir' => getlistname($rs->special_cid, 'list_dir'), 'jumpurl' => $data['special_jumpurl']), true, true);
				action_log('admin/special/add', 'special', $rs->special_id, 1);
				return $this->success('添加专题成功！' . baidutui($readurl, 'add', 1), url('admin/special/index'), admin_url('admin/html/special_detail_id', ['ids' => $rs->special_id]));
			} else {
				action_log('admin/special/add', 'special', 0, 0);
				return $this->success('添加专题失败！', url('admin/special/index'));
			}
		} else {
			$info = array();
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign('listspecial', F('_data/listspecial'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Special');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = model('Special')->save($data, array('special_id' => $data['special_id']));
			if ($result !== false) {
				$this->_after_insert($data['special_id'], $data);
				action_log('admin/special/edit', 'special', $data['special_id'], 1);
				$readurl = xianyu_data_url('home/special/read', array('id' => $data['special_id'], 'pinyin' => $data['special_letters'], 'cid' => $data['special_cid'], 'dir' => getlistname($data['special_cid']), 'jumpurl' => $data['special_jumpurl']), true, true);
				return $this->success('编辑专题成功！' . baidutui($readurl, 'update', 1), url('admin/special/index'), admin_url('admin/html/special_detail_id', ['ids' => $data['special_id']]));
			} else {
				action_log('admin/special/edit', 'special', 0, 0);
				return $this->success('编辑专题失败！', url('admin/special/index'));
			}
		} else {
			$info = db('special')->find($id);
			if (!$info) {
				action_log('admin/special/edit', 'special', 0, 0);
				return $this->error("ID操作失败！");
			}
			unset($where);
			$rs = db('Topic');
			$where['topic_tid'] = $id;
			$where['topic_sid'] = 1;
			$info['countvod'] = $rs->where($where)->count();
			$where['topic_sid'] = 2;
			$info['countnews'] = $rs->where($where)->count();
			$where['topic_sid'] = 3;
			$info['countstar'] = $rs->where($where)->count();
			$info['special_prty_array'] = model('Prty')->prty_data($id, 10);
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('listspecial', F('_data/listspecial'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function _after_insert($id = "", $data = "")
	{
		if (!empty($id) && !empty($data)) {
			if ($data["special_prty"]) {
				model('Prty')->prty_update($id, $data["special_prty"], 10);
			}
		}
		$this->_after_update($id, $data);
	}
	public function _after_update($id = "", $data = "")
	{
		if (config('data_cache_special')) {
			Cache::rm('data_cache_special_' . $id);
		}
		Cache::clear('foreach_special');
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_special_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['special_cid'], 'list_id_big') . '/' . md5(getlistname($data['special_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['special_cid'] . '/' . md5(getlistname($data['special_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['special_cid'], 'list_id_big') . '/' . md5(getlistname($data['special_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['special_cid'] . '/' . md5(getlistname($data['special_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_special_read')) {
				deldir(RUNTIME_PATH . 'html/pc/news/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/news/' . get_small_id($id) . '/' . $id . '/');
			}
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/special/del', 'special', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$this->_after_del_file($id);
		$this->delfile($id);
	}
	public function _after_del_file($id = "")
	{
		$array = db('special')->field('special_id,special_cid,special_letters,special_logo,special_banner,special_content')->where('special_id', 'in', $id)->select();
		foreach ($array as $val) {
			if ($this->config_url['url_html']) {
				@unlink('./' . xianyu_url_html('home/special/read', array('id' => $val['special_id'], 'pinyin' => $val['special_letters'], 'cid' => $val['special_cid'], 'dir' => getlistname($val['special_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
			}
			$this->_after_update($val['special_id'], $val);
			@unlink('./' . config('upload_path') . '/' . $val['special_logo']);
			@unlink('./' . config('upload_path') . '/' . $val['special_content']);
			xianyu_del_img_file($val['special_content']);
		}
	}
	public function delfile($id)
	{
		db('Prty')->where('prty_id', 'in', $id, 'and', 'prty_sid', 10)->delete();
		db('Topic')->where('topic_tid', 'in', $id)->delete();
		$result = db('Special')->where('special_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/special/del', 'special', $id, 1);
			return $this->success('删除专题成功！', Cookie('special_url_forward'));
		} else {
			action_log('admin/special/del', 'special', $id, 0);
			return $this->error('删除专题失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/special/status', 'special', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['special_id'] = array('IN', $id);
		$result = db('Special')->where($map)->setField('special_status', $status);
		if ($result !== false) {
			action_log('admin/special/status', 'special', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('special_url_forward'));
		} else {
			action_log('admin/special/status', 'special', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/special/pestcid', 'special', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		if (empty($cid)) {
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['special_id'] = array('IN', $id);
			$result = db('Special')->where($map)->setField('special_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/special/pestcid', 'special', $id, 1);
			return $this->success('转移分类成功', Cookie('special_url_forward'));
		} else {
			action_log('admin/special/pestcid', 'special', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function stars()
	{
		$where['special_id'] = input('id/d', '');
		$data['special_stars'] = input('stars/d', '');
		$result = db('Special')->where($where)->update($data);
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
		$tid = intval(input('tid/d', ''));
		$sid = intval(input('sid/d', ''));
		$type = trim(input('type', ''));
		$lastdid = intval(input('lastdid'));
		if ($did && $tid) {
			$rs = db('Topic');
			if ($type == 'add') {
				$rsid = $rs->where(["topic_sid" => $sid, 'topic_did' => $did, 'topic_tid' => $tid])->value('topic_did');
				if (!$rsid) {
					$count = $rs->where(["topic_sid" => $sid, 'topic_tid' => $tid])->max('topic_oid');
					$data['topic_did'] = $did;
					$data['topic_tid'] = $tid;
					$data['topic_sid'] = $sid;
					$data['topic_oid'] = $count + 1;
					$rs->insert($data);
				}
			} elseif ($type == 'del') {
				$where['topic_did'] = $did;
				$where['topic_tid'] = $tid;
				$where['topic_sid'] = $sid;
				$rs->where($where)->delete();
			} elseif ($type == 'up') {
				$where['topic_did'] = $did;
				$where['topic_tid'] = $tid;
				$where['topic_sid'] = $sid;
				$rs->where($where)->setInc('topic_oid');
				$where['topic_did'] = $lastdid;
				$rs->where($where)->setDec('topic_oid');
			} elseif ($type == 'down') {
				$where['topic_did'] = $did;
				$where['topic_tid'] = $tid;
				$where['topic_sid'] = $sid;
				$rs->where($where)->setDec('topic_oid');
				$where['topic_did'] = $lastdid;
				$rs->where($where)->setInc('topic_oid');
			}
		}
		if ($tid && $sid == 1) {
			return $this->showvod($did, $tid);
		} elseif ($tid && $sid == 2) {
			return $this->shownews($did, $tid);
		} elseif ($tid && $sid == 3) {
			return $this->showstar($did, $tid);
		} else {
			return '请先创建专辑！';
		}
	}
	public function showstar($did, $tid)
	{
		$where = array();
		$where['topic_sid'] = 3;
		$where['topic_tid'] = $tid;
		$rs = db('Topic');
		$maxoid = $rs->where($where)->max('topic_oid');
		$minoid = $rs->where($where)->min('topic_oid');
		$list = $rs->alias('t')->field('topic_oid,star_id,star_name,star_cid,star_letters,star_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('star s', 's.star_id=t.topic_did', 'LEFT')->join('list l', 'l.list_id=s.star_cid', 'LEFT')->where($where)->order('topic_oid desc')->select();
		$this->assign('tid', $tid);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_star', $list);
		$this->assign('count', count($list));
		return view('special_star_ids');
	}
	public function shownews($did, $tid)
	{
		$where = array();
		$where['topic_sid'] = 2;
		$where['topic_tid'] = $tid;
		$rs = db('topic');
		$maxoid = $rs->where($where)->max('topic_oid');
		$minoid = $rs->where($where)->min('topic_oid');
		$list = $rs->alias('t')->field('topic_oid,news_id,news_name,news_cid,news_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('news n', 'n.news_id=t.topic_did', 'LEFT')->join('list l', 'l.list_id=n.news_cid', 'LEFT')->where($where)->order('topic_oid desc')->select();
		$this->assign('tid', $tid);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_news', $list);
		$this->assign('count', count($list));
		return view('special_news_ids');
	}
	public function showvod($did, $tid)
	{
		$where = array();
		$where['topic_sid'] = 1;
		$where['topic_tid'] = $tid;
		$rs = db('topic');
		$maxoid = $rs->where($where)->max('topic_oid');
		$minoid = $rs->where($where)->min('topic_oid');
		$list = $rs->alias('t')->field('topic_oid,vod_id,vod_name,vod_cid,vod_letters,vod_jumpurl,list_id,list_name,list_dir,list_pid,list_jumpurl')->join('vod v', 'v.vod_id=t.topic_did', 'LEFT')->join('list l', 'l.list_id=v.vod_cid', 'LEFT')->where($where)->order('topic_oid desc')->select();
		$this->assign('tid', $tid);
		$this->assign('max_oid', $maxoid);
		$this->assign('min_oid', $minoid);
		$this->assign('list_vod', $list);
		$this->assign('count', count($list));
		return view('special_vod_ids');
	}
}