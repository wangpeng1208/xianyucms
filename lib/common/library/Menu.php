<?php
namespace app\common\library;
use app\common\model\AuthRule;
use \com\Tree;
use think\exception\PDOException;
class Menu{	
    /**
     * 创建菜单
     * @param array $menu
     * @param mixed $parent 父类的name或pid
     */
    public static function create($menu, $parent = 0){
        if (!is_numeric($parent)){
            $parentRule = AuthRule::getByName($parent);
            $pid = $parentRule ? $parentRule['id'] : 0;
        }
        else{
            $pid = $parent;
        }
        $allow = array_flip(['name', 'title', 'icon', 'condition', 'remark', 'type', 'sort']);
        foreach ($menu as $k => $v){
            $hasChild = isset($v['sublist']) && $v['sublist'] ? true : false;
            $data = array_intersect_key($v, $allow);
            $data['type'] = isset($data['type']) ? $data['type'] : ($hasChild ? 2 : 1);
            $data['icon'] = isset($data['icon']) ? $data['icon'] : ($hasChild ? 'fa fa-list' : 'fa fa-circle-o');
            $data['pid'] = $pid;
			$data['sort'] = isset($data['sort']) ? $data['sort'] : 0;
            $data['status'] = 1;
            try{
                $menu = AuthRule::create($data);
                if ($hasChild){
                    self::create($v['sublist'], $menu->id);
                }
            }
            catch (PDOException $e){
                print_r($e);
            }
        }
    }
    /**
     * 删除菜单
     * @param string $name 规则name 
     * @return boolean
     */
    public static function delete($name){
        $id = db('AuthRule')->where('name',$name)->value('id');
        if ($id){
           $ruleList = db('AuthRule')->where($where)->order('sort asc,id asc')->select();
		   $ids = Tree::instance()->init($ruleList,'id','pid')->getChildrenIds($id, true);
		   if($ids){
               AuthRule::destroy($ids);
           }
           return true;
        }
        return false;
    }

}
