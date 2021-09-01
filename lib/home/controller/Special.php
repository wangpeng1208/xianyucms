<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;
use think\Db;

class Special extends Home
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
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_special_info($this->Url['pinyin'], 'special_letters', 'special_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('list_vod', $array_detail['list_vod']);
            $this->assign('list_news', $array_detail['list_news']);
            $this->assign('list_star', $array_detail['list_star']);
            return view('/' . $array_detail['read']['special_skin']);
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
        if (config('data_cache_special')) {
            $array_detail = Cache::get('data_cache_special' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['special_id'] = $id;
        $where['special_status'] = array('eq', 1);
        $array = Db::name('special')->where($where)->find();
        if (!empty($array)) {
            //解析标签
            $array_detail = $this->Lable_Special_Read($array);
            if (config('data_cache_special')) {
                Cache::tag('model_special')->set('data_cache_special_' . $id, $array_detail, intval(config('data_cache_special')));
            }
            return $array_detail;
        }
        return false;
    }
}