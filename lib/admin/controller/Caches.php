<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use think\Cache;
class Caches extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$this->setNav();
		$this->assign('list', F('_data/listtree'));
		$this->assign(config());
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function clear()
	{
		$this->dellogo();
		$this->tempcache();
		Cache::clear();
		return $this->success('清空缓存成功');
	}
	public function del()
	{
		$this->tempcache();
		rmdirs(RUNTIME_PATH . 'cache/play_pwd/');
		cache('xianyucms_version', NULL);
		$this->foreach_all();
		if (config('html_cache_on')) {
			@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
			@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
		}
		return $this->success('清空缓存成功');
	}
	public function temp()
	{
		$this->tempcache();
		return $this->success('清空缓存成功');
	}
	public function tempcache()
	{
		@array_map('unlink', glob(TEMP_PATH . '/*.php'));
		@unlink(RUNTIME_PATH . '/classmap.php');
		@unlink(RUNTIME_PATH . '/init.php');
		@unlink(RUNTIME_PATH . '/route.php');
		@rmdir(TEMP_PATH);
	}
	public function datacache()
	{
		@array_map('unlink', glob(RUNTIME_PATH . '/schema/*.php'));
		@rmdir(RUNTIME_PATH . '/schema');
		return $this->success('清空缓存成功');
	}
	public function caijicache()
	{
		@array_map('unlink', glob(RUNTIME_PATH . '/data/_get/*.php'));
		@unlink(RUNTIME_PATH . 'data/_collect/xucai.php');
		return $this->success('清空缓存成功');
	}
	public function delindex()
	{
		$this->delcache('url_cache_index');
		return $this->success('清空缓存成功');
	}
	public function dellist()
	{
		$this->delcache('url_cache_index');
		return $this->success('清空缓存成功');
	}
	public function delhtmlindex()
	{
		@unlink(RUNTIME_PATH . 'html/pc/index.' . config('url_html_suffix'));
		@unlink(RUNTIME_PATH . 'html/mobile/index.' . config('url_html_suffix'));
		return $this->success('清空缓存成功');
	}
	public function delhtmlpage()
	{
		deldir(RUNTIME_PATH . 'html/pc/my/');
		deldir(RUNTIME_PATH . 'html/mobile/my/');
		return $this->success('清空缓存成功');
	}
	public function delforeachall()
	{
		$this->foreach_all();
		return $this->success('清空缓存成功');
	}
	public function delforeach($id)
	{
		$this->deltagcache($id);
		return $this->success('清空缓存成功');
	}
	public function delmodelall()
	{
		$this->model_all();
		return $this->success('清空缓存成功');
	}
	public function delmodel()
	{
		$day = input('day/d', '');
		$sid = input('sid/d', '');
		$this->model_cache($day, $sid);
		return $this->success('清空缓存成功');
	}
	public function delhtml()
	{
		$sid = input('sid/d', '');
		$day = input('day/d', '');
		if (!$sid) {
			return $this->error('请选择相关模型');
		}
		if (!$day) {
			return $this->error('请选择时间');
		}
		$this->html_cache($sid, $day);
		return $this->success('清空缓存成功');
	}
	public function dellisthtml()
	{
		$cid = input('cid/d', '');
		$p = input('p/s', '');
		$this->html_list_cache($cid, $p);
		return $this->success('清空缓存成功');
	}
	public function deltypehtml()
	{
		$cid = input('cid/d', '');
		$this->html_type_cache($cid);
		return $this->success('清空缓存成功');
	}
	public function delurllistcache($id)
	{
		$this->deltagcache('url_cache_list_' . $id);
		return $this->success('清空缓存成功');
	}
	public function delurlcache($id)
	{
		$this->deltagcache('url_cache_' . $id);
		return $this->success('清空缓存成功');
	}
	private function foreach_all()
	{
		$foreach_array = array('foreach_vod', 'foreach_news', 'foreach_star', 'foreach_starhz', 'foreach_story', 'foreach_actor', 'foreach_role', 'foreach_tv', 'foreach_slide', 'foreach_link', 'foreach_special');
		foreach ($foreach_array as $key => $val) {
			$this->deltagcache($val);
		}
	}
	private function model_all()
	{
		$foreach_array = array('model_vod', 'model_news', 'model_star', 'model_story', 'model_actor', 'model_role', 'model_tv', 'model_special');
		foreach ($foreach_array as $key => $val) {
			$this->deltagcache($val);
		}
	}
	private function model_cache($day, $sid)
	{
		$mode = list_search(F('_data/modellist'), 'id=' . $sid);
		$name = $mode[0]['name'];
		$modelname = "model_" . $name;
		if (is_numeric($day)) {
			$list = db($name)->where($name . '_addtime', 'gt', getxtime($day))->field($name . '_id')->select();
			if (!empty($list)) {
				foreach ($list as $key => $val) {
					$this->delcache('data_cache_vod_' . $val[$name . '_id']);
				}
			}
		} else {
			$this->deltagcache($modelname);
		}
	}
	private function url_cache($day, $sid)
	{
		$mode = list_search(F('_data/modellist'), 'id=' . $sid);
		$name = $mode[0]['name'];
		$urlname = "url_cache_" . $name;
		if (is_numeric($day)) {
			$list = db($name)->where($name . '_addtime', 'gt', getxtime($day))->field($name . '_id')->select();
			if (!empty($list)) {
				foreach ($list as $key => $val) {
					if ($sid == 1 || $sid == 2 || $sid == 3 || $sid == 4 || $sid == 7) {
						$this->deltagcache('url_cache_' . $name . '_' . $val[$name . '_id']);
					} else {
						$this->delcache('url_cache_' . $name . '_' . $val[$name . '_id']);
					}
				}
			}
		} else {
			$this->deltagcache($urlname);
		}
	}
	private function html_cache($sid, $day)
	{
		$mode = list_search(F('_data/modellist'), 'id=' . $sid);
		$name = $mode[0]['name'];
		if ($sid == 8 || $sid == 10 || $sid == 11) {
			if (config('html_cache_' . $name . '_list')) {
				$list = db('list')->where('list_sid', $sid)->field('list_id')->select();
				foreach ($list as $key => $val) {
					@unlink(RUNTIME_PATH . 'html/pc/' . $name . '/' . $val['list_id'] . '.' . config('url_html_suffix'));
					@unlink(RUNTIME_PATH . 'html/mobile/' . $name . '/' . $val['list_id'] . '.' . config('url_html_suffix'));
				}
			}
		} elseif ($day) {
			if ($sid == 1 || $sid == 2 || $sid == 3 || $sid == 7 || $sid == 10) {
				$field = "{$name}_id,{$name}_cid";
			}
			if ($sid == 4 || $sid == 5 || $sid == 6) {
				$field = "{$name}_id,{$name}_cid,{$name}_vid";
			}
			$list = db($name)->where($name . '_addtime', 'gt', getxtime($day))->field($field)->order($name . '_addtime desc')->select();
			if ($list) {
				foreach ($list as $key => $val) {
					if ($sid == 1) {
						if (config('html_cache_vod_list')) {
							@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/pc/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
						}
						deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($val[$name . '_id']) . '/' . $val[$name . '_id'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($val[$name . '_id']) . '/' . $val[$name . '_id'] . '/');
					}
					if ($sid == 2 || $sid == 3 || $sid == 7 || $sid == 10) {
						if (config('html_cache_' . $name . '_list')) {
							@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/pc/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
						}
						deldir(RUNTIME_PATH . 'html/pc/' . $name . '/' . get_small_id($val[$name . '_id']) . '/' . $val[$name . '_id'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/' . $name . '/' . get_small_id($val[$name . '_id']) . '/' . $val[$name . '_id'] . '/');
					}
					if ($sid == 4 || $sid == 6) {
						if (config('html_cache_' . $name . '_list')) {
							@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/pc/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
						}
						deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/pc/' . $name . '/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/' . $name . '/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
					}
					if ($sid == 5) {
						if (config('html_cache_' . $name . '_list')) {
							@unlink(RUNTIME_PATH . 'html/pc/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/pc/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . getlistpid($val[$name . '_cid'], 'list_id_big') . '/' . md5(getlistname($val[$name . '_cid'], 'list_url_big')) . '.' . config('url_html_suffix'));
							@unlink(RUNTIME_PATH . 'html/mobile/show/' . $val[$name . '_cid'] . '/' . md5(getlistname($val[$name . '_cid'], 'list_url')) . '.' . config('url_html_suffix'));
						}
						deldir(RUNTIME_PATH . 'html/pc/vod/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/pc/actor/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/vod/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
						deldir(RUNTIME_PATH . 'html/mobile/actor/' . get_small_id($val[$name . '_vid']) . '/' . $val[$name . '_vid'] . '/');
					}
				}
			}
		}
	}
	private function html_list_cache($cid, $p)
	{
		$mode = list_search(F('_data/list'), 'list_id=' . $cid);
		$listurl = xianyu_list_url('home/' . $mode[0]['name'] . '/show', array('id' => $cid, 'dir' => $mode[0]['list_dir'], 'p' => $p));
		if ($p == 1) {
			@unlink(RUNTIME_PATH . 'html/pc/show/' . $cid . '/' . md5(getlistname($cid, 'list_url')) . '.' . config('url_html_suffix'));
			@unlink(RUNTIME_PATH . 'html/mobile/show/' . $cid . '/' . md5(getlistname($cid, 'list_url')) . '.' . config('url_html_suffix'));
		} elseif ($p > 1) {
			for ($i = 1; $i <= $p; $i++) {
				$listurls = str_replace('xianyupage', $i, $listurl);
				@unlink(RUNTIME_PATH . 'html/pc/show/' . $cid . '/' . md5($listurls) . '.' . config('url_html_suffix'));
				@unlink(RUNTIME_PATH . 'html/mobile/show/' . $cid . '/' . md5($listurls) . '.' . config('url_html_suffix'));
			}
		} else {
			deldir(RUNTIME_PATH . 'html/pc/show/' . $cid . '/');
			deldir(RUNTIME_PATH . 'html/mobile/show/' . $cid . '/');
		}
	}
	private function html_type_cache($cid)
	{
		if (is_numeric($day)) {
			deldir(RUNTIME_PATH . 'html/pc/type/' . $cid . '/');
			deldir(RUNTIME_PATH . 'html/mobile/type/' . $cid . '/');
		}
	}
	private function deltagcache($tag)
	{
		Cache::clear($tag);
	}
	private function delcache($id)
	{
		Cache::rm($id);
	}
	private function dellogo()
	{
		$files = (array) glob(LOG_PATH . '*');
		foreach ($files as $path) {
			if (is_dir($path)) {
				array_map('unlink', glob($path . '/*.log'));
				@rmdir($path);
			} else {
				@unlink($path);
			}
		}
	}
}