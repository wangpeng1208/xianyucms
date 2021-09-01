<?php

namespace app\user\model;

use think\Model;

/**
 * 用户所有积分内容设计思路
 * 1.积分 score，用于用户组升级
 * 2.金币 coin，用于礼物
 * 3.砖石，用于购买vip及礼物
 * 3.豆豆，扩展
 * 以后再做 先做简单的影视积分
 */
class Score extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'score_addtime';//自动写入时间

    public function getTypeTextAttr($value, $data)
    {
        $status = [1 => '注册赠送', 2 => '影币充值', 3 => '付费点播', 4 => 'VIP升级', 5 => '管理员操作', 6 => '影币返还', 7 => '管理补单', 8 => '卡密充值', 9 => '推广积分', 10 => '注册赠送VIP', 11 => '评论赠送'];
        return $status[$data['score_type']];
    }

    //更新影币记录及用户积分
    public function user_score($uid, $type, $ext, $sid = 0, $did = 0)
    {
        $info = $this->score_insert($uid, $type, $ext, $sid, $did);
        if ($info['score_id']) {
            $user_score = $this->score_sum('score_uid=' . $uid);
            return model('user')->where('userid=' . $uid)->setField('score', $user_score);
        }
        return false;
    }

    //新增积分日志记录
    public function score_insert($uid, $type, $ext, $sid = 0, $did = 0)
    {
        $userconfig = F('_data/userconfig_cache');
        if ($type == 11 && $userconfig['user_cm_score_day']) {
            $where = array();
            $where['score_uid'] = $uid;
            $where['score_type'] = $type;
            $extcount = $this->where($where)->whereTime('score_addtime', 'today')->sum('score_ext');
            if ($extcount >= intval($userconfig['user_cm_score_day'])) {
                return false;
            }
        }
        $data = array();
        $data['score_uid'] = $uid;
        $data['score_type'] = $type;
        $data['score_ext'] = $ext;
        $data['score_sid'] = $sid;
        $data['score_did'] = $did;
        $this->data($data);
        $this->isUpdate(false)->save();
        $data['score_id'] = $this->score_id;
        if (!$data['score_id']) {
            $this->error = $this->getError();
            return false;
        }
        return $data;
    }

    //统计购买记录
    public function count_score_log($score_uid, $score_type = 3, $score_sid = 1, $score_did = 0)
    {
        $where = array();
        $where['score_uid'] = $score_uid;
        $where['score_type'] = $score_type;
        $where['score_sid'] = $score_sid;
        $where['score_did'] = $score_did;
        return $this->where($where)->count('score_id');
    }

    //根据影币日志统计影币
    public function score_sum($where, $field = 'score_ext')
    {
        return $this->where($where)->sum($field);
    }

    /**
     * @param $user_id 用户id
     * @param $user_viptime vip到期时间
     * @param $user_score 当前会员积分
     * @param $vip_day  购买天数
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function user_viptime($user_id, $user_viptime, $user_score, $vip_day)
    {

        $payconfig = F('_data/payconfig_cache');
        // 不得低于最低天数
        if (abs($vip_day) < intval($payconfig['user_pay_vip_small'])) {
            $result['msg'] = '续费时长不得低于' . intval($payconfig['user_pay_vip_small']) . '天！';
            $result['rcode'] = -1;
            return $result;
        }
        // 每日价格
        $ext_conf = intval($payconfig['user_pay_vip_day']);
        // 所需总费
        $ext_total = $vip_day * $ext_conf;

        //影币不足请先充值
        if ($user_score < $ext_total) {
            $result['msg'] = "影币不足，共需要" . $ext_total . "个影币，请先冲值！";
            $result['rcode'] = -2;
            return $result;
        }
        $score_type = 4;
        $ext_day = '+' . abs($vip_day) . ' day';

        //更新影币记录
        $info = $this->score_insert($user_id, $score_type, -$ext_total, 0, 0);
        //更新用户信息表
        if ($info['score_id']) {
            $data = array();
            $data['userid'] = $user_id;
            $data['score'] = $user_score - $ext_total;
            $data['viptime'] = strtotime($ext_day, $user_viptime);
            //VIP过期时间小于操作时间时 以操作时间开始计算
            if ($score_type == 4 && ($user_viptime < time())) {
                $data['viptime'] = strtotime($ext_day, time());
            }
            if (db('user')->update($data)) {
                $result['data'] = date('Y/m/d H:i:s', $data['viptime']);
                $result['msg'] = '升级VIP时间完成';
                $result['rcode'] = 1;
                return $result;
            } else {
                $result['msg'] = '升级失败';
                $result['rcode'] = -3;
                return $result;
            }
        }
        $result['msg'] = '操作失败';
        $result['rcode'] = -4;
        return $result;
    }

    public function getlist($where, $page)
    {
        $list = $this
            ->alias('s')
            ->join('user u', 'u.userid=s.score_uid', 'LEFT')
            ->where($where)->order('score_addtime desc ')
            ->paginate($page['limit'], false, ['page' => $page['currentpage']]);
        return $list;
    }

    public function user_reg_time($user_id, $user_day)
    {
        $ext_day = '+' . abs($user_day) . ' day';
        $data = array();
        $data['userid'] = $user_id;
        $data['viptime'] = strtotime($ext_day, time());
        db('user')->update($data);
    }

}