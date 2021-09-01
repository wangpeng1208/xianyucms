<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Getcm extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function douban()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_douban');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$admin['type'] = input('type', config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'vod_' . $admin['type'] . ' ' . $admin['order'];
		$limit = 5;
		$field = "vod_id,vod_cid,vod_name,vod_mcid,vod_actor,vod_cid,vod_letters,vod_doubanid";
		$where['vod_doubanid'] = array('neq', '0');
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$count = db('Vod')->where($where)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$list = db('Vod')->where($where)->field($field)->order($admin['orders'])->limit($limit)->page($admin['p'])->select();
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('get/index');
		$co = new \com\Curl();
		$urlarray = config('http_api');
		echo $urlarray;
		foreach ($list as $key => $value) {
			if ($value['vod_doubanid']) {
				$title = model('Cm')->douban($value['vod_doubanid'], $value['vod_id']);
				$status = !empty($title['code']) ? "success" : "danger";
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <span class=\\"text-' . $status . '\\">' . $title['msg'] . '</span></td></tr>';
			}
			$this->show_msg($msg);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/getcm/douban', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_douban', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_douban', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/cm/index', [], 302);
		}
	}
	public function mianbao()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_mianbao');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$admin['type'] = input('type', config('admin_order_type'));
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = 'vod_' . $admin['type'] . ' ' . $admin['order'];
		$limit = config('api_cai_limit');
		$where['reid'] = array('neq', '0');
		$whereOr['vod_reurl'] = array('like', '%http://www.mianbao%');
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$list = db('Vod')->field('vod', 'vod_id,vod_name,vod_actor,vod_cid,vod_letters,reid,HasGetComment,vod_reurl')->where($where)->whereOr($whereOr)->page($admin['p'])->order($admin['orders'])->limit($limit)->select();
		$count = db('Vod')->where($where)->whereOr($whereOr)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('get/index');
		$co = new \com\Curl();
		$urlarray = config('http_api');
		foreach ($list as $key => $value) {
			if ($value['reid']) {
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <span class=\\"text-success\\">' . model('Cm')->mianbao($value['vod_id'], $value['reid'], "", $p) . '</span></td></tr>';
			} else {
				$url = $this->mianbaourl($value['vod_reurl']);
				if ($url) {
					$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <span class=\\"text-success\\">' . model('Cm')->mianbao($value['vod_id'], "", $url, $p) . '</span></td></tr>';
				}
			}
			$this->show_msg($msg);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/getcm/mianbao', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_mianbao', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_mianbao', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/cm/index', [], 302);
		}
	}
	public function mianbaourl($url)
	{
		if ($url) {
			$urlarray = explode(',', $url);
			foreach ($urlarray as $key => $value) {
				if (strpos($value, 'mianbao') !== false) {
					$urlarray[] = $value;
					break;
				}
			}
			return $urlarray[0];
		} else {
			return false;
		}
	}
	public function getdouban()
	{
		$id = intval(input('id/d', 0));
		$vid = intval(input('vid/d', 0));
		$where['vod_id'] = $vid;
		$data = model('Cm')->douban($id, $vid);
		db('Vod')->where('vod_id', $vid)->setField('HasGetComment', 1);
		if ($data['code']) {
			return $this->success($data['msg']);
		} else {
			return $this->error($data['msg']);
		}
	}
	public function getmianbao()
	{
		$id = intval(input('id/d', 0));
		$vid = intval(input('vid/d', 0));
		$url = str_replace('@', '/', input('url/s', ""));
		$where['vod_id'] = $vid;
		$data = model('Cm')->mianbao($vid, $id, $url, $p);
		db('Vod')->where('vod_id', $vid)->setField('HasGetComment', 1);
		return $data;
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}