<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;
use think\Db;

class Role extends Home
{
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
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            return view('/' . $array_detail['read']['role_skin']);
        } else {
            abort(404, '页面不存在');
        }
    }

    public function ajax()
    {
        if ($this->request->isAjax()) {
            $array_detail = $this->get_cache_detail($this->Url['id']);
            if ($array_detail) {
                $this->assign($array_detail['show']);
                $this->assign($array_detail['read']);
                return view('/' . $array_detail['read']['role_skin'] . "_ajax");
            } else {
                abort(404, '页面不存在');
            }
        }
    }

// 从数据库获取数据
    private function get_cache_detail($id)
    {
        if (!$id) {
            return false;
        }
        //优先读取缓存数据
        if (config('data_cache_role')) {
            $array_detail = Cache::get('data_cache_role_' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['role_id'] = $id;
        $where['role_status'] = array('eq', 1);
        $array = Db::name('role')->field('r.*,st.*,story_id,story_cid,story_status,actor_id,actor_cid,actor_status,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_play,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters')->alias('r')->join('actor a', 'a.actor_vid = r.role_vid', 'LEFT')->join('vod v', 'v.vod_id = r.role_vid  and v.vod_status=1', 'LEFT')->join('story s', 's.story_vid = r.role_vid  and s.story_status=1', 'LEFT')->join('star st', 'st.star_name = r.role_star and st.star_status=1', 'LEFT')->where($where)->find();
        if ($array) {
            //解析标签
            $array_detail = $this->Lable_Role_Read($array);
            //print_r($array_detail) ;
            if (config('data_cache_actor')) {
                Cache::tag('model_role')->set('data_cache_role_' . $id, $array_detail, intval(config('data_cache_role')));
            }
            return $array_detail;
        }
        return false;
    }
}