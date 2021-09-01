<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;
class Config extends Model{	
	protected $type = array(
		'id'  => 'integer',
	);
	protected $auto = array('name', 'update_time', 'status'=>1);
	protected $insert = array('create_time');
    protected function setUpdateTimeAttr(){
        return time();
    }
    protected function setCreateTimeAttr(){
        return time();
    }	
    protected function setNameAttr($value){
        return strtolower($value);
    }

    protected function getTypeTextAttr($value, $data){
    	$type = config('config_type_list');
    	$type_text = explode(',', $type[$data['type']]);
        return $type_text[0];
    }

	public function lists(){
		$map    = array('status' => 1);
		$data   = $this->db('config')->where($map)->field('type,name')->select();
        $lists = require RUNTIME_PATH .'/conf/configdata.php';
		$config = array();
		if($data && is_array($data)){
			foreach ($data as $value) {
				$config[$value['name']] = $this->parse($value['type'], $lists[$value['name']]);
			}
		}
		return $config;
	}

	/**
	 * 根据配置类型解析配置
	 * @param  integer $type  配置类型
	 * @param  string  $value 配置值
	 */
	private function parse($type, $value){
		switch ($type) {
			case 'textarea': //解析数组
			$array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
			if(strpos($value,':')){
				$value  = array();
				foreach ($array as $val) {
					$list = explode(':', $val);
					if(isset($list[2])){
						$value[$list[0]]   = $list[1].','.$list[2];
					}else{
						$value[$list[0]]   = $list[1];
					}
				}
			}else{
				$value =    $array;
			}
			break;
		}
		return $value;
	}
}