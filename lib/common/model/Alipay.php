<?php

namespace app\common\model;

use think\Model;

class Alipay extends Model
{
    public function Config()
    {
        $pay_config = F('_data/payconfig_cache');
        $config = [
            'alipay' => [
                // 沙箱模式
                'debug' => false,
                // 应用ID
                'app_id' => $pay_config['alipay_app_id'],
                // 支付宝公钥(1行填写)
                'public_key' => $pay_config['alipay_public_key'],
                // 支付宝私钥(1行填写)
                'private_key' => $pay_config['alipay_private_key'],
                // 支付成功通知地址
                'notify_url' => config('site_url') . config('site_path') . 'notify.alipay.php',
                // 网页支付回跳地址
                'return_url' => config('site_url') . config('site_path') . 'notify.alipay.php',
            ]
        ];
        return $config;
    }

    public function submit($user_id, $post)
    {
        $options = [
            'out_trade_no' => date("YmdHis") . mt_rand(10000, 99999), // 商户订单号
            'total_amount' => number_format($post, 2), // 支付金额
            'subject' => '积分充值 UID:' . $user_id, // 支付订单描述
        ];
        //写入订单
        model('Orders')->data(
            ['order_sign' => $options['out_trade_no'],
                'order_uid' => $user_id,
                'order_money' => $options['total_amount'],
                'order_info' => $options['subject'],
                'order_type' => 1
            ]
        )->save();
        return $this->pay($options);
    }

    public function pay($options)
    {
        $pay = new \Pay\Pay($this->config());
        try {
            $result = $pay->driver('alipay')->gateway('web')->apply($options);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function notify($data)
    {
        $pay = new \Pay\Pay($this->config());
        if ($pay->driver('alipay')->gateway()->verify($data)) {
            model('Orders')->update_order($data['out_trade_no'], $data['total_amount']);
            return 'success';
        } else {
            return 'fail';
        }
    }

}