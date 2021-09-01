<?php


namespace app\admin\controller;

use think\Console;
use think\Route;
use app\common\controller\Admin;
class Config extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
		$this->model = model('Config');

	}
	public function show()
	{
		$admin['group'] = input('group', 0, 'trim');
		$admin['status'] = input('status/d', '');
		$admin['wd'] = input('wd', '', 'trim');
		$admin['type'] = input('type', 'id');
		$admin['order'] = input('order', 'desc');
		$admin['orders'] = $admin['type'] . ' ' . $admin['order'];
		$admin['p'] = input('p/d', '');
		$limit = config('admin_list_pages');
		if ($admin['status'] == 1) {
			$where['status'] = array('eq', $admin['status']);
		} elseif ($admin['status'] == 2) {
			$where['status'] = array('eq', 0);
		} else {
			$where['status'] = array('gt', -1);
		}
		if ($admin['group']) {
			$where['group'] = $admin['group'];
		}
		if ($admin['wd']) {
			$where['name|title'] = array('like', '%' . $admin['wd'] . '%');
		}
		$list = db('Config')->where($where)->order($admin['type'] . ' ' . $admin['order'])->paginate($limit, false, ['page' => $admin['p']]);
		$admin['p'] = "xianyupage";
		$pageurl = url('admin/config/show', $admin);
		$pages = adminpage($list->currentPage(), $list->lastPage(), 3, $pageurl, '');
		Cookie('config_url_forward', $_SERVER['REQUEST_URI']);
		$data = array('group_list' => config('config_group_list'), 'type_list' => config('config_type_list'), 'page' => $pages, 'list' => $list);
		$this->assign($admin);
		$this->assign($data);
		$this->setNav();
		return view('show', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function index($id = 1)
	{
		$lists = (include RUNTIME_PATH . 'conf/configdata.php');
		$list = db('Config')->field('id,name,title,extra,remark,type,group,value,other,status')->order('sort')->select();
		foreach ($list as $key => $val) {
			$list[$key]['value'] = $lists[$val['name']];
		}
		foreach ($list as $key => $val) {
			$data[$val['group']][] = $val;
		}
		$tplpclist = glob(ROOT_PATH . 'tpl/home/*');
		$userpclist = glob(ROOT_PATH . 'tpl/user/*');
		if ($tplpclist) {
			foreach ($tplpclist as $i => $file) {
				$homedir[$i]['filename'] = basename($file);
			}
			$this->assign('homedir', $homedir);
		}
		if ($userpclist) {
			foreach ($userpclist as $i => $file) {
				$userdir[$i]['name'] = basename($file);
			}
			$this->assign('userdir', $userdir);
		}
		if ($list) {
			$this->assign('list', $data);
			$this->assign('list_menu', config('config_group_list'));
		}
		$this->assign('xianyu_api', config('xianyu_api'));
		$this->assign('id', $id);
		$this->setNav();
		$this->list_cache();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function up($id = 1)
	{
		if (IS_POST) {
			$config = $this->request->post('config/a');
			$result = $this->validate($config, 'Config.update');
			if (true !== $result) {
				action_log('admin/config/up', 'config', 0, 0);
				return $this->error($result);
			}
			arr2file(RUNTIME_PATH . '/conf/configdata.php', $config);
			action_log('admin/config/up', 'config', 1, 1);
			$this->config_cache($config);
			if ($config['play_area']) {
				$play_area_array = explode(',', $config['play_area']);
				foreach ($play_area_array as $key => $value) {
					$play_area[$key]['name'] = $value;
					$play_area[$key]['ename'] = getletters($value);
				}
				F('_data/area', $play_area);
			}
			$this->hotkeywords_cache();
			return $this->success("更新成功！");
		}
	}
	public function add()
	{
		if (IS_POST) {
			$configs = model('Config');
			$data = $this->request->post();
			$result = $this->validate($data, 'Config');
			if (true !== $result) {
				return $this->error($result);
			}
			$configs->save($data);
			if ($configs->id) {
				action_log('admin/config/add', 'config', $configs->id, 1);
				return $this->success('新增成功', url('admin/config/show'));
			} else {
				action_log('admin/config/add', 'config', 0, 0, $configs->getError());
				return $this->error($configs->getError());
			}
		} else {
			return view('edit');
		}
	}
	public function edit($id = 0)
	{
		if (IS_POST) {
			$config = model('Config');
			$data = $this->request->post();
			$result = $this->validate($data, 'Config');
			if (true !== $result) {
				return $this->error($result);
			}
			$result = $config->save($data, array('id' => $data['id']));
			if (false !== $result) {
				action_log('admin/config/edit', 'config', $data['id'], 1);
				return $this->success('更新成功', Cookie('config_url_forward'));
			} else {
				action_log('admin/config/edit', 'config', 0, 0);
				return $this->error("更新失败");
			}
		} else {
			$info = array();
			$info = db('Config')->field(true)->find($id);
			if (false === $info) {
				return $this->error('获取配置信息错误');
			}
			$this->assign('info', $info);
			return view('edit');
		}
	}
	public function del()
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/config/del', 'config', 0, 0);
			return $this->error('请选择要操作的数据!');
		}
		$map = array('id' => array('in', $id));
		if (db('Config')->where($map)->delete()) {
			action_log('admin/config/del', 'config', $id, 1);
			return $this->success('删除成功', Cookie('config_url_forward'));
		} else {
			action_log('admin/config/del', 'config', $id, 0);
			return $this->error('删除失败！');
		}
	}
	public function status($status = "")
	{
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			action_log('admin/config/status', 'config', 0, 0);
			return $this->error("非法操作！");
		}
		$message = !$status ? '隐藏' : '显示';
		$map['id'] = array('IN', $id);
		$result = db('Config')->where($map)->setField('status', $status);
		if ($result !== false) {
			action_log('admin/config/status', 'config', $id, 1, '设置' . $message . '状态成功！');
			return $this->success('设置' . $message . '状态成功！', Cookie('config_url_forward'));
		} else {
			action_log('admin/config/status', 'config', $id, 0, '设置' . $message . '状态失败！');
			return $this->error('设置' . $message . '状态失败！');
		}
	}
	public function editable($name = null, $value = null, $pk = null)
	{
		if ($name && ($value != null || $value != '') && $pk) {
			db('Config')->where(array('id' => $pk))->setField($name, $value);
		}
	}
	public function sort()
	{
		if (IS_GET) {
			$ids = input('ids');
			$map = array('status' => array('gt', -1));
			if (!empty($ids)) {
				$map['id'] = array('in', $ids);
			} elseif (input('group')) {
				$map['group'] = input('group');
			}
			$list = db('Config')->where($map)->field('id,title')->order('sort asc,id asc')->select();
			$this->assign('list', $list);
			$this->setMeta('配置排序');
			return $this->fetch();
		} elseif (IS_POST) {
			$ids = input('post.ids');
			$ids = explode(',', $ids);
			foreach ($ids as $key => $value) {
				$res = db('Config')->where(array('id' => $value))->setField('sort', $key + 1);
			}
			if ($res !== false) {
				action_log('admin/config/sort', 'config', 1, 1);
				return $this->success('排序成功！', Cookie('config_url_forward'));
			} else {
				action_log('admin/config/sort', 'config', 0, 0);
				return $this->error('排序失败！');
			}
		} else {
			action_log('admin/config/sort', 'config', 0, 0);
			return $this->error('非法请求！');
		}
	}
	public function check($name = '')
	{
		if (!extension_loaded($name)) {
			return $this->error('！系统没有检查到' . $name . '缓存扩展,强制开启可能导致程序运行出错。');
		} else {
			return $this->success('正确安装相关缓存');
		}
	}
}