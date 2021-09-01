<?php

namespace app\user\controller;

use app\common\controller\All;
use app\common\library\Email;

use \app\user\model\User;
use think\Db;
use \think\Validate;

class Login extends All
{
    public function _initialize()
    {
        return parent::_initialize();
    }
    public function ajax() {
        return view('login/ajax');
    }
    /**
     * 登录
     */
    public function index()
    {
        if ($this->request->isGet()) {
            $redirect = $this->request->param("redirect");
            if (empty($redirect)) {
                $redirect = $this->request->server('HTTP_REFERER');
            } else {
                if (strpos($redirect, '/') === 0 || strpos($redirect, 'http') === 0) {
                } else {
                    $redirect = base64_decode($redirect);
                }
            }
            if (!empty($redirect)) {
                session('login_http_referer', $redirect);
            }
            if (cmf_is_user_login()) { //已经登录时直接跳到首页
//                halt(session('user'));
                return redirect(xianyu_url('user/center/index'));
            } else {
                return $this->fetch(":login");
            }
        }
        if ($this->request->isPost()) {
            $rules = [
                'username' => 'require',
                'password' => 'require|min:6|max:32',
            ];
            $message = [
                'username.require' => '用户名不能为空',
                'password.require' => '密码不能为空',
                'password.max' => '密码不能超过32个字符',
                'password.min' => '密码不能小于6个字符',
            ];
            if ($this->config['userconfig']['user_code']) {
                $rules['validate'] = 'require';
                $message['validate.require'] = '验证码不能为空';
            }
            $validate = new Validate($rules);
            $validate->message($message);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if ($this->config['userconfig']['user_code'] && !cmf_captcha_check($data['validate'])) {
                $this->error('验证码错误!');
            }

            $userModel = new User();
            $user['password'] = $data['password'];
            $user['username'] = $data['username'];
            if ($validate::is($data['username'], 'email')) {
                $user['login_type'] = 'email';
            } else if (cmf_check_mobile($data['username'])) {
                $user['login_type'] = 'mobile';
            } else {
                $user['login_type'] = 'username';
            }
            $log = $userModel->login($user);
            $redirect = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;
            switch ($log) {
                case 0:
                    cmf_user_action('login');
                    //是否需要跳转
                    if($data['jump']){
                        return json(['msg'=>"登录成功",'rcode'=>1,'redir'=>xianyu_user_url('user/center/index'),'wantredir'=>1]);
                    }else{
                        return json(['msg'=>"登录成功",'rcode'=>1]);
                    }
                    break;
                case 1:
                    $this->error(lang('密码不正确!'));
                    break;
                case 2:
                    $this->error('账户不存在');
                    break;
                case 3:
                    $this->error('账号被禁止访问系统');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        }

    }

    public function edit(){
        if($this->request->isPost()){
            $rules = [
                'nickname' => 'require|max:25',

            ];
            $validate = new Validate($rules);
            $validate->message([
                'nickname.require' => '用户名不能为空',
                'nickname.max' => '用户名不能超过20个字符',
                'nickname.min' => '密码不能不少于3位字符',
            ]);
            $data = $this->request->post();

            $save['nickname'] = $data['nickname'];
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $result = Db::name('user')->where('userid',cmf_get_current_user_id())->setField('nickname' , $data['nickname']);
            if($result){
                session('user.nickname', $data['nickname']);
                $token = Db::name('user_token')->where('user_id',cmf_get_current_user_id())->value('token');
                session('token', $token);
                cookie('token', $token,24*3600*365);
                return json(['msg'=>"注册成功,gogogo",'rcode'=>1,'redir'=>xianyu_user_url('user/center/index'),'wantredir'=>1]);
            }else{
                return  json([
                    'code'=>'-1',
                    'msg'=>'设置失败'
                ]);
            }

        }
        $user = session('user');
        if(!$user){
            $this->error("您尚未登录", xianyu_user_url("user/login/index"));
        }
        $this->assign($user);
        return $this->fetch('');
    }
    /**
     * 找回密码
     */

    public function forgetpwd()
    {

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $validate = $data['validate'];
            if(!captcha_check($validate)){
                return json(['msg' => "验证码错误!", 'rcode' => -1]);
            }
            $validate = new Validate();
            if($validate::is($data['email'], 'email') == false){
                return json(['msg' => "邮箱格式错误!", 'rcode' => -1]);
            };

            $emailarray = explode("@", $email);
            $rs = new User();
            $user = $rs->where('email', $email)->find();
            $email = new Email;
            $result = $email->forgetpwdemail($user);
            if ($result) {
                return json(['msg' => "系统已经将链接发至您的邮箱，请登录您邮箱进行密码重置", 'rcode' => 1, 'redir' => "http://mail." . $emailarray[1], 'wantredir' => 1]);
            } else {
                return json(["msg" => $email . "请联系网站管理", "rcode" => "-1"]);
            }
        } else {
            return $this->fetch('/find_password');
        }

    }
}
/**
 * public function index($username = '', $password = '', $validate = '', $jump = false)
 * {
 *
 * if (user_islogin()) {
 * return $this->redirect(xianyu_user_url('user/center/index'));
 * }
 * $userconfig = F('_data/userconfig_cache');
 * if ($this->request->isPost() || input('get.qrsig')) {
 * if ($this->request->isPost()) {
 * if (!$username || !$password) {
 * return $this->error('用户名或者密码不能为空！', '');
 * }
 * if ($userconfig['user_code']) {
 * //验证码验证
 * $result = $this->checkcode($validate);
 * if ($result != 1) {
 * return json(['msg' => "验证码错误", 'rcode' => -1]);
 * }
 * }
 * $user = model('User');
 * $info = $user->login($username, $password);
 * } else {
 * $qrsig = input('get.qrsig');
 * if (empty($qrsig)) {
 * $this->error('qrsig不能为空');
 * }
 * $url = 'https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken=' . $this->getqrtoken($qrsig) . '&login_sig=&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-' . time() . '0000&js_ver=10194&js_type=1&pt_uistyle=40&aid=549000912&daid=5&';
 * $ret = $this->get_curl($url, 0, $url, 'qrsig=' . $qrsig . '; ', 1);
 * if (preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)) {
 * $r = explode("','", str_replace("', '", "','", $arr[1]));
 * if ($r[0] == 0) {
 * preg_match('/uin=(\d+)&/', $ret, $uin);
 * $openid = $uin[1];
 * preg_match('/skey=@(.{9});/', $ret, $skey);
 * preg_match('/superkey=(.*?);/', $ret, $superkey);
 * $data = $this->get_curl($r[2], 0, 0, 0, 1);
 * $pskey = null;
 * if ($data) {
 * preg_match("/p_skey=(.*?);/", $data, $matchs);
 * $pskey = $matchs[1];
 * }
 * if ($pskey) {
 * $password = rand_string(10);
 * $data = array(
 * 'username' => '',
 * 'nickname' => $r[5],
 * 'password' => $password,
 * 'email' => $openid . "@qq.com",
 * 'openid' => $openid,
 * 'channel' => 'qq',
 * );
 * //                            $rs = new \app\user\model\User();
 *
 * //                            $user = $rs->doLogin($data);
 * session('user_auth_api', $data);
 * session('user_auth_num', 1);
 * $user = $this->GetApiUser($openid, '', $data);
 * if ($user) {
 * $this->success('登录成功');
 * } else {
 * $this->success('注册成功');
 * }
 * //
 * } else {
 * $this->error('获取相关信息失败');
 * }
 * } elseif ($r[0] == 65) {
 * $this->error('二维码已失效');
 * } elseif ($r[0] == 66) {
 * $this->error('二维码未失效');
 * } elseif ($r[0] == 67) {
 * $this->error('正在验证二维码');
 * } else {
 * $this->error($r[4]);
 * }
 * } else {
 * $this->error($ret);
 * }
 * }
 *
 * if ($info['userid'] > 0) {
 * //判断是否开启邮箱认证
 * if ($userconfig['user_email_auth'] && $info['groupid'] == 1) {
 * return json(array("msg" => "登录成功,邮箱需要验证", "rcode" => "-1", "redir" => xianyu_user_url('user/reg/auth'), "wantredir" => 1));
 * }
 * //是否需要跳转
 * if ($jump) {
 * return json(['msg' => "登录成功", 'rcode' => 1, 'redir' => xianyu_user_url('user/center/index'), 'wantredir' => 1]);
 * } else {
 * return json(['msg' => "登录成功", 'rcode' => 1]);
 * }
 * } else {
 * switch ($info) {
 * case -1:
 * $error = '用户不存在！';
 * break; //系统级别禁用
 * case -2:
 * $error = '密码错误！';
 * break;
 * case -3:
 * $error = '用户被锁定！';
 * break;
 * default:
 * $error = '未知错误！';
 * break; // 0-接口参数错误（调试阶段使用）
 * }
 * return json(["msg" => $error, "rcode" => -1]);
 * }
 * } else {
 * // 二维码
 * $url = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.5409099' . time() . '&daid=5';
 * $arr = $this->get_curl_split($url);
 * preg_match('/qrsig=(.*?);/', $arr['header'], $match);
 * if ($qrsig = $match[1]) {
 * $this->assign('qrsig', $qrsig);
 * $this->assign('qrcode', base64_encode($arr['body']));
 * return view('/login');
 * } else {
 * $this->error('二维码获取失败');
 * }
 * }
 * }
 *
 * // 第三方绑定查询
 *
 * public function GetApiUser($openid = "", $channel = "", $data = [])
 * {
 * $where['openid'] = $openid;
 * $user = Db::name('user_api')->alias('a')->join('user u', 'u.userid=a.uid')->join('user_detail d', 'd.userid=a.uid')->where($where)->find();
 * if ($user['userid']) {
 * if ($user['islock'] == 1) {
 * $this->error = "用户被禁用！";
 * }
 * $this->autoLogin($user);//更新登录信息
 * return $user;
 * } else {
 * // 没有的话 是 注册
 * $this->autoreg($data['username'], $data['email'], $data['password']);
 * }
 *
 * }
 *
 * // 用户注册
 *
 * public function autoreg($username = '', $email = '', $password = '', $isautologin = true)
 * {
 * $userconfig = F('_data/userconfig_cache');
 * $user_api = session('user_auth_api');
 * $data['username'] = !empty($username) ? $username : rand_string(6);
 * $data['nickname'] = !empty($user_api['nickname']) ? $user_api['nickname'] : $username;
 * $data['encrypt'] = rand_string(6);
 * $data['email'] = $email;
 * $data['password'] = $password;
 * $data['regdate'] = time();
 * $data['lastdate'] = time();
 * $data['regip'] = get_client_ip(1);
 * $data['loginnum'] = 0;
 * $data['islock'] = 0;
 * if (!empty($user_api['openid'])) {
 * $data['channel'] = !empty($user_api['channel']) ? $user_api['channel'] : "";
 * $data['openid'] = !empty($user_api['openid']) ? $user_api['openid'] : "";
 * $data['avatar'] = !empty($user_api['avatar']) ? $user_api['avatar'] : "";
 * $data['sex'] = !empty($user_api['sex']) ? $user_api['sex'] : "";
 * $data['prov_id'] = !empty($user_api['province']) ? $user_api['province'] : "";
 * $data['city_id'] = !empty($user_api['city']) ? $user_api['city'] : "";
 * }
 * $data['groupid'] = !empty($data['openid']) ? 0 : $userconfig['user_email_auth'];
 *
 *
 * session('user_auth_api', null);
 * Db::name('user')->insert($data);
 * $userId = Db::name('user')->getLastInsID();
 * $data['userid'] = $userId;
 *
 * $rs = UserModel::get($userId);
 * if (!empty($user_api['openid'])) {
 * $rs->detail()->save($data);
 * $rs->api()->save($data);
 * }
 * if ($userconfig['user_register_score']) {
 * model('Score')->user_score($userId, 1, intval($userconfig['user_register_score']));
 * }
 * if ($userconfig['user_register_vip']) {
 * model('Score')->user_reg_time($userId, intval($userconfig['user_register_vip']));
 * }
 *
 * if ($this->userpid && $userconfig['user_register_score_uid']) {
 * model('Score')->user_score($userId, 9, intval($userconfig['user_register_score_uid']));
 * }
 * if ($isautologin) {
 * $udata = $rs->getuserinfo();
 * return $this->autoLogin($udata);
 * }
 * return 1;
 *
 * }
 *
 * public function forgetpwd($email = '', $validate = '')
 * {
 * $userconfig = F('_data/userconfig_cache');
 * if ($this->request->isPost()) {
 * $emailarray = explode("@", $email);
 * $rs = model('User');
 * $result = $this->checkcode($validate);
 * if ($result != 1) {
 * return json(['msg' => "验证码错误", 'rcode' => -1]);
 * }
 * $user = $rs->where('email', $email)->find();
 * $email = new Email;
 * $result = $email->forgetpwdemail($user);
 * if ($result) {
 * return json(['msg' => "系统已经将链接发至您的邮箱，请登录您邮箱进行密码重置", 'rcode' => 1, 'redir' => "http://mail." . $emailarray[1], 'wantredir' => 1]);
 * } else {
 * return json(["msg" => $email . "请联系网站管理", "rcode" => "-1"]);
 * }
 *
 * }
 * return view('/forgetpwd');
 * }
 *
 * public function repass()
 * {
 * $code = htmlspecialchars(input('code/s', ''));
 * $userconfig = F('_data/userconfig_cache');
 * $auth_key = !empty($userconfig['user_key']) ? $userconfig['user_key'] : 'xianyucms';
 * $cms_auth_key = md5($auth_key);
 * $code_res = sys_auth($code, 'DECODE', $cms_auth_key);
 * if (empty($code_res)) {
 * return $this->error('验证地址错误请重新找回密码', xianyu_user_url('user/login/forgetpwd'));
 * }
 * $code_arr = explode('#', $code_res);
 * $userid = isset($code_arr[0]) ? $code_arr[0] : '';
 * $codetime = isset($code_arr[1]) ? $code_arr[1] : 0;
 * $codecache = cache('user_forgetpwd_' . $userid);
 * if ($codecache != $code) {
 * return $this->error('验证连接已使用，请重新找回密码', xianyu_user_url('user/login/forgetpwd'));
 * }
 * if ($this->request->isPost()) {
 * $data['password'] = htmlspecialchars(input('password/s'));
 * $data['repassword'] = htmlspecialchars(input('repassword/s'));
 * $data['encrypt'] = rand_string(6);
 * $data['userid'] = $userid;
 * $result = $this->validate($data, 'user.password');
 * if ($result != 1) {
 * return json(['msg' => $result, 'rcode' => -1]);
 * }
 * $rs = model('User');
 * $rs->save($data, array('userid' => $userid));
 * cache('user_forgetpwd_' . $userid, NULL);
 * if ($rs) {
 * return json(["msg" => "密码重置成功，请使用新密码登录!", "rcode" => "1", "redir" => xianyu_user_url('user/login/index'), "wantredir" => 1]);
 * } else {
 * return json(["msg" => "密码重置失败，请重新获取找回验证码", "rcode" => "-1", "redir" => xianyu_user_url('user/login/forgetpwd'), "wantredir" => 1]);
 * }
 * } else {
 * if (time() - $codetime > 3600) {
 * return $this->error('验证连接超时请重发验证码', xianyu_user_url('user/login/forgetpwd'));
 * } else {
 * return view('/repass');
 * }
 * }
 * }
 *
 * private function getqrtoken($qrsig)
 * {
 * $len = strlen($qrsig);
 * $hash = 0;
 * for ($i = 0; $i < $len; $i++) {
 * $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
 * $hash &= 2147483647;
 * }
 * return $hash & 2147483647;
 * }
 *
 * public $ua = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36";
 *
 * public function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
 * {
 * $ch = curl_init();
 * curl_setopt($ch, CURLOPT_URL, $url);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
 * $httpheader[] = "Accept: application/json";
 * $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
 * $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
 * $httpheader[] = "Connection: keep-alive";
 * curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
 * if ($post) {
 * curl_setopt($ch, CURLOPT_POST, 1);
 * curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
 * }
 * if ($header) {
 * curl_setopt($ch, CURLOPT_HEADER, TRUE);
 * }
 * if ($cookie) {
 * curl_setopt($ch, CURLOPT_COOKIE, $cookie);
 * }
 * if ($referer) {
 * curl_setopt($ch, CURLOPT_REFERER, $referer);
 * }
 * if ($ua) {
 * curl_setopt($ch, CURLOPT_USERAGENT, $ua);
 * } else {
 * curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
 * }
 * if ($nobaody) {
 * curl_setopt($ch, CURLOPT_NOBODY, 1);
 *
 * }
 * curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 * curl_setopt($ch, CURLOPT_ENCODING, "gzip");
 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 * $ret = curl_exec($ch);
 * curl_close($ch);
 * return $ret;
 * }
 *
 * public function get_curl_split($url)
 * {
 * $ch = curl_init();
 * curl_setopt($ch, CURLOPT_URL, $url);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
 * // $httpheader[] = "Accept: ";
 * $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
 * $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
 * $httpheader[] = "Connection: keep-alive";
 * curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
 * curl_setopt($ch, CURLOPT_HEADER, TRUE);
 * curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
 * curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 * curl_setopt($ch, CURLOPT_ENCODING, "gzip");
 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 * $ret = curl_exec($ch);
 * $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
 * $header = substr($ret, 0, $headerSize);
 * $body = substr($ret, $headerSize);
 * $ret = array();
 * $ret['header'] = $header;
 * $ret['body'] = $body;
 * curl_close($ch);
 * return $ret;
 * }
 * }
 */