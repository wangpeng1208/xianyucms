<?php

namespace app\home\controller;

use app\common\controller\Home;

class Tag extends Home
{
    public function show()
    {
        if ($this->Url['dir']) {
            $mode = list_search(F('_data/modellist'), 'name=' . $this->Url['dir']);
        } else {
            $mode = list_search(F('_data/modellist'), 'id=' . $this->Url['id']);
        }
        if ($mode[0]) {
            config('params', array('id' => $mode[0]['id'], 'dir' => $mode[0]['name'], 'tag' => urlencode($this->Url['tag']), 'p' => "xianyupage"));
            $channel = $this->Lable_Tag($this->Url, $mode[0]);
            $this->assign('param', $this->Url);
            $this->assign($channel);
            //AJAX页面加载
            if ($this->request->isAjax()) {
                $channel['list_skin_ajax'] = $channel['list_skin'] . "_ajax";
                return view('/' . $channel['list_skin_ajax']);
            } else {
                return view('/' . $channel['list_skin']);
            }
        } else {
            abort(404, '页面不存在');
        }
    }

}