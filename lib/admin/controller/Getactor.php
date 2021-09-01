<?php


namespace app\admin\controller;

use app\common\library\Insert;
use app\common\controller\Admin;
class Getactor extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function role()
	{
		$urlarray = config('http_api');
		$vid = intval(input('vid/d', 0));
		$url = str_replace('@', '/', input('url/s', ""));
		$rand = array_rand($urlarray, 1);
		$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/actor.php?url=" . $url;
		$data = xianyu_get_url($apiurl, 3);
		$data = json_decode($data, true);
		$add = new Insert();
		$status = $add->actor_add($data['actor'], $vid);
		if ($status['code']) {
			return $this->success($status['msg'], Cookie('actor_role_url_forward'));
		} else {
			return $this->error($status['msg']);
		}
	}
}