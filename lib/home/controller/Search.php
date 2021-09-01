<?php

namespace app\home\controller;

use app\common\controller\Home;
use think\Db;

class Search extends Home
{
    public function index()
    {
        $mode = list_search(F('_data/modellist'), 'id=' . $this->Url['sid']);
        $JumpUrl = param_jump($this->Url);
        config('params', array('wd' => $this->Url['wd'], 'p' => "xianyupage"));
        $channel = $this->Lable_Search($this->Url);
        $this->assign($this->Url);
        $this->assign($channel);
        if ($this->request->isAjax() && !empty($this->Url['sid'])) {
            $array_search['search_skin'] = 'search_' . $mode[0]['name'] . '_ajax';
            return view('/' . $array_search['search_skin']);
        } else {
            return view('/' . $channel['search_skin']);
        }
    }

    //搜索页变量定义
    public function Lable_Search($param)
    {
        if ($param['sid']) {
            $mode = list_search(F('_data/modellist'), 'id=' . $param['sid']);
            $array_search = array();
            $array_search['search_modesid'] = $mode[0]['id'];
            $array_search['search_modename'] = $mode[0]['name'];
            $array_search['search_modetitle'] = $mode[0]['title'];
        }
        $array_search['search_actor'] = $param['actor'];
        $array_search['search_director'] = $param['director'];
        $array_search['search_area'] = $param['area'];
        $array_search['search_langaue'] = $param['langaue'];
        $array_search['search_year'] = $param['year'];
        $array_search['search_wd'] = $param['wd'];
        $array_search['search_name'] = $param['name'];
        $array_search['search_title'] = $param['title'];
        $array_search['search_page'] = !empty($param['page']) ? $param['page'] : 1;
        $array_search['search_letter'] = $param['letter'];
        $array_search['search_order'] = $param['order'];
        $array_search['search_skin'] = !empty($param['sid']) ? 'search_' . $mode[0]['name'] : "search";
        return $array_search;
    }

    // 联想搜索
    public function vod()
    {
        $wd = htmlspecialchars(input('q/s', ''));
        $limit = !empty($this->Url['limit']) ? $this->Url['limit'] : '10';
        $where['vod_name|vod_letters|vod_aliases'] = array('like', $wd . '%');
        $where["vod_status"] = 1;
        $rs = Db::name('vod');
        $data = $rs->field('vod_id,vod_cid,vod_pic,vod_pic,vod_gold,vod_area,vod_content,vod_continu,vod_actor,vod_title,vod_language,vod_filmtime,vod_director,vod_year,vod_name,vod_title,vod_letters,vod_addtime')->where($where)->limit($limit)->order('vod_addtime desc')->select();
        $count = $rs->where($where)->count('vod_id');
        if ($count) {
            foreach ($data as $key => $val) {
                $list['data'][$key]['vod_name'] = $val['vod_name'];
                $list['data'][$key]['vod_title'] = $val['vod_title'];
                $list['data'][$key]['vod_url'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
            }
            return json(['data' => $list['data'], 'info' => 'ok', 'status' => 1]);
        } else {
            return json(['data' => $data, 'info' => 'ok', 'status' => 0]);
        }
    }

    // 联想搜索
    public function ajax()
    {

        $limit = !empty($this->Url['limit']) ? $this->Url['limit'] : '10';
        $where['vod_name|vod_letters|vod_aliases'] = array('like', $this->Url['wd'] . '%');
        $rs = Db::name('vod');
        $data = $rs->field('vod_id,vod_cid,vod_pic,vod_pic,vod_gold,vod_area,vod_content,vod_continu,vod_actor,vod_title,vod_language,vod_filmtime,vod_director,vod_year,vod_name,vod_title,vod_letters,vod_addtime')->where($where)->limit($limit)->order('vod_addtime desc')->select();
        $count = $rs->where($where)->count('vod_id');
        if ($count) {
            foreach ($data as $key => $val) {
                $data[$key]['list_id'] = $val['vod_cid'];
                $data[$key]['list_name'] = getlistname($val['vod_cid'], 'list_name');
                $data[$key]['list_url'] = getlistname($val['vod_cid'], 'list_url');
                $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
                $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
            }
            return jsonp($data);
        }
    }
}