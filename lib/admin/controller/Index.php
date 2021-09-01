<?php


namespace app\admin\controller;

use app\common\controller\Admin;
class Index extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index()
	{
		$this->setMenu();
		return view('index');
	}
	public function show()
	{
		$env = $this->check_env();
		if ('IS_WRITE') {
			$dirfile = $this->check_dirfile();
			$this->assign('dirfile', $dirfile);
		}
		$func = $this->check_func();
		$this->assign('env', $env);
		$this->assign('func', $func);
		$this->status['index'] = 'success';
		$this->status['check'] = 'primary';
		$this->assign('status', $this->status);
		$this->setNav();
		$apiurl = config('xianyucms.api_url');
		$xianyu = '';
		return view('show', [], ['</head>' => $xianyu, '</body>' => config('xianyucms.copyright')]);
	}
	public function phpinfo()
	{
		phpinfo();
	}
	private function check_env()
	{
		$items = array('os' => array('操作系统', '不限制', '类Unix', PHP_OS, 'success'), 'php' => array('PHP版本', '5.4.X', '5.3+', PHP_VERSION, 'success'), 'upload' => array('附件上传', '不限制', '2M+', '未知', 'success'), 'gd' => array('GD库', '>2.0', '2.0+', '未知', 'success'), 'disk' => array('磁盘空间', '>100M', '不限制', '未知', 'success'));
		if ($items['php'][3] < $items['php'][1]) {
			$items['php'][4] = 'error';
			session('error', true);
		}
		if (@ini_get('file_uploads')) {
			$items['upload'][3] = ini_get('upload_max_filesize');
		}
		$tmp = function_exists('gd_info') ? gd_info() : array();
		if (empty($tmp['GD Version'])) {
			$items['gd'][3] = '未安装';
			$items['gd'][4] = 'error';
			session('error', true);
		} else {
			$items['gd'][3] = $tmp['GD Version'];
		}
		unset($tmp);
		if (function_exists('disk_free_space')) {
			$items['disk'][3] = floor(disk_free_space(ROOT_PATH) / (1024 * 1024)) . 'M';
		}
		return $items;
	}
	private function check_dirfile()
	{
		$items = array(array('dir', '可读/可写', '可读/可写', 'success', 'runtime/'), array('dir', '可读/可写', '可读/可写', 'success', config('upload_path') . '/'), array('dir', '可读/可写', '可读/可写', 'success', config('upload_path') . '-s/'));
		foreach ($items as $key => $val) {
			$item = URL_PATH . $val[4];
			if ('dir' == $val[0]) {
				if (!is_writable($item)) {
					if (is_dir($item)) {
						$items[$key][2] = '可读/<font style="color:#F00">不可写</font>';
						$items[$key][3] = 'error';
					} else {
						$items[$key][2] = '<font style="color:#F00">不存在</font>';
						$items[$key][3] = 'error';
					}
				}
			} else {
				if (file_exists($item)) {
					if (!is_writable($item)) {
						$items[$key][2] = '<font style="color:#F00">不可写</font>';
						$items[$key][3] = 'error';
					}
				} else {
					if (!is_writable(dirname($item))) {
						$items[$key][2] = '<font style="color:#F00">不存在</font>';
						$items[$key][3] = 'error';
					}
				}
			}
		}
		return $items;
	}
	private function check_func()
	{
		$items = array(array('file_get_contents', '支持', 'success', '函数'), array('mb_strlen', '支持', 'success', '函数'), array('openssl', '支持', 'success', '模块'), array('memcache', '支持', 'success', '模块'));
		foreach ($items as $key => $val) {
			if ('类' == $val[3] && !class_exists($val[0]) || '模块' == $val[3] && !extension_loaded($val[0]) || '函数' == $val[3] && !function_exists($val[0])) {
				$items[$key][1] = '<font style="color:#F00">不支持</font>';
				$items[$key][2] = 'error';
				session('error', true);
			}
		}
		return $items;
	}
}