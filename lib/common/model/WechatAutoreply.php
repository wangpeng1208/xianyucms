<?php

namespace app\common\model;

use think\Model;

class WechatAutoreply extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'auto_addtime';
    protected $updateTime = 'auto_uptime';

}
