<?php

namespace app\common\model;

use think\Model;

class Vodtv extends Model
{
    //获取相关的数据转为字符串
    public function tv_data($id, $sid)
    {
        $rs = db("vodtv");
        $data['vodtv_id'] = $id;
        $data['vodtv_sid'] = $sid;
        $list = $rs->where($data)->column('vodtv_name');
        return $list;
    }

    public function tv_update($id, $cid, $sid)
    {
        $rs = db("Vodtv");
        $data['vodtv_id'] = $id;
        $data['vodtv_sid'] = $sid;
        $rs->where($data)->delete();
        if (!empty($cid)) {
            if (is_array($cid)) {
                $weekdaycid_arr = $cid;
            } else {
                $weekdaycid_arr = explode(',', $cid);
            }
            $weekdaycid_arr = array_unique($weekdaycid_arr);
            foreach ($weekdaycid_arr as $key => $val) {
                if ($val == "未知") {
                    continue;
                }
                $datas[$key]['vodtv_id'] = $id;
                $datas[$key]['vodtv_sid'] = $sid;
                $datas[$key]['vodtv_name'] = $val;
            }
            if (isset($datas)) {
                $rs->insertAll($datas);
            }
        }
    }

    public function tv_add($id, $cid, $sid)
    {
        if (!empty($cid) && !empty($id) && !empty($sid)) {
            $rs = db("Vodtv");
            $data['vodtv_id'] = $id;
            $data['vodtv_name'] = $cid;
            $data['vodtv_sid'] = $sid;
            $rs->where($data)->delete();
            $rs->insert($data);
        }
    }
}