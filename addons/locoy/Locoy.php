<?php
namespace addons\Locoy;
use app\common\library\Menu;
use think\Addons;
/**
 * 数据库插件
 */
class Locoy extends Addons{

     /**
     * 插件安装方法
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall(){
        return true;
    }

}
