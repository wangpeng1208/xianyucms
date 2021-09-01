<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\model;
use think\Model;
class Actors extends Model {
    public function actors_update($id, $actors, $type) {
        $rs = db("Actors");
        $data['actors_id'] = $id;
        $data['actors_type'] = $type;
        $rs->where($data)->delete();
        if (!empty($actors) && !empty($id) && !empty($type)) {
            if (is_array($actors)) {
                $actor_arr = $actors;
            } else {
                $actor_arr = explode(',', str_replace(array(
                    '/',
                    '|',
                    ' ',
                    '，',
                    '、'
                ) , ',', $actors));
            }
            $actor_arr = array_filter(array_unique($actor_arr));
            foreach ($actor_arr as $key => $val) {
                if ($val == "未知") {
                    continue;
                }
                $datas[$key]['actors_id'] = $id;
                $datas[$key]['actors_type'] = $type;
                $datas[$key]['actors_name'] = $val;
            }
            if (isset($datas)) {
                $rs->insertAll($datas);
            }
        }
    }
}

