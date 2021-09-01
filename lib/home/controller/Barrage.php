<?php

namespace app\home\controller;

use think\Controller;

class Barrage extends Controller
{
    //弹幕调用
    public function index()
    {
        if ($this->request->isAjax()) {
            $config = get_addon_config('barrage');
            $vod_id = $this->Url['id'];
            $t = input('t/d', 0);
            $oncomm = $config['barrage_oncomm'];
            if (!empty($t)) {
                $limit = 1;
                $color = $config['barrage_opencolor'];
                $order = "cm_id";
                $result['looper_time'] = 0;
            } else {
                $limit = $config['barrage_limit'];
                $color = $config['barrage_color'];
                $order = $config['barrage_order_type'];
                $result['looper_time'] = $config['barrage_time'];
                $where['cm_status'] = 1;
            }
            if (!empty($vod_id)) {
                $rs = model("Cm");
                $where['cm_vid'] = $vod_id;
                $total = $rs->getcmcount($where);
                if ($oncomm) {
                    if ($total > 0) {
                        $list = $rs->gettcomments($where, $limit, $order);  //查询所有结果集
                    } else {
                        $list = $rs->gettcomments($w, $limit, $order);
                    }
                } else {
                    $list = $rs->gettcomments($where, $limit, $order);
                }
                foreach ($list as $key => $value) {
                    $avatar = ps_getavatar($value['cm_uid'], $value['pic'], $value['api'][0]['avatar']);
                    $image = $avatar['small'];
                    $result['items'][$key]['img'] = $image;    //图片
                    $result['items'][$key]['info'] = msubstr($value['cm_content'], 0, $config['barrage_info'], '...');     //文字
                    $result['items'][$key]['href'] = xianyu_user_url('user/home/index', array('id' => $value['cm_uid']), true, false); //连接
                    $result['items'][$key]['close'] = $config['barrage_close']; //显示关闭按钮
                    $result['items'][$key]['speed'] = $config['barrage_speed']; //延迟,单位秒,默认6
                    $result['items'][$key]['color'] = $color; //颜色,默认白色
                    $result['items'][$key]['bottom'] = $config['barrage_bottom'];//距离底部高度,单位px,默认随机
                    $result['items'][$key]['old_ie_color'] = '#000000';//ie低版兼容色,不能与网页背景相同,默认黑色
                }
            }
            return json($result);
        }
    }
}