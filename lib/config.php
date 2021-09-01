<?php
$database = require RUNTIME_PATH.'conf/database.php';
$config = require RUNTIME_PATH.'conf/config.php';
$array =[
    // 设置超级管理员ID
    'user_administrator' => 1,
    // 开启后台登录验证码
    'admin_login_validate' => false,	//开启验证码为：true 关闭：false
    // 插件目录设置
	'addon_dir'   => ['lib', 'public','tpl'],
    // +----------------------------------------------------------------------
    // 默认模块名
    'default_module'         => 'home',
    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------
    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '-',
    // 是否开启路由
    'url_route_on'           => true,
    // +----------------------------------------------------------------------
    // | 模版相关设置
    // +----------------------------------------------------------------------	
	//PC手机模版目录名设置
    'theme' => [
		'pc_theme' => $config['default_theme'],	
		'm_theme'=>   $config['mobile_theme'],	
    ],	
	//模版相关路径缓存与过滤空格设置
    'template' => [
		'view_path' => ROOT_PATH.'tpl' . DS . 'home' . DS,
		'tpl_cache' => $config['tpl_cache'],
		'strip_space' => $config['strip_space'],	
    ],
    // 视图输出字符串内容替换
    'view_replace_str' => [
		'__TPL__' => ROOT_PATH.'tpl',	
	],	
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
	//404 等状态页面设置
    'http_exception_template'    =>  [
    // 定义404错误的重定向页面地址
    404 =>  ROOT_PATH.'tpl' . DS . 'home' . DS .$config['default_theme'].DS.'my_404.html',
    // 还可以定义其它的HTTP status
    401 =>  ROOT_PATH.'tpl' . DS . 'home' . DS .$config['default_theme'].DS.'my_401.html',
     ],
    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache'                  => [
	    //缓存存储驱动
	    'type'   => $config['data_cache_type'], //如果设置缓存出现错误手动设置为file
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => $config['data_cache_prefix'],
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],
    // | 日志设置
    // +----------------------------------------------------------------------
    'log'  => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'   => $config['log_type'],
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => ['sql'],
    ],
    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------
    'session'            => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'i7_',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        'expire' => 24*3600*180
    ],
    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => 'i7_',
        // cookie 保存时间
        'expire'    => 24*3600*180,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],
	'cache_prefix' => $config['data_cache_prefix'],
	'database'=>$database,
    // +----------------------------------------------------------------------
    // | 静态缓存相关设置
    // +----------------------------------------------------------------------
    'html_cache_compile_type' => 'file',//缓存存储驱动
    'html_cache_rules' => [
    //'静态地址' => array('静态规则', '有效期', '附加规则'),
    //1.任意控制器的任意操作都适用
	'home:index:index'=>['index',$config['html_cache_index']],
    'home:vod:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_vod_list']],
	'home:vod:type'=>['type/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_vod_type']],
	'home:vod:read'=>['vod/{id|get_small_id}{pinyin|get_small_vod_id}/{id}{pinyin|get_vod_id}/index',$config['html_cache_vod_read']],
	'home:vod:play'=>['vod/{id|get_small_id}{pinyin|get_small_vod_id}/{id}{pinyin|get_vod_id}/play_{$_SERVER.REQUEST_URI|md5}',$config['html_cache_vod_play']],
	'home:vod:news'=>['vod/{id|get_small_id}{pinyin|get_small_vod_id}/{id}{pinyin|get_vod_id}/news_{$_SERVER.REQUEST_URI|md5}',$config['html_cache_vod_other']],
	'home:vod:filmtime'=>['vod/{id|get_small_id}{pinyin|get_small_vod_id}/{id}{pinyin|get_vod_id}/filmtime',$config['html_cache_vod_other']],	
	'home:story:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_story_list']],
	'home:story:read'=>['story/{id|get_small_story_id}{pinyin|get_small_vod_id}/{id|get_story_id}{pinyin|get_vod_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_story_read']],	
	'home:actor:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_actor_list']],
	'home:actor:read'=>['actor/{id|get_small_actor_id}{pinyin|get_small_vod_id}/{id|get_actor_id}{pinyin|get_vod_id}/index',$config['html_cache_actor_read']],	
	'home:role:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_role_list']],
	'home:role:read'=>['actor/{id|get_small_role_id}/{id|get_role_id}/{id}',$config['html_cache_role_read']],	
	'home:news:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_news_list']],
	'home:news:read'=>['news/{id|get_small_id}/{id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_news_read']],	
	'home:star:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_list']],
	'home:star:type'=>['type/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_type']],	
	'home:star:read'=>['star/{id|get_small_id}{pinyin|get_small_star_id}/{id}{pinyin|get_star_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_read']],
	'home:star:work'=>['star/{id|get_small_id}{pinyin|get_small_star_id}/{id}{pinyin|get_star_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_other']],
	'home:star:hz'=>['star/{id|get_small_id}{pinyin|get_small_star_id}/{id}{pinyin|get_star_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_other']],
	'home:star:role'=>['star/{id|get_small_id}{pinyin|get_small_star_id}/{id}{pinyin|get_star_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_other']],
	'home:star:news'=>['star/{id|get_small_id}{pinyin|get_small_star_id}/{id}{pinyin|get_star_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_star_other']],	
	'home:tv:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_tv_list']],
	'home:tv:read'=>['tv/{id|get_small_id}{pinyin|get_small_tv_id}/{id}{pinyin|get_tv_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_tv_read']],
	'home:special:show'=>['show/{id}{dir|get_list_id}/{$_SERVER.REQUEST_URI|md5}',$config['html_cache_special_list']],
	'home:special:read'=>['special/{id|get_small_id}{pinyin|get_small_special_id}/{id}{pinyin|get_special_id}/{id}{pinyin|get_special_id}',$config['html_cache_special_read']],
	'home:my:show'=>['my/{id}{dir|get_list_id}',$config['html_cache_my_list']],
 ],	
];
return array_merge($config,$array);