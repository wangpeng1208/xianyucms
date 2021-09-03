<?php
namespace app\common\model;
use think\Model;
class WeixinPay extends Model{
	public function Config(){
		$pay_config=F('_data/payconfig_cache');
		$config = [
		    'wechat' => [
             // 沙箱模式
		    'debug'       => false,
             // 应用ID
            'app_id'      => $pay_config['weixin_app_id'],
            // 微信支付商户号
            'mch_id'     => $pay_config['weixin_mch_id'],
            // 微信支付密钥
            'mch_key'    => $pay_config['weixin_mch_key'],
            // 支付成功通知地址
            'notify_url'  => config('site_url').config('site_path').'notify.weixinpay.php',
            // 网页支付回跳地址
            'return_url'  => config('site_url').config('site_path').'notify.weixinpay.php',
            ]
        ];
		return $config;
		}	
	public function submit($user_id,$post){
       $total_fee = sprintf("%.2f",$post);
		$options = [
            'out_trade_no' => date("YmdHis").mt_rand(10000, 99999), // 商户订单号
            'total_fee' =>  $total_fee*100, // 支付金额
            'body'      => '积分充值（UID：'.$user_id.'）', // 支付订单描述
			'spbill_create_ip' => get_client_ip(), // 支付人的 IP
            'notify_url'       => config('site_url').config('site_path').'notify.weixinpay.php',
            'return_url'  => config('site_url').config('site_path').'notify.weixinpay.php',
			
        ];
		//写入订单
		model('Orders')->data(
		    ['order_sign'=>$options['out_trade_no'],
			'order_uid'=>$user_id,
			'order_money'=>$total_fee,
			'order_info'=>$options['body'],
			'order_type'=>2
		    ]
		)->save();
        $pay = new \Pay\Pay($this->config());
        try {
             $result = $pay->driver('wechat')->gateway('scan')->apply($options);
                 return [
                        'order_sign' => $options['out_trade_no'],
                        'order_uid' =>  $user_id,
                        'order_money'      => $total_fee,
			            'order_code' => $result,
                        ];
             } catch (Exception $e) {
                 return $e->getMessage();
             }
      return false;
	}
	//订单查询 是否已完成支付
	public function check($out_trade_no){
     $pay = new \Pay\Pay($this->config());
     try {
         $reuslt = $pay->driver('wechat')->gateway('scan')->find($out_trade_no);
          return $reuslt;
        } catch (Exception $e) {
          return $e->getMessage();
        }
	}  
	public function notify($data){
		$pay = new \Pay\Pay($this->config());
		$verify = $pay->driver('wechat')->gateway('mp')->verify(file_get_contents('php://input'));
		if ($verify){
			model('Orders')->update_order($verify['out_trade_no'], $verify['total_fee']*0.01);
            return 'success';
        }else{
            return 'fail';
        }
	}	

}