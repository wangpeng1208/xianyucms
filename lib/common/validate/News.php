<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class News extends Base{
 protected $rule = [
        ['news_name', 'require|unique:news', '新闻标题不能为空|新闻标题存在'],
		['news_cid', 'require|number|checkCid', '分类不能为空|请选择分类|请选择当前分类下面的子栏目'],
		['news_content', 'require', '内容不能为空'],
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