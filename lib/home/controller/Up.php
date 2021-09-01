<?php

namespace app\home\controller;

use app\common\controller\Home;

class Up extends Home
{
    public function show()
    {
        $month = input('month/d', date('Ym')) . '01';
        $lastday = date('Y-m-d', strtotime("$month +1 month -1 day"));
        $firstday = date("Ym01", strtotime($month));
        for ($x = 1; $x < 3; $x++) {
            $lastdate[] = strtotime("-" . $x . " month", strtotime($firstday));
            $update[] = strtotime("+" . $x . " month", strtotime($firstday));
        }
        krsort($lastdate);
        if ($this->Url['dir']) {
            $this->Url['id'] = getlist($this->Url['dir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        } else {
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List) {
            config('model', $this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
            if ($this->Url['type']) {
                config('params', array('id' => $List[0]['list_id'], 'dir' => $List[0]['list_dir'], 'type' => $this->Url['type'], 'month' => $this->Url['month'], 'p' => "xianyupage"));
            } else {
                config('params', array('id' => $List[0]['list_id'], 'dir' => $List[0]['list_dir'], 'month' => date('Ym', strtotime($month)), 'p' => "xianyupage"));
            }
            if ($List[0]['list_id'] == 2) {
                $up_title = "开播";

            } else {
                $up_title = "上映";

            }
            $this->assign('param', $this->Url);
            $this->assign($List[0]);
            $this->assign('lastdate', $lastdate);
            $this->assign('update', $update);
            $this->assign('month', strtotime($month));
            $this->assign('firstday', strtotime($firstday));
            $this->assign('lastday', strtotime($lastday));
            $this->assign('up_title', $up_title);
            return view('/up_list');
        }
    }
}