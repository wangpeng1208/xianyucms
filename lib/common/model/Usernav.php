<?php

namespace app\common\model;

use think\Model;

class Usernav extends Model
{
    protected function getcidAttr($value, $data)
    {
        $sid = array(0 => '会员中心', 1 => '网站前台');
        return $sid[$data['cid']];
    }
}