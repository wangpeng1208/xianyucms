<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
$_GET['s'] = 'home/notify/cloudpay';
error_reporting(E_ALL & ~E_NOTICE );
error_reporting(0);
// [ 应用入口文件 ]
@set_time_limit(300);
@ini_set("memory_limit",'-1');
// 定义入口文件目录
define('URL_PATH',__DIR__ .'/');
// 定义PUBLIC目录名称
define('PUBLIC_URL',basename(dirname(__FILE__)));
// 定义入口文件所在位置
define('PUBLIC_PATH','');
// 定义应用目录
define('APP_PATH', __DIR__ . '/../lib/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';