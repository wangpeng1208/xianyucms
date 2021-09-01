<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Insert;
class Card extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['status'] = input('status/d', '');
		$admin['score'] = input('score/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'card_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['uid']) {
			$where['card_uid'] = $admin['uid'];
		}
		if ($admin['score']) {
			$where['card_score'] = $admin['score'];
		}
		if ($admin['status']) {
			$where['card_status'] = $admin['status'];
		}
		if ($admin['wd']) {
			$where['card_number'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('card')->alias('c')->join('user u', 'u.userid=c.card_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		$count_total = db('card')->sum('card_score');
		$count_use = db('card')->where('card_status', 1)->sum('card_score');
		$count_nouse = db('card')->where('card_status', 0)->sum('card_score');
		Cookie('card_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/card/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$money = [1, 5, 10, 50, 100, 500, 1000];
		$data = array('list' => $list, 'scorelist' => $money, 'count_total' => $count_total, 'count_use' => $count_use, 'count_nouse' => $count_nouse, 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function create()
	{
		$data = array();
		for ($i = 0; $i < intval($_POST['card_num']); $i++) {
			$data[$i]['card_score'] = intval($_POST['card_score']);
			$data[$i]['card_number'] = strtoupper($data[$i]['card_score'] . '#' . md5(uniqid() . rand(1000, 9999)));
			$data[$i]['card_addtime'] = time();
		}
		if (model('Card')->saveAll($data)) {
			action_log('admin/card/create', 'oeders', 1, 1);
			return $this->success('卡密生成成功！');
		} else {
			action_log('admin/card/create', 'oeders', 0, 0);
			return $this->error(model('Card')->getError());
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/card/del', 'card', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$result = db('card')->where('card_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/card/del', 'card', $result, 1);
			return $this->success('删除卡密成功！', Cookie('card_url_forward'));
		} else {
			action_log('admin/card/del', 'card', 0, 0);
			return $this->error('删除卡密失败！');
		}
	}
}