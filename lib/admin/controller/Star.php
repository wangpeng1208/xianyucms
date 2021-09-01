<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use think\Cache;
use app\common\controller\Admin;
class Star extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['nid'] = input('nid/d', '');
		$admin['tid'] = input('tid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['sid'] = input('sid/d', '');
		$admin['view'] = input('view/d', '');
		$admin['stars'] = input('stars/d', '');
		$admin['status'] = input('status/d', '');
		$admin['pic'] = input('pic/d', '');
		$admin['content'] = input('content/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['xb'] = urldecode(trim(input('xb', '')));
		$admin['tag'] = urldecode(trim(input('tag', '')));
		$admin['type'] = input('type', 'star_' . config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['cid']) {
			$where['star_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['wd']) {
			$where['star_name'] = array('like', '%' . $admin['wd'] . '%');
		}
		if ($admin['xb']) {
			$where['star_xb'] = array('eq', $admin['xb']);
		}
		if ($admin['stars']) {
			$where['star_stars'] = array('eq', $admin['stars']);
		}
		if ($admin["pic"] == 1) {
			$where['star_pic'] = array('eq', "");
		}
		if ($admin["pic"] == 2) {
			$where['star_pic'] = array('neq', "");
		}
		if ($admin["content"] == 1) {
			$where['star_content'] = array('eq', "");
		}
		if ($admin["content"] == 2) {
			$where['star_content'] = array('neq', "");
		}
		if ($admin['status'] == 1) {
			$where['star_status'] = array('eq', $admin['status']);
		}
		if ($admin['status'] == 2) {
			$where['star_status'] = array('eq', 0);
		}
		if ($admin['tag']) {
			$where['tag_sid'] = 3;
			$where['tag_name'] = $admin['tag'];
			$list = db('tag')->field('star_content,star_info,star_description,star_keywords', true)->alias('t')->join('star s', 's.star_id=t.tag_id', 'LEFT')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		} else {
			$list = db('star')->field('star_content,star_info,star_description,star_keywords', true)->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		}
        // 文章数量统计
        foreach ($list as $key => $value) {
            $news_count = db('Newsrel')->where('newsrel_did',$value['star_id'])->count('newsrel_nid');
            if($news_count){
                $array_topic['3-' . $value['star_id']] = $news_count;
            }else{
                $array_topic['3-' . $value['star_id']] = 0;
            }
        }
		Cookie('star_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/star/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign('liststar', F('_data/liststar'));
		$this->assign('array_count', $array_topic);
		$this->assign($data);
		if ($admin['nid']) {
			$this->assign('nid', $admin['nid']);
			return view('news/news_star');
		} elseif ($admin['tid']) {
			$this->assign('tid', $admin['tid']);
			return view('special/special_star');
		} elseif ($admin['view']) {
			return view('view');
		} else {
			$this->setNav();
			$this->assign(F('_data/url_html_config'));
			return view('index', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function add()
	{
		$rs = model('star');
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Star');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data);
			if ($rs->star_id) {
				$this->_after_insert($rs->star_id, $data);
				action_log('admin/star/add', 'star', $rs->star_id, 1);
				$readurl = xianyu_data_url('home/star/read', array('id' => $rs->star_id, 'pinyin' => $rs->star_letters, 'cid' => $rs->star_cid, 'dir' => getlistname($rs->star_cid, 'list_dir'), 'jumpurl' => $data['star_jumpurl']), true, true);
				return $this->success('添加明星成功！' . baidutui($readurl, 'add', 1), url('admin/star/index'), admin_url('admin/html/star_detail_id', ['ids' => $rs->star_id]));
			} else {
				action_log('admin/star/add', 'star', 0, 0);
				return $this->success('添加明星失败！');
			}
		} else {
			$info['admin_time_edit'] = config("admin_time_edit");
			$info['countnews'] = 0;
			$datass['star_data'][0]['title'] = "";
			$datass['star_data'][0]['info'] = "";
			$data = array('info' => $info, 'data' => $datass);
			$this->assign('liststar', F('_data/liststar'));
			$this->assign($data);
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function edit($id = null)
	{
		$rs = model('star');
		if (IS_POST) {
			$data = $this->request->post();
			$result = $this->validate($data, 'Star');
			if (true !== $result) {
				return $this->error($result);
			}
			$rs->save($data, array('star_id' => $data['star_id']));
			if ($rs->star_id) {
				$this->_after_insert($rs->star_id, $data);
				action_log('admin/star/edit', 'star', $rs->star_id, 1);
				$readurl = xianyu_data_url('home/star/read', array('id' => $rs->star_id, 'pinyin' => $rs->star_letters, 'cid' => $rs->star_cid, 'dir' => getlistname($rs->star_cid, 'list_dir'), 'jumpurl' => $data['star_jumpurl']), true, true);
				return $this->success('编辑明星成功！' . baidutui($readurl, 'update', 1), url('admin/star/index'), admin_url('admin/html/star_detail_id', ['ids' => $data['star_id']]));
			} else {
				action_log('admin/star/edit', 'star', 0, 0);
				return $this->success('编辑明星失败！');
			}
		} else {
			$info = $rs->where(array('star_id' => $id))->find()->toArray();
			if (!$info) {
				action_log('admin/star/edit', 'star', 0, 0);
				return $this->error("ID操作失败！");
			}
			$info['star_info'] = xianyu_news_img_array($info['star_info']);
			foreach (explode('||', $info['star_info']) as $k => $val) {
				$datas = explode('@@', $val);
				$datass['star_data'][$k]['title'] = $datas[0];
				$datass['star_data'][$k]['info'] = $datas[1];
			}
			$where['newsrel_did'] = $id;
			$where['newsrel_sid'] = 3;
			$info['countnews'] = db("Newsrel", [], false)->where($where)->count();
			$info['star_letter'] = getletter($info['star_name']);
			$info['star_prty_array'] = model('Prty')->prty_data($id, 3);
			$info['admin_time_edit'] = config("admin_time_edit");
			$data = array('info' => $info, 'data' => $datass);
			$this->assign($data);
			$this->assign('liststar', F('_data/liststar'));
			$this->setNav();
			return view('edit', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function _after_insert($id = "", $data = "")
	{
		if (!empty($id) && !empty($data)) {
			if ($data["star_tag"]) {
				model('Tag')->tag_update($id, $data["star_tag"], 3);
			}
			if ($data["star_prty"]) {
				model('Prty')->prty_update($id, $data["star_prty"], 3);
			}
		}
		$this->delcache($id, $data);
	}
	public function delcache($id = "", $data = "")
	{
		if (config('data_cache_star')) {
			Cache::rm('data_cache_star_' . $id);
		}
		Cache::clear('foreach_star');
		if (config('html_cache_on')) {
			if (config('html_cache_index')) {
				@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
			}
			if (config('html_cache_star_list')) {
				@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($data['star_cid'], 'list_id_big') . '/' . md5(getlistname($data['star_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $data['star_cid'] . '/' . md5(getlistname($data['star_cid'], 'list_url')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($data['star_cid'], 'list_id_big') . '/' . md5(getlistname($data['star_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $data['star_cid'] . '/' . md5(getlistname($data['star_cid'], 'list_url')) . '.' . config('url_html_suffix'));
			}
			if (config('html_cache_star_read')) {
				deldir(RUNTIME_PATH . 'html/pc/star/' . get_small_id($id) . '/' . $id . '/');
				deldir(RUNTIME_PATH . 'html/mobile/star/' . get_small_id($id) . '/' . $id . '/');
			}
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/star/del', 'star', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$this->_after_del_file($id);
		$this->delfile($id);
	}
	public function _after_del_file($id = "")
	{
		$array = db('star')->field('star_id,star_cid,star_letters,star_pic,star_bigpic,star_content,star_info')->where('star_id', 'in', $id)->select();
		foreach ($array as $val) {
			if ($this->config_url['url_html']) {
				@unlink('./' . xianyu_url_html('home/star/read', array('id' => $val['star_id'], 'pinyin' => $val['star_letters'], 'cid' => $val['star_cid'], 'dir' => getlistname($val['star_cid'], 'list_dir'))) . '.' . config('url_html_suffix'));
			}
			$this->delcache($val['star_id'], $val);
			@unlink('./' . config('upload_path') . '/' . $val['star_pic']);
			@unlink('./' . config('upload_path') . '/' . $val['star_bigpic']);
			xianyu_del_img_file($val['star_content']);
			xianyu_del_img_file($val['star_info']);
		}
	}
	public function delfile($id)
	{
		db('Prty')->where('prty_id', 'in', $id, 'and', 'prty_sid', 3)->delete();
		db('Topic')->where('topic_did', 'in', $id, 'and', 'topic_sid', 3)->delete();
		db('Newsrel')->where('newsrel_did', 'in', $id, 'and', 'newsrel_sid', 3)->delete();
		db('Tag')->where('tag_id', 'in', $id, 'and', 'tag_sid', 3)->delete();
		$result = db('Star')->where('star_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/star/del', 'star', $id, 1);
			return $this->success('删除明星成功！', Cookie('star_url_forward'));
		} else {
			action_log('admin/star/del', 'star', $id, 0);
			return $this->error('删除明星失败！');
		}
	}
	public function pestcid($cid = "")
	{
		$id = $this->getArrayParam('id');
		$cid = input('pestcid');
		if (empty($id)) {
			action_log('admin/star/pestcid', 'star', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		if (empty($cid)) {
			action_log('admin/star/pestcid', 'star', 0, 0);
			return $this->error("请选择分类才能操作！");
		}
		if (getlistson($cid)) {
			$map['star_id'] = array('IN', $id);
			$result = db('Star')->where($map)->setField('star_cid', $cid);
		} else {
			return $this->error('请选择当前大类下面的子分类！');
		}
		if ($result !== false) {
			action_log('admin/star/pestcid', 'star', $id, 1);
			return $this->success('转移分类成功', Cookie('star_url_forward'));
		} else {
			action_log('admin/star/pestcid', 'star', $id, 0);
			return $this->error('转移分类失败');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/star/status', 'star', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		if (empty($status)) {
			$status = input('star_status');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['Star_id'] = array('IN', $id);
		$result = db('Star')->where($map)->setField('star_status', $status);
		if ($result !== false) {
			action_log('admin/star/status', 'star', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('star_url_forward'));
		} else {
			action_log('admin/star/status', 'star', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('Star')->where(array('star_id' => $pk))->setField($name, $value);
		}
	}
	public function stars()
	{
		$where['star_id'] = input('id/d', '');
		$data['star_stars'] = input('stars/d', '');
		$result = db('Star')->where($where)->update($data);
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
		if ($did && $sid == 3) {
			return $this->shownews($did, $nid);
		} else {
			return '请先添加明星！';
		}
	}
	public function shownews($did, $nid)
	{
		$where = array();
		$where['newsrel_sid'] = 3;
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
		return view('star_news_ids');
	}
}