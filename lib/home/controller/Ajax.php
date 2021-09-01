<?php

namespace app\home\controller;

use app\common\controller\Home;

class Ajax extends Home
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $this->assign('param', $this->Url);
            $array_list = list_search(F('_data/list'), 'list_id=' . $this->Url['cid']);
            $this->assign('param', $this->Url);
            $this->assign('list', $array_list[0]);
            return view('/' . $array_list[0]['list_skin_detail'] . "_ajax");
        }
    }
}