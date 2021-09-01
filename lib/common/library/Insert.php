<?php
namespace app\common\library;
class Insert{
    public function vod($data){
		return vod($data);	
    }	
    public function news($data){
		return news($data);	
    }
	
    public function star($data){
	    return star($data);	
   }
    public function story($data){
		return story($data);		

   }
   public function story_add($data,$vid){ 
        return story_add($data,$vid);
   } 	
    public function actor($data){
	    return actor($data);
    }
    public function actor_add($data,$vid){
        return actor_add($data,$vid);
   }
	public function Cm($data){
		return Cm($data);
	}
	public function cm_add($data,$vid){
		return cm_add($data,$vid);
	}	       
}
require APP_PATH.'common/library/Insert_sys.php';
