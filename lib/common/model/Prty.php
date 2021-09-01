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
class Prty extends Model {
    //获取相关的数据转为字符串
    public function prty_data($id, $sid) {
        $rs = db("Prty");
        $data['prty_id'] = $id;
        $data['prty_sid'] = $sid;
        $list = $rs->where($data)->select();
        foreach ($list as $key => $val) {
            $value[$key] = $val['prty_cid'];
        }
        if ($value) {
            return $value;
        } else {
            return array();
        }
    }
    public function prty_update($id, $cid, $sid) {
        $rs = db("Prty");
        $data['prty_id'] = $id;
		$data['prty_sid'] = $sid;
        $rs->where($data)->delete();
        if(!empty($cid) && !empty($id) && !empty($sid)) {
            if (is_array($cid)) {
                $prtycid_arr = $cid;
            } else {
                $prtycid_arr = explode(',', $cid);
                $prtycid_arr = array_filter(array_unique($prtycid_arr));
            }
            foreach ($prtycid_arr as $key => $val) {
                $datas[$key]['prty_id'] = $id;
                $datas[$key]['prty_cid'] = $val;
                $datas[$key]['prty_sid'] = $sid;
            }
            if ($datas) {
                $rs->insertAll($datas);
            }
        }
    }
}

