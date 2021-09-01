<?php
namespace app\common\model;
use think\Model;
class Paypal extends Model{
    public function submit($user_id,$score_ext){
        $options = [
		    'cmd' => '_xclick',
			'quantity' => 1,
			'business' => 1,
			'item_name' => 'Score（UID：'.$user_id.'）',
			'amount' => sprintf("%.2f",$score_ext),
			'currency_code' => 'USD',
            'invoice' => date("YmdHis") . mt_rand(10000, 99999),
            'return' => config('site_url') . config('site_path') . 'notify.paypal.php',
			'notify_url' => config('site_url') . config('site_path') . 'notify.paypal.php',
            'charset' => 'utf-8',
        ];
        //写入订单
		model('Orders')->data(
		    ['order_sign'=>$options['invoice'],
			'order_uid'=>$user_id,
			'order_money'=>$options['amount'],
			'order_info'=>$options['item_name'],
			'order_type'=>3
		    ]
		)->save();
        return $this->pay($options);
    }
    public function pay($para, $method='POST', $button_name='正在跳转支付页面'){
        $sHtml="<form id='pay' name='pay' action='https://www.paypal.com/cgi-bin/webscr' method='".$method."' style='margin:50px auto; text-align:center'>";
		while (list ($key, $val) = each ($para)) {
			$sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		$sHtml = $sHtml."<script>document.forms['pay'].submit();</script>";
		return $sHtml;		
    }
    public function notify($data){
        $data['cmd'] = '_notify-validate';
		$json = ff_file_get_contents('https://www.paypal.com/cgi-bin/webscr', 10, '', $data);
		if ($json ==  "VERIFIED") {//已经通过认证
			if($data['payment_status'] == 'Completed'){//检查付款状态  Pending 等侍中|Failed 失败
				model('Orders')->update_order($data['invoice'],$data['payment_fee']);
				return 'success';
			}else {
				return 'fail';
			}
		}else{
			return $json;   
		}
    }
}