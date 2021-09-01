<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use app\common\controller\Admin;
class Playlog extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['uid'] = input('uid/d', '');
		$admin['vid'] = input('vid/d', '');
		$admin['cid'] = input('cid/d', '');
		$admin['view'] = input('view/d', '');
		$admin['wd'] = urldecode(trim(input('wd', '')));
		$admin['type'] = input('type', 'log_addtime');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$admin['p'] = input('p/d', '');
		$where = "";
		if ($admin['cid']) {
			$where['vod_cid'] = getlistsqlin($admin['cid']);
		}
		if ($admin['uid']) {
			$where['log_uid'] = $admin['uid'];
		}
		if ($admin['vid']) {
			$where['log_vid'] = $admin['vid'];
		}
		if ($admin['wd']) {
			$where['username|nickname'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Playlog')->alias('p')->join('User u', 'u.userid=p.log_uid', 'LEFT')->join('vod v', 'v.vod_id=p.log_vid', 'LEFT')->where($where)->order($admin['orders'])->paginate($limit, false, ['page' => $admin['p']]);
		Cookie('playlog_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/playlog/index', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		$data = array('list' => $list, 'page' => $pages);
		$this->assign($admin);
		$this->assign('listvod', F('_data/listvod'));
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
			action_log('admin/playlog/del', 'playlog', 0, 0);
			return $this->error("必须选择ID才能操作！");
		}
		$result = db('Playlog')->where('log_id', 'in', $id)->delete();
		if ($result) {
			action_log('admin/playlog/del', 'playlog', $id, 1);
			return $this->success('删除播放记录成功！', Cookie('playlog_url_forward'));
		} else {
			action_log('admin/playlog/del', 'playlog', $id, 0);
			return $this->error('删除播放记录失败！');
		}
	}
}