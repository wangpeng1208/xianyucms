<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;
use think\Db;

class Tv extends Home
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
        if ($List && $List[0]['list_sid'] == 7) {
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
        $this->Url['p'] = input('p/d', '');
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_tv_info($this->Url['pinyin'], 'tv_letters', 'tv_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            if ($this->request->isAjax()) {
                $array_detail['tv_skin_detail_ajax'] = $array_detail['read']['tv_skin_detail'] . "_ajax";
                return view('/' . $array_detail['tv_skin_detail_ajax']);
            } else {
                return view('/' . $array_detail['read']['tv_skin_detail']);
            }
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
        if (config('data_cache_tv')) {
            $array_detail = Cache::get('data_cache_tv' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['tv_id'] = $id;
        $where['tv_status'] = array('eq', 1);
        $array = $array = Db::name('tv')->where($where)->find();
        if (!empty($array)) {
            //解析标签
            $array_detail = $this->Lable_Tv_Read($array);
            if (config('data_cache_tv')) {
                Cache::tag('model_tv')->set('data_cache_tv_' . $id, $array_detail, intval(config('data_cache_tv')));
            }
            return $array_detail;
        }
        return false;
    }
}