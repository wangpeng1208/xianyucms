<?php

namespace app\common\controller;

use think\Controller;

class All extends Controller
{
    protected $config = null;
    /*
     * $this->config['root'] 根目录
     * */
    public function _initialize()
    {
        // 控制器初始化
        parent::_initialize();
        $this->config = $this->label();
//        halt($this->config);
        $this->assign($this->config);
        // 注册推广id 任意链接上加上?fid=推广人id即可进行推广
        $fid = input('fid/d');
        if(isset($fid) && $fid>0){
            session('userpid', $fid);
        }
    }

    //全局标签定义
    public function label()
    {
        $array = array();
        $array['domain'] = strtolower($this->request->domain());
        $array['model'] = strtolower($this->request->module());
        $array['controller'] = strtolower($this->request->controller());
        $array['action'] = strtolower($this->request->action());
        $array['allurl'] = $array['model'] . '/' . $array['controller'] . '/' . $array['action'];
        $array['root'] = config('site_path');
        $array['tpl'] = $array['root'] . str_replace('./', '', config('template.view_base') . 'home/' . config('default_theme')) . '/';
        $array['gb_url'] = xianyu_url('home/gb/show');
        $array['sitetitle'] = config('site_title');
        $array['sitename'] = config('site_name');
        $array['siteurl'] = config('site_url');
        $array['m_siteurl'] = config('mobile_url');
        $array['url'] = $_SERVER["REQUEST_URI"];
        $array['pc_url'] = $array['siteurl'] . $_SERVER["REQUEST_URI"];
        $array['m_url'] = $array['m_siteurl'] . $_SERVER["REQUEST_URI"];
        $array['sitepath'] = config('site_path');
        $array['hotkey'] = hot_keywords(config('site_hotkeywords'));
        $array['keywords'] = config('site_keyword');
        $array['description'] = config('site_description');
        $array['email'] = config('site_email');
        $array['copyright'] = config('site_copyright');
        $array['tongji'] = config('site_tongji');
        $array['video_copyright'] = config('copyright_txt');
        $array['userconfig'] = F('_data/userconfig_cache');
        $array['snsconfig'] = get_addon_config('snslogin');
        $array['payconfig'] = F('_data/payconfig_cache');
        $array['list_mod'] = F('_data/modellist');
        if ($array['list_mod']) {
            foreach ($array['list_mod'] as $k => $v) {
                $array['list_' . $v['name']] = F('_data/list' . $v['name']);
            }
        }
        $array['list_emots'] = F('_data/list_emots');
        $array['list_area'] = explode(',', config('play_area'));
        $array['list_language'] = explode(',', config('play_language'));
        $array['list_year'] = explode(',', config('play_year'));
        $array['list_letter'] = (range('A', 'Z'));
        $array['list_order'] = array('addtime', 'hits', 'gold');
        $array['list_area_all'] = F('_data/area');
        $array['tpl_id'] = config('tpl_id');
        $array['public'] = PUBLIC_PATH;
        $array['public_dir'] = config('site_path') . PUBLIC_PATH . 'tpl/';
        if (config('site_cssjsurl')) {
            $array['apicss'] = config('site_cssjsurl');
        } else {
            $array['apicss'] = config('site_path') . PUBLIC_PATH . 'tpl/';
        }
        //老板变量兼容
        $array['mov_id'] = $array['tpl_id'][1];
        $array['tv_id'] = $array['tpl_id'][2];
        $array['dm_id'] = $array['tpl_id'][3];
        $array['zy_id'] = $array['tpl_id'][4];
        $array['wei_id'] = $array['tpl_id'][5];
        $array['newss_id'] = $array['tpl_id'][6];
        $array['list_slide'] = F('_data/list_slide');
        $array['list_link'] = F('_data/list_link');
        $array['list_menu'] = F('_data/listtree');
        $array['s_area'] = $array['list_area'];
        $array['s_language'] = $array['list_language'];
        $array['s_year'] = $array['list_year'];
        $array['s_picm'] = array('1', '2');
        $array['s_letter'] = $array['list_letter'];
        $array['s_order'] = $array['list_order'];
        return $array;
    }


}

?>