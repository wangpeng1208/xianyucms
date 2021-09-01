<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;
use think\Db;

class Actor extends Home
{
//列表
    public function show()
    {
        if ($this->Url['listdir']) {
            $this->Url['id'] = getlist($this->Url['listdir'], 'list_dir', 'list_id');
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

    public function read()
    {
        if (!empty($this->Url['pinyin']) && empty($this->Url['vid'])) {
            $this->Url['vid'] = get_vod_info($this->Url['pinyin'], 'vod_letters', 'vod_id');
            $this->Url['id'] = get_actor_info($this->Url['vid'], 'actor_vid', 'actor_id');
        } elseif (!empty($this->Url['vid'])) {
            $this->Url['id'] = get_actor_info($this->Url['vid'], 'actor_vid', 'actor_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            return view('/' . $array_detail['read']['actor_skin']);
        } else {
            abort(404, '页面不存在');
        }
    }

// 从数据库获取数据
    private function get_cache_detail($id)
    {
        if (!$id) {
            return false;
        }
        //优先读取缓存数据
        if (config('data_cache_actor')) {
            $array_detail = Cache::get('data_cache_actor_' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['actor_id'] = $id;
        $where['actor_status'] = array('eq', 1);
        $array = Db::name('actor')->field('a.*,story_id,story_cid,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_play,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid and v.vod_status=1', 'LEFT')->join('story s', 's.story_vid = a.actor_vid and s.story_status=1', 'LEFT')->where($where)->find();
        if (!empty($array)) {
            //解析标签
            $array_detail = $this->Lable_Actor_Read($array);
            if (config('data_cache_actor')) {
                Cache::tag('model_actor')->set('data_cache_actor_' . $id, $array_detail, intval(config('data_cache_actor')));
            }
            return $array_detail;
        }
        return false;
    }
}