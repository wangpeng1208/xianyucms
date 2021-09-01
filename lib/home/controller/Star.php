<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Cache;
use think\Db;

class Star extends Home
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
        if ($List && $List[0]['list_sid'] == 3) {
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
        config('params', array('id' => $this->Url['id'], 'dir' => $this->Url['dir'], 'sex' => $this->Url['sex'], 'zy' => $this->Url['zy'], 'area' => $this->Url['area'], 'letter' => $this->Url['letter'], 'order' => $this->Url['order'], 'p' => "xianyupage"));
        $List = list_search(F('_data/list'), 'list_id=' . $this->Url['id']);
        if ($List && $List[0]['list_sid'] == 3) {
            $channel = $this->Lable_List($this->Url, $List[0]);
            $channel['thisurl'] = "";
            $channel['thisurl'] = str_replace('xianyupage', $this->Url['page'], xianyu_url($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action(), config('params')));
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

        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        if ($array_detail) {
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            return view('/' . $array_detail['read']['star_skin_detail']);
        } else {
            abort(404, '页面不存在');
        }
    }

    public function ajax()
    {
        if ($this->request->instance()->isAjax()) {

            if ($this->Url['pinyin']) {
                $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
            }
            $array_detail = $this->get_cache_detail($this->Url['id']);
            if ($array_detail) {
                $this->assign($array_detail['show']);
                $this->assign($array_detail['read']);
                return view('/' . $array_detail['read']['star_skin_detail'] . "_ajax");
            } else {
                abort(404, '页面不存在');
            }
        }
    }

    public function work()
    {
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        if ($this->Url['vcid']) {
            $this->Url['vdir'] = getlist($this->Url['vcid'], 'list_id', 'list_dir');
        } else {
            $this->Url['vcid'] = getlist($this->Url['vdir'], 'list_dir', 'list_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'vcid' => $this->Url['vcid'], 'vdir' => $this->Url['vdir'], 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('param', $this->Url);
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('thisurl', str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/star/work', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => $this->Url['page']))));
            return view('/' . $array_detail['read']['star_skin_detail'] . '_work');
        } else {
            abort(404, '页面不存在');
        }
    }

    public function info()
    {
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('thisurl', str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/star/info', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => $this->Url['page']))));
            return view('/' . $array_detail['read']['star_skin_detail'] . '_info');
        } else {
            abort(404, '页面不存在');
        }
    }

    public function hz()
    {
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('param', $this->Url);
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('thisurl', str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/star/hz', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => $this->Url['page']))));
            return view('/' . $array_detail['read']['star_skin_detail'] . '_hz');
        } else {
            abort(404, '页面不存在');
        }
    }

    public function role()
    {
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('param', $this->Url);
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('thisurl', str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/star/role', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => $this->Url['page']))));
            return view('/' . $array_detail['read']['star_skin_detail'] . '_role');
        } else {
            abort(404, '页面不存在');
        }
    }

    public function news()
    {
        if ($this->Url['pinyin']) {
            $this->Url['id'] = get_star_info($this->Url['pinyin'], 'star_letters', 'star_id');
        }
        $array_detail = $this->get_cache_detail($this->Url['id']);
        config('params', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => "xianyupage"));
        if ($array_detail) {
            $this->assign('param', $this->Url);
            $this->assign('page', $this->Url['page']);
            $this->assign($array_detail['show']);
            $this->assign($array_detail['read']);
            $this->assign('thisurl', str_replace('xianyupage', $this->Url['page'], xianyu_data_url('home/star/news', array('id' => $array_detail['read']['star_id'], 'pinyin' => $array_detail['read']['star_letters'], 'cid' => $array_detail['read']['star_cid'], 'dir' => getlistname($array_detail['read']['star_cid'], 'list_dir'), 'p' => $this->Url['page']))));
            return view('/' . $array_detail['read']['star_skin_detail'] . '_news');
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
        if (config('data_cache_star')) {
            $array_detail = Cache::get('data_cache_star' . $id);
            if ($array_detail) {
                return $array_detail;
            }
        }
        //未中缓存则从数据库读取
        $where = array();
        $where['star_id'] = $id;
        $where['star_status'] = array('eq', 1);
        $array = $array = Db::name('star')->where($where)->find();
        if (!empty($array)) {
            //解析标签
            $array_detail = $this->Lable_Star_Read($array);
            if (config('data_cache_star')) {
                Cache::tag('model_star')->set('data_cache_star_' . $id, $array_detail, intval(config('data_cache_star')));
            }
            return $array_detail;
        }
        return false;
    }
}