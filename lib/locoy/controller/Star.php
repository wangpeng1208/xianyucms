<?php

namespace app\locoy\controller;

use app\common\controller\Locoy;
use app\common\library\Insert;

class Star extends Locoy
{
    public function Add()
    {
        if ($this->request->instance()->isPost()) {
            $data = input('post.');
            if (!is_int($data['star_cid'])) {
                $data['star_cid'] = $this->getapilist($data['star_cid'], 'list_name', 'list_id', 3);
            }
            $add = new Insert;
            $return = $add->star($data);
            if ($return['code']) {
                return "成功：" . $return['msg'];
            } else {
                return "失败：" . $return['msg'];
            }
        }
    }
}
