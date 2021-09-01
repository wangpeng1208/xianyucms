<?php
namespace app\user\model;
use think\Model;

class Playlog extends Model
{
    public function getplaylog($where, $page)
    {
        $list = db('playlog')
            ->alias('p')
            ->field('p.*,vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl')
            ->join('vod v', 'v.vod_id=p.log_vid', 'LEFT')
            ->where($where)->order('log_addtime desc')
            ->paginate($page['limit'], false, ['page' => $page['currentpage']]);
        //print_r($list) ;
        return $list;
    }

    public function find()
    {
        if (cmf_is_user_login()) {
            $list = db('playlog')
                ->alias('p')
                ->field('p.*,vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl')
                ->join('vod v', 'v.vod_id=p.log_vid', 'LEFT')
                ->where(['log_uid' => cmf_get_current_user_id()])->order('log_addtime desc')
                ->limit(10)
                ->select();
        } else {
            $cookie = cookie('xianyu_playlog');
            if ($cookie) {
                $array_column = array_column($cookie, 'log_addtime');
                array_multisort($array_column, SORT_DESC, $cookie);//更具播放时间排序cookie数组
                $cookie = array_slice(array_values($cookie), 0, 10);//重置新数组KEY并截取10
                $vid = implode(',', array_column($cookie, 'log_vid'));
                $order = 'field(vod_id,' . $vid . ')';
                $vodlist = db('vod')
                    ->field('vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl')
                    ->where('vod_id', 'in', $vid)
                    ->orderRaw($order)
                    ->select();
                foreach ($vodlist as $key => $value) {
                    $list[] = $value + $cookie[$key];
                }

            }
        }
        if ($list) {
            foreach ($list as $key => $value) {
                $data[$key]['log_id'] = $value['log_id'];
                $data[$key]['vod_id'] = $value['vod_id'];
                $data[$key]['vod_name'] = $value['vod_name'];
                $data[$key]['play_name'] = $value['log_urlname'];
                $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $value['log_vid'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir'), 'jumpurl' => $value['vod_jumpurl']));
                $data[$key]['vod_palyurl'] = xianyu_play_url(array('id' => $value['log_vid'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir'), 'sid' => $value['log_sid'], 'pid' => $value['log_pid']));
                if ($value['log_pid'] < $value['log_maxnum']) {
                    $data[$key]['vod_playnext'] = xianyu_play_url(array('id' => $value['log_vid'], 'pinyin' => $value['vod_letters'], 'cid' => $value['vod_cid'], 'dir' => getlistname($value['vod_cid'], 'list_dir'), 'sid' => $value['log_sid'], 'pid' => $value['log_pid'] + 1));
                }
                $data[$key]['play_addtime'] = $value['log_addtime'];
            }
            return $data;
        } else {
            return false;
        }

    }

    public function add($post)
    {
        if (cmf_is_user_login()) {
            $data = array();
            $data['log_vid'] = $post['log_vid'];
            $data['log_sid'] = $post['log_sid'];
            $data['log_pid'] = $post['log_pid'];
            $data['log_urlname'] = $post['log_urlname'];
            $data['log_maxnum'] = $post['log_maxnum'];
            $data['log_uid'] = cmf_is_user_login();
            $data['log_addtime'] = time();
            $result = $this->save($data, ['log_vid' => $data['log_vid'], 'log_uid' => $data['log_uid']]);
            if (!$result) {
                $result = $this->insert($data);
            }
        } else {
            $info = array();
            $info['id_' . $post['log_vid']]['log_vid'] = $post['log_vid'];
            $info['id_' . $post['log_vid']]['log_sid'] = $post['log_sid'];
            $info['id_' . $post['log_vid']]['log_pid'] = $post['log_pid'];
            $info['id_' . $post['log_vid']]['log_urlname'] = $post['log_urlname'];
            $info['id_' . $post['log_vid']]['log_maxnum'] = $post['log_maxnum'];
            $info['id_' . $post['log_vid']]['log_addtime'] = time();
            $cookie_old = cookie('xianyu_playlog');
            if ($cookie_old) {
                $datas = array_merge($cookie_old, $info);
            } else {
                $datas = $info;
            }
            $cookie = array_slice($datas, 0, 10);
            cookie('xianyu_playlog', $cookie, 2592000);
        }
    }

    public function del($post)
    {
        if (cmf_is_user_login() && $post['log_id']) {
            $result = $this->where('log_id', $post['log_id'])->delete();
            if ($result) {
                return ['msg' => '删除成功', 'rcode' => '1'];
            }
        } else {
            $cookie_old = cookie('xianyu_playlog');
            unset($cookie_old['id_' . $post['log_vid']]);
            cookie('xianyu_playlog', $cookie_old, 2592000);
            return ['msg' => '删除成功', 'rcode' => '1'];
        }
    }

}