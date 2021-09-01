<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use think\addons\AddonException;
use think\addons\Service;
use think\Config;
use think\Exception;
class Addon extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$admin['p'] = input('p/d', 1);
		$admin['t'] = input('t/d', 8);
		$addon = F('_addon/data_' . $admin['t'] . '_' . $admin['p']);
		if (empty($addon)) {
			$addon = xianyu_get_url(config('xianyucms.api_url') . 'addon/index/t/' . $admin['t'] . '/p/' . $admin['p'], 5);
		}
		if ($addon) {
			F('_addon/data_' . $admin['t'] . '_' . $admin['p'], $addon);
			$data = json_decode($addon, true);
		}

		$addons = get_addon_list();
		foreach ($addons as $k => &$v) {
			$config = get_addon_config($v['name']);
			$v['config'] = $config ? 1 : 0;
		}
		$flag = array('recommend' => '<span class="label label-success">推荐</span>', 'hot' => '<span class="label label-danger">热门</span>', 'new' => '<span class="label label-info">最新</span>');
		$page['pageindex'] = $data['page']['pageindex'];
		$page['pagecount'] = $data['page']['pagecount'];
		$page['pagesize'] = $data['page']['pagesize'];
		$page['recordcount'] = $data['page']['recordcount'];
		$page['pageurl'] = url('admin/addon/index', array('p' => 'xianyupage'));
		$page['pagelist'] = adminpage($page['pageindex'], $page['pagecount'], 3, $page['pageurl'], '');
		$data = array('list' => $data['rows'], 'addon' => $addons, 'page' => $page, 'flag' => $flag);
		Cookie('addon_url_forward', $_SERVER['REQUEST_URI']);
		$this->assignconfig(['addons' => $addons]);
		$this->assign($data);
		$this->setNav();
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function configs()
	{
		$name = input('name/s', '');
		if (!$name) {
			$this->error('参数name不能为空');
		}
		if (!is_dir(ADDON_PATH . $name)) {
			$this->error('没有相关目录');
		}
		$info = get_addon_info($name);
		$config = get_addon_fullconfig($name);
		if (!$info) {
			$this->error('记录未找到');
		}
		if ($this->request->isPost()) {
			$params = $this->request->post("row/a");
			if ($params) {
				$configList = [];
				foreach ($config as $k => &$v) {
					if (isset($params[$v['name']])) {
						if ($v['type'] == 'array') {
							$fieldarr = $valuearr = [];
							$field = $params[$v['name']]['field'];
							$value = $params[$v['name']]['value'];
							foreach ($field as $m => $n) {
								if ($n != '') {
									$fieldarr[] = $field[$m];
									$valuearr[] = $value[$m];
								}
							}
							$params[$v['name']] = array_combine($fieldarr, $valuearr);
							$value = $params[$v['name']];
						} else {
							$value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
						}
						$v['value'] = $value;
					}
				}
				try {
					set_addon_fullconfig($name, $config);
					return $this->success('更新成功', Cookie('addon_url_forward'));
				} catch (Exception $e) {
					return $this->error($e->getMessage());
				}
			}
			$this->error('参数不能为空');
		}
		$this->view->assign("addon", ['info' => $info, 'config' => $config]);
		return $this->view->fetch();
	}
	public function install()
	{
		$name = $this->request->post("name");
		$force = (int) $this->request->post("force");
		if (!$name) {
			$this->error('参数name不能为空');
		}
		try {
			Service::install($name, $force);
			$info = get_addon_info($name);
			$info['config'] = get_addon_config($name) ? 1 : 0;
			$this->success("安装成功", null, ['addon' => $info]);
		} catch (AddonException $e) {
			$this->result($e->getData(), $e->getCode(), $e->getMessage());
		} catch (Exception $e) {
			$this->error($e->getMessage(), $e->getCode());
		}
	}
	public function uninstall()
	{
		$name = $this->request->post("name");
		$force = (int) $this->request->post("force");
		if (!$name) {
			$this->error('参数name不能为空');
		}
		try {
			Service::uninstall($name, $force);
			$this->success("卸载成功", Cookie('addon_url_forward'));
		} catch (AddonException $e) {
			$this->result($e->getData(), $e->getCode(), $e->getMessage());
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
	}
	public function state()
	{
		$name = $this->request->post("name");
		$action = $this->request->post("action");
		$force = (int) $this->request->post("force");
		if (!$name) {
			$this->error('参数name不能为空');
		}
		try {
			$action = $action == 'enable' ? $action : 'disable';
			Service::$action($name, $force);
			$this->success("操作成功", Cookie('addon_url_forward'));
		} catch (AddonException $e) {
			$this->result($e->getData(), $e->getCode(), $e->getMessage());
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
	}
	public function local()
	{
		$file = $this->request->file('file');
		$addonTmpDir = RUNTIME_PATH . 'addons';
		if (!is_dir($addonTmpDir)) {
			@mkdir($addonTmpDir, 0755, true);
		}
		$info = $file->rule('uniqid')->validate(['size' => 10240000, 'ext' => 'zip'])->move($addonTmpDir);
		if ($info) {
			$tmpName = substr($info->getFilename(), 0, stripos($info->getFilename(), '.'));
			$tmpAddonDir = ADDON_PATH . $tmpName;
			$tmpFile = $addonTmpDir . $info->getSaveName();
			try {
				Service::unzip($tmpName);
				@unlink($tmpFile);
				$infoFile = $tmpAddonDir . DS . 'info.ini';
				if (!is_file($infoFile)) {
					throw new Exception("插件配置文件未找到");
				}
				$config = Config::parse($infoFile, '', $tmpName);
				$name = isset($config['name']) ? $config['name'] : '';
				if (!$name) {
					throw new Exception("插件配置信息不正确");
				}
				$newAddonDir = ADDON_PATH . $name . DS;
				if (is_dir($newAddonDir)) {
					throw new Exception("上传的插件已经存在");
				}
				rename($tmpAddonDir, $newAddonDir);
				try {
					$info = get_addon_info($name);
					if ($info['state']) {
						$info['state'] = 0;
						set_addon_info($name, $info);
					}
					$class = get_addon_class($name);
					if (class_exists($class)) {
						$addon = new $class();
						$addon->install();
					}
					Service::importsql($name);
					$info['config'] = get_addon_config($name) ? 1 : 0;
					@unlink($tmpFile);
					return json_encode(array('code' => 1, 'msg' => '插件安装成功，你需要手动启用该插件，使之生效', 'data' => array('addon' => $info)));
				} catch (Exception $e) {
					@rmdirs($newAddonDir);
					return json_encode(array('code' => 0, 'msg' => $e->getMessage()));
				}
			} catch (Exception $e) {
				@unlink($tmpFile);
				@rmdirs($tmpAddonDir);
				return json_encode(array('code' => 0, 'msg' => $e->getMessage()));
			}
		} else {
			return json_encode(array('code' => 0, 'msg' => $file->getError()));
		}
	}
	public function downloaded()
	{
		$addons = get_addon_list();
		$data = $addons;
		foreach ($data as $k => $v) {
			$data[$k]['flag'] = '';
			$data[$k]['banner'] = '';
			$data[$k]['image'] = '';
			$data[$k]['donateimage'] = '';
			$data[$k]['demourl'] = '';
			$data[$k]['price'] = '0.00';
			$data[$k]['url'] = '/addons/' . $v['name'];
			$data[$k]['createtime'] = 0;
		}
		foreach ($addons as $k => &$v) {
			$config = get_addon_config($v['name']);
			$v['config'] = $config ? 1 : 0;
		}
		$data = array('list' => $data, 'addon' => $addons);
		Cookie('addon_url_forward', $_SERVER['REQUEST_URI']);
		$this->assignconfig(['addons' => $addons]);
		$this->assign($data);
		return view('index', [], ['</body>' => config('xianyucms.copyright')]);
	}
	public function refresh()
	{
		$addon = xianyu_get_url(config('xianyucms.api_url') . 'addon/index/t/8/p/1', 5);
		if ($addon) {
			F('_addon/data_8_1', $addon);
			$data = json_decode($addon, true);
			if ($data['page']['pagecount'] > 1) {
				for ($x = 2; $x <= $data['page']['pagecount']; $x++) {
					$addons = xianyu_get_url(config('xianyucms.api_url') . 'addon/index/t/8/p/' . $x, 5);
					F('_addon/data_8_' . $x, $addons);
				}
			}
			$this->success("刷新成功", Cookie('addon_url_forward'));
		} else {
			$this->error("刷新失败");
		}
	}
}