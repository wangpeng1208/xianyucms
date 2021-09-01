<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Story extends Base{
 protected $rule = [
        ['story_vid', 'require|number|unique:story', '视频ID不能为空|视频ID必须为数字|视频ID已存在'],
		['story_continu', 'number', '连载集数必须为数字'],
		['story_page', 'number', '连载集数必须为数字'],
		['story_cid', 'require|number|checkCid', '剧情分类不能为空|剧情分类必须为数字|请选择当前剧情分类下面的子栏目'],
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