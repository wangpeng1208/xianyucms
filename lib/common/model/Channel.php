<?php

namespace app\common\model;

use think\Model;

class Channel extends Model
{
    protected $name = "List";

    protected function setListdirAttr($value, $data)
    {
        $pinyin = new \com\Hzpy();
        if (empty($value)) {
            $listdir = getletters(trim($data['list_name']), 'list');
            return $listdir;
        } else {
            return trim($value);
        }
    }

}