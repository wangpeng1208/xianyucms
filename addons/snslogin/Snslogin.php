<?php
namespace addons\snslogin;
use app\common\library\Menu;
use think\Addons;
/**
 * 数据库插件
 */
class Snslogin extends Addons{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        $menu = [];
        Menu::create($menu, '');
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        Menu::delete('');
        return true;
    }


}
