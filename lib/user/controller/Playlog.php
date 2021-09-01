<?php
namespace app\user\controller;
use app\common\controller\All;
use think\Controller;
use app\user\model\Playlog as PlaylogModel;

class Playlog extends All {
    public function get(){
        $rs = new PlaylogModel();
        $list= $rs->find();
        $this->assign('list',$list);
		return view('/playlog/get');
    }
    public function set(){
		    if($this->request->isPost()){
                $data['log_vid'] = input('log_vid/d');
                $data['log_sid'] = input('log_sid/d');
                $data['log_pid'] = input('log_pid/d') ;
                $data['log_urlname'] = input('log_urlname/s');
                $data['log_maxnum'] = input('log_maxnum/d');
                $rs = new PlaylogModel();
                return $rs->add($data);
			}
    }
    public function clear(){
		if(cmf_is_user_login()){
		   $result=db('playlog')->where('log_uid',cmf_get_current_user_id())->delete();
		}else{
		   cookie('xianyu_playlog',null);
		}
		return ['msg'=>'清空成功','rcode' =>'1']; 
    }
    public function del(){
		    if(request()->isPost()){
            $data['log_id'] = input('log_id/d');
			$data['log_vid'] = input('log_vid/d');
			return model('playlog')->del($data);
			}
    }		

}

