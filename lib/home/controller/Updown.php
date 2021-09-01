<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Db;

class Updown extends Home
{
    public function index()
    {
        $mode = list_search(F('_data/modellist'), 'id=' . $this->Url['sid']);
        if ($this->Url['id'] && $this->Url['type'] && $mode[0]['name']) {
            return json($this->show($this->Url['id'], $this->Url['type'], $mode[0]['name']));
        }
    }

    public function show($id = "", $type = "", $model)
    {
        $userconfig = F('_data/userconfig_cache');
        $cookiename = md5('updown-' . ip2long(get_client_ip()) . '-' . intval($id) . '-' . intval($model));
        $cookie = cookie($cookiename);
        if ($cookie) {
            return ["msg" => "点评速度太快休息一会吧！", "rcode" => "-1", "data" => "0"];
        }
        if ('up' == $type) {
            Db::name($model)->where($model . '_id', $id)->setInc($model . '_up');
        } elseif ('down' == $type) {
            Db::name($model)->where($model . '_id', $id)->setInc($model . '_down');
        }
        cookie($cookiename, true, intval($userconfig['user_second']));
        $array = Db::name($model)->where($model . '_id', $id)->field('' . $model . '_up,' . $model . '_down')->find();
        if (!$array) {
            $array[$model . '_up'] = 0;
            $array[$model . '_down'] = 0;
        }
        $arrays['data'] = $array[$model . '_up'] . ':' . $array[$model . '_down'];
        $arrays['msg'] = "点评成功";
        $arrays['rcode'] = 1;
        return $arrays;
    }
}