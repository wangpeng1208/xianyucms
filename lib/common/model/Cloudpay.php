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
class Cloudpay extends Model{
    public function submit($user_id, $score_ext){
        $options = [
            'order_sign' => date("YmdHis") . mt_rand(10000, 99999),
            // 商户订单号
            'order_money' => number_format($score_ext, 2),
            // 支付金额
            'order_info' => 'score UID:'.$user_id,
            // 支付订单描述
            'order_type' => 4,
            // 支付方式
            'order_uid' => $user_id,
        ];
        //写入订单
        model('Orders')->data($options)->save();
        return $this->pay($options);
    }
    public function pay($options){
        $payconfig = F('_data/payconfig_cache');
        //组装参数
        $data = array(
            "appid" => trim($payconfig['cloudpay_id']),
            //平台ID
            "rmb" => trim($options['order_money']),
            //金额100元
            "dingdan" => trim($options['order_sign']),
            //订单号
            "body" => trim($payconfig['order_info']),
            //商品名称
            "notify_url" => config('site_url') . config('site_path') . 'notify.cloudpay.php',
            //通知地址
            "return_url" => config('site_url') . config('site_path') . 'notify.cloudpay.php',
        );
        //进行MD5签名
        $sign = $this->sign($data);
        //将签名结果加入请求提交参数组中
        $data['sign'] = $sign;
        //生成支付请求Uri
        $url = "http://api.atfpay.net/pay?" . http_build_query($data);
        return $url;
    }
    public function notify($data){
        $payconfig = F('_data/payconfig_cache');
        $sign = $this->sign($data);
        if ($sign == $data['sign']) {
            if ($data['zt'] == '1') {
                model('Orders')->update_order($data['dingdan'], $data['rmb']);
            }
            return 'success';
        } else {
            //验证失败
            return 'fail';
        }
    }
    function sign($arr){
        $payconfig = F('_data/payconfig_cache');
        $arr_filter = array();
        foreach ($arr as $key => $val) {
            if ($key == "sign" || $val == "") {
                continue;
            }
            $arr_filter[$key] = $arr[$key];
        }
        //对数组排序
        ksort($arr_filter);
        reset($arr_filter);
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $arg = "";
        foreach ($arr_filter as $key => $val) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        //把字符串加上商户密钥,进行MD5加密
        $sign = md5($arg . $payconfig['cloudpay_key']);
        return $sign;
    }
}