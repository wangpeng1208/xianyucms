<?php
error_reporting(E_ALL & ~E_NOTICE );
error_reporting(0);
// [ 应用入口文件 ]
@set_time_limit(300);
@ini_set("memory_limit",'-1');
// 定义入口文件目录
define('URL_PATH',__DIR__ .'/');

// 定义入口文件所在位置
define('PUBLIC_PATH','');
// 定义应用目录
define('APP_PATH', __DIR__ . '/../lib/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
