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
class Codepay extends Model{
	public function submit($user_id,$pay_type,$score_ext){
		$pay_type = 5;
		if($post == 'code_weixinpay'){
			$pay_type = 6;
		}elseif($post == 'code_qqpay'){
			$pay_type = 7;
		}
		$options = [
            'order_sign' => date("YmdHis").mt_rand(10000, 99999), // 商户订单号
            'order_money' => number_format($score_ext,2), // 支付金额
            'order_info'      => 'Score UID:'.$user_id, // 支付订单描述
			'order_type'      => $pay_type, // 支付方式
			'order_uid'      => $user_id, 
        ];
		
		//写入订单
		model('Orders')->data($options)->save();
		return $this->pay($options);
	}
    public function pay($options){
		$payconfig=F('_data/payconfig_cache');
       //组装参数	
		$data = array(
			"id" => trim($payconfig['codepay_id']),//你的码支付ID
			"type" => $options['order_type'],//1支付宝支付 3微信支付 2QQ钱包
			"price" => $options['order_money'],//金额100元
			"pay_id" => $options['order_sign'], //唯一标识 可以是用户ID,用户名,session_id(),订单ID,ip 付款后返回
			"notify_url" => config('site_url').config('site_path').'notify.codepay.php',//通知地址
			"return_url" =>config('site_url').config('site_path').'notify.codepay.php',//跳转地址
			"debug" => 1,//软件未启动的话
			"ac" => 1,//即时到账和代收款默认1 代收款需要申请
			"param" => "",//自定义参数
		); 
		ksort($data); //重新排序$data数组
		reset($data); //内部指针指向数组中的第一个元素
		
		$sign = ''; //初始化需要签名的字符为空
		$urls = ''; //初始化URL参数为空
		
		foreach ($data as $key => $val) { //遍历需要传递的参数
				if ($val == ''|| $key == 'sign') continue; //跳过这些不参数签名
				if ($sign != '') { //后面追加&拼接URL
						$sign .= "&";
						$urls .= "&";
				}
				$sign .= "$key=$val"; //拼接为url参数形式
				$urls .= "$key=" . urlencode($val); //拼接为url参数形式并URL编码参数值
		}
		
		$query = $urls . '&sign='.md5($sign.trim($payconfig['codepay_key'])); //创建订单所需的参数
		$url = "http://api2.fateqq.com:52888/creat_order/?{$query}"; //支付页面
		return $url;
		
	}
	public function notify($data){
		$payconfig=F('_data/payconfig_cache');
		// $post['pay_id'] 这是付款人的唯一身份标识或订单ID
		// $post['pay_no'] 这是流水号 没有则表示没有付款成功 流水号不同则为不同订单
		// $post['money'] 这是付款金额
		// $post['param'] 这是自定义的参数
		ksort($data); //排序post参数
		reset($data); //内部指针指向数组中的第一个元素
		$sign = '';
		foreach ($data as $key => $val) {
			if ($val == '') continue; //跳过空值
			if ($key != 'sign') { //跳过sign
				$sign .= "$key=$val&"; //拼接为url参数形式
			}
		}
		if (!$data['pay_no'] || md5(substr($sign,0,-1).trim($payconfig['codepay_key'])) != $data['sign']) {
			return 'fail';
		}else{
			model('Orders')->update_order($data['pay_id'], $data['money']);
			return 'success';
		}
	}

}