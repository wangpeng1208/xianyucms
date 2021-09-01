<?php


namespace app\admin\controller;

use app\common\library\Insert;
use app\common\controller\Admin;
class Getstar extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$array_url = array();
		$array_url['action'] = input('action/s', '');
		$array_url['type'] = intval(input('type/d', ''));
		$array_url['p'] = input('p/d', 1);
		$array_url['page'] = $array_url['p'];
		$array_url['sex'] = input('sex/d', '');
		$array_url['xb'] = !empty($array_url['sex']) ? '女' : '男';
		$array_url['starids'] = input('starids/s', '');
		if (input('post.id/a', '')) {
			$array_url['starids'] = implode(',', input('post.id/a', ''));
		}
		$json_data = $this->get_api_data($array_url);
		$where['list_sid'] = 3;
		$where['list_pid'] = 0;
		$pid = db('List')->where($where)->value('list_id');
		$urlarray = config('http_api');
		if ($array_url['action']) {
			$this->assign($json_data['page']);
			echo $this->fetch('ajax_show');
			$add = new Insert();
			foreach ($json_data['liststar'] as $key => $star) {
				$rand = array_rand($urlarray, 1);
				$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/star.php?ids=" . $star['star_id'];
				$datalist = json_decode(xianyu_get_url($apiurl, 3), true);
				$starlist[$key] = $datalist['list'][0];
				$starlist[$key]['star_xb'] = $array_url['xb'];
				$starlist[$key]['star_cid'] = $pid;
				unset($starlist[$key]['star_id']);
				$title = $add->star($starlist[$key]);
				$status = !empty($title['code']) ? "success" : "danger";
				$id = !empty($title['code']) ? " ID:" . $title['code'] : "";
				$msg = '<tr><td><i class=\\"fa fa-circle\\"></i> 第 <span style=\\"color:#F00\\">' . (($array_url['page'] - 1) * $json_data['page']['pagesize'] + $key + 1) . '</span> 条数据 ' . $starlist[$key]['star_name'] . ' <span class=\\"text-' . $status . '\\">' . $title['msg'] . $id . '</span></td></tr>';
				$this->show_msg($msg);
			}
			if ($array_url['page'] < $json_data['page']['pagecount']) {
				$jumpurl = str_replace('xianyupage', $array_url['page'] + 1, $json_data['tpl']['pageurl']);
				F('_get/jumpurl_star', $jumpurl);
				$this->show_msg("<tr><td>" . config('player_collect_time') . "秒后将自动采集下一页!，正在释放服务器资源...</td></tr>");
				echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . $jumpurl . '>';
			} else {
				F('_get/jumpurl_star', NULL);
				$this->show_msg("<tr><td>数据采集完成</td></tr>");
			}
		} else {
			$this->assign($array_url);
			$this->assign($json_data['tpl']);
			$this->assign($json_data['page']);
			$this->assign('jumpurl', F('_get/jumpurl_star'));
			$this->assign('star_list', $json_data['listclass']);
			$this->assign('star_data', $json_data['liststar']);
			return view('data_show');
		}
	}
	public function get_api_data($array_url)
	{
		$co = new \com\Curl();
		$urlarray = config('http_api');
		$rand = array_rand($urlarray, 1);
		$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/star.php";
		$array_tpl['httpurl'] = $apiurl . '?sex=' . $array_url['sex'] . '&p=' . $array_url['page'] . '&ids=' . $array_url['starids'];
		$json = xianyu_get_url($array_tpl['httpurl'], 10);
		if ($json) {
			$json = json_decode($json, true);
			if (!$json['list']) {
				return $this->error("连接资源库成功、但数据格式不正确。");
			}
		} else {
			return $this->error("连接资源库失败、通常为服务器网络不稳定或禁用了采集。");
		}
		if ($json['page']['recordcount']) {
			$array_page = array();
			$array_page['pageindex'] = $json['page']['pageindex'];
			$array_page['pagecount'] = $json['page']['pagecount'];
			$array_page['pagesize'] = $json['page']['pagesize'];
			$array_page['recordcount'] = $json['page']['recordcount'];
		}
		$array_tpl = array();
		$array_url['p'] = 'xianyupage';
		$array_url['apiurl'] = base64_encode(base64_encode($array_url['apiurl']));
		$array_tpl['pageurl'] = url('admin/getstar/index', $array_url);
		$array_tpl['pagelist'] = adminpage($array_page['pageindex'], $array_page['pagecount'], 3, $array_tpl['pageurl'], '');
		if ($json['list']) {
			$array['liststar'] = $json['list'];
		}
		$array['tpl'] = $array_tpl;
		$array['page'] = $array_page;
		$array['listclass'] = $listclass;
		return $array;
	}
	function show_msg($msg, $name = "showmsg")
	{
		echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
		ob_flush();
		flush();
		sleep(0);
	}
}