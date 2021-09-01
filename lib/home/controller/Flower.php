<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Db;

class Flower extends Home
{
    public function index()
    {
        $mode = list_search(F('_data/modellist'), 'id=' . $this->Url['sid']);
        if ($this->Url['id'] && $mode[0]['name']) {
            return json($this->show($this->Url['id'], $mode[0]['name']));
        }
    }

    public function show($id = "", $model)
    {
        $userconfig = F('_data/userconfig_cache');
        $cookiename = md5('flower-' . ip2long(get_client_ip()) . '-' . intval($id) . '-' . intval($model));
        $cookie = cookie($cookiename);
        if ($cookie) {
            return ["msg" => "您已经送过花啦，休息一会吧！", "rcode" => "-1", "data" => "0"];
        }
        Db::name($model)->where($model . '_id', $id)->setInc($model . '_flower');
        cookie($cookiename, true, intval($userconfig['user_second']));
        $array = Db::name($model)->where($model . '_id', $id)->field($model . '_flower')->find();
        if (!$array) {
            $array[$model . '_flower'] = 0;
        }
        $arrays['data'] = $array[$model . '_flower'];
        $arrays['msg'] = "送花成功";
        $arrays['rcode'] = 1;
        return $arrays;
    }
}