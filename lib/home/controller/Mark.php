<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\model\VodMark;
use think\Db;

class Mark extends Home
{
    public function ajax()
    {
        if ($this->request->isAjax()) {
            $p = input('p/d', 1);
            $JumpUrl = param_jump($this->Url);
            $JumpUrl['p'] = 'xianyupage';
            config('params', $JumpUrl);
            $total = 0;
            $rs = new VodMark();
            if (!empty($this->Url['id'])) {
                $start = [];
                $markdata = $rs->getMark($this->Url['id']);
                $value = $rs->getMarkValue($this->Url['id'], get_client_ip());
                if ($value > 0) {
                    $start['hadpingfen'] = 1;
                    $start['mystars'] = $value;
                } else {
                    $start['mystars'] = 0;
                }
                $start['curpingfen'] = [];
                $vod_gold = Db::name('vod')->where([
                    'vod_id' => $this->Url['id'],
                    'vod_status' => 1
                ])->value('vod_gold');
                if ($markdata != null) {
                    $start['curpingfen']['a'] = $markdata['F5'];
                    $start['curpingfen']['b'] = $markdata['F4'];
                    $start['curpingfen']['c'] = $markdata['F3'];
                    $start['curpingfen']['d'] = $markdata['F2'];
                    $start['curpingfen']['e'] = $markdata['F1'];
                    $rate = $rs->getRating($markdata);
                    if (count($rate) > 0) {
                        $start['curpingfen']['pinfenb'] = $rate['R2'];
                        $start['curpingfen']['pinfen'] = $rate['R1'];
                    } else {
                        $start['curpingfen']['a'] = 0;
                        $start['curpingfen']['b'] = 0;
                        $start['curpingfen']['c'] = 0;
                        $start['curpingfen']['d'] = 0;
                        $start['curpingfen']['e'] = 0;
                        $start['curpingfen']['pinfenb'] = $vod_gold * 10;
                        $start['curpingfen']['pinfen'] = $vod_gold;
                    }
                }
                $start['curpingfen']['num'] = $total;
                //是否收藏
                $userinfo = session('user');
                if ($userinfo['userid']) {
                    $favorite = Db::name('favorite')->where(array('favorite_vid' => $this->Url['id'], 'favorite_uid' => $userinfo['userid']))->value('favorite_id');
                    if ($favorite) {
                        $start['loveid'] = $this->Url['id'];
                    }
                    $remin = Db::name('remind')->where(array('remind_vid' => $this->Url['id'], 'remind_uid' => $userinfo['userid']))->value('remind_id');
                    if ($remin) {
                        $start['remindid'] = $this->Url['id'];
                    }
                }
                $result['star'] = $start;
            }
            return json($result);
        }
    }

    //增加评分
    public function add()
    {
        $vod_id = input('id/d', 0);
        $value = input('val/d', 0);
        $result = array("msg" => "提交评分失败", "rcode" => "-1");
        if (isset($vod_id) && isset($value)) {
            if ($value == "1" || $value == "2" || $value == "3" || $value == "4" || $value == "5") {
                //查询一下
                $rs = new VodMark();
                $ip = ip2long(get_client_ip());
                $count = $rs->where(array('mark_vid' => $vod_id, 'mark_ip' => $ip))->count();
                if ($count > 0) { //存在
                    $result['msg'] = "已经评分,请务重复评分";
                    $result['rcode'] = -2;
                } else { //增加评分
                    $mark = array();
                    $mark['mark_vid'] = $vod_id;
                    $mark['mark_ip'] = $ip;
                    $mark['mark_addtime'] = time();
                    $mark['F' . $value] = 1;
                    $id = $rs->insertGetId($mark);
                    if ($id > 0) {
                        $result['msg'] = "提交评分成功";
                        $result['rcode'] = 1;
                        //
                        $data = $rs->getMark($vod_id);
                        $rate = $rs->getRating($data);
                        if (count($rate) > 0) {
                            Db::name('vod')->where('vod_id', $vod_id)->setField('vod_gold', $rate['R1']);
                        }
                    }
                    //重新计算一下
                }
            }
        }
        return json($result);
    }

    public function love()
    {
        if ($this->request->isAjax()) {
            $userinfo = session('user');
            if (!$userinfo['userid']) {
                return json(["msg" => "请先登录", "rcode" => "-1"]);
            }
            $id = input('id/d', 0); //影片ID
            $cid = input('cid/d', 0); //影片CID
            if ($userinfo['userid'] && $id && $cid) {
                $favorite = Db::name('favorite')->where(array('favorite_vid' => $id, 'favorite_uid' => $userinfo['userid']))->value('favorite_id');
                if ($favorite) {
                    $result = Db::name('favorite')->where(array('favorite_vid' => $id, 'favorite_uid' => $userinfo['userid']))->delete();
                    if ($result) {
                        return json(array("msg" => "取消收藏成功", "rcode" => 1));
                    }
                } else {
                    $data['favorite_vid'] = $id;
                    $data['favorite_uid'] = $userinfo['userid'];
                    $data['favorite_cid'] = getlistpid($cid);
                    $data['favorite_addtime'] = time();
                    $id = Db::name('favorite')->insertGetId($data);
                    if ($id > 0) {
                        return json(array("msg" => "收藏成功", "rcode" => 1));
                    }
                }
            }
            return json(array("msg" => "收藏失败", "rcode" => -1));
        }
    }

    public function remind()
    {
        if ($this->request->isAjax()) {
            $userinfo = session('user');
            if (!$userinfo['userid']) {
                return json(["msg" => "请先登录", "rcode" => "-1"]);
            }
            $id = input('id/d', 0); //影片ID
            $cid = input('cid/d', 0); //影片CID
            if ($userinfo['userid'] && $id && $cid) {
                $favorite = Db::name('remind')->where(array('remind_vid' => $id, 'remind_uid' => $userinfo['userid']))->value('remind_id');
                if ($favorite) {
                    $result = Db::name('remind')->where(array('remind_vid' => $id, 'remind_uid' => $userinfo['userid']))->delete();
                    if ($result) {
                        return json(array("msg" => "取消订阅成功", "rcode" => 1));
                    }
                } else {
                    $vod_addtime = get_vod_info($id, 'vod_id', 'vod_addtime');
                    $data['remind_vid'] = $id;
                    $data['remind_uid'] = $userinfo['userid'];
                    $data['remind_cid'] = getlistpid($cid);
                    $data['remind_addtime'] = time();
                    $data['remind_uptime'] = $vod_addtime;
                    $id = Db::name('remind')->insertGetId($data);
                    if ($id > 0) {
                        return json(array("msg" => "订阅成功", "rcode" => 1));
                    }
                }
            }
            return json(array("msg" => "订阅失败", "rcode" => -1));
        }
    }

}