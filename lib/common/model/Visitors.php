<?php

namespace app\common\model;

use think\Model;

class Visitors extends Model
{
    //protected $updateTime = 'visitors_addtime';
    protected $insert = ['visitors_ip'];

    protected function setVisitorsIpAttr()
    {
        return ip2long(get_client_ip());
    }

    public function api()
    {
        return $this->hasMany('User_api', 'uid', 'visitors_userid');
    }

    //更具用户ID获取访客
    public function GetVisitors($params, $where)
    {
        $data = db('Visitors')->alias('v')->join('user u', 'u.userid = v.visitors_userid', 'LEFT')->join('User_api a', 'a.uid = v.visitors_userid', 'LEFT')->field($params['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();
        return $data;
    }

    //更具用户ID获取访客
    public function add($uid = "", $userid = "")
    {
        $visitors = model("Visitors");
        $data['visitors_userid'] = $userid;
        $data['visitors_uid'] = $uid;
        $data['visitors_addtime'] = time();
        $visitors->validate(true)->save($data);
        if ($visitors->getError()) {
            $where['visitors_userid'] = $userid;
            $where['visitors_uid'] = $uid;
            $visitors->where($where)->update(['visitors_addtime' => time(), 'visitors_ip' => ip2long(get_client_ip())]);
        }
    }


}