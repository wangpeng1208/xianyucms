<?php

namespace app\locoy\controller;

use app\common\controller\Locoy;
use app\common\library\Insert;

class Story extends Locoy
{
    public function Add()
    {
        if ($this->request->instance()->isPost()) {
            $data = input('post.');
            $add = new Insert;
            $return = $add->story($data);
            if ($return['code']) {
                return "成功：" . $return['msg'];
            } else {
                return "失败：" . $return['msg'];
            }
        }
    }

}
