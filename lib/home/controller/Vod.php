<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Db;
use think\Cache;

class Vod extends Home
{
    public function show()
    {
        if ($this->Url['dir']) {
            $this->Url['id'] = getlist($this->Url['dir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        } else {
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $this->assign('param', $this->Url);
            $this->assign($channel);
            return view('/' . $channel['list_skin']);
        } else {
            abort(404, '页面不存在');
        }
    }

    //列表检索
    public function type()
    {
        if ($this->Url['order'] == "addtime") {
            $this->Url['order'] = "";
        }
        if ($this->Url['dir']) {
            $this->Url['id'] = getlist($this->Url['dir'], 'list_dir', 'list_id');
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        } else {
            $this->Url['dir'] = getlist($this->Url['id'], 'list_id', 'list_dir');
        }
        if (!empty($this->Url['mename']) && empty($this->Url['mcid'])) {
            $this->Url['mcid'] = get_mcat_ename($this->Url['mename'], $this->Url['id']);
        }
        if (!empty($this->Url['mcid']) && empty($this->Url['mename'])) {
            $this->Url['mename'] = get_mcat_id($this->Url['mcid']);
        }
        if (!empty($this->Url['earea']) && empty($this->Url['area'])) {
            $this->Url['area'] = get_area($this->Url['earea'], 'ename', 'name');
        }
        if (!empty($this->Url['area']) && empty($this->Url['earea'])) {
            $this->Url['earea'] = get_area($this->Url['area'], 'name', 'earea');
        }
        config('params', array('id' => $this->Url['id'], 'dir' => $this->Url['dir'], 'mcid' => $this->Url['mcid'], 'mename' => $this->Url['mename'], 'area' => $this->Url['area'], 'earea' => $this->Url['earea'], 'year' => $this->Url['year'], 'letter' => $this->Url['letter'], 'order' => $this->Url['order'], 'p' => "xianyupage"));
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List && $List[0]['list_sid'] == 1) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $this->assign('param', $this->Url);
            $this->assign($channel);
            //AJAX页面加载
            if ($this->request->isAjax()) {
                $channel['list_skin_ajax'] = $channel['list_skin_type'] . "_ajax";
                return view('/' . $channel['list_skin_ajax']);
            } else {
                return view('/' . $channel['list_skin_type']);
            }
        } else {
            abort(404, '页面不存在');
        }
    }

    public function read()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['id'])) {
            $this->Url['id'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            return view('/' . $array_detail['read']['vod_skin_detail']);
        } else {
            abort(404, '页面不存在');
        }
    }


    // 影片播放页
    public function play()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['id'])) {
            $this->Url['id'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $array_detail['read'] = $this->Lable_Vod_Play($array_detail['read'], $array_detail['show'], array('id' => $this->Url['id'], 'sid' => $this->Url['sid'], 'pid' => $this->Url['pid']));
            if ($this->Url['pid'] > $array_detail['read']['play_count']) {
                abort(404, '播放集数不存在');
            }
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);

            return view('/' . $array_detail['read']['vod_skin_play']);
        } else {
            abort(404, '页面不存在');
        }
    }

    // VIP权限播放

    public function vip()
    {
        $array_detail = $this->get_cache_detail($this->Url['id']);
        $detail = $this->Lable_Vod_Play_vip($array_detail['read'], $array_detail['show'], array('id' => $this->Url['id'], 'sid' => $this->Url['sid'], 'pid' => $this->Url['pid']));
        $wechat = F('_data/wechatconfig_cache');
        $detail['play_qrcode'] = $wechat['wechat_qrcode'];
        $detail['userid'] = cmf_is_user_login();
        //试看结束提示 action= trysee|ispay|play|pwd
        if ($this->Url['type'] == 'trysee') {
            $this->assign($detail);
            return view('/vod_player_trysee');
        }
        //微信获取密码
        if ($detail['play_vipplay'] == 3) {
            $pwdcookie = cookie(md5('play_' . $this->Url['id'] . '_pwd'));
            if ($pwdcookie != md5(get_client_ip())) {
                $detail['play_status'] = 600;
            }
        } //视频是否需要权限
        elseif ($detail['play_vipplay'] || $detail['play_pay']) {
            //用户是否登录
            if ($detail['userid'] < 1) {
                $detail['play_status'] = 500;//用户未登录
            } else {
                $user = model('User')->where('user_status', 0)->find(cmf_is_user_login());
                if (!$user) {
                    $detail['play_status'] = 501;//没有找到用户或用户被锁定
                } else {
                    $detail['user_score'] = $user['score'];
                    $detail['user_deadtime'] = $user['viptime'];
                }
            }
            //VIP包月权限
            if ($user && $detail['play_vipplay'] == 2) {
                if (time() > $user['viptime']) {
                    $detail['play_status'] = 502;//vip到期 提示
                }
            }
            //单片点播权限
            if ($user && $detail['play_pay']) {
                if (!model('score')->count_score_log($detail['userid'], 3, 1, $detail['play_id'])) {
                    $detail['play_status'] = 503;//未查询到购买记录
                }
            }
        }
        //状态提示
        $this->assign($detail);
        if ($detail['play_status'] == 500 || $detail['play_status'] == 501) {
            $detail['play_tips'] = $this->view->fetch('/vod_play_vip_login');
        } elseif ($detail['play_status'] == 502) {
            $detail['play_tips'] = $this->view->fetch('/vod_play_vip_ispay');
        } elseif ($detail['play_status'] == 503) {
            $detail['play_tips'] = $this->view->fetch('/vod_play_vip_price');
        } elseif ($detail['play_status'] == 600) {
            $detail['play_tips'] = $this->view->fetch('/vod_play_weixin');
        } else {
            $detail['play_tips'] = '播放正常';
        }
        //单片付费点播扣点处理
        if ($this->Url['type'] == "ispay" && $detail['play_status'] == 503) {
            if ($user['score'] < $detail['play_pay']) {
                $detail['play_status'] = 504;//用户影币不足提示充值
                $detail['play_tips'] = $this->view->fetch('/vod_play_vip_short');
            } else {
                model('score')->user_score($detail['userid'], 3, -abs($detail['play_pay']), 1, $detail['play_id']);
                $detail['play_status'] = 200;//扣除用户影币成功就改为可播放状态
                $detail['play_tips'] = '播放正常';
            }
        }
        if ($this->Url['type'] == "pwd") {
            $vodpwd = cache($this->Url['id'] . '_' . input('pwd/d', ''), '', array('prefix' => 'play_pwd'));
            if ($vodpwd) {
                $detail['play_status'] = 200;
                $detail['play_tips'] = '播放正常';
                cache($this->Url['id'] . '_' . input('pwd/d', ''), NULL, array('prefix' => 'play_pwd'));
                cookie(md5('play_' . $this->Url['id'] . '_pwd'), md5(get_client_ip()), $wechat['wechat_playtime']);
            } else {
                $detail['play_status'] = 600;
                $detail['play_tips'] = $this->view->fetch('/vod_play_weixin');
            }

        }
        //直接输出或ajax返回
        if ($this->Url['type'] == "pwd") {
            $data['status'] = $detail['play_status'];
            $data['tips'] = $detail['play_tips'];
            return json($data);
        } elseif ($this->Url['type'] == "ispay") {
            $data['data'] = $detail['play_pay'];
            $data['status'] = $detail['play_status'];
            $data['info'] = $detail['play_tips'];
            return json($data);
        } else {
            $this->assign($detail);
            return view('/vod_play_vip');
        }

    }

    public function filmtime()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['id'])) {
            $this->Url['id'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
        }
        $JumpUrl = param_jump($this->Url);
        $JumpUrl['p'] = 'xianyupage';
        config('jumpurl', xianyu_url($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action(), $JumpUrl, true, false));
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign($array_detail['tv']);
            $this->assign("thisurl", xianyu_data_url('home/vod/filmtime', array('id' => $array_detail['read']['vod_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['vod_cid'], 'dir' => $array_detail['show']['list_dir'])));
            $array_detail['read']['vod_skin_filmtime'] = $array_detail['read']['vod_skin_detail'] . "_filmtime";
            return view('/' . $array_detail['read']['vod_skin_filmtime']);
        } else {
            abort(404, '页面不存在');
        }
    }

    // 影片内容页
    public function news()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['id'])) {
            $this->Url['id'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['vod_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['vod_cid'], 'dir' => getlistname($array_detail['read']['vod_cid'], 'list_dir'), 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign("thisurl", str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/vod/news', array('id' => $array_detail['read']['vod_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['vod_cid'], 'dir' => $array_detail['show']['list_dir'], 'p' => $this->Url['page']))));
            $array_detail['read']['vod_skin_news'] = $array_detail['read']['vod_skin_detail'] . "_news";
            return view('/' . $array_detail['read']['vod_skin_news']);
        } else {
            abort(404, '页面不存在');
        }
    }

    // 影片下载页面
    public function down()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['id'])) {
            $this->Url['id'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
        }
        $JumpUrl = param_jump($this->Url);
        $JumpUrl['p'] = 'xianyupage';
        config('jumpurl', xianyu_url($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action(), $JumpUrl, true, false));
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign("thisurl", xianyu_data_url('home/vod/down', array('id' => $array_detail['read']['vod_id'], 'pinyin' => $array_detail['read']['vod_letters'], 'cid' => $array_detail['read']['vod_cid'], 'dir' => $array_detail['show']['list_dir'])));
            $array_detail['read']['vod_skin_down'] = $array_detail['read']['vod_skin_detail'] . "_down";
            return view('/' . $array_detail['read']['vod_skin_down']);
        } else {
            abort(404, '页面不存在');
        }
    }

    // 从数据库获取数据
    private function get_cache_detail($vod_id)
    {
        if (!$vod_id) {
            return false;
        }
        //优先读取缓存数据
        if (config('data_cache_vod')) {
            $array_detail = Cache::get('data_cache_vod_' . $vod_id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['vod_id'] = $vod_id;
        $where['vod_status'] = array('eq', 1);
        $array = Db::name('vod')->field('v.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status')->alias('v')->join('story s', 's.story_vid = v.vod_id', 'LEFT')->join('actor a', 'a.actor_vid = v.vod_id', 'LEFT')->where($where)->find();
        if ($array) {
            //解析标签
            $array_detail = $this->Lable_Vod_Read($array);
            if (config('data_cache_vod')) {
                Cache::tag('model_vod')->set('data_cache_vod_' . $vod_id, $array_detail, intval(config('data_cache_vod')));
            }
            return $array_detail;
        }
        return false;
    }
}
