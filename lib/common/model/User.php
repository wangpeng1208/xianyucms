<?php

namespace app\common\model;

use app\common\library\Redis;
use think\Model;
use think\DB;

class User extends Model
{
    protected $insert = ['regip', 'regdate', 'userpid'];
    protected $auto = ['isRemind', 'isstation', 'lastdate'];

    protected function setPasswordAttr($value, $data)
    {
        return md5(md5($value) . $data['encrypt']);
    }

    protected function setUserpidAttr()
    {
        return intval(cookie('xianyucms_register_pid'));
    }

    protected function setRegdateAttr()
    {
        return time();
    }

    protected function setLastdateAttr()
    {
        return time();
    }

    protected function setRegipAttr()
    {
        return ip2long(get_client_ip());
    }

    protected function setNicknameAttr($value, $data)
    {
        if ($value) {
            return $value;
        } else {
            return $data['username'];
        }
    }

    protected function setisRemindAttr($value)
    {
        return !empty($value) ? $value : 0;
    }

    protected function setisstationAttr($value)
    {
        return !empty($value) ? $value : 0;
    }

    public function getgroupnameAttr()
    {
        $gbcid = array(0 => '未开启认证注册', 1 => '未认证邮箱', 2 => '邮箱认证成功');
        return $gbcid;
    }

    public function detail()
    {
        return $this->hasOne('UserDetail', 'userid');
    }

    public function visitors()
    {
        return $this->hasMany('visitors', 'visitors_userid');
    }

    public function comment()
    {
        return $this->hasMany('cm', 'cm_uid');
    }

    public function favorite()
    {
        return $this->hasMany('Favorite', 'favorite_uid');
    }

    public function remind()
    {
        return $this->hasMany('Remind', 'remind_uid');
    }

    public function playlog()
    {
        return $this->hasMany('Playlog', 'log_uid');
    }

    public function gb()
    {
        return $this->hasMany('gb', 'gb_uid');
    }

    public function orders()
    {
        return $this->hasMany('orders', 'order_uid');
    }

    public function msg()
    {
        return $this->hasMany('msg', 'msg_uid');
    }

    public function score()
    {
        return $this->hasMany('score', 'score_uid');
    }

    public function order()
    {
        return $this->hasMany('orders', 'order_uid');
    }

    public function api()
    {
        return $this->hasMany('UserApi', 'uid');
    }



    /**
     * 修改用户资料
     */
    public function editUser($data, $ischangepwd = false)
    {
        $rs = model('User');
        if ($data['userid']) {
            if (!$ischangepwd || ($ischangepwd && $data['password'] == '')) {
                unset($data['encrypt']);
                unset($data['password']);
            } else {
                $data['encrypt'] = rand_string(6);
            }
            $rs->save($data, array('userid' => $data['userid']));
            if ($rs->detail->userid) {
                $rs->detail->save($data);
            } else {
                $rs->detail()->save($data);
            }
            return $rs->userid;
        } else {
            $this->error = "非法操作！";
            return false;
        }
    }

    //获取用户资料
    public function getuserinfo($userid = "")
    {
        if (!$userid) {
            return false;
        }
//        $user = $this->withCount('comment,favorite,remind,playlog,gb,msg,score,order')->relation('detail,api')->where('userid',$userid)->find();
        $user = $this->withCount('comment,favorite,remind,playlog,gb,msg,score,order')->relation('detail')->where('userid', $userid)->find();
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }



    /**
     * 用户登录
     */
    public function login($username = '', $password = '', $type = 1)
    {
        $map = array();
        if (\think\Validate::is($username, 'email')) {
            $type = 2;
        } elseif (preg_match("/^1[34578]{1}\d{9}$/", $username)) {
            $type = 3;
        }
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['userid'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        $user = $this->relation('detail,api')->where($map)->find();
        if ($user['userid']) {
            /* 验证用户密码 */
            if (md5(md5($password) . $user['encrypt']) === $user['password']) {
                if ($user['islock'] == 1) {
                    return -3; //用户被禁用
                }
                $user_api = session('user_auth_api');
                if (!empty($user_api['openid'])) {
                    $data['channel'] = !empty($user_api['channel']) ? $user_api['channel'] : "";
                    $data['openid'] = !empty($user_api['openid']) ? $user_api['openid'] : "";
                    $data['avatar'] = !empty($user_api['avatar']) ? $user_api['avatar'] : "";
                    $user->api()->save($data);
                    session('user_auth_api', null);
                }
                $this->autoLogin($user); //更新用户登录信息
                return $user; //登录成功，返回用户ID
            } else {
                return -2; //用户密码错误
            }
        } else {
            return -1; //用户不存在
        }
    }



}