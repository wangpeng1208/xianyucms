<?php

namespace app\home\controller;

use app\common\controller\Home;

class Map extends Home
{
    public function show()
    {
        if ($this->Url['dir']) {
            $this->Url['id'] = getlist($this->Url['dir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }

        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List && $List[0]['list_sid'] == 11) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $this->assign('page', $this->Url['page']);
            $this->assign($channel);
            $rss = $this->view->fetch('/' . $channel['list_skin']);
            return xml($rss);
        } else {
            abort(404, '页面不存在');
        }
    }
}