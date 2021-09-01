<?php


namespace app\admin\controller;

use think\Db;
use app\common\library\Insert;
use app\common\controller\Admin;
class Getstory extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function tvmao()
	{
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_tvmao');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$limit = config('api_cai_limit');
		$tplid = config('tpl_id');
		$where['vod_cid'] = getlistsqlin($tplid[2]);
		$field = "vod_id,vod_name,vod_actor,vod_cid,vod_year,vod_letters,vod_doubanid";
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$count = Db::name('Vod')->where($where)->count('vod_id');
		$totalpages = ceil($count / $limit);
		$list = Db::name('Vod')->field($field)->where($where)->order('vod_addtime desc')->limit($limit)->page($admin['p'])->select();
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('get/index');
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$add = new Insert();
		foreach ($list as $key => $value) {
			$rand = array_rand($urlarray, 1);
			$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/tvmaostory.php";
			$data = $co->get($apiurl, array('name' => urlencode($value['vod_name']), 'actor' => urlencode(format_vodname($value['vod_actor'])), 'year' => $value['vod_year']));
			$storyinfo = json_decode($data, true);
			if (!empty($storyinfo) && !empty($storyinfo['story_content'])) {
				$title = $add->story_add($storyinfo, $value['vod_id']);
				$status = !empty($title['code']) ? "success" : "danger";
				$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <span class=\\"text-' . $status . '\\">TV猫' . $title['msg'] . $id . '</span></td></tr>';
			} else {
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <font color=red>没有采集到TV猫剧情跳过</font></td></tr>';
			}
			$this->show_msg($msg);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/getstory/tvmao', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_tvmao', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_tvmao', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/story/index', [], 302);
		}
	}
	public function up()
	{
		$admin['p'] = input('p', 1);
		$admin['p'] = input('p', 1);
		$admin['d'] = input('d', 0);
		if ($admin['p'] == 1 && empty($admin['d'])) {
			$jumpurl = F('_get/jumpurl_storyup');
			if ($jumpurl) {
				return $this->success('上次存在未完成任务', $jumpurl);
			}
		}
		$limit = config('api_cai_limit');
		$tplid = config('tpl_id');
		$where['story_url'] = array('neq', '');
		$where['story_cont'] = 0;
		if ($admin['d']) {
			$where['vod_addtime'] = array('gt', getxtime($admin['d']));
		}
		$count = Db::name('Story')->alias('s')->field('s.*,vod_id,vod_cid,vod_name')->join('vod v', 'v.vod_id=s.story_vid', 'LEFT')->where($where)->count('story_id');
		$totalpages = ceil($count / $limit);
		$list = Db::name('story')->alias('s')->field('s.*,vod_id,vod_cid,vod_name')->join('vod v', 'v.vod_id=s.story_vid', 'LEFT')->where($where)->order('vod_addtime desc')->limit($limit)->page($admin['p'])->select();
		$this->setNav();
		$page['recordcount'] = $count;
		$page['pagecount'] = $totalpages;
		$page['pageindex'] = $admin['p'];
		$page['pagesize'] = $limit;
		$this->assign($page);
		echo $this->fetch('get/index');
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$add = new Insert();
		foreach ($list as $key => $value) {
			$rand = array_rand($urlarray, 1);
			$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/newstory.php?url=" . $value['story_url'];
			$data = xianyu_get_url($apiurl);
			$storyinfo = json_decode($data, true);
			if (!empty($storyinfo) && !empty($storyinfo['story_content'])) {
				$title = $add->story_add($storyinfo, $value['vod_id']);
				$status = !empty($title['code']) ? "success" : "danger";
				$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <span class=\\"text-' . $status . '\\">' . $title['msg'] . $id . '</span></td></tr>';
			} else {
				$msg = '<tr><td>第<font color=red>' . (($page['pageindex'] - 1) * $page['pagesize'] + $key + 1) . '</font>个视频 ID:<font color=green>' . $value['vod_id'] . '</font> ' . $value['vod_name'] . ' <font color=red>没有采集到剧情跳过</font></td></tr>';
			}
			$this->show_msg($msg);
		}
		if ($totalpages > $admin['p']) {
			$jumpurl = url('admin/getstory/up', array('p' => $admin['p'] + 1, 'd' => $admin['d']));
			F('_get/jumpurl_storyup', $jumpurl);
			$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页，正在释放服务器资源...</td></tr>");
			echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
		} else {
			F('_get/jumpurl_storyup', NULL);
			$this->show_msg("<tr><td>恭喜您，所有采集任务已经完成！</td></tr>");
			return $this->redirect('admin/story/index', [], 302);
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