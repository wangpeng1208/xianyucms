<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;

class News extends Home
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
        if ($List && $List[0]['list_sid'] == 2) {
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
        $array_detail = $this->get_cache_detail($this->Url['id'], $this->Url);
        $this->Url['page'] = !empty($this->Url['page']) ? $this->Url['page'] : 1;
        if ($array_detail) {
            $this->assign($array_detail['read']);
            if ($array_detail['read']['images_slide']) {
                $this->assign($array_detail['read']['images_slide'][$this->Url['page']]);
            }
            if ($array_detail['read']['news_pages']) {
                $this->assign($array_detail['read']['news_pages'][$this->Url['page']]);
            }
            $this->assign($array_detail['show']);
            return view('/' . $array_detail['read']['news_skin_detail']);
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
        if (config('data_cache_news')) {
            $array_detail = Cache::get('data_cache_news_' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['news_id'] = $id;
        $where['news_status'] = array('eq', 1);
        $data = model('news')->where($where)->relation('newsrel,tag')->find();

        if (!empty($data)) {
            $array = $data->toArray();
            //解析标签
            $array_detail = $this->Lable_News_Read($array);
            if (config('data_cache_news')) {
                Cache::tag('model_news')->set('data_cache_news_' . $id, $array_detail, intval(config('data_cache_news')));
            }
            return $array_detail;
        }
        return false;
    }
}