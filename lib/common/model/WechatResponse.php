<?php

namespace app\common\model;

use think\Model;

class WechatResponse extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'response_addtime';
    protected $updateTime = 'response_uptime';
    protected $auto = ['response_eventkey', 'response_content'];

    protected function setResponseEventkeyAttr($value)
    {
        $eventkey = isset($value) && $value ? $value : uniqid();
        return $eventkey;
    }

}
