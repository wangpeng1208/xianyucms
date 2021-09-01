<?php


namespace app\admin\controller;

use think\Db;
use app\common\controller\Admin;
class Sendurl extends Admin
{
    protected $config;
	public function _initialize()
	{
		parent::_initialize();

		$this->config = F('_data/sendurl');
	}
	public function index()
	{
		if (IS_POST) {
			$post = input('post.');
			F('_data/sendurl', $post);
			action_log('admin/sendurl/index', 'user', $result, 1);
			return $this->success('配置更新成功！');
		}
		$config = F('_data/sendurl');
		$this->assign($config['sendurl']);
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('listnews', F('_data/listnews'));
		$this->assign('liststar', F('_data/liststar'));
		$this->assign('liststory', F('_data/liststory'));
		$this->assign('listactor', F('_data/listactor'));
		$this->assign('listrole', F('_data/listrole'));
		$this->assign('listspecial', F('_data/listspecial'));
		$this->assign('jumpurl', F('_sendurl/next'));
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function send()
	{
		$url = array();
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['cid'] = input('cid/s', '');
		$url['sid'] = input('sid/s', '');
		$url['jump'] = intval(input('jump/d', ''));
		$method = input('method/s', '');
		if ($method == 'index') {
			return $this->redirect('admin/sendurl/index_sendurl');
		} else {
			return $this->redirect('admin/sendurl/' . $method, $url);
		}
	}
	public function list_sendurl()
	{
		$url = array();
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['cid'] = input('cid/s', '');
		$url['sid'] = input('sid/d', 1);
		$url['key'] = intval(input('key/d', ''));
		$url['p'] = intval(input('p/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		$mode = list_search(F('_data/modellist'), 'id=' . $url['sid']);
		F('_sendurl/next', admin_url('admin/sendurl/list_sendurl', $url));
		$array_list = F('_data/list');
		$array = cache('list_sendurl_' . $url['sid'] . '_' . $url['cid']);
		if (!$array) {
			if ($url['day']) {
				$array_listids = db($mode[0]['name'])->group($mode[0]['name'] . '_cid')->whereTime($mode[0]['name'] . '_addtime', $url['day'])->column($mode[0]['name'] . '_cid');
				foreach ($array_listids as $k => $value) {
					$list_pid = getlistname($value, 'list_pid');
					if ($list_pid) {
						$array_pidids[$k] = $list_pid;
					}
				}
				if ($array_pidids) {
					$array_listids = array_unique(array_merge($array_listids, $array_pidids));
				}
			} elseif ($url['time']) {
				$array_listids = db($mode[0]['name'])->group($mode[0]['name'] . '_cid')->where($mode[0]['name'] . '_addtime', 'egt', $url['time'])->column($mode[0]['name'] . '_cid');
				foreach ($array_listids as $k => $value) {
					$list_pid = getlistname($value, 'list_pid');
					if ($list_pid) {
						$array_pidids[$k] = $list_pid;
					}
				}
				if ($array_pidids) {
					$array_listids = array_unique(array_merge($array_listids, $array_pidids));
				}
			} else {
				if ($url['cid']) {
					$array_listids = explode(',', $url['cid']);
				} else {
					$array_listids = array_column(list_search(F('_data/list'), 'list_sid=' . $url['sid']), 'list_id');
				}
			}
			$k = 0;
			foreach ($array_listids as $keys => $value) {
				$list = list_search($array_list, 'list_id=' . $value);
				if ($list[0]['list_limit']) {
					$totalpages = ceil(getcount($value, $url['sid']) / $list[0]['list_limit']);
				} else {
					$totalpages = 1;
				}
				for ($page = 1; $page <= $totalpages; $page++) {
					if ($url['day'] || $url['time']) {
						if ($page >= 4 && $page <= $totalpages - 2) {
							continue;
						}
					}
					$array[$k]['id'] = $value;
					$array[$k]['dir'] = $list[0]['list_dir'];
					$array[$k]['name'] = $list[0]['list_name'];
					$array[$k]['page'] = $page;
					$k++;
				}
			}
			cache('list_sendurl_' . $url['sid'] . '_' . $url['cid'], $array);
		}
		$pages['recordcount'] = count($array);
		$pages['pagecount'] = ceil(count($array) / 500);
		$pages['pageindex'] = $url['p'];
		$pages['pagesize'] = 500;
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		$key = $url['key'];
		if ($array) {
			for ($i = 1; $i <= 500; $i++) {
				if (!$array[$key]) {
					break;
				}
				$urlarray[] = str_replace('xianyupage', $array[$key]['page'], xianyu_list_url('home/' . $mode[0]['name'] . '/show', array('id' => $array[$key]['id'], 'dir' => $array[$key]['dir'], 'p' => $array[$key]['page']), true, true));
				$url_send = str_replace('xianyupage', $array[$key]['page'], xianyu_list_url('home/' . $mode[0]['name'] . '/show', array('id' => $array[$key]['id'], 'dir' => $array[$key]['dir'], 'p' => $array[$key]['page']), true, true));
				$msg = '<tr><td> 栏目：' . $array[$key]['name'] . '第' . $array[$key]['page'] . '页列表地址：<a href=\\"' . $url_send . '\\" target=\\"_blank\\">' . $url_send . '</a></td></tr>';
				echo $this->show_msg($msg);
				$key++;
			}
		}
		echo $this->show_msg($msg = '<tr><td>' . baidutui($urlarray, 'update', 3) . '</td></tr>');
		if ($array && $key < count($array)) {
			$jumpurl = admin_url('admin/sendurl/list_sendurl', ['sid' => $url['sid'], 'cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'key' => $key, 'p' => $url['p'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成列表页面');
		} else {
			if ($url['jump']) {
				cache('list_sendurl_' . $url['sid'] . '_' . $url['cid'], NULL);
				if ($url['sid'] == 1) {
					return $this->jump(admin_url('admin/sendurl/news_detail_cids', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '视频列表页面推送完成,下一步将推送文章内容');
				} elseif ($url['sid'] == 2) {
					return $this->jump(admin_url('admin/sendurl/star_detail_cids', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '文章列表页面推送完成,下一步将推送明星内容');
				} elseif ($url['sid'] == 3) {
					return $this->jump(admin_url('admin/sendurl/special_detail_cids', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '明星列表页面推送完成,下一步将推送专题内容');
				} elseif ($url['sid'] == 10) {
					return $this->jump(admin_url('admin/sendurl/tv_detail_cids', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '专题列表页面推送完成,下一步将推送节目表内容');
				} elseif ($url['sid'] == 7) {
					return $this->jump(admin_url('admin/sendurl/my_detail_cids', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '节目列表页面推送完成,下一步将推送单页');
				}
			} else {
				cache('list_sendurl_' . $url['sid'] . '_' . $url['cid'], NULL);
				F('_sendurl/next', NULL);
				return $this->jump(admin_url('admin/sendurl/send', array('method' => 'index')), '列表页面推送完成,下一步将推送首页');
			}
		}
	}
	public function vod_detail_cids()
	{
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['cid'] = input('cid/s', '');
		$url['sid'] = input('sid/d', 1);
		$url['key'] = intval(input('key/d', ''));
		$url['p'] = intval(input('p/d', 1));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/vod_detail_cids', $url));
		$limit = 100;
		$where = array();
		$where['vod_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['vod_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['vod_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['vod_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('vod')->field('vod_name,vod_id,vod_cid,vod_letters,vod_play,vod_url,vod_jumpurl,story_id,story_cid,story_page,actor_id,actor_cid')->alias('v')->join('story s', 's.story_vid = v.vod_id', 'LEFT')->join('actor a', 'a.actor_vid = v.vod_id', 'LEFT')->where($where)->whereTime('vod_addtime', $url['day'])->order('vod_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('vod')->field('vod_name,vod_id,vod_cid,vod_letters,vod_play,vod_url,vod_jumpurl,story_id,story_cid,story_page,actor_id,actor_cid')->alias('v')->join('story s', 's.story_vid = v.vod_id', 'LEFT')->join('actor a', 'a.actor_vid = v.vod_id', 'LEFT')->where($where)->order('vod_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/news_detail_cids', array('day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump'])), '没有需要推送的数据，下一步将推送文章连接...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '视频没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$vodurl[$key]['vod'] = xianyu_data_url('home/vod/read', array('id' => $value['vod_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir'), 'jumpurl' => $value['vod_jumpurl']), true, true);
			$vodurl[$key]['filmtime'] = xianyu_data_url('home/vod/filmtime', array('id' => $value['vod_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 内容地址：<a href=\\"' . $vodurl[$key]['vod'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['vod'] . '</td></tr>');
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 上映地址：<a href=\\"' . $vodurl[$key]['filmtime'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['filmtime'] . '</td></tr>');
			if ($value['story_id']) {
				if ($this->config['sendurl']['sendurl_vodstory'] || $url['jump']) {
					$vodurl[$key]['story'] = $this->story_page($value);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . count($vodurl[$key]['story']) . '条剧情分集</td></tr>');
				} else {
					$vodurl[$key]['story'] = xianyu_data_url('home/story/read', array('id' => $value['story_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['story_cid'], 'dir' => getlistname($value['story_cid'], 'list_dir')), true, true);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 剧情首页：<a href=\\"' . $vodurl[$key]['filmtime'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['story'] . '</td></tr>');
				}
			}
			if ($value['actor_id']) {
				$vodurl[$key]['actor'] = xianyu_data_url('home/actor/read', array('id' => $value['actor_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['actor_cid'], 'dir' => getlistname($value['actor_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 演员表：<a href=\\"' . $vodurl[$key]['actor'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['actor'] . '</td></tr>');
				if ($this->config['sendurl']['sendurl_vodrole'] || $url['jump']) {
					$vodurl[$key]['role'] = $this->role_page($value);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' ' . count($vodurl[$key]['role']) . '条角色地址</td></tr>');
				}
			}
			if ($this->config['sendurl']['sendurl_vodplay'] || $url['jump']) {
				$vodurl[$key]['play'] = $this->play_page($value);
				echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . count($vodurl[$key]['play']) . '组播放地址</td></tr>');
			}
		}
		if ($vodurl) {
			$result = [];
			array_walk_recursive($vodurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/vod_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/list_sendurl', ['day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump']]), '稍等一会，准备推送视频分类页...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index', ['jump' => $url['jump']]), '恭喜您，视频内容页全部推送完毕。');
			}
		}
	}
	public function vod_detail_id()
	{
		$url['ids'] = input('ids/s', $ids);
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		$url['time'] = intval(input('time/d', 0));
		F('_html/sendurl', admin_url('admin/html/vod_detail_id', $url));
		$limit = 100;
		$where = array();
		$where['vod_status'] = array('eq', 1);
		$where['vod_id'] = array('in', $url['ids']);
		$infos = db('vod')->field('vod_name,vod_id,vod_cid,vod_letters,vod_play,vod_url,vod_jumpurl,story_id,story_cid,story_page,actor_id,actor_cid')->alias('v')->join('story s', 's.story_vid = v.vod_id', 'LEFT')->join('actor a', 'a.actor_vid = v.vod_id', 'LEFT')->where($where)->order('vod_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/sendurl/index'), '视频没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$vodurl[$key]['vod'] = xianyu_data_url('home/vod/read', array('id' => $value['vod_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir'), 'jumpurl' => $value['vod_jumpurl']), true, true);
			$vodurl[$key]['filmtime'] = xianyu_data_url('home/vod/filmtime', array('id' => $value['vod_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 内容地址：<a href=\\"' . $vodurl[$key]['vod'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['vod'] . '</td></tr>');
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 上映地址：<a href=\\"' . $vodurl[$key]['filmtime'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['filmtime'] . '</td></tr>');
			if ($value['story_id']) {
				if ($this->config['sendurl']['sendurl_vodstory'] || $url['jump']) {
					$vodurl[$key]['story'] = $this->story_page($value);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . count($vodurl[$key]['story']) . '条剧情分集</td></tr>');
				} else {
					$vodurl[$key]['story'] = xianyu_data_url('home/story/read', array('id' => $value['story_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['story_cid'], 'dir' => getlistname($value['story_cid'], 'list_dir')), true, true);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 剧情首页：<a href=\\"' . $vodurl[$key]['filmtime'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['story'] . '</td></tr>');
				}
			}
			if ($value['actor_id']) {
				$vodurl[$key]['actor'] = xianyu_data_url('home/actor/read', array('id' => $value['actor_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['actor_cid'], 'dir' => getlistname($value['actor_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 演员表：<a href=\\"' . $vodurl[$key]['actor'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['actor'] . '</td></tr>');
				if ($this->config['sendurl']['sendurl_vodrole'] || $url['jump']) {
					$vodurl[$key]['role'] = $this->role_page($value);
					echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' ' . count($vodurl[$key]['role']) . '条角色地址</td></tr>');
				}
			}
			if ($this->config['sendurl']['sendurl_vodplay'] || $url['jump']) {
				$vodurl[$key]['play'] = $this->play_page($value);
				echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . count($vodurl[$key]['play']) . '组播放地址</td></tr>');
			}
		}
		if ($vodurl) {
			$result = [];
			array_walk_recursive($vodurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/vod_detail_id', ['ids' => $ids, 'page' => $page + 1]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			echo $this->show_msg("<tr><td>恭喜您，视频页面全部推送完成。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function play_page($array)
	{
		$array_server = explode('$$$', $array['vod_server']);
		$array_player = explode('$$$', $array['vod_play']);
		$array_urllist = explode('$$$', $array['vod_url']);
		$config_player = F('_data/player');
		$config_server = F('_data/play_server');
		foreach ($array_player as $key => $value) {
			if ($value == "down") {
				$array_down_player[$key] = $value;
				$array_down_urllist[$key] = $array_urllist[$key];
			}
			if (empty($config_player[$value]) || $config_player[$value]['play_display'] == 0 || $value == "down") {
				unset($array_player[$key]);
				unset($array_urllist[$key]);
			}
		}
		$array_player = array_values($array_player);
		$array_urllist = array_values($array_urllist);
		$playlist = array();
		foreach ($array_player as $sid => $val) {
			$playlist[] = $this->playlist_ones($array, $array_urllist[$sid], $sid);
		}
		return $playlist;
	}
	

	    
	    
	public function playlist_ones($data, $playurl, $sid)
	{
		$config_player = F('_data/player');
		$urllist = array();
		$array_url = explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $playurl));
		foreach ($array_url as $key => $val) {
			$urllist[$key] = xianyu_play_url(array('id' => $data['vod_id'], 'pinyin' => $data['vod_letters'], 'cid' => $data['vod_cid'], 'dir' => getlistname($data['vod_cid'], 'list_dir'), 'sid' => $sid, 'pid' => $key + 1), true, true);
		}
		return $urllist;
	}
	public function story_page($data)
	{
		for ($i = 1; $i <= $data['story_page']; $i++) {
			$storyurl[] = str_replace('xianyupage', $i, xianyu_data_url('home/story/read', array('id' => $data['story_id'], 'pinyin' => $data['vod_letters'], 'cid' => $data['story_cid'], 'dir' => getlistname($data['story_cid'], 'list_dir'), 'p' => $i), true, true));
		}
		return $storyurl;
	}
	public function role_page($data)
	{
		$rolearray = db('role')->alias('r')->where('role_vid', 'eq', $data['vod_id'])->field('role_id,role_cid')->select();
		foreach ($rolearray as $key => $val) {
			$roleurl[] = xianyu_data_url('home/role/read', array('id' => $val['role_id'], 'pinyin' => $data['vod_letters'], 'cid' => $val['role_cid'], 'dir' => getlistname($val['role_cid'], 'list_dir')), true, true);
		}
		return $roleurl;
	}
	public function story_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/story_detail_cids', $url));
		$limit = 30;
		$where = array();
		$where['story_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['story_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['story_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['story_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('story')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,story_id,story_cid,story_page')->alias('s')->join('vod v', 'v.vod_id = s.story_vid', 'RIGHT')->where($where)->whereTime('story_addtime', $url['day'])->order('story_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('story')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,story_id,story_cid,story_page')->alias('s')->join('vod v', 'v.vod_id = s.story_vid', 'RIGHT')->where($where)->order('story_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '剧情没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$storyurl[$key] = $this->story_page($value);
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . count($storyurl[$key]) . '条剧情分集</td></tr>');
		}
		if ($storyurl) {
			$result = [];
			array_walk_recursive($storyurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/story_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，剧情内容页全部推送完毕。');
		}
	}
	public function actor_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/actor_detail_cids', $url));
		$limit = 500;
		$where = array();
		$where['actor_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['actor_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['actor_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['actor_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('actor')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,actor_id,actor_cid')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'RIGHT')->where($where)->whereTime('actor_addtime', $url['day'])->order('actor_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('actor')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,actor_id,actor_cid')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'RIGHT')->where($where)->order('actor_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '演员表没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$vodurl[$key]['actor'] = xianyu_data_url('home/actor/read', array('id' => $value['actor_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['actor_cid'], 'dir' => getlistname($value['actor_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 演员表：<a href=\\"' . $vodurl[$key]['actor'] . '\\" target=\\"_blank\\">' . $vodurl[$key]['actor'] . '</td></tr>');
		}
		if ($vodurl) {
			$result = [];
			array_walk_recursive($vodurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/actor_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，演员表内容页全部推送完毕。');
		}
	}
	public function role_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/role_detail_cids', $url));
		$limit = 500;
		$where = array();
		$where['role_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['role_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['role_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['role_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('role')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,role_id,role_cid,role_name')->alias('r')->join('vod v', 'v.vod_id = r.role_vid', 'RIGHT')->where($where)->whereTime('role_addtime', $url['day'])->order('role_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('role')->field('vod_name,vod_id,vod_cid,vod_letters,vod_jumpurl,role_id,role_cid,role_name')->alias('r')->join('vod v', 'v.vod_id = r.role_vid', 'RIGHT')->where($where)->order('role_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '角色没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$roleurl[$key] = xianyu_data_url('home/role/read', array('id' => $value['role_id'], 'pinyin' => $value['vod_letters'], 'cid' => $value['role_cid'], 'dir' => getlistname($value['role_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>视频：' . $value['vod_name'] . ' 角色：' . $value['role_name'] . '<a href=\\"' . $roleurl[$key] . '\\" target=\\"_blank\\">' . $roleurl[$key] . '</td></tr>');
		}
		if ($roleurl) {
			$result = [];
			array_walk_recursive($roleurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/role_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			F('_sendurl/next', NULL);
			return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，角色内容页全部推送完毕。');
		}
	}
	public function news_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/news_detail_cids', $url));
		$limit = 200;
		$where = array();
		$where['news_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['news_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['news_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['news_addtime'] = array('egt', $time);
		}
		if (!empty($url['day'])) {
			$infos = db('news')->where($where)->whereTime('news_addtime', $url['day'])->order('news_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('news')->where($where)->order('news_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/star_detail_cids', array('day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump'])), '文章没有需要推送的数据，下一步将推送明星内容...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '文章没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$newsurl[$key]['url'] = $this->news_create($value);
			$url[$key] = xianyu_data_url('home/news/read', array('id' => $value['news_id'], 'cid' => $value['news_cid'], 'dir' => getlistname($value['news_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>文章：' . $value['news_name'] . ' 共' . count($newsurl[$key]['url']) . '分页,第一页地址：<a href=\\"' . $url[$key] . '\\" target=\\"_blank\\">' . $url[$key] . '</td></tr>');
		}
		if ($newsurl) {
			$result = [];
			array_walk_recursive($newsurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/news_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/list_sendurl', ['day' => $url['day'], 'time' => $url['time'], 'sid' => 2, 'jump' => $url['jump']]), '稍等一会，准备推送文章分类页面...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，文章内容页全部推送完毕。');
			}
		}
	}
	public function news_detail_id()
	{
		$url = array();
		$url['ids'] = input('ids/s', $ids);
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/news_detail_cids', $url));
		$where['news_status'] = array('eq', 1);
		$where['news_id'] = array('in', $url['ids']);
		$infos = db('news')->where($where)->order('news_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/sendurl/index'), '文章没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$newsurl[$key]['url'] = $this->news_create($value);
			$url[$key] = xianyu_data_url('home/news/read', array('id' => $value['news_id'], 'cid' => $value['news_cid'], 'dir' => getlistname($value['news_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>文章：' . $value['news_name'] . ' 共' . count($newsurl[$key]['url']) . '分页,第一页地址：<a href=\\"' . $url[$key] . '\\" target=\\"_blank\\">' . $url[$key] . '</td></tr>');
		}
		if ($newsurl) {
			$result = [];
			array_walk_recursive($newsurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/news_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			echo $this->show_msg("<tr><td>恭喜您，文章页面全部推送完成。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function news_create($array)
	{
		$info = $this->Lable_News_Read($array);
		$this->assign($info['read']);
		for ($i = 1; $i <= $info['read']['news_page_key']; $i++) {
			$newsurl[$i]['url'] = str_replace('xianyupage', $i, xianyu_data_url('home/news/read', array('id' => $info['read']['news_id'], 'cid' => $info['read']['news_cid'], 'dir' => getlistname($info['read']['news_cid'], 'list_dir'), 'p' => $i, 'jumpurl' => $info['read']['news_jumpurl']), true, true));
		}
		return $newsurl;
	}
	public function star_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/star_detail_cids', $url));
		$limit = 100;
		$where = array();
		$where['star_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['star_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['star_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['star_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('star')->where($where)->whereTime('star_addtime', $url['day'])->order('star_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('star')->where($where)->order('star_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/special_detail_cids', array('day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump'])), '明星没有需要推送的数据，下一步将推送专题内容...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '明星没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$starurl[$key]['url'] = xianyu_data_url('home/star/read', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'jumpurl' => $value['star_jumpurl']), true, true);
			echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '内容地址：<a href=\\"' . $starurl[$key]['url'] . '\\" target=\\"_blank\\">' . $starurl[$key]['url'] . '</td></tr>');
			$starurl[$key]['infourl'] = xianyu_data_url('home/star/info', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '资料地址：<a href=\\"' . $starurl[$key]['infourl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['infourl'] . '</td></tr>');
			if (xianyu_mysql_starhz('star:' . $value['star_name'] . ';field:star_id;limit:1;order:star_id desc')) {
				$starurl[$key]['gxurl'] = xianyu_data_url('home/star/hz', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '关系地址：<a href=\\"' . $starurl[$key]['gxurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['gxurl'] . '</td></tr>');
			}
			if (xianyu_mysql_news('news:' . $value['star_name'] . ';did:' . $value['star_id'] . ';field:news_id;limit:1;order:news_id desc')) {
				$starurl[$key]['newsurl'] = xianyu_data_url('home/star/news', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '新闻地址：<a href=\\"' . $starurl[$key]['newsurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['newsurl'] . '</td></tr>');
			}
			if (xianyu_mysql_role('starname:' . $value['star_name'] . ';limit:1;order:role_id desc')) {
				$starurl[$key]['roleurl'] = xianyu_data_url('home/star/role', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '角色地址：<a href=\\"' . $starurl[$key]['roleurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['roleurl'] . '</td></tr>');
			}
			if (xianyu_mysql_vod('actor:' . $value['star_name'] . ';field:vod_id;limit:1;order:vod_id desc')) {
				$starurl[$key]['dyurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 1, 'vdir' => getlistname(1, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '电影地址：<a href=\\"' . $starurl[$key]['dyurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['dyurl'] . '</td></tr>');
				$starurl[$key]['tvurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 2, 'vdir' => getlistname(2, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '电视剧地址：<a href=\\"' . $starurl[$key]['tvurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['tvurl'] . '</td></tr>');
				$starurl[$key]['zyurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 4, 'vdir' => getlistname(4, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '综艺地址：<a href=\\"' . $starurl[$key]['zyurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['zyurl'] . '</td></tr>');
				$starurl[$key]['workurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '作品地址：<a href=\\"' . $starurl[$key]['workurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['workurl'] . '</td></tr>');
			}
		}
		if ($starurl) {
			$result = [];
			array_walk_recursive($starurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/star_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/list_sendurl', ['day' => $url['day'], 'time' => $url['time'], 'sid' => 3, 'jump' => $url['jump']]), '稍等一会，准备推送文章分类页面...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，明星内容页全部推送完毕。');
			}
		}
	}
	public function star_detail_id()
	{
		$url = array();
		$url['ids'] = input('ids/s', $ids);
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/star_detail_cids', $url));
		$where = array();
		$where['star_status'] = array('eq', 1);
		$where['star_id'] = array('in', $url['ids']);
		$infos = db('star')->where($where)->order('star_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/sendurl/index'), '明星没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$starurl[$key]['url'] = xianyu_data_url('home/star/read', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'jumpurl' => $value['star_jumpurl']), true, true);
			echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '内容地址：<a href=\\"' . $starurl[$key]['url'] . '\\" target=\\"_blank\\">' . $starurl[$key]['url'] . '</td></tr>');
			$starurl[$key]['infourl'] = xianyu_data_url('home/star/info', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '资料地址：<a href=\\"' . $starurl[$key]['infourl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['infourl'] . '</td></tr>');
			if (xianyu_mysql_starhz('star:' . $value['star_name'] . ';field:star_id;limit:1;order:star_id desc')) {
				$starurl[$key]['gxurl'] = xianyu_data_url('home/star/hz', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '关系地址：<a href=\\"' . $starurl[$key]['gxurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['gxurl'] . '</td></tr>');
			}
			if (xianyu_mysql_news('news:' . $value['star_name'] . ';did:' . $value['star_id'] . ';field:news_id;limit:1;order:news_id desc')) {
				$starurl[$key]['newsurl'] = xianyu_data_url('home/star/news', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '新闻地址：<a href=\\"' . $starurl[$key]['newsurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['newsurl'] . '</td></tr>');
			}
			if (xianyu_mysql_role('starname:' . $value['star_name'] . ';limit:1;order:role_id desc')) {
				$starurl[$key]['roleurl'] = xianyu_data_url('home/star/role', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '角色地址：<a href=\\"' . $starurl[$key]['roleurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['roleurl'] . '</td></tr>');
			}
			if (xianyu_mysql_vod('actor:' . $value['star_name'] . ';field:vod_id;limit:1;order:vod_id desc')) {
				$starurl[$key]['dyurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 1, 'vdir' => getlistname(1, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '电影地址：<a href=\\"' . $starurl[$key]['dyurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['dyurl'] . '</td></tr>');
				$starurl[$key]['tvurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 2, 'vdir' => getlistname(2, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '电视剧地址：<a href=\\"' . $starurl[$key]['tvurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['tvurl'] . '</td></tr>');
				$starurl[$key]['zyurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir'), 'vcid' => 4, 'vdir' => getlistname(4, 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '综艺地址：<a href=\\"' . $starurl[$key]['zyurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['zyurl'] . '</td></tr>');
				$starurl[$key]['workurl'] = xianyu_data_url('home/star/work', array('id' => $value['star_id'], 'pinyin' => $value['star_letters'], 'cid' => $value['star_cid'], 'dir' => getlistname($value['star_cid'], 'list_dir')), true, true);
				echo $this->show_msg('<tr><td>明星：' . $value['star_name'] . '作品地址：<a href=\\"' . $starurl[$key]['workurl'] . '\\" target=\\"_blank\\">' . $starurl[$key]['workurl'] . '</td></tr>');
			}
		}
		if ($starurl) {
			$result = [];
			array_walk_recursive($starurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/star_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送数据');
		} else {
			echo $this->show_msg("<tr><td>恭喜您，明星页面全部推送完成。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function special_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/special_detail_cids', $url));
		$limit = 500;
		$where = array();
		$where['special_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['special_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['special_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['special_addtime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('special')->where($where)->whereTime('special_addtime', $url['day'])->order('special_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('special')->where($where)->order('special_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/tv_detail_cids', array('day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump'])), '专题没有需要推送的数据,下一步将推送节目内容...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '专题没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$specialurl[$key]['url'] = xianyu_data_url('home/special/read', array('id' => $value['special_id'], 'pinyin' => $value['special_letters'], 'cid' => $value['special_cid'], 'dir' => getlistname($value['special_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>专题：' . $value['special_name'] . '作品地址：<a href=\\"' . $specialurl[$key]['url'] . '\\" target=\\"_blank\\">' . $specialurl[$key]['url'] . '</td></tr>');
		}
		if ($specialurl) {
			$result = [];
			array_walk_recursive($specialurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/special_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			F('_html/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/list_sendurl', ['day' => $url['day'], 'time' => $url['time'], 'sid' => 10, 'jump' => $url['jump']]), '稍等一会，准备推送专题页面分类页面...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，专题页全部推送完毕。');
			}
		}
	}
	public function special_detail_id()
	{
		$url = array();
		$url['ids'] = input('ids/s', $ids);
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/special_detail_cids', $url));
		$where = array();
		$where['special_status'] = array('eq', 1);
		$where['special_id'] = array('in', $url['ids']);
		$infos = db('special')->where($where)->order('special_addtime desc')->paginate($limit, false, ['page' => $url['page']]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/sendurl/index'), '专题没有需要推送的数据。');
		}
		foreach ($infos as $key => $value) {
			$specialurl[$key]['url'] = xianyu_data_url('home/special/read', array('id' => $value['special_id'], 'pinyin' => $value['special_letters'], 'cid' => $value['special_cid'], 'dir' => getlistname($value['special_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>专题：' . $value['special_name'] . '作品地址：<a href=\\"' . $specialurl[$key]['url'] . '\\" target=\\"_blank\\">' . $specialurl[$key]['url'] . '</td></tr>');
		}
		if ($specialurl) {
			$result = [];
			array_walk_recursive($specialurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/special_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			echo $this->show_msg("<tr><td>恭喜您，专题页面全部推送完成。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function tv_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/sendurl/tv_detail_cids', $url));
		$limit = 500;
		$where = array();
		$where['tv_status'] = array('eq', 1);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['tv_cid'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['tv_cid'] = getlistsqlin($url['cid']);
			}
		}
		if ($url['time']) {
			$where['tv_lasttime'] = array('egt', $url['time']);
		}
		if (!empty($url['day'])) {
			$infos = db('tv')->where($where)->whereTime('tv_lasttime', $url['day'])->order('tv_lasttime desc')->paginate($limit, false, ['page' => $url['page']]);
		} else {
			$infos = db('tv')->where($where)->order('tv_lasttime desc')->paginate($limit, false, ['page' => $url['page']]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/tv_detail_cids', array('day' => $url['day'], 'time' => $url['time'], 'jump' => $url['jump'])), '节目表没有需要推送的数据,下一步将推送节目内容...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '节目表没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$tvurl[$key]['url'] = xianyu_url('home/tv/read', array('id' => $value['tv_id'], 'pinyin' => $value['tv_letters'], 'cid' => $value['tv_cid'], 'dir' => getlistname($value['tv_cid'], 'list_dir')), true, true);
			echo $this->show_msg('<tr><td>节目表：' . $value['tv_name'] . '地址：<a href=\\"' . $tvurl[$key]['url'] . '\\" target=\\"_blank\\">' . $tvurl[$key]['url'] . '</td></tr>');
		}
		if ($tvurl) {
			$result = [];
			array_walk_recursive($tvurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		if ($url['day'] == 'd') {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'add', 3) . '</td></tr>');
		} else {
			echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/my_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			F('_html/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/list_sendurl', ['day' => $url['day'], 'time' => $url['time'], 'sid' => 7, 'jump' => $url['jump']]), '稍等一会，准备推送单页面分类页面...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，节目表页全部推送完毕。');
			}
		}
	}
	public function my_detail_cids()
	{
		$url = array();
		$url['cid'] = input('cid/s', '');
		$url['day'] = input('day/s', '');
		$url['time'] = intval(input('time/d', 0));
		$url['page'] = intval(input('page/d', 1));
		$url['jump'] = intval(input('jump/d', ''));
		F('_sendurl/next', admin_url('admin/html/my_detail_cids', $url));
		$where = array();
		$where['list_status'] = array('eq', 1);
		$where['list_sid'] = array('eq', 8);
		if (!empty($url['cid'])) {
			if (count($url['cid']) > 1) {
				$where['list_id'] = array('in', getlistarr_tag($url['cid']));
			} else {
				$where['list_id'] = getlistsqlin($url['cid']);
			}
		}
		$infos = db('list')->where($where)->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/index_create'), '没有需要推送的数据,准备推送首页...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '没有需要推送的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$myurl[$key]['url'] = xianyu_data_url('home/my/show', array('id' => $value['list_id'], 'dir' => $value['list_dir']), true, true);
			echo $this->show_msg('<tr><td>单页：' . $value['list_name'] . '地址：<a href=\\"' . $myurl[$key]['url'] . '\\" target=\\"_blank\\">' . $myurl[$key]['url'] . '</td></tr>');
		}
		if ($myurl) {
			$result = [];
			array_walk_recursive($myurl, function ($value) use(&$result) {
				array_push($result, $value);
			});
		}
		echo $this->show_msg($msg = '<tr><td>' . baidutui($result, 'update', 3) . '</td></tr>');
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url('admin/sendurl/my_detail_cids', ['cid' => $url['cid'], 'day' => $url['day'], 'time' => $url['time'], 'page' => $url['page'] + 1, 'jump' => $url['jump']]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续推送页面');
		} else {
			F('_sendurl/next', NULL);
			if ($url['jump']) {
				return $this->jump(admin_url('admin/sendurl/index_sendurl', ['jump' => $url['jump']]), '稍等一会，准备推送网站首页...');
			} else {
				return $this->jump(admin_url('admin/sendurl/index'), '恭喜您，单页全部推送完毕。');
			}
		}
	}
	public function index_sendurl()
	{
		$jump = intval(input('jump/d', ''));
		if ($jump) {
			F('_sendurl/next', NULL);
			F('_sendurl/time', time());
			echo $this->fetch('ajax_show');
			echo $this->show_msg($msg = '<tr><td>' . baidutui(config('site_url'), 'update', 3) . '</td></tr>');
			echo $this->show_msg("<tr><td>恭喜您，页面全部推送完毕。</td></tr>");
		} else {
			return $this->success(baidutui(config('site_url'), 'update', 3) . '首页成功', admin_url('admin/sendurl/index'));
		}
	}
	public function jump($jumpurl, $html)
	{
		echo $this->show_msg("<tr><td>" . $html . "</td></tr>");
		echo '<meta http-equiv="refresh" content=' . $this->config_url['url_time'] . ';url=' . $jumpurl . '>';
		exit;
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}