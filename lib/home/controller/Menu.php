<?php

namespace app\home\controller;

use app\common\controller\Home;

class Menu extends Home
{
    public function show()
    {
        if ($this->Url['listdir']) {
            $this->Url['id'] = getlist($this->Url['listdir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        } else {
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $this->assign('param', $this->Url);
            $this->assign($channel);
            return view('/' . $channel['list_skin']);
        } else {
            abort(404, '页面不存在');
        }
    }

    //列表检索
    public function type()
    {
        if ($this->Url['listdir']) {
            $this->Url['id'] = getlist($this->Url['listdir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }
        $JumpUrl = param_jump($this->Url);
        $JumpUrl['p'] = 'xianyupage';
        config('jumpurl', xianyu_url($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action(), $JumpUrl, true, false));
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $channel['thisurl'] = str_replace('xianyupage', $param['page'], xianyu_url($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action(), array('id' => $channel['list_id'], 'listdir' => $channel['list_dir'], 'mcid' => $this->Url['mcid'], 'area' => urlencode($this->Url['area']), 'langaue' => urlencode($this->Url['langaue']), 'year' => $this->Url['year'], 'letter' => $this->Url['letter'], 'order' => 'vod_' . $this->Url['order'], 'p' => $param['page']), true, false));
            $this->assign('param', $this->Url);
            $this->assign($channel);
            //AJAX页面加载
            if ($this->request->isAjax()) {
                $channel['list_skin_ajax'] = $channel['list_skin_type'] . "_ajax";
                return view('/' . $channel['list_skin_ajax']);
            } else {
                return view('/' . $channel['list_skin_type']);
            }
        } else {
            abort(404, '页面不存在');
        }
    }
}