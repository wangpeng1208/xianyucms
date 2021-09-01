<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Insert;
class Score extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['stype'] = input('stype/d', '');
		$admin['view'] = input('view/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'score_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['uid']) {
			$where['score_uid'] = $admin['uid'];
		}
		if ($admin['stype']) {
			$where['score_type'] = $admin['stype'];
		}
		if ($admin['wd']) {
			$where['nickname|username|score_type|score_ext'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('score')->alias('s')->join('user u', 'u.userid=s.score_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('score_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/score/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$type = [1 => '注册赠送', 2 => '影币充值', 3 => '付费点播', 4 => 'VIP升级', 5 => '管理员操作', 6 => '影币返还', 7 => '管理补单', 8 => '卡密充值', 9 => '推广积分', 10 => '注册赠送VIP', 11 => '评论赠送'];
		$data = array('list' => $list, 'types' => $type, 'page' => $pages);
		$this->assign($admin);
		$this->assign($data);
		if ($admin['uid'] && $admin['view'] == 1) {
			return view('user_show');
		} else {
			$this->setNav();
			return view('index', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/score/del', 'score', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$result = db('score')->where('score_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/score/del', 'score', $result, 1);
			return $this->success('删除消费记录成功！', Cookie('score_url_forward'));
		} else {
			action_log('admin/score/del', 'score', 0, 0);
			return $this->error('删除消费记录失败！');
		}
	}
}