<?php
namespace app\api\controller;
use app\common\controller\Admin;
class Timming extends Admin{
    //构造函数
    public function _initialize(){
        parent::_initialize();
		set_time_limit(0);
		$url['tid']=input('tid/d','');
		$url['pwd']=htmlspecialchars(input('pwd/s',''));
		$url['os']=input('os/d','');
		if(!$url['tid'] || !$url['pwd']){
			return $this->error('任务ID和任务密码不能为空');
		}
		$info = model('timming')->where(array('timming_id'=>$url['tid'],'timming_status'=>1))->find();
		if(!$info){
			return $this->error('任务不存在或已禁用');
		}
		if($info['timming_password']!=$url['pwd']){
			return $this->error('任务密码错误');
		}
		$this->info=$info;
		$this->url=$url;
    }	
    public function index(){
		if($this->info['timming_apiid']){
			$apiarray=json_decode($this->info['timming_apiid'],true);
				foreach($apiarray as $key=>$value){
			        $array['cj_url'][$key] = zp_url('api/timming/data',array('tid'=>$this->info['timming_id'],'pwd'=>$this->info['timming_password'],'apiid'=>$value),false,false).'-'.$this->info['timming_cjaction'];
		        }
			$array['caiji']=$this->info['timming_cjtime'];
			$array['create']=$this->info['timming_htmltime'];
			$array['html_url']=zp_url('api/timming/'.$this->info['timming_htmlaction'],array('tid'=>$this->info['timming_id'],'pwd'=>$this->info['timming_password'],'apiid'=>$value));
			$config_url=F('_data/url_html_config');
			$array['url_html']=$config_url['url_html'];
			$array['type']=$this->info['timming_type'];
			$array['data']=$this->info;
			
			db('timming')->where('timming_id',$this->info['timming_id'])->setField('timming_uptime',time());
			$this->assign($array);
			return view('time_wait');
        }			
    }
    public function data(){
		$request = controller('admin/collect');
		$request->data();
    }
    public function create(){		
		$request = controller('admin/html');
		$request->create();
    }	
    public function create_time(){		
		$request = controller('admin/html');
		$request->create_time();
    }
    public function list_create(){	
		$request = controller('admin/html');
		$request->list_create();
    }	
    public function vod_detail_cids(){	
		$request = controller('admin/html');
		$request->vod_detail_cids();
    }
    public function vod_detail_id(){	
		$request = controller('admin/html');
		$request->vod_detail_id();
    }
    public function news_detail_cids(){	
		$request = controller('admin/html');
		$request->news_detail_cids();
    }
    public function news_detail_id(){	
		$request = controller('admin/html');
		$request->news_detail_id();
    }
    public function star_detail_id(){	
		$request = controller('admin/html');
		$request->news_detail_id();
    }
    public function star_detail_cids(){	
		$request = controller('admin/html');
		$request->star_detail_cids();
    }
    public function story_detail_id(){	
		$request = controller('admin/html');
		$request->story_detail_id();
    }
    public function story_detail_cids(){	
		$request = controller('admin/html');
		$request->story_detail_cids();
    }
    public function actor_detail_id(){	
		$request = controller('admin/html');
		$request->actor_detail_id();
    }
    public function actor_detail_cids(){	
		$request = controller('admin/html');
		$request->actor_detail_cids();
    }
    public function special_detail_id(){	
		$request = controller('admin/html');
		$request->special_detail_id();
    }
    public function special_detail_cids(){	
		$request = controller('admin/html');
		$request->special_detail_cids();
    }
    public function role_detail_id(){	
		$request = controller('admin/html');
		$request->role_detail_id();
    }	
    public function role_detail_cids(){	
		$request = controller('admin/html');
		$request->role_detail_cids();
    }
    public function my_detail_cids(){	
		$request = controller('admin/html');
		$request->my_detail_cids();
    }
    public function index_create(){	
		$request = controller('admin/html');
		$request->index_create();
    }	
}