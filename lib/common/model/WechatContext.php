<?php

namespace app\common\model;

use think\Model;

class WechatContext extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'context_addtime';
    protected $updateTime = 'context_uptime';
}
