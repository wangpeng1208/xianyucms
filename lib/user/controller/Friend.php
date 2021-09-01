<?php
namespace app\user\controller;
use app\common\controller\User;
use app\user\model\friend as friendModel;


class Friend extends User
{
    protected $uid;
    protected $friendModel;
    public function _initialize()
    {
        $this->uid = cmf_get_current_user_id();
        $this->friendModel = new friendModel();
        parent::_initialize();
    }

    public function index()
    {
        halt('功能制作中');
        // 所有好友id
        $data = $this->friendModel->where([ 'uid'=> $this->uid ])->column('fid');
        $where['userid'] = ['in', implode( $data , ',' )];

        // 查询好友资料
        $friends = db('user')->where($where)->field('nickname,userid,avatar')->select();
        $this->assign('uid', $this->uid);
        $this->assign('friends', $friends);
        return view('index');
    }

    // 聊天
    public function chat(){
        $uid = input('uid/d');
        $fid = input('fid/d');
        if(empty($uid) || empty($fid)){
            return $this->error('error');
        }
        if(!$this->friendModel->isFriend($uid,$fid)){
            return $this->error('不是好友');
        }

        $data['u'] = $this->getUserInfo($uid);
        $data['f'] = $this->getUserInfo($fid);

        $this->assign('data', $data);

        return view('chat');

    }
//    todo vip等级
    public function getUserInfo($userid)
    {
        $result =  db('user')->where('userid',$userid)->field('nickname,userid,pic,viptime')->find();
        if( !empty($result['viptime']) && ($result['viptime'] < time() ) ){
            $result['isVip'] = 1;
        }
        unset($result['viptime']);
        return $result;

    }

//    添加好友 扣除个人积分 +被添加人积分
    public function addFriend(){
        $data['email'] = input('email');
        $data['user'] = user_islogin();
        print_r($data);
    }


}