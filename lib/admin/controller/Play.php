<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Play extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['type'] = input('type', 'oid');
		$admin['order'] = input('order', 'asc');
		$admin['status'] = input('status/d', '');
		$admin['orders'] = 'play_' . $admin['type'] . ' ' . $admin['order'];
		$limit = config('admin_list_pages');
		if ($admin['status'] == 1) {
			$where['play_status'] = array('eq', $admin['status']);
		} elseif ($admin['status'] == 2) {
			$where['play_status'] = array('eq', 0);
		}
		$list = db('play')->where($where)->order($admin['orders'])->select();
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		$data = array('list' => $list, 'order' => $admin['order'], 'orders' => $admin['orders'], 'type' => $admin['type']);
		$this->play_cache();
		$this->assign('listplayer', F('_data/player'));
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['<div class="panel-body">' => '<div class="panel-body">' . config('xianyucms.ad'), '</body>' => config('xianyucms.copyright')]);
	}
	public function add()
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Play');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('play')->insert($data);
			if ($result) {
				$copy = copy(PUBLIC_PATH . "player/xianyucms.js", PUBLIC_PATH . "player/" . $data['play_name'] . ".js");
				$this->play_cache();
				action_log('admin/play/add', 'play', $result, 1);
				return $this->success('添加成功！', url('admin/play/index'));
			} else {
				action_log('admin/play/add', 'play', 0, 0);
				return $this->error('添加失败');
			}
		} else {
			$info['play_oid'] = db('play')->max('play_oid') + 1;
			$data = array('info' => $info, 'public' => rtrim(PUBLIC_PATH, '/'));
			$this->assign($data);
			$this->setNav();
			return view('add', [], ['<div class="panel-lead">' => '<div class="panel-lead">' . config('xianyucms.ad')]);
		}
	}
	public function edit($id = null)
	{
		if (IS_POST) {
			$data = input('post.');
			$result = $this->validate($data, 'Play');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = db('play')->update($data);
			if ($result !== false) {
				$this->play_cache();
				action_log('admin/play/edit', 'play', $id, 1);
				return $this->success('编辑播放器成功！', url('admin/play/index'));
			} else {
				action_log('admin/play/edit', 'play', 0, 0);
				return $this->error('编辑失败');
			}
		} else {
			$info = db('play')->where(array('play_id' => $id))->find();
			if (!$info) {
				action_log('admin/play/edit', 'play', $id, 0);
				return $this->error("ID操作失败！");
			}
			$data = array('info' => $info, 'public' => rtrim(PUBLIC_PATH, '/'));
			$this->assign($data);
			$this->setNav();
			return view('add', []);
		}
	}
	public function updateall()
	{
		$id = $this->getArrayParam('id');
		$array = input('post.');
		if (empty($id)) {
			action_log('admin/play/updateall', 'play', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		foreach ($array['id'] as $key => $value) {
			$data['play_oid'] = intval($array['play_oid'][$value]);
			$data['play_name'] = $array['play_name'][$value];
			$data['play_copyright'] = $array['play_copyright'][$value];
			$data['play_title'] = $array['play_title'][$value];
			$result = db('play')->where('play_id = ' . intval($value))->update($data);
		}
		$this->play_cache();
		action_log('admin/play/updateall', 'play', 1, 1);
		return $this->success('批量更新播放器成功！');
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/play/del', 'play', 0, 0);
			return $this->error("非法操作！", '');
		}
		$map['play_id'] = array('IN', $id);
		$result = db('play')->where($map)->delete();
		if ($result) {
			$this->play_cache();
			action_log('admin/play/del', 'play', $id, 1);
			return $this->success('删除成功！');
		} else {
			action_log('admin/play/del', 'play', $id, 0);
			return $this->error('删除失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/play/status', 'play', 0, 0);
			return $this->error("非法操作！", '');
		}
		$message = !$status ? '禁用' : '启用';
		$map['play_id'] = array('IN', $id);
		$result = db('play')->where($map)->setField('play_status', $status);
		if ($result !== false) {
			$this->play_cache();
			action_log('admin/play/status', 'play', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/play/status', 'play', $id, 1, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function diplay($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/play/diplay', 'play', 0, 0);
			return $this->error("非法操作！", '');
		}
		$message = !$status ? '隐藏' : '显示';
		$map['play_id'] = array('IN', $id);
		$result = db('play')->where($map)->setField('play_display', $status);
		if ($result !== false) {
			$this->play_cache();
			action_log('admin/play/diplay', 'play', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！');
		} else {
			action_log('admin/play/diplay', 'play', $id, 1, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function delplay()
	{
		$limit = 1000;
		$one = trim(input('one/d', 1));
		$admin['player'] = trim(input('player/s', ''));
		if ($admin['player']) {
			$where['vod_play'] = array('like', '%' . trim($admin['player']) . '%');
			$list = db('vod')->field('vod_id,vod_name,vod_play,vod_url')->where($where)->order('vod_id desc')->limit($limit)->select();
		}
		foreach ($list as $key => $value) {
			if (empty($one) && $list[$key]['vod_play'] == $admin['player']) {
				continue;
			} else {
				$playurls = $this->del_play($list[$key]['vod_play'], $admin['player'], $list[$key]['vod_url']);
				$edit['vod_id'] = $list[$key]['vod_id'];
				$edit['vod_play'] = !empty($playurls['vod_play']) ? $playurls['vod_play'] : "";
				$edit['vod_url'] = !empty($playurls['vod_url']) ? $playurls['vod_url'] : "";
				$result = db('vod')->update($edit);
			}
		}
		$neslist = db('vod')->where($where)->count();
		if ($neslist > 0) {
			$jumpurl = url('admin/play/delplay', array('player' => $admin['player'], 'one' => $one));
			return $this->success("正在删除...还剩({$neslist})", $jumpurl, $neslist);
		} else {
			return $this->error("没有相关数据");
		}
	}
	function del_play($play, $delplay, $url)
	{
		$array_delplay = explode(',', $delplay);
		$array_play = explode('$$$', $play);
		$array_url = explode('$$$', $url);
		$array_newplay = array_diff($array_play, $array_delplay);
		if ($array_newplay) {
			foreach ($array_newplay as $key => $value) {
				$array_newurl[] = $array_url[$key];
			}
			$new['vod_play'] = implode('$$$', $array_newplay);
			$new['vod_url'] = implode('$$$', $array_newurl);
			return $new;
		} else {
			return false;
		}
	}
}