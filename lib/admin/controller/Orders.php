<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use app\common\library\Insert;
class Orders extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		header('Content-Type:text/html; charset=utf-8');

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['intro'] = input('intro/d', '');
		$admin['status'] = input('status/d', '');
		$admin['ispay'] = input('ispay/d', '');
		$admin['view'] = input('view/d', '');
		$admin['stype'] = input('stype/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'order_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$where = "";
		if ($admin['uid']) {
			$where['order_uid'] = $admin['uid'];
		}
		if ($admin['status']) {
			$where['order_status'] = $admin['status'];
		}
		if ($admin['ispay']) {
			$where['order_ispay'] = $admin['ispay'];
		}
		if ($admin['stype']) {
			$where['order_type'] = $admin['stype'];
		}
		if ($admin['wd']) {
			$where['nickname|username|order_info|order_sign'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('orders')->alias('o')->join('user u', 'u.userid=o.order_uid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('orders_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/orders/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$type = [1 => '支付宝', 2 => '微信', 3 => 'PayPal', 4 => '云支付', 5 => '码支付支付宝', 6 => '码支付微信', 7 => '码支付QQ钱包'];
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
	public function sign()
	{
		$result = model('Orders')->update_order(input('id', 0), input('money', 0), 7);
		if ($result) {
			action_log('admin/oeders/sign', 'oeders', $result, 1);
			return $this->success('补单成功');
		} else {
			action_log('admin/oeders/sign', 'oeders', 0, 0);
			return $this->error('补单失败');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/orders/del', 'orders', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$result = db('orders')->where('order_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/orders/del', 'orders', $id, 1);
			return $this->success('删除订单成功！', Cookie('orders_url_forward'));
		} else {
			action_log('admin/orders/del', 'orders', 0, 0);
			return $this->error('删除订单失败！');
		}
	}
}