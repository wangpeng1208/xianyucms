<?php
namespace app\user\controller;
use app\common\controller\Home;
use think\Db;
class Qrcodelogin extends Home
{
    public function qq()
    {
        // halt(user_islogin());
        // exit;
        if(user_islogin()){
			return $this->redirect(xianyu_user_url('user/center/index'));
		}
        $url = 'https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.5409099' . time() . '&daid=5';
        $arr = $this->get_curl_split($url);
        preg_match('/qrsig=(.*?);/', $arr['header'], $match);
        if ($qrsig = $match[1]) {
            $this->assign('qrsig', $qrsig);
            $this->assign('qrcode', base64_encode($arr['body']));
            return $this->fetch('/login/ajax');
        } else {
            $this->error('二维码获取失败');
        }
    }

    public function listenQq()
    {
        $qrsig = input('get.qrsig');

        if (empty($qrsig)) {
            $this->error('qrsig不能为空');
        }
        $url = 'https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken=' . $this->getqrtoken($qrsig) . '&login_sig=&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-' . time() . '0000&js_ver=10194&js_type=1&pt_uistyle=40&aid=549000912&daid=5&';
        $ret = $this->get_curl($url, 0, $url, 'qrsig=' . $qrsig . '; ', 1);
        if (preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)) {
            $r = explode("','", str_replace("', '", "','", $arr[1]));
            if ($r[0] == 0) {
                preg_match('/uin=(\d+)&/', $ret, $uin);
                $openid = $uin[1];
                preg_match('/skey=@(.{9});/', $ret, $skey);
                preg_match('/superkey=(.*?);/', $ret, $superkey);
                $data = $this->get_curl($r[2], 0, 0, 0, 1);
                $pskey = null;
                if ($data) {
                    preg_match("/p_skey=(.*?);/", $data, $matchs);
                    $pskey = $matchs[1];
                }
                if ($pskey) {
                    $ip = ip2long($this->request->ip(0, true));
                    $findThirdPartyUser = db("user_api")->where('openid', $openid)->find();
                    if ($findThirdPartyUser) {
                        $userData = [
                            'lastip' => $ip,
                            'lastdate' => time(),
                            'loginnum' => Db::raw('loginnum+1'),
                        ];
                         $userApi = [
                            'qqzone' => $r[2],
                        ];
                        Db::name("user")
                            ->where('userid', $findThirdPartyUser['uid'])
                            ->update($userData);
                         Db::name("user_api")
                            ->where('uid', $findThirdPartyUser['uid'])
                            ->update($userApi);
                        $user = Db::name("user")->where('userid', $findThirdPartyUser['uid'])->find();
                    } else {
                        $userId = Db::name("user")->insertGetId([
                            'regdate' => time(),
                            'islock' => 0,
                            'score' => 100,
                            'nickname' => $r[5],
                            'username' => $r[5],
                            'regip' => $ip,
                            'avatar' => 'https://s1.ax1x.com/2020/10/09/0BXqIg.jpg', // 默认头像
                            'lastip' => $ip,
                            'loginnum' => 1,
                            'lastdate' => time(),
                            'email' => $openid."@qq.com",
                            'iemail' => $openid."@qq.com",
                            'isRemind' => 1,
                            'isstation' => 1,
                        ]);
                        Db::name("user_api")->insert([
                            'openid' => $openid,
                            'uid' => $userId,
                            'channel' => 'qq',
                            'qqzone' => $r[2],
                        ]);
                        $user = Db::name("user")->where('userid', $userId)->find();
                    }


                $userconfig=F('_data/userconfig_cache');
        		$avatar=ps_getavatar($user['userid'],$user['pic'],$user['pic']);
        		/* 记录登录SESSION和COOKIES */
        		$auth = array(
        			'userid'  => $user['userid'],
        			'username'  => $user['username'],
        			'nickname'  => $user['nickname'],
        			'useremail'  => $user['email'],
        			'groupid'  => $user['groupid'],
        			'avatar'  => $avatar['big'],
        			'lastdate' => $user['lastdate'],
        			'lastip' => $user['lastip'],
        			'user_email_auth' => $userconfig['user_email_auth'],
        		);
        		if($userconfig['user_email_auth'] && $user['groupid']==1){
        		    cookie('user_auth', null);
        		    cookie('user_auth_sign', null);
        		    session('user_auth', null);
        		    session('user_auth_sign', null);
        			session('user_auth_temp',$auth);//记录临时session方便重发邮件修改密码
        		}
        		else{
        		//print_r(data_auth_sign($auth)) ;	
        		cookie('user_auth', $auth);
        		cookie('user_auth_sign', data_auth_sign($auth));
        		session('user_auth', $auth);
        		session('user_auth_sign', data_auth_sign($auth));
        		session('user_auth_temp',$null);
        		}
		
		
		
                    $this->success('登录成功');
                } else {
                    $this->error('获取相关信息失败');
                }
            } elseif ($r[0] == 65) {
                $this->error('二维码已失效');
            } elseif ($r[0] == 66) {
                $this->error('二维码未失效');
            } elseif ($r[0] == 67) {
                $this->error('正在验证二维码');
            } else {
                $this->error($r[4]);
            }
        } else {
            $this->error($ret);
        }
    }

    private function getqrtoken($qrsig)
    {
        $len = strlen($qrsig);
        $hash = 0;
        for ($i = 0; $i < $len; $i++) {
            $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
            $hash &= 2147483647;
        }
        return $hash & 2147483647;
    }

    public $ua = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36";

    public function get_curl($url, $post = 0, $referer = 0, $cookie = 0, $header = 0, $ua = 0, $nobaody = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: application/json";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: keep-alive";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
        }
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if ($ua) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
        }
        if ($nobaody) {
            curl_setopt($ch, CURLOPT_NOBODY, 1);

        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    public function get_curl_split($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: keep-alive";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($ret, 0, $headerSize);
        $body = substr($ret, $headerSize);
        $ret = array();
        $ret['header'] = $header;
        $ret['body'] = $body;
        curl_close($ch);
        return $ret;
    }
}
