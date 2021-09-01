<?php
namespace app\user\controller;
use app\common\controller\User;
class Payment extends User{	
    //构造函数
    public function _initialize(){
        parent::_initialize();
		$this->payconfig = F('_data/payconfig_cache');
		$this->assign('payconfig',$this->payconfig);
    }
	
    public function index(){
		$play_type=htmlspecialchars(input('pay_type/s',''));
	    $score_ext=htmlspecialchars(input('score_ext/d',''));
		if ($this->request->isPost() || !empty($play_type) && !empty($score_ext)){
	        $userid = $this->userinfo['userid'];
            if($play_type=='alipay'){ 
                return model('Alipay')->submit($userid,$score_ext) ;
	        }elseif($play_type=='weixinpay'){
              $data=model('WeixinPay')->submit($userid,$score_ext);
              if($data['order_code']){
                  $this->assign($data);
                  return view('/payment/weixin');
              }
	        }elseif($play_type=='paypal'){
				return $this->display(model('Paypal')->submit($userid,$score_ext));
	        }elseif($play_type=='cloudpay'){
		        return $this->redirect(model('Cloudpay')->submit($userid,$score_ext));
	        }elseif(in_array($play_type,array('code_alipay','code_weixinpay','code_qqpay')) ){
			    return $this->redirect(model('Codepay')->submit($userid,$play_type,$score_ext));
		    }else{
		       return $this->error('支付平台错误!');	  
	        }
		}else{
			$array = array();
	        if($this->payconfig['codepay_onoff']){
		        $code_pay_type = explode(',',$this->payconfig['codepay_type']);
		        if(in_array(1,$code_pay_type)){
			     array_push($array, 'code_alipay');
		        }
		        if(in_array(2,$code_pay_type)){
			    array_push($array, 'code_weixinpay');
		        }
		        if(in_array(3,$code_pay_type)){
			    array_push($array, 'code_qqpay');
		        }
	        }
            if($this->payconfig['alipay_onoff']){
		        array_push($array, 'alipay');
	        }
            if($this->payconfig['weixin_onoff']){
		        array_push($array, 'weixinpay');
	        }
            if($this->payconfig['cloudpay_onoff']){
		        array_push($array, 'cloudpay');
	        }
            if($this->payconfig['paypal_onoff']){
		        array_push($array,'paypal');
	        }
            $this->assign('pay_list',$array);			
		    return view('index');
		}
    }
    public function card(){
		$card_number=htmlspecialchars(input('card_number/s',''));
		if ($this->request->isPost()){
	        $userid = $this->userinfo['userid'];
			if($card_number){
			$rs=model('Card');
			$result=$rs->recharge($card_number,$userid);
			    if($result['id']){
				   return $this->success('充值'.$result['data'].'影币成功','',$result['data']);
			    }else{
				   return $this->error($rs->getError());
			    }
		    }      
		    return $this->error('请输入充值卡密！');
		}
		return view('card');
    }
	//订单查询
	public function check(){
		$type = htmlspecialchars(input('type/s',''));
		$order = htmlspecialchars(input('order/d',''));
		if($type == 'weixinpay'){
           $reuslt=model('WeixinPay')->check($order);
			if($reuslt['return_code']=='SUCCESS' && $reuslt['result_code']=='SUCCESS' && $reuslt['trade_state']=='SUCCESS'){
			   return $this->success('付款成功','',$reuslt['total_fee']*0.01*$this->payconfig['user_pay_scale']);
		    }else{
              return $this->error('订单未支付');
              }
		}
		return $this->error('查询失败');
	}  


}
