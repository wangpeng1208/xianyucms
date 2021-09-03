<?php

namespace app\common\model;

use think\Model;

class Base extends Model
{
    protected $type = array(
        'id' => 'integer',
        'cover_id' => 'integer',
    );

    public function change()
    {
        $data = \think\Request::instance()->post();
        if (isset($data['id']) && $data['id']) {
            return $this->save($data, array('id' => $data['id']));
        } else {
            return $this->save($data);
        }
    }
}