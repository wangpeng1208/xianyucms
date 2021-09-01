<?php
namespace app\locoy\controller;
use app\common\controller\Locoy;
use app\common\library\Insert;
class Vod extends Locoy{  
    public function Add(){
	if ($this->request->instance()->isPost()){
		$data   = input('post.');
		if(!is_int($data['vod_cid'])){
		$data['vod_cid']=$this->getapilist($data['vod_cid'],'list_name','list_id',1);	
		}	
		$result = $this->validate($data,'vod');
		if(true !== $result){		
            return $result;
        }
		$add= new Insert;
		$return=$add->vod($data);
        if($return['code']){
			return "成功：".$return['msg'];
		}else{
			return "失败：".$return['msg'];
			
		}	
	  }
    }




    public function t(){
    	echo $this->getapilist('国产剧','list_name','list_id',1);
    }



}
