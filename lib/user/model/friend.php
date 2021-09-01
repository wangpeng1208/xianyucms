<?php
namespace app\user\model;


use think\Model;

class friend extends Model
{
    protected $table = 'xianyucms_user_friend';
// 判断是否是好友
    public function isFriend($uid, $fid){
        $u = $this->where([
            'uid'=>$uid,
            'fid'=>$fid
        ])->find();
        $f = $this->where([
            'uid'=>$fid,
            'fid'=>$uid
        ])->find();
        return !empty($u) && !empty($f);
    }
}