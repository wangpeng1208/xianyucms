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
class Weekday extends Model {
    //获取相关的数据转为字符串
    public function weekday_data($id, $sid) {
        $rs = db("Weekday");
        $data['weekday_id'] = $id;
        $data['weekday_sid'] = $sid;
        $list = $rs->where($data)->select();
        foreach ($list as $key => $val) {
            $value[$key] = $val['weekday_cid'];
        }
        if ($value) {
            return $value;
        } else {
            return array();
        }
    }
    public function weekday_update($id, $cid, $sid) {
        $rs = db("Weekday");
        $data['weekday_id'] = $id;
        $data['weekday_sid'] = $sid;
        $rs->where($data)->delete();
        if (!empty($cid) && !empty($id) && !empty($sid)) {
            if (is_array($cid)) {
                $weekdaycid_arr = $cid;
            } else {
                $weekdaycid_arr = explode(',', $cid);
                $weekdaycid_arr = array_filter(array_unique($weekdaycid_arr));
            }
            foreach ($weekdaycid_arr as $key => $val) {
                $datas[$key]['weekday_id'] = $id;
                $datas[$key]['weekday_sid'] = $sid;
                $datas[$key]['weekday_cid'] = $val;
            }
            if (isset($datas)) {
                $rs->insertAll($datas);
            }
        }
    }
}

