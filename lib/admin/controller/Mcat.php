<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Mcat extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$where = array('list_pid' => 0, 'list_sid' => 1);
		$tree = db('List')->where($where)->field("list_id,list_name,list_oid")->order('list_oid asc')->select();
		foreach ($tree as $k => $v) {
			$tree[$k]['son'] = model('Mcat')->list_cat($v['list_id']);
			$tree[$k]['total'] = $tree[$k]['son'] == null ? 0 : count($tree[$k]['son']);
		}
		$mcat = db('Mcat')->order('m_cid asc')->select();
		foreach ($tree as $k => $v) {
			$mcat[$k]['son'] = $mcat[0]['m_cid'];
		}
		F('_data/mcat', $tree);
		F('_data/mcid', $mcat);
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$this->setNav();
		$this->assign('list', $tree);
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Mcat');
			if (true !== $result) {
				return $this->error($result);
			}
			if (!$data['m_ename']) {
				$data['m_ename'] = getletters($data['m_name'], 'mcat', $data['m_list_id']);
			}
			$result = db('Mcat')->insertGetId($data);
			if ($result) {
				$this->list_cache();
				action_log('admin/mcat/add', 'mcat', $result, 1);
				return $this->success('新增成功', Cookie('__forward__'));
			} else {
				action_log('admin/mcat/add', 'mcat', 0, 0);
				return $this->error("新增错误");
			}
		} else {
			$id = input('id', 0);
			$info['m_cid'] = 0;
			$info['m_list_id'] = $id;
			$info['m_order'] = db('Mcat')->where('m_list_id', $id)->max('m_order') + 1;
			$data = array('info' => $info);
			$this->assign($data);
			$this->assign('list_tree', F('_data/listtree'));
			return view('edit');
		}
	}
	public function edit($id = 0)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Mcat');
			if (true !== $result) {
				return $this->error($result);
			}
			if (!$data['m_ename']) {
				$data['m_ename'] = getletters($data['m_name'], 'mcat', $data['m_list_id']);
			}
			$result = db('Mcat')->update($data);
			if ($result) {
				$this->list_cache();
				action_log('admin/mcat/edit', 'mcat', $id, 1);
				return $this->success('编辑成功', Cookie('__forward__'));
			} else {
				action_log('admin/mcat/edit', 'mcat', 0, 0);
				return $this->error("编辑失败");
			}
		} else {
			$info = array();
			$id = input('id', 0);
			$where = array();
			$where['m_cid'] = $id;
			$info = db('Mcat')->where($where)->find();
			$this->assign('list_tree', F('_data/listtree'));
			$this->assign('info', $info);
			return view('edit');
		}
	}
	public function up()
	{
		$id = $this->getArrayParam('id');
		$array = input('post.');
		if (empty($id)) {
			action_log('admin/mcat/up', 'mcat', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		foreach ($array['id'] as $key => $value) {
			$data['m_cid'] = $value;
			$data['m_order'] = intval($array['m_order'][$value]);
			$data['m_name'] = $array['m_name'][$value];
			if ($array['m_ename'][$value]) {
				$data['m_ename'] = $array['m_ename'][$value];
			} else {
				$data['m_ename'] = getletters($array['m_name'][$value], 'mcat', $value);
			}
			$result = db('Mcat')->update($data);
		}
		action_log('admin/mcat/up', 'mcat', $id, 1);
		$this->list_cache();
		return $this->success('批量更新栏目成功！');
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/mcat/del', 'mcat', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		$where['m_cid'] = array('in', $id);
		if (db('Mcat')->where($where)->delete()) {
			$this->list_cache();
			action_log('admin/mcat/del', 'mcat', $id, 1);
			return $this->success('删除类型成功');
		} else {
			action_log('admin/mcat/del', 'mcat', 0, 0);
			return $this->error('删除类型失败！');
		}
	}
	public function clear($id = "")
	{
		if (empty($id)) {
			action_log('admin/mcat/clear', 'mcat', 0, 0);
			return $this->error('请选择要清空的栏目!');
		}
		$where['m_list_id'] = array('eq', $id);
		if (db('Mcat')->where($where)->delete()) {
			$this->list_cache();
			action_log('admin/mcat/clear', 'mcat', $id, 1);
			return $this->success('清空类型成功');
		} else {
			action_log('admin/mcat/clear', 'mcat', 0, 0);
			return $this->error('清空类型失败！');
		}
	}
}