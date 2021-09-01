<?php


namespace app\user\model;

use think\Db;
use think\Model;

class User extends Model
{

    /**
     * @var int|null
     */
    private  $userId;
    // 只读字段
    protected $readonly = ['username'];
    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $this->userId = cmf_get_current_user_id();
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
     * 用户登录
     *
     * @param $user
     * @return int [返回数据  3：拉黑；  2：账号不存在；  1：密码不正确； 0：成功 ]
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login($user)
    {

        switch ($user['login_type']) {
            case 'email':
                $result = $this->where('email', $user['username'])->find();
                break;
            case 'mobile':
                $result = $this->where('mobile', $user['username'])->find();
                break;
            default:
                $result = $this->where('username', $user['username'])->find();
        }
        $result = $this->where('username', $user['username'])->find();

        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['password'], $result['password']);
            $hookParam = [
                'user' => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_client_ip(1),
                ];
                $this->where('userid', $result["userid"])->update($data);
                $token = cmf_generate_user_token($result["userid"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                    cookie('token', $token,24*3600*365);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user' => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    /**
     * 用户注册
     * 1 已经注册过 0 注册成功
     * @param $user
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function register($user)
    {
        $result = $this->where('username', $user['username'])->whereOr('email', $user['email'])->find();
        if (empty($result)) {
            $data = [
                'username' => empty($user['username']) ? '' : $user['username'],
                'email' => empty($user['email']) ? '' : $user['email'],
                'mobile' => empty($user['mobile']) ? '' : $user['mobile'],
                'nickname' => empty($user['nickname']) ? '' : $user['nickname'],
                'password' => cmf_password($user['password']),
                'userpid' => $user['userpid'] ?? '',
                'regip' => get_client_ip(1),
                'regdate' => time(),
                'last_login_time' => time(),
                'user_status' => 2,
            ];
            $userId = Db::name("user")->insertGetId($data);
            $data = Db::name("user")->where('userid', $userId)->find();
            cmf_update_current_user($data);
            $token = cmf_generate_user_token($userId, 'web');
            if (!empty($token)) {
                session('token', $token);
            }
            return 0;
        }
        return 1;
    }

    /**
     * 获取用户资料
     */
    public function getUserInfo($uid)
    {
        if(empty($uid)){
            $uid = $this->userId;
        }
        if (!$uid) {
            return false;
        }
        $user = $this->withCount('comment,favorite,remind,playlog,gb,msg,score,order')
                    ->relation('detail,api')
                    ->where('userid', $uid)
                    ->find();
        if ($user) {
            return $user->toArray();
        } else {
            return false;
        }
    }
    // 所有修改操作需要全部分开

    /**
     * 修改个人个人资料
     * 邮箱、订阅、密码、等
     * @param array $data
     * @param string $field
     * @return int
     */
    public function editData($data=[], $field='')
    {
        $result = $this->allowField($field)->save($data, ['userid' => $this->userId]);
        if ($result) {
            $userInfo = $this->where('userid', $this->userId)->find();
            cmf_update_current_user($userInfo->toArray());
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * 用户密码修改
     * @param $user
     * @return int [1重复密码需要相同 2旧密码不正确 0成功]
     */
    public function editPassword($user)
    {
        if ($user['password'] != $user['repassword']) {
            return 1;
        }
        $pass = $this->where('userid', $this->userId)->find()->toArray();
        if (!cmf_compare_password($user['old_password'], $pass['password'])) {
            return 2;
        }
        $data['password'] = cmf_password($user['password']);
        $this->where('userid', $this->userId)->update($data);
        return 0;
    }

}