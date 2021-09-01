<?php


namespace app\admin\controller;

use think\validate;
use app\common\library\Insert;
use app\common\controller\Admin;
class Collect extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

		$this->assign($this->config_url);
	}
	public function index($cid = 1)
	{
		$where['collect_cid'] = $cid;
		$model = list_search(F('_data/modellist'), 'id=' . $cid);
		$lists = db('collect')->order('collect_oid asc')->select();
		$httpdata = cache('xianyu_collect');
		if (empty($httpdata)) {
			$httpdata = xianyu_get_url('http://yanzheng.97bike.com/xml/collect.json', 5);
			$httpdata = json_decode($httpdata, true);
			cache('xianyu_collect', $httpdata, 18000);
		}
		if ($httpdata) {
			$newslist = array_merge($httpdata, $lists);
			F('_collect/list', $newslist);
			$list = list_search($newslist, 'collect_cid=' . $cid);
		} else {
			$list = list_search($lists, 'collect_cid=' . $cid);
		}
		$data = array('list' => $list);
		$model = list_search(F('_data/modellist'), 'id=' . $cid);
		$this->assign('jumpurl', F('_collect/xucai'));
		$this->assign('cid', $cid);
		$this->assign('model', $model[0]);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'collect');
			if (true !== $result) {
				action_log('add_collect', 'collect', 1, session('member_auth.uid'), 0);
				return $this->error($result);
			}
			$result = db('collect')->insertGetId($data);
			if ($result) {
				action_log('admin/collect/add', 'collect', $result, 1);
				return $this->success('添加采集资源成功！', url('admin/collect/index', array('cid' => $data['collect_cid'])));
			} else {
				action_log('admin/collect/add', 'collect', 0, 0);
				return $this->error('添加采集资源失败！');
			}
		} else {
			$info['collect_oid'] = db('collect')->max('collect_oid') + 1;
			$data = array('info' => $info);
			$this->assign($data);
			$this->setNav();
			return view('edit');
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'collect');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('collect')->where('collect_id', $id)->update($data);
			if ($result) {
				action_log('admin/collect/edit', 'collect', $id, 1);
				return $this->success('编辑采集资源成功！', url('admin/collect/index', array('cid' => $data['collect_cid'])));
			} else {
				action_log('admin/collect/edit', 'collect', 0, 0);
				return $this->error('编辑采集资源失败！');
			}
		} else {
			$info = db('collect')->where('collect_id', $id)->find();
			$data = array('info' => $info);
			$this->assign($data);
			$this->setNav();
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/collect/del', 'collect', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['collect_id'] = array('IN', $id);
		$result = db('collect')->where($map)->delete();
		if ($result) {
			action_log('admin/collect/del', 'collect', $id, 1);
			return $this->success('删除采集资源成功！');
		} else {
			action_log('admin/collect/del', 'collect', 0, 0);
			return $this->error('删除采集资源失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/collect/status', 'collect', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$message = !$status ? '正常' : '锁定';
		$map['collect_id'] = array('IN', $id);
		$result = db('collect')->where($map)->setField('collect_status', $status);
		if ($result !== false) {
			action_log('admin/collect/status', 'collect', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/collect/status', 'collect', 0, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function data()
	{
		$array_url = array();
		$array_url['os'] = intval(input('os/d', ''));
		$array_url['tid'] = intval(input('tid/d', ''));
		$array_url['pwd'] = input('pwd/s');
		$array_url['apiid'] = intval(input('apiid'));
		$collect = list_search(F('_collect/list'), 'collect_id=' . $array_url['apiid']);
		$model = list_search(F('_data/modellist'), 'id=' . $collect[0]['collect_cid']);
		$array_url['type'] = intval($collect[0]['collect_type']);
		$array_url['apiurl'] = $collect[0]['collect_url'];
		$array_url['status'] = $collect[0]['collect_status'];
		$array_url['other'] = $collect[0]['collect_other'];
		if ($collect[0]['collect_cid'] == 99) {
			$array_url['model'] = 'cm';
			$model[0]['title'] = "评论";
		} else {
			$array_url['model'] = $model[0]['name'];
		}
		$array_url['action'] = input('action/s', '');
		$array_url['again'] = intval(input('again/d', 0));
		if (input('id/a', '')) {
			$array_url['vodids'] = implode(',', input('id/a', ''));
		}
		$array_url['cid'] = input('cid/d', '');
		$array_url['wd'] = input('wd/s', '');
		$array_url['h'] = intval(input('h/d', ''));
		$array_url['p'] = input('p/d', 1);
		$array_url['inputer'] = input('inputer', '');
// 		print_r($array_url);
		$this->assign($collect[0]);
		$this->assign('model', $model[0]);
		if ($collect[0]['collect_cid'] == 1) {
			if ($this->vod_caiji($array_url)) {
				return $this->fetch('vod_show');
			}
		} else {
			if ($this->oeder_caiji($array_url)) {
				return $this->fetch($array_url['model'] . '_show');
			}
		}
	}
	public function oeder_caiji($array_url)
	{
		$json_data = $this->get_api_order_json($array_url);
		if (in_array($array_url['action'], array('ids', 'day', 'all', 'week'))) {
			$this->assign($json_data['page']);
			if (!$array_url['tid'] || $array_url['tid'] && $array_url['p'] <= 1 || $array_url['tid'] && !$array_url['os']) {
				echo $this->fetch('ajax_show');
			}
			if ($json_data['listdata']) {
				$add = new Insert();
				$insert = $array_url['model'];
				foreach ($json_data['listdata'] as $key => $data) {
					$title = $add->{$insert}($data);
					$status = !empty($title['code']) ? "success" : "danger";
					$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
					$msg = '<tr><td><i class=\\"fa fa-circle\\"></i> 第 <span style=\\"color:#F00\\">' . (($array_url['p'] - 1) * $json_data['page']['pagesize'] + $key + 1) . '</span> 条数据 ' . $list_name[$key] . ' ' . $data['vod_name'] . $data[$array_url['model'] . '_name'] . ' <span class=\\"text-' . $status . '\\">' . $title['msg'] . $id . '</span></td></tr>';
					$this->show_msg($msg);
				}
			}
			if (in_array($array_url['action'], array('ids', 'day', 'week', 'all'))) {
				if ($array_url['p'] < $json_data['page']['pagecount']) {
					$jumpurl = str_replace('xianyupage', $array_url['p'] + 1, $json_data['tpl']['pageurl']);
					F('_collect/xucai', $jumpurl);
					if ($array_url['tid'] && $array_url['os']) {
						$array_url['p'] = $array_url['p'] + 1;
						return $this->oeder_caiji($array_url);
					} else {
						$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页!，正在释放服务器资源...</td></tr>");
						echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
					}
				} else {
					F('_collect/xucai', NULL);
					$this->show_msg("<tr><td>数据采集完成</td></tr>");
				}
			}
		} else {
			$array_url['vodids'] = '';
			$this->assign($array_url);
			$this->assign($json_data['tpl']);
			$this->assign($json_data['page']);
			$this->assign('list', $json_data['listclass']);
			$this->assign('data', $json_data['listdata']);
			$this->setNav();
			return 1;
		}
	}
	public function vod_caiji($array_url)
	{
		if ($array_url['type'] == 1) {
			$json_data = $this->get_api_xml($array_url);
		} else {
			$json_data = $this->get_api_json($array_url);
		}
		if (in_array($array_url['action'], array('ids', 'day', 'all', 'week'))) {
          //halt($json_data);
			$this->assign($json_data['page']);
			if (!$array_url['tid'] || $array_url['tid'] && $array_url['p'] <= 1 || $array_url['tid'] && !$array_url['os']) {
				echo $this->fetch('ajax_show');
			}
			if ($json_data['listdata']) {
				$rs = model('Collect');
				foreach ($json_data['listdata'] as $key => $vod) {
					$array_vod_play = explode('$$$', $vod['vod_play']);
					$array_vod_url = explode('$$$', $vod['vod_url']);
					$list_name[$key] = '[' . getlistname($vod['vod_cid']) . ']';
					$return = $rs->vod_insert($vod, config('force_collect'), $array_url['status']);
					$status = !empty($return['code']) ? "success" : "danger";
					$id = !empty($return['code']) ? " ID:" . $return['code'] : "";
					$msg = '<tr><td><i class=\\"fa fa-circle\\"></i> 第 <span style=\\"color:#F00\\">' . (($array_url['p'] - 1) * $json_data['page']['pagesize'] + $key + 1) . '</span> 条数据 ' . $list_name[$key] . ' ' . $vod['vod_name'] . '  共有' . count($array_vod_play) . '组播放器  <span class=\\"text-' . $status . '\\">' . $return['msg'] . $id . '</span></td></tr>';
					$this->show_msg($msg);
				}
			}
			if (in_array($array_url['action'], array('ids', 'day', 'week', 'all'))) {
				if ($array_url['p'] < $json_data['page']['pagecount']) {
					$jumpurl = str_replace('xianyupage', $array_url['p'] + 1, $json_data['tpl']['pageurl']);
					F('_collect/xucai', $jumpurl);
					if ($array_url['tid'] && $array_url['os']) {
						$array_url['p'] = $array_url['p'] + 1;
						return $this->vod_caiji($array_url);
					} else {
						$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页!，正在释放服务器资源...</td></tr>");
						echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
					}
				} else {
					F('_collect/xucai', NULL);
					$this->show_msg("<tr><td>数据采集完成</td></tr>");
					die;
				}
			}
		} else {
			$this->assign($array_url);
			$this->assign($json_data['tpl']);
			$this->assign($json_data['page']);
			$this->assign('list', $json_data['listclass']);
			$this->assign('data', $json_data['listdata']);
			$this->setNav();
			return 1;
		}
	}
	public function get_api_xml($array_url)
	{
		$array_tpl['httpurl'] = '&wd=' . urldecode($array_url['wd']) . '&t=' . $array_url['cid'] . '&h=' . $array_url['h'] . '&inputer=' . $array_url['inputer'] . '&ids=' . $array_url['vodids'] . '&pg=' . $array_url['p'] . '&' . $array_url['other'];
		if ($array_url['action'] && $array_url['action'] != "show") {
			$array_tpl['httpurl'] = str_replace('?ac=list', '?ac=videolist', url_repalce($array_url['apiurl'])) . $array_tpl['httpurl'];
		} else {
			$array_tpl['httpurl'] = url_repalce($array_url['apiurl']) . $array_tpl['httpurl'];
		}
		$xml_data = xianyu_get_url($array_tpl['httpurl'], 10);
		if (!$xml_data) {
			return $this->error("连接资源库失败，通常为服务器网络不稳定或禁用了采集。");
		}
		$xml = @simplexml_load_string($xml_data);
		if (is_null($xml)) {
			return $this->error("XML格式不正确，不支持采集。");
		}
		preg_match('<list page="([0-9]+)" pagecount="([0-9]+)" pagesize="([0-9]+)" recordcount="([0-9]+)">', $xml_data, $page_array);
		$array_page = array();
		$array_page['pageindex'] = $page_array[1];
		$array_page['pagecount'] = $page_array[2];
		$array_page['pagesize'] = $page_array[3];
		$array_page['recordcount'] = $page_array[4];
		preg_match_all('/<ty id="([0-9]+)">([\\s\\S]*?)<\\/ty>/', $xml_data, $list_array);
		foreach ($list_array[1] as $key => $value) {
			$listclass[$key]['list_id'] = $value;
			$listclass[$key]['list_name'] = $list_array[2][$key];
			$listclass[$key]['bind_id'] = $array_url['apiid'] . '_' . $listclass[$key]['list_id'];
		}
		$array_tpl = array();
		$array_url['p'] = 'xianyupage';
		$array_url['apiurl'] = "";
		$array_url['type'] = "";
		$array_url['status'] = "";
		$array_url['other'] = "";
		$array_tpl['pageurl'] = url(strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action()), $array_url);
		$array_tpl['pagelist'] = adminpage($array_page['pageindex'], $array_page['pagecount'], 3, $array_tpl['pageurl'], '');
		$listvod = array();
		$key = 0;
		if ($xml->list->video) {
			foreach ($xml->list->video as $video) {
				$listvod[$key]['vod_addtime'] = (string) $video->last;
				$listvod[$key]['vod_id'] = (string) $video->id;
				$listvod[$key]['vod_tid'] = (string) $video->tid;
				$listvod[$key]['vod_cid'] = intval(collect_bind_id($array_url['apiid'] . '_' . $listvod[$key]['vod_tid']));
				$listvod[$key]['vod_name'] = (string) $video->name;
				$listvod[$key]['list_name'] = (string) $video->type;
				$listvod[$key]['vod_pic'] = (string) $video->pic;
				$listvod[$key]['vod_keywords'] = format_vodactor((string) $video->keywords);
				$listvod[$key]['vod_total'] = 0;
				$listvod[$key]['vod_filmtime'] = (string) $video->filmtime;
				$listvod[$key]['vod_tv'] = (string) $video->tv;
				$listvod[$key]['vod_diantai'] = format_vodactor((string) $video->diantai);
				$listvod[$key]['vod_aliases'] = (string) $video->aliases;
				$listvod[$key]['vod_language'] = (string) $video->lang;
				$listvod[$key]['vod_area'] = (string) $video->area;
				$listvod[$key]['vod_year'] = (string) $video->year;
				$listvod[$key]['vod_continu'] = (string) $video->state;
				$listvod[$key]['vod_status'] = 1;
				$listvod[$key]['vod_doubanid'] = 0;
				$listvod[$key]['vod_actor'] = format_vodactor((string) $video->actor);
				$listvod[$key]['vod_director'] = format_vodactor((string) $video->director);
				$listvod[$key]['vod_content'] = (string) $video->des;
				$listvod[$key]['vod_inputer'] = $array_url['apiid'] . '_' . $listvod[$key]['vod_id'];
				$listvod[$key]['vod_reurl'] = (string) $video->reurl;
				$listvod[$key]['vod_title'] = (string) $video->note;
				if (!$listvod[$key]['vod_reurl']) {
					$listvod[$key]['vod_reurl'] = $listvod[$key]['vod_inputer'];
				}
				$vod_play = array();
				$vod_url = array();
				if ($count = count($video->dl->dd)) {
					for ($i = 0; $i < $count; $i++) {
						$vod_play[$i] = (string) $video->dl->dd[$i]['flag'];
						$vod_url[$i] = $this->xml_url_replace((string) $video->dl->dd[$i]);
					}
				} else {
					$vod_play[] = (string) $video->dt;
				}
				$listvod[$key]['vod_play'] = implode('$$$', $vod_play);
				$listvod[$key]['vod_url'] = implode('$$$', $vod_url);
				$key++;
			}
		}
		$array['tpl'] = $array_tpl;
		$array['page'] = $array_page;
		$array['listclass'] = $listclass;
		$array['listdata'] = $listvod;
		return $array;
	}
	public function get_api_order_json($array_url)
	{
		$http_url = str_replace('@', '-', $array_url['apiurl'] . '-action-' . $array_url['action'] . '-ids-' . $array_url['vodids'] . '-cid-' . $array_url['cid'] . '-inputer-' . $array_url['inputer'] . '-wd-' . urlencode($array_url['wd']) . '-h-' . $array_url['h'] . '-p-' . $array_url['p'] . '-' . $array_url['other']);
		$json = xianyu_get_url($http_url, 10);
		if ($json) {
			$json = json_decode($json, true);
			if (!$json['list']) {
				return $this->error("连接资源库成功、但数据格式不正确。");
			}
		} else {
			return $this->error("连接资源库失败、通常为服务器网络不稳定或禁用了采集。");
		}
		$array_page = array();
		$array_page['pageindex'] = $json['page']['pageindex'];
		$array_page['pagecount'] = $json['page']['pagecount'];
		$array_page['pagesize'] = $json['page']['pagesize'];
		$array_page['recordcount'] = $json['page']['recordcount'];
		$array_list = array();
		foreach ($json['list'] as $key => $value) {
			$array_list[$key]['list_id'] = $value['list_id'];
			$array_list[$key]['list_name'] = $value['list_name'];
			$array_list[$key]['bind_id'] = $array_url['apiid'] . '_' . $array_list[$key]['list_id'];
		}
		$array_tpl = array();
		$array_url['p'] = 'xianyupage';
		$array_url['apiurl'] = "";
		$array_url['type'] = "";
		$array_url['status'] = "";
		$array_url['other'] = "";
		$array_tpl['pageurl'] = url(strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action()), $array_url);
		$array_tpl['pagelist'] = adminpage($array_page['pageindex'], $array_page['pagecount'], 3, $array_tpl['pageurl'], '');
		if ($json['data']) {
			foreach ($json['data'] as $key => $value) {
				$json['data'][$key][$array_url['model'] . '_cid'] = intval(collect_bind_id($array_url['apiid'] . '_' . $value[$array_url['model'] . '_cid']));
				$json['data'][$key][$array_url['model'] . '_name'] = htmlspecialchars_decode($value[$array_url['model'] . '_name']);
			}
		}
		$array['tpl'] = $array_tpl;
		$array['page'] = $array_page;
		$array['listclass'] = $array_list;
		$array['listdata'] = $json['data'];
		return $array;
	}
	public function get_api_json($array_url)
	{
		if (strpos($array_url['apiurl'], '?') !== false) {
			$http_url = str_replace('@', '-', $array_url['apiurl'] . '-action-' . $array_url['action'] . '-vodids-' . $array_url['vodids'] . '-cid-' . $array_url['cid'] . '-inputer-' . $array_url['inputer'] . '-wd-' . urlencode($array_url['wd']) . '-h-' . $array_url['h'] . '-p-' . $array_url['p'] . '-' . $array_url['other']);
		} else {
			$api_url['apiurl'] = $array_url['apiurl'];
			unset($array_url['apiurl']);
			$http_url = str_replace(array('other=', '%3D'), array('', '='), $api_url['apiurl'] . '?' . http_build_query($array_url, '-'));
		}
		$json = xianyu_get_url($http_url, 10);
		if ($json) {
			$json = json_decode($json, true);
			if (!$json['list']) {
				return $this->error("连接资源库成功、但数据格式不正确。");
			}
		} else {
			return $this->error("连接资源库失败、通常为服务器网络不稳定或禁用了采集。");
		}
		$array_page = array();
		$array_page['pageindex'] = $json['page']['pageindex'];
		$array_page['pagecount'] = $json['page']['pagecount'];
		$array_page['pagesize'] = $json['page']['pagesize'];
		$array_page['recordcount'] = $json['page']['recordcount'];
		$array_list = array();
		foreach ($json['list'] as $key => $value) {
			$array_list[$key]['list_id'] = $value['list_id'];
			$array_list[$key]['list_name'] = $value['list_name'];
			$array_list[$key]['bind_id'] = $array_url['apiid'] . '_' . $array_list[$key]['list_id'];
		}
		$array_tpl = array();
		$array_url['p'] = 'xianyupage';
		$array_url['apiurl'] = "";
		$array_url['type'] = "";
		$array_url['status'] = "";
		$array_url['other'] = "";
		$array_tpl['pageurl'] = url(strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action()), $array_url);
		$array_tpl['pagelist'] = adminpage($array_page['pageindex'], $array_page['pagecount'], 3, $array_tpl['pageurl'], '');
		if ($json['data']) {
			foreach ($json['data'] as $key => $value) {
				$json['data'][$key]['vod_cid'] = intval(collect_bind_id($array_url['apiid'] . '_' . $value['vod_cid']));
				$json['data'][$key]['vod_name'] = htmlspecialchars_decode($value['vod_name']);
				$json['data'][$key]['vod_actor'] = format_vodactor($value['vod_actor']);
				$json['data'][$key]['vod_director'] = format_vodactor($value['vod_director']);
				$json['data'][$key]['vod_inputer'] = $array_url['apiid'] . '_' . $value['vod_id'];
				if (!$json['data'][$key]['vod_reurl']) {
					$json['data'][$key]['vod_reurl'] = $array_url['apiid'] . '_' . $value['vod_id'];
				}
			}
		}
		$array['tpl'] = $array_tpl;
		$array['page'] = $array_page;
		$array['listclass'] = $array_list;
		$array['listdata'] = $json['data'];
		return $array;
	}
	public function setbind()
	{
		$bind['bind'] = input('bind/s', '');
		$cid = input('cid/s', '');
		$sid = input('sid/s', '');
		$list = db('List')->field('list_id,list_pid,list_sid,list_name')->where('list_sid', $sid)->order('list_id asc')->select();
		foreach ($list as $key => $value) {
			if (!getlistson($list[$key]['list_id'])) {
				unset($list[$key]);
			}
		}
		$array_bind = F('_collect/bind');
		$this->assign('list_cid', $array_bind[$bind['bind']]);
		$this->assign('list', $list);
		$this->assign('cid', $cid);
		$this->assign($bind);
		return view('setbind');
	}
	public function insertbind()
	{
		if ('IS_AJAX') {
			$bind = trim(input('bind', ''));
			$cid = intval(input('cid/d', ''));
			$bindcache = F('_collect/bind');
			if (!is_array($bindcache)) {
				$bindcache = array();
				$bindcache['1_1'] = 0;
			}
			$bindkey = $bind;
			$bindinsert[$bindkey] = $cid;
			$bindarray = array_merge($bindcache, $bindinsert);
			F('_collect/bind', $bindarray);
			if (!empty($bind) && !empty($cid)) {
				return json(array("code" => 1, "msg" => "绑定成功"));
			}
			if (empty($cid) && !empty($bind)) {
				return json(array("code" => 2, "msg" => "取消成功"));
			} else {
				return json(array('code' => 0, 'msg' => '绑定失败'));
			}
		}
	}
	public function xml_url_replace($playurl)
	{
		$array_url = array();
		$arr_ji = explode('#', str_replace('||', '//', $playurl));
		foreach ($arr_ji as $key => $value) {
			$urlji = explode('$', $value);
			if (count($urlji) > 1) {
				$array_url[$key] = $urlji[0] . '$' . trim($urlji[1]);
			} else {
				$array_url[$key] = trim($urlji[0]);
			}
		}
		return implode(chr(13), $array_url);
	}
	public function win()
	{
		$list = F('_collect/list');
		$data = array('list' => $list);
		$this->setNav();
		$this->assign('jumpurl', F('_collect/xucai'));
		$this->assign($data);
		return view('time_win', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function wait()
	{
		$infos = db('list')->field('list_id')->where('list_sid=1')->order('list_id asc')->select();
		foreach ($infos as $key => $value) {
			$list[$key] = $value['list_id'];
		}
		$this->assign('list_vod_all', implode(',', $list));
		$array = $_REQUEST['ds'];
		$array['min'] = $array['caiji'] + $array['create'];
		$this->assign($array);
		return view('time_wait', [], ['</body>' => config('xianyucms.copyright')]);
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}