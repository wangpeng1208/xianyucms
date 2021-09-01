<?php
define('APP_PATH', __DIR__ . '/lib/');
define('PUBLIC_URL',basename(dirname(__FILE__)));
define('BIND_MODULE','push/Events');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';