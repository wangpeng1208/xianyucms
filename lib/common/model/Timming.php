<?php

namespace app\common\model;

use think\Model;

class Timming extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'timming_addtime';//自动写入时间

    protected function settimmingaddtimeAttr()
    {
        return time();
    }

    public function settimmingapiidAttr($value, $data)
    {
        if ($data['timming_apiid']) {
            return json_encode($data['timming_apiid']);
        } else {
            return $value;
        }
    }


}