<?php
namespace app\common\model;
use think\Model;
class Module extends Model{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'module_addtime';
    protected $updateTime = 'module_uptime';
	protected function setModuleValueAttr($value,$data){
	    if($data['mode'] == 'json'){
		    $fieldarr = $valuearr = [];
            $field = $data['field'];
            $value = $data['value']; 
            foreach ($field as $k => $v){
                if ($v != ''){
                            $fieldarr[] = $field[$k];
                            $valuearr[] = $value[$k];
                }
            }
             return json_encode(array_combine($fieldarr, $valuearr), JSON_UNESCAPED_UNICODE);		
	    }else{
		    return $value;
	    }	
  
    }
}	
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------