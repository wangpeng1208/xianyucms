<?php
namespace app\home\controller;
use think\Controller;
use think\Request;
class Notify extends Controller{	
 	// 支付宝通知处理
	public function alipay(){
		if($this->request->isPost()){
            $post=Request::instance()->post(false);
            echo(model('Alipay')->notify($post));
        }else{
			return $this->success('支付成功',xianyu_user_url('user/center/order'));
		}
	}
 	// 微信通知处理
	public function weixinpay(){
		if($this->request->isPost()){
            $post=Request::instance()->post(false);
            echo(model('WeixinPay')->notify($post));
        }else{
			return $this->success('支付成功',xianyu_user_url('user/center/order'));
		}
	} 
	// paypal定单异步通知处理
	public function paypal(){
		if($this->request->isPost()){
            $post=Request::instance()->post(false);
			echo(model('Paypal')->notify($post));
		}else{
			return $this->success('支付成功',xianyu_user_url('user/center/order'));
		}
	}		
 	// 码支付定单异步通知处理
	public function codepay(){
		if($this->request->isPost()){
            $post=Request::instance()->post(false);
			echo model('Codepay')->notify($post);
		}else{
			return $this->success('支付成功',xianyu_user_url('user/center/order'));
		}
	}
 	// 云支付定单异步通知处理
	public function cloudpay(){
		if($this->request->isPost()){
             $post=Request::instance()->post(false);
			echo model('Cloudpay')->notify($post);
		}else{
			return $this->success('支付成功',xianyu_user_url('user/center/order'));
		}
	}	

}