<?php
// +----------------------------------------------------------------------
// | ZanPianCMS
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyucms.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://bbs.xianyucms.com>
// +----------------------------------------------------------------------
namespace app\locoy\controller;
use app\common\controller\Locoy;
use app\common\library\Insert;
class Actor extends Locoy{  
    public function Add(){
		if ($this->request->instance()->isPost()){
            $data   = input('post.');
		    $add= new Insert;
		    $return=$add->actor($data);
            if($return['code']){
			  return "成功：".$return['msg'];
		    }else{
			  return "失败：".$return['msg'];
		    }	
        }			
    }

}
