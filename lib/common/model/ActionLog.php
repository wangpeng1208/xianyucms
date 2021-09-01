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
class ActionLog extends Model{
	protected function getModelIdAttr($value, $data){
		$value = $this->get_document_field($data['model'], "name", "id");
		return $value ? $value : 0;
	}
/**
 * 根据条件字段获取数据
 * @param mixed $value 条件，可用常量或者数组
 * @param string $condition 条件字段
 * @param string $field 需要返回的字段，不传则返回整个数据
 */
  function get_document_field($value = null, $condition = 'id', $field = null) {
	if (empty($value)) {
		return false;
	}
	//拼接参数
	$map[$condition] = $value;
	$info= db('Model')->where($map);
	if (empty($field)) {
		$info = $info->field(true)->find();
	} else {
		$info = $info->value($field);
	}
	return $info;
  }	
}