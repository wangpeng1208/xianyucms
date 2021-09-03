<?php

namespace app\common\model;

use think\Model;

class Urls extends Model
{
    //获取相关的数据转为字符串
    public function urls_data($id)
    {
        $rs = db("Urls");
        $data['urls_id'] = $id;
        $list = $rs->where($data)->select();
        foreach ($list as $key => $val) {
            $value[$key] = $val['urls_reurl'];
        }
        if ($value) {
            return $value;
        } else {
            return array();
        }
    }

    public function urls_update($id, $urls)
    {
        $rs = db("Urls");
        $data['urls_id'] = $id;
        $rs->where($data)->delete();
        if (!empty($urls) && !empty($id)) {
            if (is_array($urls)) {
                $urls_arr = $urls;
            } else {
                $urls_arr = explode(',', $urls);
                $urls_arr = array_filter(array_unique($urls_arr));
            }
            foreach ($urls_arr as $key => $val) {
                $datas[$key]['urls_id'] = $id;
                $datas[$key]['urls_reurl'] = $val;
            }
            if (isset($datas)) {
                $rs->insertAll($datas);
            }
        }
    }
}

