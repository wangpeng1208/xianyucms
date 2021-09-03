<?php

namespace app\common\model;

use think\Model;

class Ads extends Model
{
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}
