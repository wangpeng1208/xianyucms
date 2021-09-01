<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

/**
 * Class Vod
 * 将本站作为采集库
 * Time： 2021-08-30
 * api Url：?s=api-collect-json-pwd-sdf89ef9875
 * @package app\api\controller
 */
class Collect extends Controller
{
    public function json()
    {
        $param = input('');
        if ($param['pwd'] !== 'sdf89ef9875') {
            return $this->error('密码错误');
        }
        $where['list_skin_detail'] = 'vod_detail';
        $where['list_status'] = 1;
        $list_arr = DB::name('list')->where($where)->field('list_id,list_name')->select();
        foreach ($list_arr as $key => $value) {
            $arr['list'][$key]['list_id'] = $value['list_id'];
            $arr['list'][$key]['list_name'] = $value['list_name'];
        }
        $page_limit = 10;
        if ($param['cid']) {
            $where2['vod_cid'] = $param['cid'];
        }
        if ($param['vodids']) {
            $where2['vod_id'] = ['in', $param['vodids']];
        }
        if ($param['wd']) {
            $where2['vod_name'] = ['like', '%' . $param['wd'] . '%'];
        }
        if ($param['h']) {
            $where2['vod_addtime'] = ['gt', time() - $param['h'] * 60 * 60];
        }
        if ($param['p']) {
            $p = $param['p'];
        } else {
            $p = 1;
        }
        $where2['vod_status'] = 1;
        $vod_list = DB::name('vod')->where($where2)->paginate($page_limit, false, ['page' => $p]);
        $vod_list_count = DB::name('vod')->where($where2)->count();

        if ($param['action'] == 'ids' || $param['action'] == 'all' || $param['action'] == 'day') {
            foreach ($vod_list as $key => $value) {
                $arr['data'][$key]['list_id'] = $this->get_list($value['vod_cid'])['list_id'];
                $arr['data'][$key]['list_name'] = $this->get_list($value['vod_cid'])['list_name'];
                $arr['data'][$key]['list_pid'] = $this->get_list($value['vod_cid'])['list_pid'];
                $arr['data'][$key]['vod_tid'] = $value['vod_cid'];
                $arr['data'][$key]['vod_cid'] = $value['vod_cid'];
                $arr['data'][$key]['vod_id'] = $value['vod_id'];
                $arr['data'][$key]['vod_name'] = $value['vod_name'];
                $arr['data'][$key]['vod_title'] = $value['vod_title'];
                $arr['data'][$key]['vod_play'] = $value['vod_play'];
                $arr['data'][$key]['vod_addtime'] = date('Y-m-d H:i:s', $value['vod_addtime']);
                $arr['data'][$key]['vod_pic'] = $value['vod_pic'];

                $list_name = $this->get_list($value['vod_cid'])['list_name'];

                $list_name = mb_substr($list_name, 0, 2);
                $vod_keywords_arr = explode(',', $list_name . ',' . $value['vod_keywords']);
                $vod_keywords_arr = array_unique($vod_keywords_arr);
                $vod_keywords = implode(',', $vod_keywords_arr);
                $arr['data'][$key]['vod_keywords'] = $vod_keywords;
                // echo $vod_keywords.PHP_EOL;
                // $arr['data'][$key]['vod_keywords'] = $value['vod_keywords'] ;
                $arr['data'][$key]['vod_total'] = $value['vod_total'];
                $arr['data'][$key]['vod_filmtime'] = $value['vod_filmtime'];
                $arr['data'][$key]['vod_tv'] = $value['vod_tv'];
                $arr['data'][$key]['vod_diantai'] = $value['vod_diantai'];
                // $arr['data'][$key]['vod_aliases'] = $value['vod_aliases'] ;
                $arr['data'][$key]['vod_language'] = $value['vod_language'];
                $arr['data'][$key]['vod_area'] = $value['vod_area'];
                $arr['data'][$key]['vod_year'] = $value['vod_year'];
                $arr['data'][$key]['vod_continu'] = $value['vod_continu'];
                $arr['data'][$key]['vod_status'] = $value['vod_status'];
                $arr['data'][$key]['vod_doubanid'] = $value['vod_doubanid'];
                $arr['data'][$key]['vod_actor'] = $value['vod_actor'];
                $arr['data'][$key]['vod_director'] = $value['vod_director'];
                $arr['data'][$key]['vod_content'] = $value['vod_content'];
                $arr['data'][$key]['vod_inputer'] = $value['vod_inputer'];
                $arr['data'][$key]['vod_reurl'] = $value['vod_reurl'];
                $arr['data'][$key]['vod_title'] = $value['vod_title'];
                $arr['data'][$key]['vod_play'] = $value['vod_play'];
                $arr['data'][$key]['vod_url'] = $value['vod_url'];
                $arr['data'][$key]['vod_title'] = $value['vod_title'];
                $arr['data'][$key]['vod_gold'] = $value['vod_gold'];
                $arr['data'][$key]['vod_length'] = $value['vod_length'];
                //可以继续添加自己以前扩展的字段
                $arr['data'][$key]['vod_dping'] = $value['vod_dping'];
                $arr['data'][$key]['vod_aname'] = $value['vod_aname'];
                $arr['data'][$key]['vod_ename'] = $value['vod_ename'];
            }

        } else {
            foreach ($vod_list as $key => $value) {
                $arr['data'][$key]['list_id'] = $this->get_list($value['vod_cid'])['list_id'];
                $arr['data'][$key]['list_name'] = $this->get_list($value['vod_cid'])['list_name'];
                $arr['data'][$key]['list_pid'] = $this->get_list($value['vod_cid'])['list_pid'];
                $arr['data'][$key]['vod_cid'] = $value['vod_cid'];
                $arr['data'][$key]['vod_id'] = $value['vod_id'];
                $arr['data'][$key]['vod_name'] = $value['vod_name'];
                $arr['data'][$key]['vod_title'] = $value['vod_title'];
                $arr['data'][$key]['vod_play'] = $value['vod_play'];
                $arr['data'][$key]['vod_addtime'] = date('Y-m-d H:i:s', $value['vod_addtime']);
            }
        }
        $arr['page']['pageindex'] = $p;
        $arr['page']['pagecount'] = round($vod_list_count / $page_limit);
        $arr['page']['pagesize'] = $page_limit;
        $arr['page']['recordcount'] = $vod_list_count;
        return json($arr);
    }
    /*
     * 通过视频id查询相关栏目id和名称
     */
    public function get_list($vod_cid)
    {
        $list = DB::name('list')->where('list_id', $vod_cid)->field('list_id,list_name,list_pid')->find();
        return $list;
    }

}
