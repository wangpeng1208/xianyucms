<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Insert;
class Get extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function all()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_all');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$limit = config('api_cai_limit');
		$where['vod_cid'] = array('neq', 0);
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$count = db('Vod')->where($where)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$field = "vod_id,vod_name,vod_actor,vod_cid,vod_letters,vod_doubanid";
		$list = db('Vod')->field($field)->where($where)->page($admin['p'])->order('vod_addtime desc')->limit($limit)->select();
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('index');
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$add = new Insert();
		foreach ($list as $key => $val) {
			$msg1 = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $val['vod_id'] . '</font> ' . $val['vod_name'];
			$rand = array_rand($urlarray, 1);
			$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/all.php?name=" . $val['vod_name'];
			$data = xianyu_get_url($apiurl, 5);
			$info = "";
			$info = json_decode($data, true);
			$pid = getlistname($val['vod_id'], 'list_pid');
			$tplid = config('tpl_id');
			if ($pid = $tplid[2] && $info['so']['story']['story_content'] || $info['baidu']['story']['story_content']) {
				if ($info['so']['story']['story_content']) {
					$title = $add->story_add($info['so']['story'], $val['vod_id']);
					$status = !empty($title['code']) ? "success" : "danger";
					$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
					$msg2 = ' 360百科 <span class=\\"text-' . $status . '\\">' . $title['msg'] . '</span>';
				} elseif ($info['baidu']['story']['story_content']) {
					$title = $add->story_add($info['baidu']['story'], $val['vod_id']);
					$status = !empty($title['code']) ? "success" : "danger";
					$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
					$msg2 = ' 百度百科 <span class=\\"text-' . $status . '\\">' . $title['msg'] . '</span>';
				}
			} else {
				$msg2 = ' <font color=red>视频不是电视剧或没有采集到剧情跳过</font>';
			}
			if ($info['so']['actor'] || $info['baidu']['actor']) {
				if ($info['so']['actor']) {
					$title = $add->actor_add($info['so']['actor'], $val['vod_id']);
					$status = !empty($title['code']) ? "success" : "danger";
					$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
					$msg3 = ' 360百科 <span class=\\"text-' . $status . '\\">' . $title['msg'] . '</span>';
				}
				if ($info['baidu']['actor']) {
					$title = $add->actor_add($info['baidu']['actor'], $val['vod_id']);
					$status = !empty($title['code']) ? "success" : "danger";
					$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
					$msg3 = ' 百度百科 <span class=\\"text-' . $status . '\\">' . $title['msg'] . '</span>';
				}
			} else {
				$msg3 = ' <font color=red>没有采集到角色跳过</font>';
			}
			$this->show_msg($msg1 . $msg2 . $msg3 . '</td></tr>');
			sleep(1);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/get/all', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_all', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_all', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/vod/index', [], 302);
		}
	}
	public function doubanid()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_doubanid');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$admin['type'] = input('type', config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'vod_' . $admin['type'] . ' ' . $admin['order'];
		$limit = config('api_cai_limit');
		$field = "vod_id,vod_cid,vod_name,vod_mcid,vod_actor,vod_cid,vod_letters,vod_doubanid";
		$where['vod_doubanid'] = 0;
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$count = db('Vod')->where($where)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$list = db('Vod')->where($where)->field($field)->order($admin['orders'])->limit($limit)->page($admin['p'])->select();
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('index');
		foreach ($list as $key => $value) {
			$rand = array_rand($urlarray, 1);
			$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/newdoubanid.php?name=" . $value['vod_name'] . '&actor=' . $value['vod_actor'];
			$douban = json_decode(xianyu_get_url($apiurl, 5), true);
			if (!empty($douban['id'])) {
				db('Vod')->where('vod_id', $value['vod_id'])->setField('vod_doubanid', $douban['id']);
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . '<span class=\\"text-success\\">采集到豆瓣ID:' . $douban['id'] . '</span></td></tr>';
			} else {
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <font color=red>没有采集到豆瓣ID</font></td></tr>';
			}
			$this->show_msg($msg);
			sleep(1);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/get/doubanid', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_doubanid', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_doubanid', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/vod/index', [], 302);
		}
	}
	public function doubaninfo()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_doubaninfo');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$admin['type'] = input('type', config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'vod_' . $admin['type'] . ' ' . $admin['order'];
		$where['vod_doubanid'] = array('neq', '0');
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$limit = 10;
		$field = "vod_id,vod_cid,vod_name,vod_mcid,vod_actor,vod_cid,vod_letters,vod_doubanid";
		$count = db('Vod')->where($where)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$list = db('Vod')->where($where)->field($field)->order($admin['orders'])->limit($limit)->page($admin['p'])->select();
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('index');
		foreach ($list as $key => $value) {
			$rand = array_rand($urlarray, 1);
			$apiid = "http://" . $urlarray[$rand] . "/xianyucms/vod.php";
			$info = $co->get($apiid, array('url' => $value['vod_doubanid']));
			$doubandata[$key] = json_decode($info, true);
			if (!empty($doubandata[$key])) {
				$vod['vod_aliases'] = $doubandata[$key]['stitle'];
				if (!empty($doubandata[$key]['filmtime'])) {
					$vod['vod_filmtime'] = strtotime($doubandata[$key]['filmtime'] . ' 0:0:0');
				}
				$vod['vod_language'] = $doubandata[$key]['language'];
				$vod['vod_area'] = $doubandata[$key]['area'];
				$vod['vod_year'] = $doubandata[$key]['year'];
				$vod['vod_content'] = $doubandata[$key]['content'];
				if (strpos($doubandata[$key]['pic'], 'default_large.png') === false) {
					$vod['vod_pic'] = $doubandata[$key]['pic'];
				}
				$vod['vod_total'] = $doubandata[$key]['total'];
				$vod['vod_director'] = $doubandata[$key]['director'];
				$vod['vod_actor'] = $doubandata[$key]['actor'];
				$vod['vod_gold'] = $doubandata[$key]['gold'];
				$vod['vod_golder'] = $doubandata[$key]['golder'];
				$vod['vod_mcid'] = get_mcid($value['vod_cid'], $doubandata[$key]['mcid']) . "," . $value['vod_mcid'];
				mcid_update($value['vod_id'], $vod['vod_mcid'], 1);
				$id = db('Vod')->where('vod_id', $value['vod_id'])->update($vod);
				if ($id) {
					$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . '<span class=\\"text-success\\">更新豆瓣资料成功</span></td></tr>';
				} else {
					$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <font color=red>更新豆瓣资料失败跳过</font></td></tr>';
				}
			} else {
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <font color=red>没有采集到豆瓣资料</font></td></tr>';
			}
			$this->show_msg($msg);
			sleep(1);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/get/doubaninfo', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_doubaninfo', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_doubaninfo', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/vod/index', [], 302);
		}
	}
	public function star()
	{
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$rand = array_rand($urlarray, 1);
		$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/minxing.php";
		$url = urlencode(input('url/s', ''));
		if ($url) {
			$data = $co->get($apiurl, array('url' => $url));
			if ($data) {
				$d = json_decode($data, true);
				echo $data;
			}
		}
	}
	public function keywords($key = 4)
	{
		$co = new \com\Curl();
		$name = input('post.name', '');
		$urlarray = config('http_api');
		$rand = array_rand($urlarray, 1);
		$geturl = "http://" . $urlarray[$rand] . "/xianyucms/seo.php?name=" . $name . "&k=" . $key;
		$data = xianyu_get_url($geturl, 5);
		if ($data) {
			return $data;
		} else {
			return false;
		}
	}
	public function vod()
	{
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$rand = array_rand($urlarray, 1);
		$url = urlencode(input('url/s', ''));
		if ($url) {
			$geturl = "http://" . $urlarray[$rand] . "/xianyucms/vod.php?url=" . $url;
			$data = xianyu_get_url($geturl, 5);
			if ($data) {
				$d = json_decode($data, true);
				echo $data;
			}
		}
	}
	public function story()
	{
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$rand = array_rand($urlarray, 1);
		$url = urlencode(input('url/s', ''));
		if ($url) {
			$geturl = "http://" . $urlarray[$rand] . "/xianyucms/newstory.php?url=" . $url;
			$data = xianyu_get_url($geturl, 5);
			if ($data) {
				echo $data;
			}
		}
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}