<?php

namespace app\common\controller;

use app\common\controller\All;
use think\Db;
use think\Request;

class Home extends All
{
    protected $Url = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->Url = param_url();
        config('model', $this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        config('params', array('id' => $this->Url['id'], 'dir' => $this->Url['dir'], 'p' => "xianyupage"));
        config('currentpage', $this->Url['page']);
    }

    //栏目页变量定义
    public function Lable_List($param, $array_list)
    {
        $array_list['list_page'] = !empty($param['page']) ? $param['page'] : 1;
        if (empty($array_list['list_skin'])) {
            $array_list['list_skin'] = $array_list['name'] . '_list';
        }
        if (empty($array_list['list_skin_type'])) {
            $array_list['list_skin_type'] = $array_list['name'] . '_type';
        }
        return $array_list;
    }

    //标签变量定义
    public function Lable_Tag($param, $array_list)
    {
        $array_list['list_page'] = !empty($param['page']) ? $param['page'] : 1;
        $array_list['list_skin'] = $array_list['name'] . '_tag_list';
        return $array_list;
    }

    public function Lable_Vod($array)
    {
        $array['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => getlistname($array['vod_cid'], 'list_dir'), 'jumpurl' => $array['vod_jumpurl']));
        $array['vod_playurl'] = xianyu_play_url(array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => getlistname($array['vod_cid'], 'list_dir'), 'sid' => 0, 'pid' => 1));
        $array['vod_picurl'] = xianyu_img_url($array['vod_pic']);
//        $array['vod_bigpicurl'] = xianyu_img_url($array['vod_bigpic']);
//        $array['vod_picurl_small'] = xianyu_img_smallurl($array['vod_pic']);
        $array['vod_hits_all'] = gethits(1, 'vod_hits', $array);
        $array['vod_hits_insert'] = gethits(1, 'insert', $array);
        $array['vod_hits_month'] = gethits(1, 'vod_hits_month', $array);
        $array['vod_hits_week'] = gethits(1, 'vod_hits_week', $array);
        $array['vod_hits_day'] = gethits(1, 'vod_hits_day', $array);
        $array['vod_content'] = $array['vod_content'];
        if (strstr($array['vod_play'], "down")) {
            $array['vod_downurl'] = xianyu_data_url('home/vod/down', array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => getlistname($array['vod_cid'], 'list_dir'), 'jumpurl' => $array['vod_jumpurl']));
        }
        if ($array['story_id']) {
            $array['story_readurl'] = xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir')));
        }
        if ($array['actor_id']) {
            $array['actor_readurl'] = xianyu_data_url('home/actor/read', array('id' => $array['actor_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['actor_cid'], 'dir' => getlistname($array['actor_cid'], 'list_dir')));
        }
        return $array;
    }

    /*****************影视内容,播放页公用变量定义******************************
     * @$array/具体的内容信息
     * @$array_play 为解析播放页
     * @返回赋值后的arrays 多维数组*/
    public function Lable_Vod_Read($array, $array_play = false)
    {
        $array = $this->Lable_Vod($array);
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['vod_cid']);
        //$array['thisurl'] = $array['vod_readurl'];
        //$lastplayurl = play_url_end($array['vod_url'],$array['vod_play']);
        //$array['vod_lastname'] = $lastplayurl[2];
        //$array['vod_lasturl'] = xianyu_play_url(array('id'=>$array['vod_id'],'pinyin'=>$array['vod_letters'],'cid'=>$array['vod_cid'],'dir'=>$array_list[0]['list_dir'],'sid'=>$lastplayurl[0],'pid'=>$lastplayurl[1]));
        if ($array['vod_skin']) {
            $array['vod_skin_detail'] = trim($array['vod_skin']);
            if (config('player_http') == 1 || $array['vod_copyright'] == 2) {
                $array['vod_skin_play'] = trim($array['vod_skin']) . '_play_blank';
            } else {
                $array['vod_skin_play'] = trim($array['vod_skin']) . '_play';
            }
        } else {
            $array['vod_skin_detail'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'vod_detail';
            if (config('player_http') == 1 || $array['vod_copyright'] == 2) {
                $array['vod_skin_play'] = !empty($array_list[0]['list_skin_play']) ? $array_list[0]['list_skin_play'] . '_blank' : 'vod_play_blank';
            } else {
                $array['vod_skin_play'] = !empty($array_list[0]['list_skin_play']) ? $array_list[0]['list_skin_play'] : 'vod_play';
            }
        }
        $array['vod_diantai'] = implode(',', model('Vodtv')->tv_data($array['vod_id'], 1));
        $array['vod_player'] = null;
        $array['vod_playlist'] = null;
        $array['vod_countplay'] = null;
        $array['vod_downlist'] = null;
        //播放列表解析
        $playlist = $this->playlist_all($array, $array_list[0]['list_dir']);
        //按顺序排列
        if ($playlist[0] && $array['vod_copyright'] != 1) {
            if (empty($array['vod_playoid'])) {
                ksort($playlist[0]);
            }
            $array['vod_player'] = $playlist[2];
            $array['vod_playlist'] = $playlist[0];
            $array['vod_countplay'] = count($playlist[0]);
        }
        if ($playlist[1] && $array['vod_copyright'] != 1) {
            $array['vod_downlist'] = $playlist[1];
        }
        $arrays['show'] = $array_list[0];
        unset($array['vod_url']);
        $arrays['read'] = $array;
        return $arrays;
    }

    /*****************影视播放页变量定义 适用于动态与合集为一个播放页******************************<br />
     * @$array 内容页解析好后的内容页变量 arrays['read']
     * @$array_play 为播放页URL参数 array('id'=>558,'sid'=>1,'pid'=>1)
     * @返回$array 内容页重新组装的数组*/
    public function Lable_Vod_Play($array, $array_list, $array_play)
    {
        $player = F('_data/player');
        $player_here = $array['vod_player'];
        //$array['thisurl'] = xianyu_play_url(array('id'=>$array['vod_id'],'pinyin'=>$array['vod_letters'],'cid'=>$array['vod_cid'],'dir'=>$array_list['list_dir'],'sid'=>$array_play['sid'],'pid'=>$array_play['pid']));
        $play = $array['vod_playlist'][$player[$player_here[$array_play['sid']]]['play_oid'] . '-' . $array_play['sid']];
        // 播放器相关默认配置
        $array['vod_sid'] = $array_play['sid'];
        $array['vod_pid'] = $array_play['pid'];
        $array['vod_jiname'] = $play['son'][$array_play['pid'] - 1]['playname'];
        $array['play_name'] = $play['player_name'];
        $array['play_title'] = $play['player_title'];
        $array['play_sid'] = $array_play['sid'];
        $array['play_pid'] = $array_play['pid'];
        $array['play_oid'] = $player[$play['player_name']]['play_oid'];
        $array['play_width'] = config('player_width');
        $array['play_height'] = config('player_height');
        $array['play_adurl'] = config('player_playad');
        $array['play_second'] = intval(config('player_second'));
        $array['play_apiurl'] = trim(config('player_api'));
        $array['play_cloud'] = $play['player_cloud'];
        $array['play_count'] = $play['player_count'];
        $array['play_key'] = $play['son'][$array_play['pid'] - 1]['playkey'];
        $array['play_jiname'] = $play['son'][$array_play['pid'] - 1]['playname'];
        $array['play_pic'] = $play['son'][$array_play['pid'] - 1]['playpic'];
        $array['play_fen'] = $play['son'][$array_play['pid'] - 1]['playfen'];
        $array['play_miao'] = $play['son'][$array_play['pid'] - 1]['playmiao'];
        $array['play_source'] = $play['son'][$array_play['pid'] - 1]['playsource'];
        $array['play_path'] = $play['son'][$array_play['pid'] - 1]['playpath'];
        $array['play_prevpath'] = null;
        $array['play_prevurl'] = null;
        $array['play_nextpath'] = null;
        $array['play_nexturl'] = null;
        if ($array_play['pid'] - 1 > 0) {
            $array['play_prevpath'] = $play['son'][$array_play['pid'] - 1]['playpath'];
            $array['play_prevurl'] = xianyu_play_url(array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => $array_list['list_dir'], 'sid' => $array_play['sid'], 'pid' => $array_play['pid'] - 1));
        }
        if ($array_play['pid'] + 1 <= $array['play_count']) {
            $array['play_nextpath'] = $play['son'][$array_play['pid']]['playpath'];
            $array['play_nexturl'] = xianyu_play_url(array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => $array_list['list_dir'], 'sid' => $array_play['sid'], 'pid' => $array_play['pid'] + 1));
        }
        $array['play_lastpath'] = $play['son'][$array['play_count'] - 1]['playpath'];
        $array['play_lasturl'] = xianyu_play_url(array('id' => $array['vod_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['vod_cid'], 'dir' => $array_list['list_dir'], 'sid' => $array_play['sid'], 'pid' => $play['player_count']));
        $array['play_urllist'] = $play['son'];

        //付费点播处理
        $array['play_vipplay'] = 0;
        $array['play_pay'] = 0;
        $array['play_trysee'] = 0;

        if ($array_list['list_vipplay']) {
            $array['play_vipplay'] = intval($array_list['list_vipplay']);
        }
        if ($array_list['list_pay']) {
            $array['play_pay'] = intval($array_list['list_pay']);
        }
        if ($array_list['list_trysee']) {
            $array['play_trysee'] = intval($array_list['list_trysee']);
        }
        if ($array['vod_vipplay']) {
            $array['play_vipplay'] = intval($array['vod_vipplay']);
        }
        if ($array['vod_pay']) {
            $array['play_pay'] = intval($array['vod_pay']);
        }
        if ($array['vod_trysee']) {
            $array['play_trysee'] = intval($array['vod_trysee']);
        }
        // 解析服务器变量处理
        if ($play['player_apiurl']) {
            $array['play_apiurl'] = str_replace('{playname}', $array['play_name'], $play['player_apiurl']);
        } elseif ($array['play_apiurl']) {
            $array['play_apiurl'] = str_replace('{playname}', $array['play_name'], $array['play_apiurl']);
        }
        //播放器调用
        if ($array['play_vipplay'] || $array['play_pay']) {//付费与注册用户点播
            $array['vod_player'] = '<div id="xianyucms-player-vip"><div class="xianyucms-player-box">影片需验证观看权限，请稍等。</div><iframe class="xianyucms-player-iframe" src="' . zp_url('home/vod/vip', array('type' => 'play', 'id' => $array['vod_id'], 'sid' => $array['play_sid'], 'pid' => $array['play_pid'])) . '" width="100%" height="100%" frameborder="0"></iframe></div>';
        } else {
            if ($play['player_copyright'] > 0) {
                $array['player_copyright'] = intval($play['player_copyright']);
            }
            $array['vod_player'] = $this->xianyucms_player($array);
        }
        return $array;
    }

    public function Lable_Vod_Play_Vip($array, $array_list, $array_play)
    {
        $player = F('_data/player');
        $player_here = explode('$$$', $array['vod_play']);
        foreach ($player_here as $key => $value) {
            //过滤不存在以及禁用的播放器
            if (empty($player[$value]) || $player[$value]['play_display'] == 0 || substr($value, 0, 4) == 'down') {
                unset($player_here[$key]);
            }
        }
        $player_here = array_values($player_here);
        $play = $array['vod_playlist'][$player[$player_here[$array_play['sid']]]['play_oid'] . '-' . $array_play['sid']];
        $vip['play_id'] = $array['vod_id'];
        $vip['play_sid'] = $array_play['sid'];
        $vip['play_pid'] = $array_play['pid'];
        $vip['play_name'] = $play['player_name'];
        $vip['play_title'] = $play['player_title'];
        $vip['play_width'] = config('player_width');
        $vip['play_height'] = config('player_height');
        $vip['play_apiurl'] = trim(config('player_api'));
        $vip['play_cloud'] = $play['player_cloud'];
        $vip['play_key'] = $play['son'][$array_play['pid'] - 1]['playkey'];
        $vip['play_jiname'] = $play['son'][$array_play['pid'] - 1]['playname'];
        $vip['play_pic'] = $play['son'][$array_play['pid'] - 1]['playpic'];
        $vip['play_fen'] = $play['son'][$array_play['pid'] - 1]['playfen'];
        $vip['play_miao'] = $play['son'][$array_play['pid'] - 1]['playmiao'];
        $vip['play_source'] = $play['son'][$array_play['pid'] - 1]['playsource'];
        $vip['play_path'] = $play['son'][$array_play['pid'] - 1]['playpath'];
        $vip['play_nextpath'] = $play['son'][$array_play['pid']]['playpath'];
        $vip['play_lastpath'] = $play['son'][$play['player_count'] - 1]['playpath'];
        $vip['play_status'] = 200;
        //缓冲广告与版权跳转
        $vip['play_adurl'] = 0;
        $vip['play_second'] = 0;
        $vip['play_copyright'] = 0;
        //付费点播处理
        $vip['play_vipplay'] = 0;
        $vip['play_pay'] = 0;
        $vip['play_trysee'] = 0;
        if ($array_list['list_vipplay']) {
            $vip['play_vipplay'] = intval($array_list['list_vipplay']);
        }
        if ($array_list['list_pay']) {
            $vip['play_pay'] = intval($array_list['list_pay']);
        }
        if ($array_list['list_trysee']) {
            $vip['play_trysee'] = intval($array_list['list_trysee']);
        }
        if ($array['vod_vipplay']) {
            $vip['play_vipplay'] = intval($array['vod_vipplay']);
        }
        if ($array['vod_pay']) {
            $vip['play_pay'] = intval($array['vod_pay']);
        }
        if ($array['vod_trysee']) {
            $vip['play_trysee'] = intval($array['vod_trysee']);
        }
        // 解析服务器变量处理
        if ($play['player_apiurl']) {
            $vip['play_apiurl'] = str_replace('{playname}', $array['play_name'], $play['player_apiurl']);
        } elseif ($array['play_apiurl']) {
            $vip['play_apiurl'] = str_replace('{playname}', $array['play_name'], $array['play_apiurl']);
        }
        $vip['vod_player'] = $this->xianyucms_player($vip);
        return $vip;

    }

    //组合播放地址组列表为二维数组
    public function playlist_all($array, $list_dir)
    {
        if (empty($array['vod_url'])) {
            return false;
        }
        $array_server = explode('$$$', $array['vod_server']);
        $array_player = explode('$$$', $array['vod_play']);
        $array_urllist = explode('$$$', $array['vod_url']);
        $config_player = F('_data/player');
        $config_server = F('_data/play_server');
        foreach ($array_player as $key => $value) {
            //提取下载播放组
            if (substr($value, 0, 4) == 'down') {
                $array_down_player[$key] = $value;
                $array_down_urllist[$key] = $array_urllist[$key];
            }
            //过滤不存在以及禁用的播放器
            if (empty($config_player[$value]) || $config_player[$value]['play_display'] == 0 || substr($value, 0, 4) == 'down') {
                unset($array_player[$key]);
                unset($array_urllist[$key]);
            }
        }
        $array_player = array_values($array_player);
        $array_urllist = array_values($array_urllist);
        $playlist = array();
        foreach ($array_player as $sid => $val) {
            $oid = $config_player[$val]['play_oid'];
            $playlist[$oid . '-' . $sid]['server_name'] = $array_server[$sid];
            $playlist[$oid . '-' . $sid]['server_id'] = $sid;
//            $playlist[$oid . '-' . $sid]['server_url'] = $server[$array_server[$sid]];
            $playlist[$oid . '-' . $sid]['player_sid'] = $sid + 1;
            $playlist[$oid . '-' . $sid]['player_title'] = $config_player[$val]['play_title'];
            $playlist[$oid . '-' . $sid]['player_name'] = $val;
            $playlist[$oid . '-' . $sid]['player_key'] = $config_player[$val]['play_key'];
            $playlist[$oid . '-' . $sid]['player_apiurl'] = $config_player[$val]['play_apiurl'];
            $playlist[$oid . '-' . $sid]['player_copyright'] = $config_player[$val]['play_copyright'];
            $playlist[$oid . '-' . $sid]['player_cloud'] = $config_player[$val]['play_cloud'];
            $playlist[$oid . '-' . $sid]['playername'] = $config_player[$val]['play_title'];
            $playlist[$oid . '-' . $sid]['playname'] = $val;
            $playlist[$oid . '-' . $sid]['son'] = $this->playlist_one($array['vod_id'], $array['vod_letters'], $array['vod_cid'], $array['vod_copyright'], $list_dir, $sid, $config_player[$val]['play_name'], $val, $array_urllist[$sid]);
            $playlist[$oid . '-' . $sid]['player_count'] = count($playlist[$oid . '-' . $sid]['son']);
        }
        //print_r($playlist);
        //存在下载组并循环下载数据
        if ($array_down_player && $array_down_urllist) {
            $array_down_player = array_values($array_down_player);
            $array_down_urllist = array_values($array_down_urllist);
            $downlist = array();
            foreach ($array_down_player as $sid => $val) {
                $oid = $config_player[$val]['play_oid'];
                $downlist[$oid . '-' . $sid]['server_name'] = $array_server[$sid];
                $downlist[$oid . '-' . $sid]['server_id'] = $sid;
                $downlist[$oid . '-' . $sid]['server_url'] = $server[$array_server[$sid]];
                $downlist[$oid . '-' . $sid]['down_sid'] = $sid + 1;
                $downlist[$oid . '-' . $sid]['down_name'] = $val;
                $downlist[$oid . '-' . $sid]['down_title'] = $config_player[$val]['play_title'];
                $downlist[$oid . '-' . $sid]['son'] = $this->downlist_one($array_down_urllist[$sid]);
                $downlist[$oid . '-' . $sid]['down_count'] = count($downlist[$oid . '-' . $sid]['son']);
            }
        }
        //ksort($playlist);
        // print_r($playlist) ;
        return array($playlist, $downlist, $array_player);
    }

    //分解单组播放地址链接
    public function playlist_one($vod_id, $vod_letters, $vod_cid, $vod_copyright, $list_dir, $sid, $playname, $playtitle, $playurl)
    {
        $config_player = F('_data/player');
        $authcode = new \com\AuthCode();
        $urllist = array();
        $array_url = explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $playurl));
        foreach ($array_url as $key => $val) {
            if (strpos($val, '$') > 0) {
                $ji = explode('$', $val);
                $urllist[$key]['playname'] = trim($ji['0']);
                $urllist[$key]['path'] = trim($ji['1']);
                $urllist[$key]['playpic'] = trim($ji['2']);
                $urllist[$key]['playfen'] = trim($ji['3']);
                $urllist[$key]['playmiao'] = trim($ji['4']);
                $urllist[$key]['playsource'] = trim($ji['5']);
            } else {
                $urllist[$key]['playname'] = '第' . ($key + 1) . '集';
                $urllist[$key]['path'] = trim($val);
            }
            if ($config_player[$playtitle]['play_key']) {
                $urllist[$key]['playpath'] = base64_encode($authcode->authcode($urllist[$key]['path'], "ENCODE", $config_player[$playtitle]['play_key'], $config_player[$playtitle]['play_keytime']));
            } else {
                $urllist[$key]['playpath'] = $urllist[$key]['path'];
            }
            $urllist[$key]['playkey'] = $key + 1;
            //版权跳转判断
            if (config('player_http') == 2 || $vod_copyright == 3) {
                $urllist[$key]['playurl'] = $urllist[$key]['path'];
            } else {
                $urllist[$key]['playurl'] = xianyu_play_url(array('id' => $vod_id, 'pinyin' => $vod_letters, 'cid' => $vod_cid, 'dir' => $list_dir, 'sid' => $sid, 'pid' => $key + 1));
            }
        }
        return $urllist;
    }

    //分解单组下载地址
    public function downlist_one($playurl)
    {
        $urllist = array();
        $array_url = explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $playurl));
        foreach ($array_url as $key => $val) {
            if (strpos($val, '$') > 0) {
                $ji = explode('$', $val);
                $urllist[$key]['downname'] = trim($ji[0]);
                $urllist[$key]['downpath'] = $this->ThunderEncode(trim($ji[1]));
            } else {
                $urllist[$key]['downname'] = '第' . ($key + 1) . '集';
                $urllist[$key]['downpath'] = $this->ThunderEncode(trim($val));
            }
        }
        return $urllist;
    }

    //生成迅雷地址
    public function ThunderEncode($url)
    {
        $thunderPrefix = "AA";
        $thunderPosix = "ZZ";
        $thunderTitle = "thunder://";
        $thunderUrl = $thunderTitle . base64_encode($thunderPrefix . $url . $thunderPosix);
        if (strpos($url, 'http://') !== false || strpos($url, 'ftp://') !== false) {
            return $thunderUrl;
        } else {
            return $url;
        }

    }

    //生成播放列表字符串
    public function playlist_json($vod_info_array, $vod_url_array)
    {
        if (empty($vod_url_array)) {
            return false;
        }
        //json数组创建
        $key = 0;
        $config_player = F('_data/player');
        $authcode = new \com\AuthCode();
        //print_r($vod_url_array) ;
        foreach ($vod_url_array as $val) {
            $array_urls[$key]['servername'] = "";
            $array_urls[$key]['playname'] = $val['player_name'];
            $array_urls[$key]['playtitle'] = $val['player_title'];
            foreach ($val['son'] as $keysub => $valsub) {
                $video = array($valsub["playname"], $valsub["playpath"], $valsub["playurl"]);
                if ($config_player[$val["player_name"]]['play_key']) {
                    $video[1] = base64_encode($authcode->authcode(trim($video[1]), "ENCODE", $config_player[$val["player_name"]]['play_key'], $config_player[$val["player_name"]]['play_keytime']));
                }
                $array_urls[$key]["playurls"][$keysub] = $video;
            }
            $key++;
        }
        return json_encode(array('Vod' => $vod_info_array, 'Data' => $array_urls));
    }

    public function xianyucms_player($array)
    {
        $config['player_cloud'] = config('player_cloud');
        if (!empty($array['play_cloud']) || empty($config['player_cloud']) || empty($config['player_cloud']) && empty($array['play_cloud'])) {
            $data = array();
            $data['url'] = $array['play_path'];
            $data['copyright'] = $array['player_copyright'];
            $data['name'] = !empty($array['player_copyright']) ? "copyright" : $array['play_name'];
            $data['apiurl'] = $array['play_apiurl'];
            $data['adtime'] = $array['play_second'];
            $data['adurl'] = $array['play_adurl'];
            $data['next_path'] = $array['play_nextpath'];
            return '<script>var xianyucms_player = ' . json_encode($data) . ';</script><script src="' . config('site_path') . PUBLIC_PATH . 'player/' . $data['name'] . '.js"></script>';
        } else {
            return '<script src="' . config('player_cloud') . '?url=' . base64_encode($array['play_path']) . '&name=' . $array['play_name'] . '&co=' . $array['player_copyright'] . '&api=' . base64_encode($array['play_apiurl']) . '&time=' . $array['play_second'] . '&ad=' . base64_encode($array['play_adurl']) . '&next=' . base64_encode($array['play_nextpath']) . '"></script>' . "\n";
        }

    }

    public function Lable_Story_Read($array)
    {
        $array['story_hits_all'] = gethits(4, 'story_hits', $array);
        $array['story_hits_insert'] = gethits(4, 'insert', $array);
        $array['story_hits_month'] = gethits(4, 'story_hits_month', $array);
        $array['story_hits_week'] = gethits(4, 'story_hits_week', $array);
        $array['story_hits_day'] = gethits(4, 'story_hits_day', $array);
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['story_cid']);
        $vod_list = list_search(F('_data/list'), 'list_id=' . $array['vod_cid']);
        if ($array['story_skin']) {
            $array['story_skin'] = trim($array['vod_skin']);
        } else {
            $array['story_skin'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'story_detail';
        }
        if ($array['vod_id']) {
            $array = $this->Lable_Vod($array);
        } else {
            unset($array['vod_id']);
        }
        $array['story_list'] = $this->Lable_Story_Read_List($array);
        if ($array['actor_id'] && $array['actor_status'] == 1) {
            $array['actor_readurl'] = xianyu_data_url('home/actor/read', array('id' => $array['actor_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['actor_cid'], 'dir' => getlistname($array['actor_cid'], 'list_dir')));
        } else {
            unset($array['actor_id']);
        }
        unset($array['story_content']);
        $arrays['show'] = $array_list[0];
        $arrays['read'] = $array;
        return $arrays;

    }

    //剧情内容
    public function Lable_Story_Read_List($array)
    {
        if (!empty($array['story_content'])) {//如果存在剧情
            $array['story_content'] = $array['story_content'];
            $listarray = explode('||', $array['story_content']); //将每一集分组
            foreach ($listarray as $key => $jiinfo) {
                $k = $key + 1;
                $jiinfoarray = explode('@@', $jiinfo);
                $arrays['story_list'][$key + 1]['story_name'] = strip_tags($jiinfoarray[0]);
                $arrays['story_list'][$key + 1]['story_title'] = strip_tags($jiinfoarray[1]);
                $arrays['story_list'][$key + 1]['story_info'] = $jiinfoarray[2];
                $arrays['story_list'][$key + 1]['story_page'] = $k;
                if ($k == 1) {
                    $arrays['story_list'][$k]['story_url'] = xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir')));
                    if ($array['vod_storypage'] != 1) {
                        $arrays['story_list'][$k]['story_nexturl'] = str_replace('xianyupage', $k + 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k + 1)));
                    }
                } elseif ($k == $array['story_page']) {
                    $arrays['story_list'][$k]['story_prevurl'] = str_replace('xianyupage', $k - 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k - 1)));
                    $arrays['story_list'][$k]['story_url'] = str_replace('xianyupage', $k, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k)));
                } else {
                    $arrays['story_list'][$k]['story_url'] = str_replace('xianyupage', $k, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k)));
                    if ($k == 2) {
                        $arrays['story_list'][$k]['story_prevurl'] = str_replace('xianyupage', $k - 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'))));
                    } else {
                        $arrays['story_list'][$k]['story_prevurl'] = str_replace('xianyupage', $k - 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k - 1)));
                    }
                    $arrays['story_list'][$k]['story_nexturl'] = str_replace('xianyupage', $k + 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $k + 1)));

                }
            }
            return $arrays['story_list'];
        } else {
            return false;
        }
    }

    public function Lable_Actor_Read($array)
    {
        $array = $this->Lable_Vod($array);
        $array['actor_hits_all'] = gethits(6, 'actor_hits', $array);
        $array['actor_hits_insert'] = gethits(6, 'insert', $array);
        $array['actor_hits_month'] = gethits(6, 'actor_hits_month', $array);
        $array['actor_hits_week'] = gethits(6, 'actor_hits_week', $array);
        $array['actor_hits_day'] = gethits(6, 'actor_hits_day', $array);
        $array['actor_readurl'] = xianyu_data_url('home/actor/read', array('id' => $array['actor_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['actor_cid'], 'dir' => getlistname($array['actor_cid'], 'list_dir')));
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['actor_cid']);
        $vod_list = list_search(F('_data/list'), 'list_id=' . $array['vod_cid']);
        if ($array['actor_skin']) {
            $array['actor_skin'] = trim($array['actor_skin']);
        } else {
            $array['actor_skin'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'actor_detail';
        }
        if ($array['story_id']) {
            $array['story_readurl'] = xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir')));
        }
        $arrays['show'] = $array_list[0];
        $arrays['read'] = $array;
        return $arrays;


    }

    public function Lable_Role_list($array)
    {
        if (!$array) return false;
        foreach ($array as $key => $val) {
            $arrays[$key] = $this->Lable_Role_Read($val);
        }
        return $arrays;
    }

    public function Lable_Role_Read($array)
    {
        if ($array['vod_id']) {
            $array = $this->Lable_Vod($array);
        }
        $array['role_picurl'] = xianyu_img_url($array['role_pic']);
        $array['role_picurl_small'] = xianyu_img_smallurl($array['role_pic']);
        $array['role_content'] = $array['role_content'];
        $array['role_hits_all'] = gethits(5, 'role_hits', $array);
        $array['role_hits_insert'] = gethits(5, 'insert', $array);
        $array['role_hits_month'] = gethits(5, 'role_hits_month', $array);
        $array['role_hits_week'] = gethits(5, 'role_hits_week', $array);
        $array['role_hits_day'] = gethits(5, 'role_hits_day', $array);
        $array['role_readurl'] = xianyu_data_url('home/role/read', array('id' => $array['role_id'], 'pinyin' => $array['vod_letters'], 'cid' => $array['role_cid'], 'dir' => getlistname($array['role_cid'], 'list_dir')));
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['role_cid']);
        if ($array['role_skin']) {
            $array['role_skin'] = trim($array['role_skin']);
        } else {
            $array['role_skin'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'role_detail';
        }
        $array['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $array['star_id'], 'pinyin' => $array['star_letters'], 'cid' => $array['star_cid'], 'dir' => getlistname($array['star_cid'], 'list_dir')));
        $array['star_picurl'] = xianyu_img_url($array['star_pic']);
        $array['star_picurl_small'] = xianyu_img_smallurl($array['star_pic']);
        if (!$array['star_id']) {
            $array['star_id'] = null;
            $array['star_readurl'] = xianyu_data_url('home/search/index', array('wd' => urlencode(trim($array['role_star']))));
        }
        $arrays['show'] = $array_list[0];
        $arrays['read'] = $array;
        return $arrays;

    }

    //资讯内容页变量定义
    public function Lable_News_Read($array)
    {
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['news_cid']);
        //$array['thisurl'] = $array['news_readurl'];
        $array['news_picurl'] = xianyu_img_url($array['news_pic']);
        $array['news_picurl_small'] = xianyu_img_url($array['news_pic']);
        $array['news_hits_insert'] = gethits($array_list[0]['list_sid'], 'insert', $array);
        $array['news_hits_all'] = gethits($array_list[0]['list_sid'], 'news_hits', $array);
        $array['news_hits_month'] = gethits($array_list[0]['list_sid'], 'news_hits_month', $array);
        $array['news_hits_week'] = gethits($array_list[0]['list_sid'], 'news_hits_week', $array);
        $array['news_hits_day'] = gethits($array_list[0]['list_sid'], 'news_hits_day', $array);
        $array['news_contents'] = $array['news_content'];

        $array['news_images_array'] = xianyu_img_url_array($array['news_content']);

        $array['news_images_count'] = count($array['news_images_array']);
        $array_content = preg_split("/#xianyupage#/", $array['news_contents']);
//		$slideimg = config('news_images');
        $array['news_page_key'] = 1;
        //内容页面是否有分页标签
        if (count($array_content) > 1) {
            $array['news_page_count'] = count($array_content);
            foreach ($array_content as $key => $value) {
                $key = $key + 1;
                $array['news_pages'][$key]['news_content'] = xianyu_news_img_array($value);
                $array['news_pages'][$key]['news_pagelist'] = getnewspage('home/news/read', array('id' => $array['news_id'], 'cid' => $array['news_cid'], 'dir' => $array_list[0]['list_dir'], 'p' => "xianyupage", 'jumpurl' => $array['news_jumpurl']), $key, $array['news_page_count'], 3);//数字分页
                $array['news_pages'][$key]['news_page'] = $key;
            }
            $array['news_page_key'] = $array['news_page_count'];
            unset($array['news_images_array']);
            unset($array['news_content']);
        } //内容模式为图片分页
        elseif ($array['news_mid'] == 2 && $array['news_images_count'] > 1) {
            $array['news_page_count'] = $array['news_images_count'];
            if ($array['news_page_count'] > 1) {
                //$array['images_slide'][$key]['news_pagelist']=getnewspage('home/news/read',array('id'=>$array['news_id'],'cid'=>$array['news_cid'],'dir'=>$array_list[0]['list_dir'],'p'=>"xianyupage",'jumpurl'=>$array['news_jumpurl']),$key,$array['news_page_count'],config('home_pagenum'),$jumpurl);//数字分页
                foreach ($array['news_images_array'] as $key => $value) {
                    $key = $key + 1;
                    if ($key == 1) {
                        $array['images_slide'][$key]['news_content'] = xianyu_news_images_array($array['news_content']);
                    }
                    $array['images_slide'][$key]['news_images_content'] = $value;
                    $array['images_slide'][$key]['news_pageprevurl'] = getpageprev('home/news/read', array('id' => $array['news_id'], 'cid' => $array['news_cid'], 'dir' => $array_list[0]['list_dir'], 'p' => "xianyupage", 'jumpurl' => $array['news_jumpurl']), $key, $array['news_page_count'], 3);
                    $array['images_slide'][$key]['news_pagenexturl'] = getpagenext('home/news/read', array('id' => $array['news_id'], 'cid' => $array['news_cid'], 'dir' => $array_list[0]['list_dir'], 'p' => "xianyupage", 'jumpurl' => $array['news_jumpurl']), $key, $array['news_page_count'], 3);
                    $array['images_slide'][$key]['news_pagelist'] = getnewspage('home/news/read', array('id' => $array['news_id'], 'cid' => $array['news_cid'], 'dir' => $array_list[0]['list_dir'], 'p' => "xianyupage", 'jumpurl' => $array['news_jumpurl']), $key, $array['news_page_count'], 3);//数字分页
                    $array['images_slide'][$key]['news_page'] = $key;
                }
                $array['news_page_key'] = $array['news_page_count'];
            }
            unset($array['news_content']);
            unset($array['news_images_array']);
        } //内容模式为图片幻灯
        elseif ($array['news_mid'] == 1) {
            $array['news_images_slide'] = xianyu_img_url_array($array['news_content']);
            $array['news_page_count'] = count($array['news_images_slide']);
            if ($array['news_page_count'] >= $slideimg) {
                $array['news_content'] = xianyu_news_images_array($array['news_content']);
            } else {
                $array['news_content'] = xianyu_news_img_array($array['news_content']);
                unset($array['news_images_slide']);
            }
        } else {
            $array['news_content'] = xianyu_news_img_array($array['news_content']);
        }
        unset($array['news_contents']);
        if ($array['news_skin']) {
            $array['news_skin_detail'] = trim($array['news_skin']);
        } else {
            $array['news_skin_detail'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'news_detail';
        }
        //新闻关联明星视频
        if ($array['newsrel']) {
            foreach ($array['newsrel'] as $key => $val) {
                if ($val['newsrel_sid'] == 1 && !empty($val['newsrel_did'])) {
                    $newsrelvod[$key] = $val['newsrel_did'];
                }
                if ($val['newsrel_sid'] == 3 && !empty($val['newsrel_did'])) {
                    $newsrelstar[$key] = $val['newsrel_did'];
                }
                if (empty($val['newsrel_did'])) {
                    $newsrelname[$key] = $val['newsrel_name'];
                }
            }
            if ($newsrelvod) {
                $array['news_vodid'] = implode(',', $newsrelvod);
            }
            if ($newsrelstar) {
                $array['news_starid'] = implode(',', $newsrelstar);
            }
            $newsidarray = model('newsrel')->get_newsid($newsrelvod, $newsrelname);
            $array['news_newsid'] = !empty($newsidarray) ? implode(',', array_unique($newsidarray)) : '';
            unset($array['newsrel']);
        }


        //获取上一篇下一篇
        $nextnews = detail_array($array_list[0]['name'], 'next', $array['news_id'], $array['news_cid'], 'news_id,news_name,news_cid,news_pic,news_addtime,news_remark,news_jumpurl');
        $prevnews = detail_array($array_list[0]['name'], 'prev', $array['news_id'], $array['news_cid'], 'news_id,news_name,news_cid,news_pic,news_addtime,news_remark,news_jumpurl');
        if ($nextnews) {
            $array['news_next'] = $nextnews;
            $array['news_next']['news_picurl'] = xianyu_img_url($nextnews['news_pic']);
            $array['news_next']['news_readurl'] = xianyu_data_url('home/news/read', array('id' => $nextnews['news_id'], 'cid' => $nextnews['news_cid'], 'dir' => getlistname($nextnews['news_cid'], 'list_dir'), 'jumpurl' => $nextnews['news_jumpurl']));
        }
        if ($prevnews) {
            $array['news_prev'] = $prevnews;
            $array['news_prev']['news_picurl'] = xianyu_img_url($prevnews['news_pic']);
            $array['news_prev']['news_readurl'] = xianyu_data_url('home/news/read', array('id' => $prevnews['news_id'], 'cid' => $prevnews['news_cid'], 'dir' => getlistname($prevnews['news_cid'], 'list_dir'), 'jumpurl' => $prevnews['news_jumpurl']));
        }
        if (!empty($array['news_playname']) && !empty($array['news_playurl'])) {
            $player = F('_data/player');
            $news_player = $player[$array['news_playname']];
            // 解析服务器变量处理
            if ($news_player['player_apiurl']) {
                $array['play_apiurl'] = str_replace('{playname}', $array['news_playname'], $news_player['player_apiurl']);
            } elseif (trim(config('player_api'))) {
                $array['play_apiurl'] = str_replace('{playname}', $array['news_playname'], trim(config('player_api')));
            }
            $config['player_cloud'] = config('player_cloud');
            if (!empty($news_player['play_cloud']) || empty($config['player_cloud']) || empty($config['player_cloud']) && empty($news_player['play_cloud'])) {
                $data = array();
                $data['url'] = $array['news_playurl'];
                $data['copyright'] = "";
                $data['name'] = empty($array['news_playname']) ? "copyright" : $array['news_playname'];
                $data['apiurl'] = $array['play_apiurl'];
                $data['width'] = config('player_width');
                $data['height'] = config('player_height');
                $data['adtime'] = config('player_second');
                $data['adurl'] = config('player_playad');
                $array['news_player'] = '<script>var xianyucms_player = ' . json_encode($data) . ';</script><script src="' . config('site_path') . PUBLIC_PATH . 'player/' . $data['name'] . '.js"></script>';
                unset($data);
            } else {
                $array['news_player'] = '<script src="' . config('player_cloud') . '?url=' . base64_encode($array['news_playurl']) . '&name=' . $array['news_playname'] . '&co=&api=' . base64_encode($array['play_apiurl']) . '&time=' . config('player_second') . '&ad=' . base64_encode(config('player_playad')) . '"></script>' . "\n";

            }
        }
        $arrays['show'] = $array_list[0];
        $arrays['read'] = $array;
        //print_r($arrays);
        return $arrays;
    }

    //专题内容页变量定义
    public function Lable_Special_Read($array)
    {
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['special_cid']);
        $array['special_readurl'] = xianyu_data_url('home/special/read', array('id' => $array['special_id'], 'pinyin' => $array['special_letters'], 'cid' => $array['special_cid'], 'dir' => getlistname($array['special_cid'], 'list_dir')));
        //$array['thisurl'] = xianyu_data_url('home/special/read',array('id'=>$array['special_id'],'pinyin'=>$array['special_letters'],'cid'=>$array['special_cid'],'dir'=>getlistname($array['special_cid'],'list_dir')));
        $array['special_logo'] = xianyu_img_url($array['special_logo']);
        $array['special_banner'] = xianyu_img_url($array['special_banner']);
        $array['special_hits_all'] = gethits($array_list[0]['list_sid'], 'special_hits', $array);
        $array['special_hits_insert'] = gethits($array_list[0]['list_sid'], 'insert', $array);
        $array['special_hits_month'] = gethits($array_list[0]['list_sid'], 'special_hits_month', $array);
        $array['special_hits_week'] = gethits($array_list[0]['list_sid'], 'special_hits_week', $array);
        $array['special_hits_day'] = gethits($array_list[0]['list_sid'], 'special_hits_day', $array);
        $array['special_content'] = $array['special_content'];
        if ($array['special_skin']) {
            $array['special_skin'] = trim($array['special_skin']);
        } else {
            $array['special_skin'] = !empty($array_list[0]['list_skin_detail']) ? $array_list[0]['list_skin_detail'] : 'special_detail';
        }
        //收录影视
        $where['topic_tid'] = $array['special_id'];
        $where['topic_sid'] = 1;
        $list_vod = Db::name('topic')->alias('t')->join('vod v', 'v.vod_id = t.topic_did', 'LEFT')->where($where)->order('topic_oid desc,topic_did desc')->select();
        foreach ($list_vod as $key => $val) {
            $list_vod[$key]['list_id'] = $list_vod[$key]['vod_cid'];
            $list_vod[$key]['list_name'] = getlistname($list_vod[$key]['vod_cid'], 'list_name');
            $list_vod[$key]['list_url'] = getlistname($list_vod[$key]['vod_cid'], 'list_url');
            $list_vod[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $list_vod[$key]['vod_id'], 'pinyin' => $list_vod[$key]['vod_letters'], 'cid' => $list_vod[$key]['vod_cid'], 'dir' => getlistname($list_vod[$key]['vod_cid'], 'list_dir'), 'jumpurl' => $list_vod[$key]['vod_jumpurl']));
            $list_vod[$key]['vod_playurl'] = xianyu_play_url(array('id' => $list_vod[$key]['vod_id'], 'pinyin' => $list_vod[$key]['vod_letters'], 'cid' => $list_vod[$key]['vod_cid'], 'dir' => getlistname($list_vod[$key]['vod_cid'], 'list_dir'), 'sid' => 0, 'pid' => 1));
            $list_vod[$key]['vod_picurl'] = xianyu_img_url($list_vod[$key]['vod_pic']);
            $list_vod[$key]['vod_bigpicurl'] = xianyu_img_url($list_vod[$key]['vod_bigpic']);
            $list_vod[$key]['vod_picurl_small'] = xianyu_img_smallurl($list_vod[$key]['vod_pic']);
            unset($list_vod[$key]['vod_url']);
        }
        //收录资讯
        unset($where);
        $where['topic_sid'] = 2;
        $where['topic_tid'] = $array['special_id'];
        $list_news = Db::name('topic')->alias('t')->join('news n', 'n.news_id = t.topic_did', 'LEFT')->where($where)->order('topic_oid desc,topic_did desc')->select();
        foreach ($list_news as $key => $val) {
            $list_news[$key]['list_id'] = $val['news_cid'];
            $list_news[$key]['list_name'] = getlistname($val['news_cid'], 'list_name');
            $list_news[$key]['list_url'] = getlistname($val['news_cid'], 'list_url');
            $list_news[$key]['news_readurl'] = xianyu_data_url('home/news/read', array('id' => $val['news_id'], 'cid' => $val['news_cid'], 'dir' => getlistname($val['news_cid'], 'list_dir'), 'jumpurl' => $val['news_jumpurl']));
            $list_news[$key]['news_picurl'] = xianyu_img_url($val['news_pic']);
            $list_news[$key]['news_picurl_small'] = xianyu_img_smallurl($val['news_pic']);
            unset($list_news[$key]['news_content']);
        }
        //收录明星
        unset($where);
        $where['topic_sid'] = 3;
        $where['topic_tid'] = $array['special_id'];
        $list_star = Db::name('topic')->alias('t')->join('star s', 's.star_id = t.topic_did', 'LEFT')->where($where)->order('topic_oid desc,topic_did desc')->select();
        foreach ($list_star as $key => $val) {
            $list_star[$key]['list_id'] = $val['star_cid'];
            $list_star[$key]['list_name'] = getlistname($val['star_cid'], 'list_name');
            $list_star[$key]['list_url'] = getlistname($val['star_cid'], 'list_url');
            $list_star[$key]['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $val['star_id'], 'pinyin' => $val['star_letters'], 'cid' => $val['star_cid'], 'dir' => getlistname($val['star_cid'], 'list_dir'), 'jumpurl' => $val['star_jumpurl']));
            $list_star[$key]['star_picurl'] = xianyu_img_url($val['star_pic']);
            $list_star[$key]['star_bigpicurl'] = xianyu_img_url($val['star_bigpic']);
            $list_star[$key]['star_picurl_small'] = xianyu_img_smallurl($val['star_pic']);
            unset($list_star[$key]['star_info']);
        }
        $arrays['read'] = $array;
        $arrays['show'] = $array_list[0];
        $arrays['list_vod'] = $list_vod;
        $arrays['list_news'] = $list_news;
        $arrays['list_star'] = $list_star;
        return $arrays;
    }


    public function Lable_Tv_Read($array)
    {
        $Url['p'] = input('p/d', '');
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['tv_cid']);
        $array['tv_picurl'] = xianyu_img_url($array['tv_pic'], $array['tv_content']);
        $array['tv_picurl_small'] = xianyu_img_smallurl($array['tv_pic'], $array['tv_content']);
        $array['tv_hits_all'] = gethits($array_list[0]['list_sid'], 'tv_hits', $array);
        $array['tv_hits_insert'] = gethits($array_list[0]['list_sid'], 'insert', $array);
        $array['tv_hits_month'] = gethits($array_list[0]['list_sid'], 'tv_hits_month', $array);
        $array['tv_hits_week'] = gethits($array_list[0]['list_sid'], 'tv_hits_week', $array);
        $array['tv_hits_day'] = gethits($array_list[0]['list_sid'], 'tv_hits_day', $array);
        $array['tv_content'] = $array['tv_content'];
        if ($array['tv_skin']) {
            $array['tv_skin_detail'] = '' . trim($array['tv_skin']);
        } else {
            $array['tv_skin_detail'] = !empty($array_list[0]['list_skin_detail']) ? '' . $array_list[0]['list_skin_detail'] : 'tv_detail';
        }
        //周期列表解析
        $week = str_replace('0', '7', date("w"));
        $date = date("Y年m月d日");
        $time = strtotime(date("H:i"));
        $array['tv_wid'] = !empty($Url['p']) ? $Url['p'] : $week;
        $data = json_decode($array['tv_data'], true);
        if (count($data) > 1) {
            foreach ($data as $key => $value) {
                $array_week['week'][] = str_replace(array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'), array('1', '2', '3', '4', '5', '6', '7'), $value['week']);
                $array['tv_week_list'][$key]['wid'] = str_replace(array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'), array('1', '2', '3', '4', '5', '6', '7'), $value['week']);
                $array['tv_week_list'][$key]['week'] = $value['week'];
                $array['tv_week_list'][$key]['date'] = $value['date'];
                if ($date == $value['date']) {
                    $array['tv_week_list'][$key]['day'] = 1;
                }
                $array['tv_week_list'][$key]['url'] = str_replace('xianyupage', $array['tv_week_list'][$key]['wid'], xianyu_url('home/tv/read', array('id' => $array['tv_id'], 'pinyin' => $array['tv_letters'], 'cid' => $array['tv_cid'], 'dir' => getlistname($array['tv_cid'], 'list_dir'), 'p' => $array['tv_week_list'][$key]['wid'])));
                if ($array['tv_wid'] != $week && $array['tv_wid'] == $array['tv_week_list'][$key]['wid']) {
                    $array['tv_week_list'][$key]['cut'] = 1;
                } elseif ($date == $array['tv_week_list'][$key]['date'] && $array['tv_wid'] == $week) {
                    $array['tv_week_list'][$key]['cut'] = 1;
                }

            }
            $week = $array_week['week'];
            foreach ($data as $key => $value) {
                $array['tv_datalist'][$week[$key]]['wid'] = str_replace(array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'), array('1', '2', '3', '4', '5', '6', '7'), $value['week']);

                $array['tv_datalist'][$week[$key]]['week'] = $value['week'];
                $array['tv_datalist'][$week[$key]]['date'] = $value['date'];
                $array['tv_datalist'][$week[$key]]['url'] = str_replace('xianyupage', $array['tv_week_list'][$key]['wid'], xianyu_url('home/tv/read', array('id' => $array['tv_id'], 'pinyin' => $array['tv_letters'], 'cid' => $array['tv_cid'], 'dir' => getlistname($array['tv_cid'], 'list_dir'), 'p' => $array['tv_week_list'][$key]['wid'])));
                if ($array['tv_wid'] != $week && $array['tv_wid'] == $array['tv_datalist'][$week[$key]]['wid']) {
                    $array['tv_datalist'][$week[$key]]['cut'] = 1;
                } elseif ($date == $value['date'] && $array['tv_wid'] == $week) {
                    $array['tv_datalist'][$week[$key]]['cut'] = 1;
                }
                $datas = explode(chr(13), trim($value['data']));
                $len = count($datas);
                $lastVal = end($datas);
                foreach ($datas as $k => $val) {
                    $datalist = explode('$', $val);
                    $ndatalist = explode('$', $datas[$k + 1]);
                    if ($date == $value['date'] && $time >= strtotime($datalist[0]) && $time < strtotime($ndatalist[0]) || $date == $value['date'] && $time >= strtotime($datalist[0]) && $k == $len - 1) {
                        $array['tv_datalist'][$week[$key]]['data'][$k]['live'] = 1;
                    }
                    $array['tv_datalist'][$week[$key]]['data'][$k]['time'] = $datalist[0];
                    $array['tv_datalist'][$week[$key]]['data'][$k]['name'] = str_replace('#', '', $datalist[1]);
                    $datalistkey[$k] = explode('#', $datalist[1]);
                    if (count($datalistkey[$k]) > 1) {
                        $array['tv_datalist'][$week[$key]]['data'][$k]['keywords'] = $datalistkey[$k][1];
                    } else {
                        $array['tv_datalist'][$week[$key]]['data'][$k]['keywords'] = preg_replace(array("/.*?:/", "/(\(.*?)\)/", "/.*?：/", "/（(.*?)）/", "/\d*/"), "", $datalist[1]);
                    }
                }
            }
        }
        $array['tv_datalistall'] = $array['tv_datalist'];
        $array['tv_datalist'] = $array['tv_datalist'][$array['tv_wid']];
        $array['tv_title'] = $array['tv_datalist']['week'];
        $array['tv_date'] = $array['tv_datalist']['date'];
        //$array['tv_url']=$array['tv_datalist']['url'];
        $array['tv_url'] = xianyu_data_url('home/tv/read', array('id' => $array['tv_id'], 'pinyin' => $array['tv_letters'], 'cid' => $array['tv_cid'], 'dir' => getlistname($array['tv_cid'], 'list_dir')));
        //$array['thisurl']=$array['tv_url'];
        $arrays['show'] = $array_list[0];
        unset($array['tv_data']);
        $arrays['read'] = $array;
        return $arrays;
    }

    //首页标签定义
    public function Lable_Index()
    {
        $array = array();
        $array['title'] = config('site_name');
        $array['model'] = 'index';
        return $array;
    }

    //明星内容页变量定义
    public function Lable_Star_Read($array)
    {
        $array_list = list_search(F('_data/list'), 'list_id=' . $array['star_cid']);
        $array['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $array['star_id'], 'pinyin' => $array['star_letters'], 'cid' => $array['star_cid'], 'dir' => getlistname($array['star_cid'], 'list_dir'), 'jumpurl' => $array['star_jumpurl']));
        //$array['thisurl'] = xianyu_data_url('home/star/read',array('id'=>$array['star_id'],'pinyin'=>$array['star_letters'],'cid'=>$array['star_cid'],'dir'=>getlistname($array['star_cid'],'list_dir'),'jumpurl'=>$array['star_jumpurl']));
        $array['star_picurl'] = xianyu_img_url($array['star_pic']);
        //设置需要判断的变量为空,避免循环静态生成出现变量继承的情况
        $array['star_bigpicurl'] = null;
        $array['star_guan'] = null;
        $array['star_xi'] = null;
        $array['star_data'] = null;
        if (!empty($array['star_bigpic'])) {
            $array['star_bigpicurl'] = xianyu_img_url($array['star_bigpic']);
        }
        $array['star_hits_insert'] = gethits($array_list[0]['list_sid'], 'insert', $array);
        $array['star_hits_all'] = gethits($array_list[0]['list_sid'], 'star_hits', $array);
        $array['star_hits_month'] = gethits($array_list[0]['list_sid'], 'star_hits_month', $array);
        $array['star_hits_week'] = gethits($array_list[0]['list_sid'], 'star_hits_week', $array);
        $array['star_hits_day'] = gethits($array_list[0]['list_sid'], 'star_hits_day', $array);
        $array['star_content'] = $array['star_content'];
        if ($array['star_skin']) {
            $array['star_skin_detail'] = '' . trim($array['star_skin']);
        } else {
            $array['star_skin_detail'] = !empty($array_list[0]['list_skin_detail']) ? '' . $array_list[0]['list_skin_detail'] : 'star_detail';
        }
        if (!empty($array['star_guanxi'])) {
            $guanxiarray = explode(',', $array['star_guanxi']);
            if (!empty($array['star_guanxi'])) {
                foreach ($guanxiarray as $key => $val) {
                    $gx = explode('@@', $val);
                    $guanxi[$key]['title'] = $gx[0];
                    $guanxi[$key]['name'] = $gx[1];
                    $guan[$gx[1]] = $gx[0];
                    $xi[$key] = $gx[1];
                }
            }
            foreach ($guanxi as $key => $val) {
                $stararray = get_star_find($val['name'], 'star_name');
                if (!empty($stararray)) {
                    $guan_xi[$key] = $stararray;
                    $guan_xi[$key]['star_gx'] = $val['title'];
                    $guan_xi[$key]['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $stararray['star_id'], 'pinyin' => $stararray['star_letters'], 'cid' => $stararray['star_cid'], 'dir' => getlistname($stararray['star_cid'], 'list_dir'), 'jumpurl' => $stararray['star_jumpurl']));
                    $guan_xi[$key]['star_picurl'] = xianyu_img_url($stararray['star_pic']);
                    $guan_xi[$key]['star_bigpicurl'] = xianyu_img_url($stararray['star_bigpic']);
                    $guan_xi[$key]['star_picurl_small'] = xianyu_img_smallurl($stararray['star_pic']);
                    $guan_xi[$key]['star_content'] = $stararray['star_content'];
                    unset($guan_xi[$key]['star_info']);
                } else {
                    unset($guanxi[$key]);
                }
            }
            $array['star_guanxi'] = $guan_xi;
            $array['star_guan'] = $guan;
            $array['star_xi'] = implode(',', $xi);
        }
        if (!empty($array['star_info'])) {
            foreach (explode('||', $array['star_info']) as $k => $val) {
                $info = explode('@@', $val);
                $array['star_data'][$k]['title'] = $info[0];
                $array['star_data'][$k]['info'] = $info[1];
            }
        }
        unset($array['star_info']);
        $arrays['read'] = $array;
        $arrays['show'] = $array_list[0];
        return $arrays;
    }

    //明星作品变量定义
    public function Lable_work_Read($array, $array_play = false)
    {
        $array['sid'] = "star";
        $array['title'] = $array['star_name'] . '-' . C('site_name');
        $array['star_readurl'] = ff_star_url('read', $array['star_id'], $array['star_pyname'], $page);
        //$array['thisurl'] = ff_star_url('read',$array['star_id'],$array['star_pyname'],$page);
        $array['star_pic'] = ff_img_url($array['star_pic']);
        $array['star_skin'] = 'pp_starwork';
        $arrays['work'] = $array;
        return $arrays;
    }

    /**
     * 检测验证码
     * @param integer $id 验证码ID
     * @return boolean     检测结果
     */
    protected function checkVerify($code, $id = 1)
    {
        if ($code) {
            $verify = new \org\Verify();
            $result = $verify->check($code, $id);
            if (!$result) {
                return $this->error("验证码错误！");
            }
        } else {
            return $this->error("验证码为空！");
        }
    }

    protected function checkcode($code, $id = 1)
    {
        if ($code) {
            $verify = new \org\Verify(array('reset' => false));
            $result = $verify->check($code, $id);
            if (!$result) {
                return "验证码错误";
            } else {
                return true;
            }
        } else {
            return "验证码为空";
        }
    }
}

?>