<?php

namespace app\locoy\controller;

use app\common\controller\Locoy;
use app\common\library\Insert;

class News extends Locoy
{
    public function Add()
    {
        if ($this->request->instance()->isPost()) {
            $data = input('post.');
            if (!is_int($data['news_cid'])) {
                $data['news_cid'] = $this->getapilist($data['news_cid'], 'list_name', 'list_id', 2);
            }
            $add = new Insert;
            $return = $add->news($data);
            if ($return['code']) {
                return "成功：" . $return['msg'];
            } else {
                return "失败：" . $return['msg'];
            }
        }
    }
}
