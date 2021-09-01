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
class Orders extends Model{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'order_addtime';//自动写入时间
	protected $insert = ['order_ip','order_gid' => 1,'order_ispay'=> 1];  //新增订单自动完成
	protected $update = ['order_paytime','order_confirmtime']; //更新订单自动完成
	protected function setorderipAttr(){
        return ip2long(get_client_ip());
    }
	protected function setorderpaytimeAttr(){
        return time();
    }
	protected function setorderconfirmtimeAttr(){
        return time();
    }
    public function getIspayTextAttr($value,$data){
        $status = [0=>'未付款',1=>'付款中',2=>'已付款'];
        return $status[$data['order_ispay']];
    }
    public function getStatusTextAttr($value,$data){
        $status = [0=>'未付款',1=>'已确认',2=>'已取消',3=>'无效',4=>'退货'];
        return $status[$data['order_status']];
    }
    public function getTypeTextAttr($value,$data){
        $status = [1=>'支付宝',2=>'微信',3=>'PayPal',4=>'云支付',5=>'支付宝',6=>'微信',7=>'QQ钱包'];
        return $status[$data['order_type']];
    }		
	//根据订单号修改订单状态及更新用户影币
	public function update_order($sign,$money,$type=2){
		$payconfig=F('_data/payconfig_cache');
		$where = array();
		$where['order_sign'] = array("eq", $sign);
		//查询订单
		$info = $this->field('order_uid,order_ispay,order_money')->where($where)->find();
		//未付款状态用金额相同
		if(($info['order_ispay'] < 2) && ($info['order_money']==$money)){
			//更新订单状态
			$this->save(['order_status'=>1,'order_ispay'=>2,'order_shipping'=>1],$where);
			//更新用户积分
			return model('Score')->user_score($info['order_uid'],$type,intval($info['order_money']*$payconfig['user_pay_scale']),0,0);
		}
	}
	public function getlist($where,$page){
		$list = $this->where($where)->order('order_addtime desc ')->paginate($page['limit'],false,['page'=>$page['currentpage']]); 
		return $list ;
	}	
	
}