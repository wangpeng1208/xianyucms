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
class WechatResponse extends Model{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'response_addtime';
    protected $updateTime = 'response_uptime';
	protected $auto = ['response_eventkey', 'response_content'];
	protected function setResponseEventkeyAttr($value){
		$eventkey = isset($value) && $value ? $value : uniqid();
		return $eventkey;
	}	

}
