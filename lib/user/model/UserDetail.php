<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\user\model;
use think\Db;
use think\Model;
class UserDetail extends Model{

    private  $userId;
    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->userId = cmf_get_current_user_id();
    }
    public function api()
    {
        return $this->hasMany('UserApi', 'uid');
    }
    // 修改用户资料
    public function editUser($data)
    {
        $data['birthday'] = !empty($data['birthday'])? strtotime($data['birthday']) : '';
        // 使用数据库方法查找更新 避免手工删除掉
        $result = Db::name('user_detail')->where('userid', $this->userId)->find();
        if(!empty($result)){
            $result = Db::name('user_detail')->where('userid', $this->userId)->update($data);
        }else{
            $data['userid'] = $this->userId;
            $result = Db::name('user_detail')->insert($data);
        }
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }
}