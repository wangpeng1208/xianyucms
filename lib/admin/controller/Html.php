<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use think\Cache;
class Html extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function show()
	{
		$array = array();
		$array['url_html'] = 'disabled';
		if ($this->config_url['url_html']) {
			$array['url_html'] = '';
			if (!$this->config_url['url_vod_data']) {
				$array['url_vod_data'] = 'disabled';
			}
			if (!$this->config_url['url_vod_list']) {
				$array['url_vod_list'] = 'disabled';
			}
			if (!$this->config_url['url_news_data']) {
				$array['url_news_data'] = 'disabled';
			}
			if (!$this->config_url['url_news_list']) {
				$array['url_news_list'] = 'disabled';
			}
			if (!$this->config_url['url_star_data']) {
				$array['url_star_data'] = 'disabled';
			}
			if (!$this->config_url['url_star_list']) {
				$array['url_star_list'] = 'disabled';
			}
			if (!$this->config_url['url_story_data']) {
				$array['url_story_data'] = 'disabled';
			}
			if (!$this->config_url['url_story_list']) {
				$array['url_story_list'] = 'disabled';
			}
			if (!$this->config_url['url_actor_data']) {
				$array['url_actor_data'] = 'disabled';
			}
			if (!$this->config_url['url_actor_list']) {
				$array['url_actor_list'] = 'disabled';
			}
			if (!$this->config_url['url_role_data']) {
				$array['url_role_data'] = 'disabled';
			}
			if (!$this->config_url['url_role_list']) {
				$array['url_role_list'] = 'disabled';
			}
			if (!$this->config_url['url_my_list']) {
				$array['url_my_list'] = 'disabled';
			}
			if (!$this->config_url['url_special_data']) {
				$array['url_special_data'] = 'disabled';
			}
			if (!$this->config_url['url_special_list']) {
				$array['url_special_list'] = 'disabled';
			}
		}
		$this->assign($array);
		$this->setNav();
		$this->assign('listvod', F('_data/listvod'));
		$this->assign('listnews', F('_data/listnews'));
		$this->assign('liststar', F('_data/liststar'));
		$this->assign('liststory', F('_data/liststory'));
		$this->assign('listactor', F('_data/listactor'));
		$this->assign('listrole', F('_data/listrole'));
		$this->assign('listspecial', F('_data/listspecial'));
		$this->assign('jumpurl', F('_html/next'));
		return view('show', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function index()
	{
		$array = db('Module')->where('module_name', 'url_html_config')->find();
		if ($array != null) {
			$value = json_decode($array["module_value"], true);
			F('_data/url_html_config', $value);
			$this->assign($value);
		}
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function up()
	{
		if (IS_POST) {
			$post = input('post.');
			$data['module_value'] = json_encode($post['config']);
			$data['module_uptime'] = time();
			if (db('Module')->where('module_name', 'url_html_config')->find()) {
				$result = db('Module')->where('module_name', 'url_html_config')->update($data);
			} else {
				$data['module_name'] = 'url_html_config';
				$result = db('Module')->insert($data);
			}
			if (!$post['config']['url_html']) {
				@unlink('./index.' . config('url_html_suffix'));
			}
			$this->list_cache();
			if ($result) {
				action_log('admin/html/up', 'user', $result, 1);
				return $this->success('配置更新成功！');
			} else {
				action_log('admin/html/up', 'user', 0, 0);
				return $this->error('配置未做修改！');
			}
		}
	}
	public function check($status, $err, $jumpurl = "")
	{
		$jumpurl = !empty($jumpurl) ? $jumpurl : admin_url('admin/html/show');
		if (!$this->config_url['url_html']) {
			return $this->error('动态模式运行，不需要生成任何数据！', admin_url('admin/html/show'));
		}
		if (!$status) {
			return $this->error('"' . $err . '"模块动态运行，不需要生成静态网页！', $jumpurl);
		}
	}
	public function jump($jumpurl, $html)
	{
		echo $this->show_msg("<tr><td>" . $html . "</td></tr>");
		echo '<meta http-equiv="refresh" content=' . $this->config_url['url_time'] . ';url=' . $jumpurl . '>';
		exit;
	}
	public function create()
	{
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$create = intval(input('create/d', 0));
		$cid = input('cid/s', '');
		$sid = input('sid/s', '');
		$method = input('method/s', '');
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($method == 'index') {
			return $this->redirect('admin/html/index_create');
		} else {
			if ($tid && $os) {
				return $this->{$method};
			} else {
				return $this->redirect('admin/html/' . $method, ['day' => $day, 'time' => $time, 'create' => $create, 'cid' => $cid, 'sid' => $sid, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
			}
		}
	}
	public function create_time()
	{
		$times = F('_html/time');
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$time = !empty($times) ? intval($times) : time();
		if ($tid && $os) {
			$this->request->instance()->get(['tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'time' => $time, 'jump' => 1]);
			return $this->vod_detail_cids();
		} else {
			return $this->redirect(strtolower($this->request->module() . '/' . $this->request->controller()) . '/vod_detail_cids', ['tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'time' => $time, 'jump' => 1]);
		}
	}
	public function list_create()
	{
		$sid = input('sid/d', 1);
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$key = intval(input('key/d', ''));
		$p = intval(input('p/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		$mode = list_search(F('_data/modellist'), 'id=' . $sid);
		if ($jump) {
			if (!$this->config_url['url_html_list']) {
				if ($tid && $os) {
					if ($sid == 1) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->news_detail_cids();
					} elseif ($sid == 2) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->star_detail_cids();
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
							return $this->list_create();
						}
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->story_detail_cids();
					} elseif ($sid == 4) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->actor_detail_cids();
					} elseif ($sid == 6) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->list_create();
					} elseif ($sid == 5) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->special_detail_cids();
					} elseif ($sid == 10) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->my_detail_cids();
					}
				} else {
					if ($sid == 1) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '视频列表页面生成完成,下一步将生成文章内容');
					} elseif ($sid == 2) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '文章列表页面生成完成,下一步将生成明星内容');
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '明星列表页面生成完成,下一步将生成剧情分类页面');
						}
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '明星列表页面生成完成,下一步将生成剧情内容');
					} elseif ($sid == 4) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '剧情列表页面生成完成,下一步将生成演员表内容');
					} elseif ($sid == 6) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '演员列表页面生成完成,下一步将生成角色列表');
					} elseif ($sid == 5) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '角色列表页面生成完成,下一步将生成专题内容');
					} elseif ($sid == 10) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/my_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '专题列表页面生成完成,下一步将生成单页内容');
					}
				}
			}
			if (!$this->config_url['url_' . $mode[0]['name'] . '_list']) {
				if ($tid && $os) {
					if ($sid == 1) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->news_detail_cids();
					} elseif ($sid == 2) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->star_detail_cids();
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
							return $this->list_create();
						}
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->story_detail_cids();
					} elseif ($sid == 4) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->actor_detail_cids();
					} elseif ($sid == 6) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->list_create();
					} elseif ($sid == 5) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->special_detail_cids();
					} elseif ($sid == 10) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->my_detail_cids();
					}
				} else {
					if ($sid == 1) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '视频列表页面生成完成,下一步将生成文章内容');
					} elseif ($sid == 2) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '文章列表页面生成完成,下一步将生成明星内容');
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '明星列表页面生成完成,下一步将生成剧情分类页面');
						}
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '明星列表页面生成完成,下一步将生成剧情内容');
					} elseif ($sid == 4) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '剧情列表页面生成完成,下一步将生成演员表内容');
					} elseif ($sid == 6) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '演员列表页面生成完成,下一步将生成角色列表');
					} elseif ($sid == 5) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '角色列表页面生成完成,下一步将生成专题内容');
					} elseif ($sid == 10) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/my_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '专题列表页面生成完成,下一步将生成单页内容');
					}
				}
			}
		} else {
			$this->check($this->config_url['url_' . $mode[0]['name'] . '_list'], $mode[0]['title'] . '列表页', admin_url('admin/html/show'));
		}
		F('_html/next', admin_url('admin/html/list_create', ['sid' => $sid, 'cid' => $cid, 'day' => $day, 'time' => $time, 'key' => $key, 'p' => $p, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$array_list = F('_data/list');
		$array = cache('list_create_' . $sid . '_' . $cid . '_' . $day . '_' . $time);
		if (!$array) {
			if ($day) {
				$array_listids = db($mode[0]['name'])->group($mode[0]['name'] . '_cid')->whereTime($mode[0]['name'] . '_addtime', $day)->column($mode[0]['name'] . '_cid');
				foreach ($array_listids as $k => $value) {
					$list_pid = getlistname($value, 'list_pid');
					if ($list_pid) {
						$array_pidids[$k] = $list_pid;
					}
				}
				if ($array_pidids) {
					$array_listids = array_unique(array_merge($array_listids, $array_pidids));
				}
			} elseif ($time) {
				$array_listids = db($mode[0]['name'])->group($mode[0]['name'] . '_cid')->where($mode[0]['name'] . '_addtime', 'egt', $time)->column($mode[0]['name'] . '_cid');
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
				if ($cid) {
					$array_listids = explode(',', $cid);
				} else {
					$array_listids = array_column(list_search(F('_data/list'), 'list_sid=' . $sid), 'list_id');
				}
			}
			$k = 0;
			foreach ($array_listids as $keys => $value) {
				$list = list_search($array_list, 'list_id=' . $value);
				if ($list[0]['list_limit']) {
					$totalpages = ceil(getcount($value, $sid) / $list[0]['list_limit']);
					$uppage = ceil(count($array) / $list[0]['list_limit']) + 1;
				} else {
					$totalpages = 1;
					$uppage = 4;
				}
				for ($page = 1; $page <= $totalpages; $page++) {
					if ($day || $time) {
						if ($page > $uppage && $page <= $totalpages - $uppage) {
							continue;
						}
					}
					$array[$k]['id'] = $value;
					$array[$k]['page'] = $page;
					$k++;
				}
			}
			cache('list_create_' . $sid . '_' . $cid, $array);
		}
		$pages['recordcount'] = count($array);
		$pages['pagecount'] = ceil(count($array) / $this->config_url['url_number']);
		$pages['pageindex'] = $p;
		$pages['pagesize'] = $this->config_url['url_number'];
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if ($array) {
			for ($i = 1; $i <= $this->config_url['url_number']; $i++) {
				if (!$array[$key]) {
					break;
				}
				$this->list_create_page(list_search($array_list, 'list_id=' . $array[$key]['id']), $array[$key]['page']);
				$key++;
			}
		}
		if ($array && $key < count($array)) {
			if ($tid && $os) {
				$this->request->instance()->get(['sid' => $sid, 'cid' => $cid, 'day' => $day, 'time' => $time, 'key' => $key, 'p' => $p + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->list_create();
			} else {
				$jumpurl = admin_url('admin/html/list_create', ['sid' => $sid, 'cid' => $cid, 'day' => $day, 'time' => $time, 'key' => $key, 'p' => $p + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成列表页面');
			}
		} else {
			if ($jump) {
				cache('list_create_' . $sid . '_' . $cid, NULL);
				if ($tid && $os) {
					if ($sid == 1) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->news_detail_cids();
					} elseif ($sid == 2) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->star_detail_cids();
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
							return $this->list_create();
						}
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->story_detail_cids();
					} elseif ($sid == 4) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->actor_detail_cids();
					} elseif ($sid == 6) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->list_create();
					} elseif ($sid == 5) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->special_detail_cids();
					} elseif ($sid == 10) {
						$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
						return $this->my_detail_cids();
					}
				} else {
					if ($sid == 1) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '视频列表页面生成完成,下一步将生成文章内容');
					} elseif ($sid == 2) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '文章列表页面生成完成,下一步将生成明星内容');
					} elseif ($sid == 3) {
						if ($this->config_url['url_vod_story']) {
							return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '明星列表页面生成完成,下一步将生成剧情分类页面');
						}
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '明星列表页面生成完成,下一步将生成剧情内容');
					} elseif ($sid == 4) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '剧情列表页面生成完成,下一步将生成演员表内容');
					} elseif ($sid == 6) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '演员列表页面生成完成,下一步将生成角色列表');
					} elseif ($sid == 5) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '角色列表页面生成完成,下一步将生成专题内容');
					} elseif ($sid == 10) {
						return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/my_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '专题列表页面生成完成,下一步将生成单页内容');
					}
				}
			} else {
				cache('list_create_' . $sid . '_' . $cid . '_' . $day . '_' . $time, NULL);
				F('_html/next', NULL);
				return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/create', array('method' => 'index', 'tid' => $tid, 'os' => $os, 'pwd' => $pwd)), '列表页面生成完成,下一步将生成首页');
			}
		}
	}
	public function list_create_page($data, $page)
	{
		$param['page'] = $page;
		$channel = $this->Lable_List($param, $data[0]);
		config('model', 'home/' . $channel['name'] . '/show');
		config('params', array('id' => $channel['list_id'], 'dir' => $channel['list_dir'], 'p' => "xianyupage"));
		config('currentpage', $page);
		$path = xianyu_url_html('home/' . $channel['name'] . '/show', array('id' => $channel['list_id'], 'dir' => $channel['list_dir'], 'p' => $page));
		$this->assign($param);
		$this->assign($channel);
		$this->assign('url', xianyu_list_url('home/' . $channel['name'] . '/show', array('id' => $channel['list_id'], 'dir' => $channel['list_dir'], 'p' => $page)));
		$this->buildHtml($path, './', '/' . $channel['list_skin']);
		$msg = '<tr><td> 栏目：' . $channel['list_name'] . ' 列表地址：<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function vod_detail_cids()
	{
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$cid = input('cid/s', '');
		$page = intval(input('page/d', 1));
		$play = intval(input('play/d', ''));
		$story = intval(input('story/d', ''));
		$actor = intval(input('actor/d', ''));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_vod_data']) {
			return $this->news_detail_cids();
		} else {
			$this->check($this->config_url['url_vod_data'], '视频内容页', admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('sid' => 1, 'day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/vod_detail_cids', ['play' => $play, 'cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		if ($this->config_url['url_vod_play'] || $this->config_url['url_vod_story'] || $this->config_url['url_vod_actor']) {
			$limit = 1;
		} else {
			$limit = $this->config_url['url_number'];
		}
		$where = array();
		$where['vod_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['vod_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['vod_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['vod_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('vod')->field('vod_id')->where($where)->whereTime('vod_addtime', $day)->order('vod_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('vod')->field('vod_id')->where($where)->order('vod_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		if (empty($os) || !empty($os) && $page <= 1 && empty($play)) {
			echo $this->fetch('ajax_show');
		}
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					return $this->news_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '没有需要生成的数据，下一步将生成文章内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '视频没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->vod_create($value['vod_id'], strtolower($this->request->module() . '/' . $this->request->controller()) . '/vod_detail_cids', $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['page' => $page + 1, 'play' => 0, 'actor' => 0, 'story' => 0]);
				return $this->vod_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/vod_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['sid' => 1, 'day' => $day, 'time' => $time, 'page' => 1, 'actor' => 0, 'story' => 0, 'play' => 0, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成视频分类页...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '恭喜您，视频内容页全部生成完毕。');
			}
		}
	}
	public function vod_detail_id()
	{
		$ids = input('ids/s', $ids);
		$page = intval(input('page/d', 1));
		$play = intval(input('play/d', ''));
		$story = intval(input('story/d', ''));
		$actor = intval(input('actor/d', ''));
		$jump = intval(input('jump/d', ''));
		$time = intval(input('time/d', 0));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_vod_data'], '视频内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/vod_detail_id', ['play' => $play, 'ids' => $ids, 'page' => $page, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		if ($this->config_url['url_vod_play'] || $this->config_url['url_vod_story'] || $this->config_url['url_vod_actor']) {
			$limit = 1;
		} else {
			$limit = $this->config_url['url_number'];
		}
		$where = array();
		$where['vod_status'] = array('eq', 1);
		$where['vod_id'] = array('in', $ids);
		$infos = db('vod')->field('vod_id')->where($where)->order('vod_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/html/show'), '视频没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->vod_create($value['vod_id'], strtolower($this->request->module() . '/' . $this->request->controller()) . '/vod_detail_id', $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/vod_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，视频内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function vod_create($data, $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd)
	{
		$info = cache(md5('vod_create_id' . $data));
		if (!$info) {
			$array = db('vod')->field('v.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status')->alias('v')->join('story s', 's.story_vid = v.vod_id and s.story_status =1', 'LEFT')->join('actor a', 'a.actor_vid = v.vod_id and a.actor_status =1', 'LEFT')->where('vod_id', $data)->find();
			$info = $this->Lable_Vod_Read($array);
			cache(md5('vod_create_id' . $data), $info, 3600);
		}
		if ($play == 0 && $story == 0 && $actor == 0) {
			$path = xianyu_url_html('home/vod/read', array('id' => $info['read']['vod_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['vod_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['vod_jumpurl']));
			$filmtimepath = xianyu_url_html('home/vod/filmtime', array('id' => $info['read']['vod_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['vod_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['vod_jumpurl']));
			$this->assign($info['show']);
			$this->assign($info['read']);
			if (empty($info['read']['story_id'])) {
				$this->assign('story_id', null);
			}
			if (empty($info['read']['actor_id'])) {
				$this->assign('actor_id', null);
			}
			$this->assign('url', xianyu_data_url('home/vod/read', array('id' => $info['read']['vod_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['vod_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['vod_jumpurl'])));
			$this->buildHtml($path, './', '/' . $info['read']['vod_skin_detail']);
			$msg = '<tr><td> 视频：' . $info['read']['vod_name'] . ' 内容地址：<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
			echo $this->show_msg($msg);
			if ($this->config_url['url_vod_filmtime']) {
				$this->assign('url', xianyu_data_url('home/vod/filmtime', array('id' => $info['read']['vod_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['vod_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['vod_jumpurl'])));
				$this->buildHtml($filmtimepath, './', '/' . $info['read']['vod_skin_detail'] . "_filmtime");
				$msg = '<tr><td> 视频：' . $info['read']['vod_name'] . ' 上映地址：<a href=\\"' . $filmtimepath . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $filmtimepath . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
				echo $this->show_msg($msg);
			}
		}
		cache('vod_create_' . $data, $data, 3600);
		if ($this->config_url['url_vod_play'] && empty($story) && empty($actor)) {
			$this->vod_play_page($info, $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd);
		} else {
			if ($this->config_url['url_vod_story'] && $this->config_url['url_story_data'] && $info['read']['story_id'] && empty($actor)) {
				$this->vod_story_create($info['read'], $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd);
			}
			if ($this->config_url['url_vod_actor'] && $this->config_url['url_actor_data'] && $info['read']['actor_id']) {
				$this->vod_actor_create($info['read'], $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd);
			}
			cache(md5('vod_create_id' . $data), NULL);
		}
		echo $this->show_msg("<tr><td> 视频：" . $info['read']['vod_name'] . "页面全部生成完毕。</td></tr>");
		cache(md5('vod_create_id' . $data), NULL);
		return $info;
	}
	public function vod_play_page($info, $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd)
	{
		if ($info['read']['vod_playlist']) {
			$urls = array();
			foreach ($info['read']['vod_playlist'] as $sid => $son) {
				$arr_sid = explode('-', $sid);
				$arr_play = array();
				foreach ($son['son'] as $pid => $value) {
					array_push($urls, array('id' => $info['read']['vod_id'], 'sid' => $arr_sid[1], 'pid' => $pid + 1));
				}
			}
			$urls_count = count($urls);
			for ($i = 0; $i < $this->config_url['url_play_number']; $i++) {
				$this->vod_play_create($info, $urls[$play]);
				$play++;
				if ($play >= $urls_count) {
					break;
				}
				if ($i == $this->config_url['url_play_number'] - 1) {
					$jumpurl = admin_url($model, ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'play' => $play, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
					$osurl = ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'play' => $play, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump];
				}
			}
			if ($jumpurl) {
				if ($tid && $os) {
					$this->request->instance()->get($osurl);
					return $this->vod_detail_cids();
				} else {
					return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成剩余播放地址');
				}
			}
			echo $this->show_msg("<tr><td>视频：" . $info['read']['vod_name'] . "播放地址生成完成</td></tr>");
		}
		if ($this->config_url['url_vod_story'] && $this->config_url['url_story_data'] && $info['read']['story_id'] && empty($actor)) {
			$this->vod_story_create($info['read'], $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $tid, $os, $pwd, $jump);
		}
		if ($this->config_url['url_vod_actor'] && $this->config_url['url_actor_data'] && $info['read']['actor_id']) {
			$this->vod_actor_create($info['read'], $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $tid, $os, $pwd, $jump);
		}
	}
	public function vod_play_create($array, $play)
	{
		$this->assign($array['show']);
		$path = xianyu_url_html('home/vod/play', array('id' => $array['read']['vod_id'], 'pinyin' => $array['read']['vod_letters'], 'cid' => $array['read']['vod_cid'], 'dir' => $array['show']['list_dir'], 'sid' => $play['sid'], 'pid' => $play['pid']));
		$this->assign($this->Lable_Vod_Play($array['read'], $array['show'], array('id' => $array['read']['id'], 'sid' => $play['sid'], 'pid' => $play['pid'])));
		$this->assign('url', xianyu_data_url('home/vod/play', array('id' => $array['read']['vod_id'], 'pinyin' => $array['read']['vod_letters'], 'cid' => $array['read']['vod_cid'], 'dir' => $array['show']['list_dir'], 'sid' => $play['sid'], 'pid' => $play['pid'])));
		$this->buildHtml($path, './', '/' . $array['read']['vod_skin_play']);
		$msg = '<tr><td> 视频：' . $array['read']['vod_name'] . ' 播放地址：<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function vod_story_create($data, $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd)
	{
		$info = cache(md5('vod_create_story_id' . $data['story_id']));
		if (!$info) {
			$array = db('story')->where('story_id', $data['story_id'])->find();
			$array = array_merge($array, $data);
			$info = $this->Lable_Story_Read($array);
			cache(md5('vod_create_story_id' . $data['story_id']), $info, 3600);
		}
		$urls_count = count($info['read']['story_list']);
		for ($i = 0; $i < $this->config_url['url_play_number']; $i++) {
			$this->story_detail_create($info, $story);
			$story++;
			if ($story >= $urls_count) {
				break;
			}
			if ($i == $this->config_url['url_play_number'] - 1) {
				$jumpurl = admin_url($model, ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'story' => $story, 'actor' => $actor, 'jump' => $jump, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
				$osurl = ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'story' => $story, 'actor' => $actor, 'jump' => $jump, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd];
			}
		}
		if ($jumpurl) {
			if ($tid && $os) {
				$this->request->instance()->get($osurl);
				return $this->vod_detail_cids();
			} else {
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成剩余剧情地址');
			}
		}
		echo $this->show_msg("<tr><td>视频：" . $info['read']['vod_name'] . "剧情页面生成完成</td></tr>");
		cache(md5('vod_create_story_id' . $data['story_id']), NULL);
	}
	public function story_detail_create($array, $page)
	{
		$this->assign($array['show']);
		$this->assign($array['read']);
		$this->assign($array['read']['story_list'][$page + 1]);
		$path = xianyu_url_html('home/story/read', array('id' => $array['read']['story_id'], 'pinyin' => $array['read']['vod_letters'], 'cid' => $array['read']['story_cid'], 'dir' => $array['show']['list_dir'], 'p' => $page + 1));
		$this->assign('url', xianyu_data_url('home/story/read', array('id' => $array['read']['story_id'], 'pinyin' => $array['read']['vod_letters'], 'cid' => $array['read']['story_cid'], 'dir' => $array['show']['list_dir'], 'p' => $page + 1)));
		$this->buildHtml($path, './', '/' . $array['read']['story_skin']);
		$msg = '<tr><td> 视频：' . $array['read']['vod_name'] . ' 剧情地址：<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function vod_actor_create($data, $model, $play, $cid, $ids, $day, $time, $page, $story, $actor, $jump, $tid, $os, $pwd)
	{
		unset($data['vod_playlist'], $data['vod_player']);
		$info = cache(md5('vod_create_actor_id' . $data['actor_id']));
		if (!$info) {
			$array = db('actor')->where('actor_id', $data['actor_id'])->find();
			$array = array_merge($data, $array);
			$info = $this->Lable_Actor_Read($array);
			$info['role'] = db('role')->alias('r')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where(['role_vid' => $array['vod_id']])->select();
			cache(md5('vod_create_actor_id' . $data['actor_id']), $info, 3600);
		}
		foreach ($info['role'] as $key => $val) {
			$info['role_list'][$key] = $this->Lable_Role_Read($val)['read'];
		}
		$info['role_list_all'] = $this->Lable_Role_list($info['role']);
		$this->assign($info['show']);
		$this->assign($info['read']);
		$this->assign('role_list', $info['role_list']);
		$path = xianyu_url_html('home/actor/read', array('id' => $info['read']['actor_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['actor_cid'], 'dir' => $info['show']['list_dir']));
		$this->assign('url', xianyu_data_url('home/actor/read', array('id' => $info['read']['actor_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['actor_cid'], 'dir' => $info['show']['list_dir'])));
		$this->buildHtml($path, './', '/' . $info['read']['actor_skin']);
		$msg = '<tr><td> 视频：' . $info['read']['vod_name'] . ' 演员表地址<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
		$urls_count = count($info['role']);
		for ($i = 0; $i < $this->config_url['url_play_number']; $i++) {
			$this->role_create($info, $actor);
			$actor++;
			if ($actor >= $urls_count) {
				break;
			}
			if ($i == $this->config_url['url_play_number'] - 1) {
				$jumpurl = admin_url($model, ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				$osurl = ['cid' => $cid, 'ids' => $ids, 'day' => $day, 'time' => $time, 'page' => $page, 'story' => $story, 'actor' => $actor, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump];
			}
		}
		if ($jumpurl) {
			if ($tid && $os) {
				$this->request->instance()->get($osurl);
				return $this->vod_detail_cids();
			} else {
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成剩余角色地址');
			}
		}
		echo $this->show_msg("<tr><td>视频：" . $info['read']['vod_name'] . " 演员表角色页面生成完成</td></tr>");
		cache(md5('vod_create_actor_id' . $data['actor_id']), NULL);
	}
	public function news_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_news_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'page' => 1, 'sid' => 2, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_news_data'], '文章内容页', admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('day' => $day, 'time' => $time, 'sid' => 2, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/news_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['news_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['news_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['news_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['news_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('news')->field('news_id')->where($where)->whereTime('news_addtime', $day)->order('news_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('news')->field('news_id')->where($where)->order('news_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(array('day' => $day, 'time' => $time, 'page' => 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump));
					return $this->star_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '文章没有需要生成的数据，下一步将生成明星内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '文章没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->news_create($value['news_id']);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->news_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'page' => 1, 'sid' => 2, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 2, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成文章分类页面...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，文章内容页全部生成完毕。');
			}
		}
	}
	public function news_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_news_data'], '新闻内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/news_detail_id', ['ids' => $ids, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['news_status'] = array('eq', 1);
		$where['news_id'] = array('in', $ids);
		$infos = db('news')->field('news_id')->where($where)->order('news_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			return $this->jump(admin_url('admin/html/show'), '文章没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->news_create($value['news_id']);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/news_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，新闻内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function news_create($data)
	{
		$array = db('news')->where('news_id', $data)->find();
		$array['newsrel'] = db('newsrel')->where('newsrel_nid', $data)->select();
		$info = $this->Lable_News_Read($array);
		$this->assign($info['show']);
		$this->assign($info['read']);
		for ($i = 1; $i <= $info['read']['news_page_key']; $i++) {
			$arrays[$i]['path'] = xianyu_url_html('home/news/read', array('id' => $info['read']['news_id'], 'cid' => $info['read']['news_cid'], 'dir' => $info['show']['list_dir'], 'p' => $i, 'jumpurl' => $info['read']['news_jumpurl']));
			if ($info['read']['images_slide']) {
				$this->assign('news_content', null);
				$this->assign($info['read']['images_slide'][$i]);
			}
			if ($info['read']['news_pages']) {
				$this->assign($info['read']['news_pages'][$i]);
			}
			$this->assign('url', xianyu_data_url('home/news/read', array('id' => $info['read']['news_id'], 'cid' => $info['read']['news_cid'], 'dir' => $info['show']['list_dir'], 'p' => $i, 'jumpurl' => $info['read']['news_jumpurl'])));
			$this->buildHtml($arrays[$i]['path'], './', '/' . $info['read']['news_skin_detail']);
			$this->assign(null);
			$msg = '<tr><td> 新闻ID' . $info['read']['news_id'] . ' 内容地址 <a href=\\"' . $arrays[$i]['path'] . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $arrays[$i]['path'] . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
			echo $this->show_msg($msg);
		}
	}
	public function star_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_star_data'], '明星内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/star_detail_id', ['ids' => $ids, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['star_status'] = array('eq', 1);
		$where['star_id'] = array('in', $ids);
		$infos = db('star')->where($where)->order('star_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			F('_html/next', NULL);
			return $this->jump(admin_url('admin/html/show'), '明星没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->star_create($value);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，明星内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function star_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_star_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 3, 'page' => 1, 'pagekey' => '', 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_star_data'], '明星内容页', admin_url('admin/html/list_create', array('day' => $day, 'time' => $time, 'sid' => 3, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/star_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['star_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['star_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['star_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['star_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('star')->where($where)->whereTime('star_addtime', $day)->order('star_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('star')->where($where)->order('star_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'pagekey' => '', 'jump' => $jump));
					return $this->story_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '明星没有需要生成的数据，下一步将生成剧情内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '明星没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->star_create($value);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->star_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/star_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 3, 'page' => 1, 'pagekey' => '', 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 3, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成明星分类页面...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，明星内容页全部生成完毕。');
			}
		}
	}
	public function star_create($data)
	{
		$info = $this->Lable_Star_Read($data);
		$this->assign($info['show']);
		$this->assign($info['read']);
		$this->assign('action', 'read');
		$path = xianyu_url_html('home/star/read', array('id' => $info['read']['star_id'], 'pinyin' => $info['read']['star_letters'], 'cid' => $info['show']['star_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['star_jumpurl']));
		$this->assign('url', xianyu_data_url('home/star/read', array('id' => $info['read']['star_id'], 'pinyin' => $info['read']['star_letters'], 'cid' => $info['show']['star_cid'], 'dir' => $info['show']['list_dir'], 'jumpurl' => $info['read']['star_jumpurl'])));
		$this->buildHtml($path, './', '/' . $info['read']['star_skin_detail']);
		$this->assign(null);
		$msg = '<tr><td> 明星' . $info['read']['star_name'] . ' 内容地址 <a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function story_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$time = intval(input('time/d', 0));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_story_data'], '剧情内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/story_detail_id', ['ids' => $ids, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = 1;
		$where = array();
		$where['story_status'] = array('eq', 1);
		$where['story_id'] = array('in', $ids);
		$infos = db('story')->field('story_id')->where($where)->order('story_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			F('_html/next', NULL);
			return $this->jump(admin_url('admin/html/show'), '剧情没有需要生成的数据。');
		}
		cache('story_key', null);
		foreach ($infos as $key => $value) {
			$this->story_create($value['story_id'], $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_id', ['ids' => $ids, 'page' => $page + 1, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，剧情内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function story_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_star_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_story_data'], '剧情内容页', admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', array('day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/story_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = 1;
		$where = array();
		$where['story_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['story_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['story_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['story_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('story')->where($where)->whereTime('story_addtime', $day)->order('story_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('story')->where($where)->order('story_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump));
					return $this->actor_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '剧情没有需要生成的数据，下一步将生成演员表内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '剧情没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->story_create($value['story_id'], $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'pagekey' => 0, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->story_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/story_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 4, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成剧情分类页...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，剧情内容页全部生成完毕。');
			}
		}
	}
	public function story_create($data, $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd)
	{
		$info = cache(md5('story_create_id' . $data));
		if (!$info) {
			$array = db('story')->field('s.*,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('s')->join('actor a', 'a.actor_vid = s.story_vid', 'LEFT')->join('vod v', 'v.vod_id = s.story_vid', 'LEFT')->where('story_id', $data)->find();
			$info = $this->Lable_Story_Read($array);
			cache(md5('story_create_id' . $data), $info, 3600);
		}
		$urls_count = count($info['read']['story_list']);
		for ($i = 0; $i < $this->config_url['url_play_number']; $i++) {
			$this->story_detail_create($info, $pagekey);
			$pagekey++;
			if ($pagekey >= $urls_count) {
				break;
			}
			if ($i == $this->config_url['url_play_number'] - 1) {
				$url = ['ids' => $ids, 'cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd];
				$jumpurl = admin_url($model, ['ids' => $ids, 'cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			}
		}
		if ($jumpurl) {
			if ($tid && $os) {
				$this->request->instance()->get($url);
				return $this->story_detail_cids();
			} else {
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成剩余剧情地址');
			}
		}
		echo $this->show_msg("<tr><td>视频：" . $info['read']['vod_name'] . "剧情页面生成完成</td></tr>");
		cache(md5('story_create_id' . $data), NULL);
	}
	public function actor_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$time = intval(input('time/d', 0));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_actor_data'], '演员表内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/actor_detail_id', ['ids' => $ids, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['actor_status'] = array('eq', 1);
		$where['actor_id'] = array('in', $ids);
		$infos = db('actor')->field('actor_id')->where($where)->order('actor_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			F('_html/next', NULL);
			return $this->jump(admin_url('admin/html/show'), '演员表没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->actor_create($value['actor_id'], $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，演员表内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function actor_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_actor_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 6, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_actor_data'], '演员表内容页', admin_url('admin/html/list_create', array('day' => $day, 'time' => $time, 'sid' => 6, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/actor_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = 1;
		$where = array();
		$where['actor_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['actor_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['actor_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['actor_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('actor')->where($where)->whereTime('actor_addtime', $day)->order('actor_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('actor')->where($where)->order('actor_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if ($this->config_url['url_vod_actor']) {
			if ($tid && $os) {
				$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump));
				return $this->special_detail_cids();
			} else {
				return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '演员表内容在视频中以生成,下一步将生成专题内容');
			}
		}
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump));
					return $this->special_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '演员表没有需要生成的数据，下一步将生成专题内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '演员表没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->actor_create($value['actor_id'], $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->actor_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/actor_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 6, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 6, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成演员列表页面...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，演员表页全部生成完毕。');
			}
		}
	}
	public function actor_create($data, $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd)
	{
		$info = cache(md5('actor_create_id' . $data));
		if (!$info) {
			$array = db('actor')->field('a.*,story_id,story_cid,story_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'LEFT')->join('story s', 's.story_vid = a.actor_vid', 'LEFT')->where('actor_id', $data)->find();
			$info = $this->Lable_Actor_Read($array);
			$info['role'] = db('role')->alias('r')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where(['role_vid' => $array['vod_id']])->select();
			cache(md5('actor_create_id' . $data), $info, 3600);
		}
		foreach ($info['role'] as $key => $val) {
			$info['role_list'][$key] = $this->Lable_Role_Read($val)['read'];
		}
		$info['role_list_all'] = $this->Lable_Role_list($info['role']);
		$this->assign($info['show']);
		$this->assign($info['read']);
		$this->assign('role_list', $info['role_list']);
		$path = xianyu_url_html('home/actor/read', array('id' => $info['read']['actor_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['actor_cid'], 'dir' => $info['show']['list_dir']));
		$this->assign('url', xianyu_data_url('home/actor/read', array('id' => $info['read']['actor_id'], 'pinyin' => $info['read']['vod_letters'], 'cid' => $info['read']['actor_cid'], 'dir' => $info['show']['list_dir'])));
		$this->buildHtml($path, './', '/' . $info['read']['actor_skin']);
		$msg = '<tr><td> 视频：' . $info['read']['vod_name'] . ' 演员表地址<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
		$urls_count = count($info['role']);
		for ($i = 0; $i < $urls_count; $i++) {
			$this->role_create($info, $pagekey);
			$pagekey++;
		}
		echo $this->show_msg("<tr><td>视频：" . $info['vod']['vod_name'] . " 角色页面生成完成</td></tr>");
		cache(md5('actor_create_id' . $data), NULL);
	}
	public function role_create($data, $page)
	{
		$role = $data['role_list_all'][$page];
		$this->assign($role['show']);
		$this->assign($role['read']);
		$this->assign($data['read']);
		$this->assign('role_list', $data['role_list']);
		$pathrole = xianyu_url_html('home/role/read', array('id' => $role['read']['role_id'], 'pinyin' => $data['read']['vod_letters'], 'cid' => $role['read']['role_cid'], 'dir' => $role['show']['list_dir']));
		$this->assign('url', xianyu_data_url('home/role/read', array('id' => $role['read']['role_id'], 'pinyin' => $data['read']['vod_letters'], 'cid' => $role['read']['role_cid'], 'dir' => $role['show']['list_dir'])));
		$this->buildHtml($pathrole, './', '/' . $role['read']['role_skin']);
		$msg = '<tr><td> 视频：' . $data['read']['vod_name'] . ' 角色' . $role['read']['role_name'] . '地址<a href=\\"' . $pathrole . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $pathrole . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function special_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_special_data'], '专题内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/special_detail_id', ['ids' => $ids, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['special_status'] = array('eq', 1);
		$where['special_id'] = array('in', $ids);
		$infos = db('special')->where($where)->order('special_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			F('_html/next', NULL);
			return $this->jump(admin_url('admin/html/show'), '专题没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->special_create($value);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，专题内容页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function special_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_special_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 10, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_special_data'], '专题内容页', admin_url('admin/html/list_create', array('day' => $day, 'time' => $time, 'sid' => 10, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/special_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['special_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['special_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['special_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['special_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('special')->where($where)->whereTime('special_addtime', $day)->order('special_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('special')->where($where)->order('special_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump));
					return $this->my_detail_cids();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/my_detail_cids', array('day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)), '专题没有需要生成的数据,下一步将生成单页内容...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '专题没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->special_create($value);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->special_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/special_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 10, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 10, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成专题页面分类页面...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，专题页全部生成完毕。');
			}
		}
	}
	public function special_create($data)
	{
		$array_detail = $this->Lable_Special_Read($data);
		$this->assign($array_detail['show']);
		$this->assign($array_detail['read']);
		$this->assign('list_vod', $array_detail['list_vod']);
		$this->assign('list_news', $array_detail['list_news']);
		$this->assign('list_star', $array_detail['list_star']);
		$path = xianyu_url_html('home/special/read', array('id' => $array_detail['read']['special_id'], 'pinyin' => $array_detail['read']['special_letters'], 'cid' => $array_detail['read']['special_cid'], 'dir' => $array_detail['show']['list_dir']));
		$this->assign('url', xianyu_data_url('home/special/read', array('id' => $array_detail['read']['special_id'], 'pinyin' => $array_detail['read']['special_letters'], 'cid' => $array_detail['read']['special_cid'], 'dir' => $array_detail['show']['list_dir'])));
		$this->buildHtml($path, './', '/' . $array_detail['read']['special_skin']);
		$msg = '<tr><td> 专题：' . $array_detail['read']['special_name'] . ' 地址<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function role_detail_id()
	{
		$ids = input('ids/s', '');
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$time = intval(input('time/d', 0));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$this->check($this->config_url['url_role_data'], '角色内容页', admin_url('admin/html/show'));
		F('_html/next', admin_url('admin/html/role_detail_id', ['ids' => $ids, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['role_status'] = array('eq', 1);
		$where['role_id'] = array('in', $ids);
		$infos = db('role')->field('r.*,st.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('r')->join('actor a', 'a.actor_vid = r.role_vid', 'LEFT')->join('vod v', 'v.vod_id = r.role_vid  and v.vod_status=1', 'LEFT')->join('story s', 's.story_vid = r.role_vid  and s.story_status=1', 'LEFT')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where($where)->order('role_addtime desc')->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_id');
		if (!$infos->total()) {
			F('_html/next', NULL);
			return $this->jump(admin_url('admin/html/show'), '角色内容没有需要生成的数据。');
		}
		foreach ($infos as $key => $value) {
			$this->role_create_id($value, $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/role_detail_id', ['ids' => $ids, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
			return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
		} else {
			F('_html/next', NULL);
			echo $this->show_msg("<tr><td>恭喜您，角色页全部生成完毕。</td></tr>");
			echo '<div id=\'close\'></div>';
			exit;
		}
	}
	public function role_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$pagekey = intval(input('pagekey/d', ''));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_role_data']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
			return $this->list_create();
		} else {
			$this->check($this->config_url['url_role_data'], '角色内容页', admin_url('admin/html/list_create', array('day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/role_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'pagekey' => $pagekey, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['role_status'] = array('eq', 1);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['role_cid'] = array('in', getlistarr_tag($cid));
			} else {
				$where['role_cid'] = getlistsqlin($cid);
			}
		}
		if ($time) {
			$where['role_addtime'] = array('egt', $time);
		}
		if (!empty($day)) {
			$infos = db('role')->field('r.*,st.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('r')->join('actor a', 'a.actor_vid = r.role_vid', 'LEFT')->join('vod v', 'v.vod_id = r.role_vid  and v.vod_status=1', 'LEFT')->join('story s', 's.story_vid = r.role_vid  and s.story_status=1', 'LEFT')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where($where)->whereTime('role_addtime', $day)->order('role_addtime desc')->paginate($limit, false, ['page' => $page]);
		} else {
			$infos = db('role')->field('r.*,st.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('r')->join('actor a', 'a.actor_vid = r.role_vid', 'LEFT')->join('vod v', 'v.vod_id = r.role_vid  and v.vod_status=1', 'LEFT')->join('story s', 's.story_vid = r.role_vid  and s.story_status=1', 'LEFT')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where($where)->order('role_addtime desc')->paginate($limit, false, ['page' => $page]);
		}
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '角色内容没有需要生成的数据,准备生成角色分类页面...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '角色内容没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->role_create_id($value, $model, $ids, $cid, $day, $time, $page, $pagekey, $tid, $os, $pwd);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
				return $this->role_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/role_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->list_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/list_create', ['day' => $day, 'time' => $time, 'sid' => 5, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成角色分类页面...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，角色页全部生成完毕。');
			}
		}
	}
	public function role_create_id($data)
	{
		$array_detail = $this->Lable_Role_Read($data);
		$this->assign($array_detail['show']);
		$this->assign($array_detail['read']);
		$path = xianyu_url_html('home/role/read', array('id' => $array_detail['read']['role_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['role_cid'], 'dir' => $array_detail['show']['list_dir']));
		$this->assign('url', xianyu_data_url('home/role/read', array('id' => $array_detail['read']['role_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['role_cid'], 'dir' => $array_detail['show']['list_dir'])));
		$this->buildHtml($path, './', '/' . $array_detail['read']['role_skin']);
		$msg = '<tr><td> 角色：' . $array_detail['read']['role_name'] . '地址<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function my_detail_cids()
	{
		$cid = input('cid/s', '');
		$day = input('day/s', '');
		$time = intval(input('time/d', 0));
		$page = intval(input('page/d', 1));
		$tid = input('tid/d', '');
		$os = input('os/d', '');
		$pwd = htmlspecialchars(input('pwd/s', ''));
		$jump = intval(input('jump/d', ''));
		if ($tid && $os && !$this->config_url['url_my_list']) {
			$this->request->instance()->get(['day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
			return $this->index_create();
		} else {
			$this->check($this->config_url['url_my_list'], '单页', admin_url('admin/html/index_create', array('tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump)));
		}
		F('_html/next', admin_url('admin/html/my_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]));
		$limit = $this->config_url['url_number'];
		$where = array();
		$where['list_status'] = array('eq', 1);
		$where['list_sid'] = array('eq', 8);
		if (!empty($cid)) {
			if (count($cid) > 1) {
				$where['list_id'] = array('in', getlistarr_tag($cid));
			} else {
				$where['list_id'] = getlistsqlin($cid);
			}
		}
		$infos = db('list')->where($where)->paginate($limit, false, ['page' => $page]);
		$pages['recordcount'] = $infos->total();
		$pages['pagecount'] = $infos->lastPage();
		$pages['pageindex'] = $infos->currentPage();
		$this->assign($pages);
		echo $this->fetch('ajax_show');
		if (!$infos->total()) {
			F('_html/next', NULL);
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->index_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/index_create', ['tid' => $tid, 'os' => $os, 'pwd' => $pwd]), '没有需要生成的数据,准备生成角色分类页面...');
				}
			} else {
				return $this->jump(admin_url('admin/html/show'), '没有需要生成的数据。');
			}
		}
		foreach ($infos as $key => $value) {
			$this->my_create($value);
		}
		if ($infos->currentPage() < $infos->lastPage()) {
			if ($tid && $os) {
				$this->request->instance()->get(['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->my_detail_cids();
			} else {
				$jumpurl = admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/my_detail_cids', ['cid' => $cid, 'day' => $day, 'time' => $time, 'page' => $page + 1, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]);
				return $this->jump($jumpurl, '正在释放系统资源' . $this->config_url['url_time'] . '秒后,将继续生成页面');
			}
		} else {
			if ($jump) {
				if ($tid && $os) {
					$this->request->instance()->get(['day' => $day, 'time' => $time, 'tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'page' => 1, 'jump' => $jump]);
					return $this->index_create();
				} else {
					return $this->jump(admin_url(strtolower($this->request->module() . '/' . $this->request->controller()) . '/index_create', ['tid' => $tid, 'os' => $os, 'pwd' => $pwd, 'jump' => $jump]), '稍等一会，准备生成网站首页...');
				}
			} else {
				F('_html/next', NULL);
				return $this->jump(admin_url('admin/html/show'), '恭喜您，单页全部生成完毕。');
			}
		}
	}
	public function my_create($data)
	{
		$channel = $this->Lable_List($Url, $data);
		$this->assign($channel);
		$path = xianyu_url_html('home/my/show', array('id' => $channel['list_id'], 'dir' => $channel['list_dir']));
		$this->assign('url', xianyu_data_url('home/my/show', array('id' => $channel['list_id'], 'dir' => $channel['list_dir'])));
		$this->buildHtml($path, './', '/' . $channel['list_skin']);
		$msg = '<tr><td> 单页：' . $channel['list_name'] . '地址<a href=\\"' . $path . '.' . config('url_html_suffix') . '\\" target=\\"_blank\\">' . $path . '.' . config('url_html_suffix') . '</a> <span class=\\"text-success\\">成功</span></td></tr>';
		echo $this->show_msg($msg);
	}
	public function index_create()
	{
		$jump = intval(input('jump/d', ''));
		$this->assign($this->Lable_Index());
		$this->assign('url', config('site_path'));
		if ($jump) {
			F('_html/next', NULL);
			F('_html/time', time());
			echo $this->fetch('ajax_show');
			$this->buildHtml('index', './', '/zp_index');
			echo $this->show_msg("<tr><td>生成首页成功恭喜您，页面全部生成完毕。</td></tr>");
			exit;
			return true;
		} else {
			$this->buildHtml('index', './', '/zp_index');
			return $this->success('生成首页成功', admin_url('admin/html/show'));
		}
	}
	protected function buildHtml($htmlfile = '', $htmlpath = '', $templateFile = '')
	{
		$path = config('template.view_path', ROOT_PATH . 'tpl' . DS . 'home' . DS) . config('theme.pc_theme') . DS;
		$this->view->config('view_path', $path);
		$content = $this->fetch($templateFile);
		$htmlpath = !empty($htmlpath) ? $htmlpath : './';
		$htmlfile = $htmlpath . $htmlfile . '.' . config('url_html_suffix');
		$File = new \think\template\driver\File();
		$File->write($htmlfile, $content);
		$this->view->config('view_path', './lib/admin/view/');
		return $content;
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}