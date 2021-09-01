<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use think\Db;
use think\Debug;
class Data extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function index($type = null)
	{
		switch ($type) {
			case 'import':
				$path = RUNTIME_PATH . DS . config('data_backup_path') . DS;
				if (!is_dir($path)) {
					mkdir($path, 0755, true);
				}
				$path = realpath($path);
				$flag = \FilesystemIterator::KEY_AS_FILENAME;
				$glob = new \FilesystemIterator($path, $flag);
				$list = array();
				foreach ($glob as $name => $file) {
					if (preg_match('/^\\d{8,8}-\\d{6,6}-\\d+\\.sql(?:\\.gz)?$/', $name)) {
						$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
						$date = "{$name[0]}-{$name[1]}-{$name[2]}";
						$time = "{$name[3]}:{$name[4]}:{$name[5]}";
						$part = $name[6];
						if (isset($list["{$date} {$time}"])) {
							$info = $list["{$date} {$time}"];
							$info['part'] = max($info['part'], $part);
							$info['size'] = $info['size'] + $file->getSize();
						} else {
							$info['part'] = $part;
							$info['size'] = $file->getSize();
						}
						$extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
						$info['compress'] = $extension === 'SQL' ? '-' : $extension;
						$info['time'] = strtotime("{$date} {$time}");
						$list["{$date} {$time}"] = $info;
					}
				}
				$title = array('title' => '数据还原', 'name' => '');
				break;
			default:
				$type = "export";
				$Db = Db::connect();
				$list = $Db->query('SHOW TABLE STATUS');
				$list = array_map('array_change_key_case', $list);
				$title = array('title' => '数据备份', 'name' => '');
		}
		$this->assign('nav_title', $title);
		$this->assign('list', $list);
		return view($type, [], ['</body>' => config('xianyu_copyright]')]);
	}
	public function optimize($tables = null)
	{
		if ($tables) {
			$Db = Db::connect();
			if (is_array($tables)) {
				$tables = implode('`,`', $tables);
				$list = $Db->query("OPTIMIZE TABLE `{$tables}`");
				if ($list) {
					action_log('admin/data/optimize', 'data', $tables, 1);
					return $this->success("数据表优化完成！");
				} else {
					action_log('admin/data/optimize', 'data', $tables, 0);
					return $this->error("数据表优化出错请重试！");
				}
			} else {
				$list = $Db->query("OPTIMIZE TABLE `{$tables}`");
				if ($list) {
					action_log('admin/data/optimize', 'data', $tables, 1);
					return $this->success("数据表'{$tables}'优化完成！");
				} else {
					action_log('admin/data/optimize', 'data', $tables, 0);
					return $this->error("数据表'{$tables}'优化出错请重试！");
				}
			}
		} else {
			action_log('admin/data/optimize', 'data', 0, 0);
			return $this->error("请指定要优化的表！");
		}
	}
	public function repair($tables = null)
	{
		if ($tables) {
			$Db = Db::connect();
			if (is_array($tables)) {
				$tables = implode('`,`', $tables);
				$list = $Db->query("REPAIR TABLE `{$tables}`");
				if ($list) {
					action_log('admin/data/repair', 'data', $tables, 1);
					return $this->success("数据表修复完成！");
				} else {
					action_log('admin/data/repair', 'data', $tables, 0);
					return $this->error("数据表修复出错请重试！", '');
				}
			} else {
				$list = $Db->query("REPAIR TABLE `{$tables}`");
				if ($list) {
					action_log('admin/data/repair', 'data', $tables, 1);
					return $this->success("数据表'{$tables}'修复完成！");
				} else {
					action_log('admin/data/repair', 'data', $tables, 0);
					return $this->error("数据表'{$tables}'修复出错请重试！", '');
				}
			}
		} else {
			action_log('admin/data/repair', 'data', 0, 0);
			return $this->error("请指定要修复的表！", '');
		}
	}
	public function clear($tables = null)
	{
		if ($tables) {
			$Db = Db::connect();
			$list = $Db->query("TRUNCATE TABLE `{$tables}`");
			action_log('admin/data/clear', 'data', $tables, 1);
			return $this->success("数据表'{$tables}'清空完成！");
		} else {
			action_log('admin/data/clear', 'data', 0, 0);
			return $this->error("请指定要清空的表！");
		}
	}
	public function del($time = 0)
	{
		if ($time) {
			$name = date('Ymd-His', $time) . '-*.sql*';
			$path = realpath(RUNTIME_PATH . DS . config('data_backup_path') . DS) . DIRECTORY_SEPARATOR . $name;
			array_map('unlink', glob($path));
			if (count(glob($path))) {
				action_log('admin/data/del', 'data', $name, 0, '备份文件删除失败，请检查权限！');
				return $this->error('备份文件删除失败，请检查权限！');
			} else {
				action_log('admin/data/del', 'data', $name, 1);
				return $this->success('备份文件删除成功！');
			}
		} else {
			action_log('admin/data/del', 'data', 0, 0);
			return $this->error('参数错误！');
		}
	}
	public function export($tables = null, $id = null, $start = null)
	{
		if (IS_POST && !empty($tables) && is_array($tables)) {
			$path = RUNTIME_PATH . config('data_backup_path');
			if (!is_dir($path)) {
				mkdir($path, 0755, true);
			}
			$config = array('path' => realpath($path) . DIRECTORY_SEPARATOR, 'part' => config('data_backup_part_size'), 'compress' => config('data_backup_compress'), 'level' => config('data_backup_compress_level'));
			$lock = "{$config['path']}backup.lock";
			if (is_file($lock)) {
				action_log('admin/data/export', 'data', $lock, 0, '检测到有一个备份任务正在执行，请稍后再试！');
				return $this->error('检测到有一个备份任务正在执行，请稍后再试！', '');
			} else {
				file_put_contents($lock, time());
			}
			if (!is_writeable($config['path'])) {
				action_log('admin/data/export', 'data', 0, 0, '备份目录不存在或不可写，请检查后重试！');
				return $this->error('备份目录不存在或不可写，请检查后重试！', '');
			}
			session('backup_config', $config);
			$file = array('name' => date('Ymd-His', time()), 'part' => 1);
			session('backup_file', $file);
			session('backup_tables', $tables);
			$Database = new \com\Database($file, $config);
			if (false !== $Database->create()) {
				$tab = array('id' => 0, 'start' => 0);
				return $this->success('初始化成功！', '', array('tables' => $tables, 'tab' => $tab));
			} else {
				action_log('admin/data/export', 'data', 0, 0, '初始化失败，备份文件创建失败！');
				return $this->error('初始化失败，备份文件创建失败！', '');
			}
		} elseif (IS_GET && is_numeric($id) && is_numeric($start)) {
			$tables = session('backup_tables');
			$Database = new \com\Database(session('backup_file'), session('backup_config'));
			$start = $Database->backup($tables[$id], $start);
			if (false === $start) {
				action_log('admin/data/export', 'data', 0, 0);
				return $this->error('备份出错！', '');
			} elseif (0 === $start) {
				if (isset($tables[++$id])) {
					$tab = array('id' => $id, 'start' => 0);
					return $this->success('备份完成！', '', array('tab' => $tab));
				} else {
					unlink(session('backup_config.path') . 'backup.lock');
					session('backup_tables', null);
					session('backup_file', null);
					session('backup_config', null);
					action_log('admin/data/export', 'data', 1, 1);
					return $this->success('备份完成！');
				}
			} else {
				$tab = array('id' => $id, 'start' => $start[0]);
				$rate = floor(100 * ($start[0] / $start[1]));
				return $this->success("正在备份...({$rate}%)", '', array('tab' => $tab));
			}
		} else {
			action_log('admin/data/export', 'data', 0, 0);
			return $this->error('参数错误！');
		}
	}
	public function import($time = 0, $part = null, $start = null)
	{
		if (is_numeric($time) && is_null($part) && is_null($start)) {
			$name = date('Ymd-His', $time) . '-*.sql*';
			$path = realpath(RUNTIME_PATH . DS . config('data_backup_path') . DS) . DIRECTORY_SEPARATOR . $name;
			$files = glob($path);
			$list = array();
			foreach ($files as $name) {
				$basename = basename($name);
				$match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
				$gz = preg_match('/^\\d{8,8}-\\d{6,6}-\\d+\\.sql.gz$/', $basename);
				$list[$match[6]] = array($match[6], $name, $gz);
			}
			ksort($list);
			$last = end($list);
			if (count($list) === $last[0]) {
				session('backup_list', $list);
				return $this->success('初始化完成！', '', array('part' => 1, 'start' => 0));
			} else {
				action_log('admin/data/import', 'data', 0, 0, '备份文件可能已经损坏，请检查！');
				return $this->error('备份文件可能已经损坏，请检查！', '');
			}
		} elseif (is_numeric($part) && is_numeric($start)) {
			$list = session('backup_list');
			$db = new \com\Database($list[$part], array('path' => realpath(config('data_backup_path')) . DIRECTORY_SEPARATOR, 'compress' => $list[$part][2]));
			$start = $db->import($start);
			if (false === $start) {
				action_log('admin/data/import', 'data', 0, 0);
				return $this->error('还原数据出错！', '');
			} elseif (0 === $start) {
				if (isset($list[++$part])) {
					$data = array('part' => $part, 'start' => 0);
					return $this->success("正在还原...#{$part}", '', $data);
				} else {
					session('backup_list', null);
					action_log('admin/data/import', 'data', 1, 1);
					return $this->success('还原完成！');
				}
			} else {
				$data = array('part' => $part, 'start' => $start[0]);
				if ($start[1]) {
					$rate = floor(100 * ($start[0] / $start[1]));
					return $this->success("正在还原...#{$part} ({$rate}%)", '', $data);
				} else {
					$data['gz'] = 1;
					return $this->success("正在还原...#{$part}", '', $data);
				}
			}
		} else {
			action_log('admin/data/import', 'data', 0, 0);
			return $this->error('参数错误！');
		}
	}
	public function replace()
	{
		if (IS_POST) {
			$data = $this->request->param();
			if (empty($data['rpfield'])) {
				$this->error("请手工指定要替换的字段");
			}
			if (empty($data['rpstring'])) {
				$this->error("请指定要被替换内容！");
			}
			$exptable = $data['exptable'];
			$Db = Db::connect();
			$exptable = $data['exptable'];
			$rpfield = trim($data['rpfield']);
			$rpstring = $data['rpstring'];
			$tostring = $data['tostring'];
			$condition = trim(stripslashes($data['condition']));
			$condition = empty($condition) ? '' : " where {$condition} ";
			$sql = "update {$exptable} set {$rpfield} = Replace({$rpfield},'{$rpstring}','{$tostring}') {$condition} ";
			if (false !== $Db->execute($sql)) {
				action_log('admin/data/replace', 'data', $rpfield, 1);
				return $this->success("批量替换完成!SQL执行语句!<br>" . $sql);
			} else {
				action_log('admin/data/replace', 'data', 0, 0);
				return $this->error("替换出错");
			}
		} else {
			$Db = Db::connect();
			$list = $Db->query('SHOW TABLE STATUS');
			$list = array_map('array_change_key_case', $list);
			$tablearr = array();
			foreach ($list as $key => $val) {
				$tablearr[$key] = current($val);
			}
			$this->setNav();
			$this->assign('list_table', $tablearr);
			return view('replace', [], ['</body>' => config('xianyucms.copyright')]);
		}
	}
	public function ajaxfields()
	{
		$id = input('post.id');
		if (!empty($id)) {
			$Db = Db::connect();
			$array = $Db->getFields($id);
			$html .= "<div style='border:1px solid #ababab;width:100%;background-color:#FEFFF0;margin-top:6px;padding:3px;line-height:160%'>";
			$html .= "表(" . $id . ")含有的字段：<br>";
			foreach ($array as $key => $val) {
				if (preg_match("/^uid|^rules|^type|^module|^username|^password|^nickname|^salt|^group_id/", $val['name'])) {
					continue;
				}
				$html .= "<a id=\"field\" title=\"" . $val['name'] . "\">" . $val['name'] . "</a>\r\n";
			}
			$html .= "</div>";
			return $this->success('获取成功', '', $html);
		} else {
			return $this->error('参数错误！');
		}
	}
	public function clearall()
	{
		$Db = Db::connect();
		$prefix = config('database.prefix');
		$table = array('action_log', 'actor', 'actors', 'ads', 'cm', 'cm_opinion', 'favorite', 'gb', 'link', 'mcid', 'msg', 'news', 'newsrel', 'playlog', 'prty', 'remind', 'role', 'slide', 'special', 'star', 'story', 'tag', 'topic', 'urls', 'visitors', 'vod', 'vodtv', 'vod_mark', 'weekday');
		foreach ($table as $key => $val) {
			$sql = "Truncate table `{$prefix}{$val}`";
			$Db->execute($sql);
		}
		action_log('admin/data/clearall', 'data', 1, 1);
		return $this->success("清空成功");
	}
	function senior()
	{
		$tables_data_length = $tables_index_length = $tables_free_length = $tables_data_count = 0;
		$tables = $list = [];
		$list = Db::query("SHOW TABLES");
		foreach ($list as $key => $row) {
			$tables[] = ['name' => reset($row), 'rows' => 0];
		}
		$data['tables'] = $tables;
		$data['saved_sql'] = [];
		$this->assign($data);
		$this->setNav();
		return $this->view->fetch();
	}
	public function query()
	{
		$do_action = $this->request->post('do_action');
		echo '<style type="text/css">
            xmp,body{margin:0;padding:0;line-height:18px;font-size:12px;font-family:"Helvetica Neue", Helvetica, Microsoft Yahei, Hiragino Sans GB, WenQuanYi Micro Hei, sans-serif;}
            hr{height:1px;margin:5px 1px;background:#e3e3e3;border:none;}
            </style>';
		if ($do_action == '') {
			exit("请选择相关表");
		}
		$tablename = $this->request->post("tablename/a");
		action_log('admin/data/query', 'data', $do_action, 1);
		if (in_array($do_action, array('doquery', 'optimizeall', 'repairall'))) {
			$this->{$do_action}();
		} else {
			if (count($tablename) == 0) {
				exit('请选择相关表');
			} else {
				foreach ($tablename as $table) {
					$this->{$do_action}($table);
					echo '<br />';
				}
			}
		}
	}
	private function viewinfo($name)
	{
		$row = Db::query("SHOW CREATE TABLE `{$name}`");
		$row = array_values($row[0]);
		$info = $row[1];
		echo "<xmp>{$info};</xmp>";
	}
	private function viewdata($name = '')
	{
		$sqlquery = "SELECT * FROM `{$name}`";
		$this->doquery($sqlquery);
	}
	private function doquery($sql = null)
	{
		$sqlquery = $sql ? $sql : $this->request->post('sqlquery');
		if ($sqlquery == '') {
			exit('SQL语句不能为空');
		}
		$sqlquery = str_replace("\r", "", $sqlquery);
		$sqls = preg_split("/;[ \t]{0,}\n/i", $sqlquery);
		$maxreturn = 50;
		$r = '';
		foreach ($sqls as $key => $val) {
			if (trim($val) == '') {
				continue;
			}
			$val = rtrim($val, ';');
			$r .= "SQL：<span style='color:green;'>{$val}</span> ";
			if (preg_match('/^(select|explain)(.*)/i ', $val)) {
				Debug::remark("begin");
				$limit = stripos(strtolower($val), "limit") !== false ? true : false;
				$count = Db::execute($val);
				if ($count > 0) {
					$resultlist = Db::query($val . (!$limit && $count > $maxreturn ? ' LIMIT ' . $maxreturn : ''));
				} else {
					$resultlist = [];
				}
				Debug::remark('end');
				$time = Debug::getRangeTime('begin', 'end', 4);
				$usedseconds = "耗时" . $time . "秒!<br />";
				if ($count <= 0) {
					$r .= '返回结果为空!';
				} else {
					$r .= "共有" . $count . "条记录!" . (!$limit && $count > $maxreturn ? ',共有' . $maxreturn . '条记录! ' : "");
				}
				$r = $r . ',' . $usedseconds;
				$j = 0;
				foreach ($resultlist as $m => $n) {
					$j++;
					if (!$limit && $j > $maxreturn) {
						break;
					}
					$r .= "<hr/>";
					$r .= "<font color='red'>记录：" . $j . "</font><br />";
					foreach ($n as $k => $v) {
						$r .= "<font color='blue'>{$k}：</font>{$v}<br/>\r\n";
					}
				}
			} else {
				Debug::remark('begin');
				$count = Db::execute($val);
				Debug::remark('end');
				$time = Debug::getRangeTime('begin', 'end', 4);
				$r .= "共影响" . $count . "条记录! 耗时：" . $time . "秒!<br />";
			}
		}
		echo $r;
	}
	private function optimizeall($name = '')
	{
		$list = Db::query("SHOW TABLES");
		foreach ($list as $key => $row) {
			$name = reset($row);
			if (Db::execute("OPTIMIZE TABLE {$name}")) {
				echo "优化表[" . $name . "]成功";
			} else {
				echo '优化表[' . $name . "]失败";
			}
			echo '<br />';
		}
	}
	private function repairall($name = '')
	{
		$list = Db::query("SHOW TABLES");
		foreach ($list as $key => $row) {
			$name = reset($row);
			if (Db::execute("REPAIR TABLE {$name}")) {
				echo "修复表[" . $name . "]成功";
			} else {
				echo '修复表[' . $name . "]失败";
			}
			echo '<br />';
		}
	}
	private function repairs($name = '')
	{
		if (Db::execute("REPAIR TABLE `{$name}`")) {
			echo "修复表[" . $name . "]成功";
		} else {
			echo '修复表[' . $name . "]失败";
		}
	}
	private function optimizes($name = '')
	{
		if (Db::execute("OPTIMIZE TABLE `{$name}`")) {
			echo "优化表[" . $name . "]成功";
		} else {
			echo '优化表[' . $name . "]失败";
		}
	}
}