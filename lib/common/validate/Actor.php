<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Actor extends Base{
 protected $rule = [
        ['actor_vid', 'require|number|unique:actor', '视频ID不能为空|视频ID必须为数字|视频ID已经存在'],
		['actor_cid', 'checkCid', '请选择当前分类下面的子栏目'],
    ];
     protected function checkCid($value){
          	$tree= list_search(F('_data/listtree'),'list_id='.$value);
	        if(!empty($tree[0]['son'])){
		       return false;
	        }else{
	           return true;
	        }
    }
}