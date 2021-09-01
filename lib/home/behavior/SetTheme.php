<?php
namespace app\home\behavior;
use think\Config;
use think\Request;
class SetTheme{
    public function run(){

		if(!config('site_status') && !session('member_auth')){
		   header("Content-Type:text/html; charset=utf-8");
		   $close="网站关闭中,请稍后再试....";
           echo $close;
		   exit();
		}
        //根据配置和参数来设置模版。
        $default_view_path = Config::get('template.view_path');//获取设置的模版路径
		$request = Request::instance();
		$domain=$request->domain();
		$mobile=config('mobile_url');
        if ($default_view_path != ''){
			if(str_replace(array('http://','https://'),'',$domain)==str_replace(array('http://','https://'),'',$mobile)){
				$theme_path = Config::get('theme.m_theme'); //设置主题默认路径为手机端
			}else{
			    $theme_path = Config::get('theme.pc_theme'); //设置主题默认路径为电脑端
			}
            Config::set('template.view_path', $default_view_path . $theme_path . DS);//设置模版路径
        }
        //根据配置和来自动切换主题
    }
}