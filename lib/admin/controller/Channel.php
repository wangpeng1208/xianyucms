<?php


namespace app\admin\controller;

use com\Tree;
use app\common\controller\Admin;
class Channel extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$title = trim(input('get.title'));
		$list = db('List')->alias('l')->field('l.*,m.id,m.name,m.title,m.icon,m.template_list,m.template_add,m.template_edit,m.template_list_skin,m.template_list_type,m.template_list_detail,m.template_list_play')->join('model m', 'm.id=l.list_sid', 'LEFT')->order('list_oid asc')->select();
		if (!empty($list)) {
			$tree = Tree::instance();
			$tree->init($list, 'list_id', 'list_pid');
			$list = $tree->getTreeList($tree->getTreeArray(0), 'list_name');
		}
		F('_data/list_menu', $list);
		$list_statu = array(1 => '否', 0 => '是');
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$this->setNav();
		$this->assign('list', $list);
		$this->assign('list_statu', $list_statu);
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function show($id = 1)
	{
		if (\think\Request::instance()->isAjax()) {
			$data = list_search(F('_data/modellist'), 'id=' . $id);
			$this->assign($data[0]);
			return view('show');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('List')->where(array('list_id' => $pk))->setField($name, $value);
		}
	}
	public function add()
	{
		if (IS_POST) {
			$channel = model('Channel');
			$data = input('post.');
			$channel->validate('Channel')->save($data);
			if ($channel->list_id) {
				$this->list_cache();
				action_log('admin/channel/add', 'channel', $channel->list_id, 1);
				return $this->success('添加成功', url('admin/channel/index'));
			} else {
				action_log('admin/channel/add', 'channel', 0, 0, $channel->getError());
				return $this->error($channel->getError());
			}
		} else {
			$pid = input('pid', 0);
			$model = F('_data/modellist');
			$menus = F('_data/list_menu');
			if (!empty($pid)) {
				$info = db('list')->alias('l')->field('list_skin,list_skin_type,list_skin_detail,list_skin_detail,list_skin_play,list_sid,name,title,template_list_skin,template_list_type,template_list_detail,template_list_play')->join('model m', 'm.id=l.list_sid', 'LEFT')->find($pid);
				$info['list_pid'] = $pid;
			} else {
				$infos = list_search(F('_data/modellist'), 'id=1');
				$info = $infos[0];
				$info['list_skin'] = $info['template_list_skin'];
				$info['list_skin_type'] = $info['template_list_type'];
				$info['list_skin_detail'] = $info['template_list_detail'];
				$info['list_skin_play'] = $info['template_list_play'];
				$info['list_sid'] = 1;
			}
			$menus = array_merge(array(0 => array('list_id' => 0, 'title_show' => '顶级菜单')), $menus);
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('model', $model);
			$this->assign('Menus', $menus);
			return view('edit');
		}
	}
	public function edit($id = 0)
	{
		if (IS_POST) {
			$Channel = model('Channel');
			$data = input('post.');
			$result = $this->validate($data, 'Channel.add');
			if (true !== $result) {
				return $this->error($result);
			}
			if ($Channel->save($data, array('list_id' => $data['id'])) !== false) {
				$this->list_cache();
				action_log('admin/channel/edit', 'channel', $id, 1);
				return $this->success('修改成功', url('admin/channel/index'));
			} else {
				action_log('admin/channel/edit', 'channel', 0, 0);
				return $this->error('修改失败');
			}
		} else {
			$info = array();
			$info = db('List')->field(true)->alias('l')->field('l.*,template_list_skin,template_list_type,template_list_detail,template_list_play')->join('model m', 'm.id=l.list_sid', 'LEFT')->find($id);
			if (false === $info) {
				action_log('admin/channel/edit', 'channel', $id, 0);
				return $this->error('没有相关栏目信息');
			}
			$model = F('_data/modellist');
			$menus = F('_data/list_menu');
			$menus = array_merge(array(0 => array('list_id' => 0, 'title_show' => '顶级菜单')), $menus);
			$this->assign('Menus', $menus);
			$this->assign('info', $info);
			$this->assign('model', $model);
			return view('edit');
		}
	}
	public function up()
	{
		$array = input('post.');
		if (empty($array)) {
			action_log('admin/channel/up', 'channel', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		foreach ($array['id'] as $key => $value) {
			$data['list_id'] = $value;
			$data['list_oid'] = intval($array['list_oid'][$value]);
			$data['list_name'] = $array['list_name'][$value];
			$data['list_skin'] = $array['list_skin'][$value];
			if (empty($array['list_dir'][$value])) {
				$data['list_dir'] = getletters(trim($array['list_name'][$value]), 'list');
			} else {
				$data['list_dir'] = $array['list_dir'][$value];
			}
			$result = db('list')->update($data);
		}
		action_log('admin/channel/up', 'channel', 1, 1);
		$this->list_cache();
		return $this->success('批量更新栏目成功！');
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/channel/del', 'channel', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		foreach ($id as $value) {
			$sid = getlistname(intval($value), 'list_sid');
			if (!getlistson(intval($value))) {
				return $this->success('请先删除本类下面的子栏目');
			}
			$this->deldata($sid, intval($value));
			db('list')->where('list_id', intval($value))->delete();
		}
		$this->list_cache();
		action_log('admin/channel/del', 'channel', $id, 1);
		return $this->success('删除成功');
	}
	public function deldata($sid, $cid)
	{
		if (in_array($sid, array(1, 2, 3, 4, 5, 6, 7, 10))) {
			$mode = list_search(F('_data/modellist'), 'id=' . $sid);
			db($mode[0]['name'])->where($mode[0]['name'] . '_cid = ' . $cid)->delete();
		}
	}
	public function status($status = "")
	{
		session('admin_menu_list', null);
		$id = $this->getArrayParam('id');
		$where['list_pid'] = array('IN', $id);
		$where['list_status'] = 1;
		$user = db('list')->where($where)->select();
		if ($user && $status == 0) {
			action_log('admin/channel/status', 'channel', $id, 0);
			return $this->error("该分类存在下级未隐藏分类不能进行隐藏", '');
		}
		$map['list_id'] = array('IN', $id);
		$result = db('list')->where($map)->setField(array('list_status' => $status));
		if ($result !== false) {
			$this->list_cache();
			action_log('admin/channel/status', 'channel', $id, 1);
			return $this->success('操作成功！');
		} else {
			action_log('admin/channel/status', 'channel', 0, 0);
			return $this->error('操作失败！');
		}
	}
}