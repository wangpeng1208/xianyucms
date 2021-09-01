<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\model;
class AuthRule extends Model{
	const rule_url = 1;
	const rule_mian = 2;
	protected $type = array(
		'id'    => 'integer',
	);
	public $keyList = array(
		array('name'=>'title','title'=>'节点名称','type'=>'text','help'=>''),
		array('name'=>'name','title'=>'节点标识','type'=>'text','help'=>''),
		array('name'=>'status','title'=>'状态','type'=>'select','option'=>array('1'=>'启用','0'=>'禁用'),'help'=>''),
		array('name'=>'condition','title'=>'条件','type'=>'text','help'=>'')
	);
	public function uprule($data, $type){
		foreach ($data as $value) {
			$data = array(
				'module' => $type,
				'type'   => 'file',
				'name'   => $value['url'],
				'title'  => $value['title'],
				'status' => 1,
			);
			$id = $this->where(array('name' => $data['name']))->value('id');
			if ($id) {
				$data['id'] = $id;
				$this->save($data, array('id' => $id));
			} else {
				self::create($data);
			}
		}
		return true;
	}
	/**
	 * 数据修改
	 * @return [bool] [是否成功]
	 */
	public function change(){
		$data = \think\Request::instance()->post();
		if (isset($data['id']) && $data['id']) {
			return $this->save($data, array('id'=>$data['id']));
		}else{
			return $this->save($data);
		}
	}	
}