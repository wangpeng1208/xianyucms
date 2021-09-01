<?php


namespace app\admin\controller;

use think\validate;
use think\Request;
use app\common\controller\Admin;
class Tag extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['p'] = input('p/d', '');
		$admin['type'] = input('type', 'tag_id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		$list = db('tag')->field('*,count(tag_name) as tag_count')->order($admin['type'] . ' ' . $admin['order'])->group('tag_sid,tag_name')->order('tag_sid asc,tag_count desc')->paginate($limit, false, ['page' => $admin['p']]);
		$datalist = $list->all();
		foreach ($datalist as $key => $val) {
			$datalist[$key]['tag_url'] = url('admin/' . ucfirst(getmodeid($datalist[$key]['tag_sid'], 'name')) . '/index', array('tag' => urlencode($datalist[$key]['tag_name'])));
			$datalist[$key]['tag_modeltitle'] = getmodeid($datalist[$key]['tag_sid'], 'title');
		}
		Cookie('tag_url_forward', $_SERVER['REQUEST_URI']);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/tag/index', $admin);
		$pages = '<ul class="pagination">' . adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '') . '</ul>';
		$data = array('data' => $datalist, 'page' => $pages, 'list' => $list);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function showajax()
	{
		$admin['sid'] = input('sid/d', '');
		$where['tag_sid'] = array('eq', intval($admin['sid']));
		$list = db('Tag')->field('*,count(tag_name) as tag_count')->where($where)->limit('60')->group('tag_name,tag_sid')->order('tag_count desc')->select();
		$data = array('list' => $list);
		$this->assign($data);
		return view('tag_ajax');
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/tag/del', 'tag', 0, 0);
			return $this->error("必须选择ID才能操作！", '');
		}
		$map['tag_name'] = array('IN', $id);
		$result = db('Tag')->where($map)->delete();
		if ($result) {
			action_log('admin/tag/del', 'tag', $id, 1);
			return $this->success('删除标签成功！', Cookie('tag_url_forward'));
		} else {
			action_log('admin/tag/del', 'tag', $id, 0);
			return $this->error('删除标签失败！');
		}
	}
}