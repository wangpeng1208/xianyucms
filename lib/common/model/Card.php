<?php

namespace app\common\model;

use think\Model;

class Card extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'card_addtime';//自动写入时间
    protected $update = ['card_usetime']; //更新订单自动完成

    protected function setcardaddtimeAttr()
    {
        return time();
    }

    protected function setcardusetimeAttr()
    {
        return time();
    }

    //卡密充值
    public function recharge($card_number, $card_uid)
    {
        $payconfig = F('_data/payconfig_cache');
        $where = array();
        $where['card_number'] = array('eq', $card_number);
        $where['card_status'] = array('eq', 0);
        $info = $this->field('card_id,card_score')->where($where)->find();
        if (!$info) {
            $this->error = "卡密错误或已充值！";
            return false;
        }
        //更新用户积分
        model("Score")->user_score($card_uid, 8, $info['card_score'], 0, 0);
        //更新卡密状态
        $this->save(['card_status' => 1, 'card_uid' => $card_uid], ['card_id' => $info['card_id']]);
        $result['data'] = $info['card_score'];
        $result['id'] = $info['card_id'];
        //正常返回卡密ID
        return $result;
    }

}