<?php

namespace app\common\model;

use app\common\library\Insert;
use think\Model;

class Collect extends Model
{
    public function vod_insert($vod, $mustup, $status = "")
    {
        $replace_play = config('replace_play');
        //播放组名替换
        if ($replace_play) {
            $vod['vod_play'] = $this->xml_play_replace($vod['vod_play']);
        }
        if (empty($vod['vod_name'])) {
            $return['msg'] = "影片名称为空,跳过";
            return $return;
        }
        $empty_playurl = config('empty_playurl');
        if (empty($empty_playurl) && empty($vod['vod_url'])) {
            $return['msg'] = "播放地址为空,跳过";
            return $return;
        }
        if (!$vod['vod_cid']) {
            $return['msg'] = "未匹配到对应栏目分类,跳过";
            return $return;
        }
        if (config('caiji_datatime')) {
            $vod['vod_addtime'] = !empty($vod['vod_addtime']) ? strtotime($vod['vod_addtime']) : time();
        } else {
            $vod['vod_addtime'] = time();
        }
        //过滤为空的播放名与播放地址
        $vod['vod_play'] = trim(implode('$$$', array_filter(explode('$$$', $vod['vod_play']))), '$$$');
        $vod['vod_url'] = trim(implode('$$$', array_filter(explode('$$$', $vod['vod_url']))), '$$$');
        //数据处理
        $vod['vod_mcid'] = model('Mcat')->get_mcid($vod['vod_cid'], $vod['vod_keywords']);
        //开启采集获取关键词
        if (config('keywords_opencai') && !empty($vod['vod_name'])) {
            //$vod['vod_skeywords']=seokeywords($vod['vod_name'],config('keywords_opencai'));
        }
        /*if ($vod['vod_filmtime']){
            if(is_int($vod['vod_filmtime']) && strlen($vod['vod_filmtime'])>=10){
            $vod['vod_filmtime']=$vod['vod_filmtime'];
            }else{
            $vod['vod_filmtime']=strtotime($vod['vod_filmtime'].' 0:0:0');
            }
        }*/
        // 要查询检查的字段
        //$field = 'vod_id,vod_cid,vod_name,vod_title,vod_actor,vod_continu,vod_isend,vod_total,vod_inputer,vod_play,vod_url';
        // 查询来源相同的数据
        $array = db('urls')->alias('u')->join('vod v', 'v.vod_id=u.urls_id', 'RIGHT')->where('urls_reurl', 'eq', $vod['vod_reurl'])->find();
        if ($array) {
            return $this->vod_update($vod, $array, $mustup);
        }
        $where = array();
        $where['vod_name'] = array('eq', $vod['vod_name']);
        $where['vod_cid'] = array('eq', $vod['vod_cid']);
        $array = db('vod')->where($where)->select();
        if ($array) {
            foreach ($array as $key => $val) {
                //无主演 或 演员完全相等时 更新该影片
                if (empty($vod['vod_actor']) || empty($val['vod_actor']) || ($val['vod_actor'] == $vod['vod_actor'])) {
                    return $this->vod_update($vod, $val, $mustup);
                }
                //有相同演员时更新该影片
                $arr_actor_1 = explode(',', format_vodactor($vod['vod_actor']));
                $arr_actor_2 = explode(',', format_vodactor($val['vod_actor']));
                if (array_intersect($arr_actor_1, $arr_actor_2)) {
                    return $this->vod_update($vod, $val, $mustup);
                }
            }
        }
        unset($where, $array);
        $list_pid = getlistname($vod['vod_cid'], 'list_pid');
        if ($list_pid) {
            $where = array();
            $where['vod_name'] = array('eq', $vod['vod_name']);
            $where['vod_cid'] = getlistsqlin($list_pid);
            $array = db('vod')->where($where)->select();
            if ($array) {
                foreach ($array as $key => $val) {
                    //无主演 或 演员完全相等时 更新该影片
                    if (empty($vod['vod_actor']) || empty($val['vod_actor']) || ($val['vod_actor'] == $vod['vod_actor'])) {
                        return $this->vod_update($vod, $val, $mustup);
                    }
                    //有相同演员时更新该影片
                    $arr_actor_1 = explode(',', format_vodactor($vod['vod_actor']));
                    $arr_actor_2 = explode(',', format_vodactor($val['vod_actor']));
                    if (array_intersect($arr_actor_1, $arr_actor_2)) {
                        return $this->vod_update($vod, $val, $mustup);
                    }
                }
            }
        }
        unset($where, $array);
        $where = array();
        $where['vod_name'] = array('eq', $vod['vod_name']);
        $array = db('vod')->where($where)->select();
        if ($array) {
            foreach ($array as $key => $val) {
                //无主演 或 演员完全相等时 更新该影片
                if (empty($vod['vod_actor']) || empty($val['vod_actor']) || ($val['vod_actor'] == $vod['vod_actor'])) {
                    return $this->vod_update($vod, $val, $mustup);
                }
                //有相同演员时更新该影片
                $arr_actor_1 = explode(',', format_vodactor($vod['vod_actor']));
                $arr_actor_2 = explode(',', format_vodactor($val['vod_actor']));
                if (array_intersect($arr_actor_1, $arr_actor_2)) {
                    return $this->vod_update($vod, $val, $mustup);
                }
            }
        }
        //相似条件判断
        if (config('player_collect_name')) {
            $length = ceil(strlen($vod['vod_name']) / 3) - intval(config('player_collect_name'));
            if ($length > 1) {
                $where = array();
                $where['vod_cid'] = array('eq', $vod['vod_cid']);
                $where['vod_name|vod_aliases'] = array('like', msubstr($vod['vod_name'], 0, $length) . '%');
                $array = db('vod')->where($where)->order('vod_id desc')->find();
                if ($array) {
                    // 主演完全相同 则检查是否需要更新
                    if (!empty($array['vod_actor']) && !empty($vod['vod_actor'])) {
                        $arr_actor_1 = explode(',', format_vodactor($vod['vod_actor']));
                        $arr_actor_2 = explode(',', format_vodactor($array['vod_actor']));
                        if (!array_diff($arr_actor_1, $arr_actor_2) && !array_diff($arr_actor_2, $arr_actor_1)) {
                            //若差集为空演员完全相同更新
                            return $this->vod_update($vod, $array, $mustup);
                        }
                        $oldcount = count($arr_actor_1);
                        $newcount = count($arr_actor_2);
                        $min = min($oldcount, $newcount);
                        if (array_intersect($arr_actor_1, $arr_actor_2) && count(array_intersect($arr_actor_1, $arr_actor_2)) >= $min) {
                            //判断演员交集并且大于其中最小的统计
                            return $this->vod_update($vod, $array, $mustup);
                        }
                    }
                    // 不是同一资源库 则标识为相似待审核
                    if (!in_array($vod['vod_inputer'], $array)) {
                        //$vod['vod_status'] = -1;
                    }
                }
            }
        }
        //是否锁定该资源
        if ($status) {
            $return['msg'] = "资源锁定,不添加新数据,跳过";
            return $return;
        }
        unset($vod['vod_id']);//销毁采集中的视频ID变量
        if (empty($vod['vod_hits'])) {
            $vod['vod_hits'] = mt_rand(0, config('rand_hits'));
        }
        if (empty($vod['vod_up'])) {
            $vod['vod_up'] = mt_rand(0, config('rand_updown'));
        }
        if (empty($vod['vod_down'])) {
            $vod['vod_down'] = mt_rand(0, config('rand_updown'));
        }
        if (empty($vod['vod_gold'])) {
            $vod['vod_gold'] = mt_rand(0, config('rand_gold'));
        }
        if (empty($vod['vod_golder'])) {
            $vod['vod_golder'] = mt_rand(0, config('rand_golder'));
        }
        $play_list_db = $this->vod_array_url($this->vod_url_array($vod['vod_play'], $vod['vod_url']));
        $vod['vod_play'] = $play_list_db['vod_play'];
        $vod['vod_url'] = $play_list_db['vod_url'];
        $vod['vod_stars'] = 1;
        $vod['vod_pic'] = model('Img')->down_load(trim($vod['vod_pic']), 'vod');
        $vod['vod_letter'] = getletter($vod['vod_name']);
        $vod['vod_letters'] = getletters(trim($vod['vod_name']), 1);
        $id = db('vod')->insertGetId($vod);
        //关联写入
        if ($id) {
            //关联写入剧情
            if (!empty($vod['story_content'])) {
                $storyadd = new Insert;
                $storyadd->story_add($vod, $id);
            }
            //关联写入TAG
            if ($vod["vod_keywords"]) {
                model('Tag')->tag_update($id, $vod["vod_keywords"], 1);
            }
            //关联写入类型
            if ($vod["vod_mcid"]) {
                model('Mcid')->mcid_update($id, $vod["vod_mcid"], 1);
            }
            //关联写入属性
            if ($vod["vod_prty"]) {
                model('Prty')->prty_update($id, $vod["vod_prty"], 1);
            }
            //关联写入周期
            if ($vod["vod_weekday"]) {
                model('Weekday')->weekday_update($id, $vod["vod_weekday"], 1);
            }
            //关联写入电台
            if ($vod["vod_diantai"]) {
                model('Vodtv')->tv_update($id, $vod["vod_diantai"], 1);
            }
            //关联入库到演员视图表
            if ($vod['vod_actor']) {
                model('Actors')->actors_update($id, $vod['vod_actor'], 1);
            }
            //管理入库到演员表视图
            if ($vod['vod_director']) {
                model('Actors')->actors_update($id, $vod['vod_director'], 2);
            }
            //管理入库到来源视图方便记录多个来源的查询
            if ($vod['vod_reurl']) {
                model('Urls')->urls_update($id, $vod['vod_reurl']);
            }
            $return['code'] = $id;
            $readurl = xianyu_data_url('home/vod/read', array('id' => $id, 'pinyin' => $vod['vod_letters'], 'cid' => $vod['vod_cid'], 'dir' => getlistname($vod['vod_cid'], 'list_dir'), 'jumpurl' => $vod['vod_jumpurl']), true, true);
            $return['msg'] = "视频添加成功" . baidutui($readurl, 'add', 2);
            return $return;
        }
        $return['msg'] = "视频添加失败";
        return $return;
    }

    //视频更新
    public function vod_update($vod, $vod_old, $mustup = false)
    {
        //关联写入剧情
        if (!empty($vod['story_content'])) {
            $storyadd = new Insert;
            $storyadd->story_add($vod, $vod_old['vod_id']);
        }
        $img = model('Img');
        // 检测是否站长手动锁定更新
        if ('xianyu' == $vod_old['vod_inputer']) {
            return ['msg' => "站长设置不更新,跳过"];;
        }
        if (config('caiji_play')) {
            // 检测播放地址是否变化
            $play_list_old = $this->vod_url_array($vod_old['vod_play'], $vod_old['vod_url']);
            $play_list_new = $this->vod_url_array($vod['vod_play'], $vod['vod_url']);
            $play_list_merge = array_merge($play_list_old, $play_list_new);//合并新旧地址
            $play_list_db = $this->vod_array_url($play_list_merge);//还原入库字段
            if ($vod_old['vod_url'] == $play_list_db['vod_url']) {
                return ['msg' => "数据未变化"];
            }
            $edit['vod_update_info'] = "更新播放地址成功";
            $edit['vod_url'] = $play_list_db['vod_url'];
            $edit['vod_play'] = $play_list_db['vod_play'];
        } else {
            $edit['vod_update_info'] = "<font color='#e74c3c'>不更新与增加播放地址</font>";
        }
        $edit['vod_id'] = $vod_old['vod_id'];
        $edit['vod_name'] = $vod['vod_name'];
        $edit['vod_inputer'] = $vod['vod_inputer'];
        $edit['vod_reurl'] = implode(',', array_unique(explode(',', $vod['vod_reurl'] . ',' . $vod_old['vod_reurl'])));
        $edit['vod_addtime'] = $vod['vod_addtime'];
        //原数据中不存在新的资料中存在的字段就更新
        if ($vod['vod_total'] && empty($vod_old['vod_total'])) {
            $edit['vod_total'] = $vod['vod_total'];
        }
        if ($vod['vod_area'] && empty($vod_old['vod_area'])) {
            $edit['vod_area'] = $vod['vod_area'];
        }
        if ($vod['vod_year'] && empty($vod_old['vod_year'])) {
            $edit['vod_year'] = $vod['vod_year'];
        }
        if ($vod['vod_aliases'] && empty($vod_old['vod_aliases'])) {
            $edit['vod_aliases'] = $vod['vod_aliases'];
        }
        if ($vod['vod_doubanid'] && empty($vod_old['vod_doubanid'])) {
            $edit['vod_doubanid'] = $vod['vod_doubanid'];
        }
        if ($vod['vod_language'] && empty($vod_old['vod_language'])) {
            $edit['vod_language'] = $vod['vod_language'];
        }
        if ($vod['vod_isend'] && empty($vod_old['vod_isend'])) {
            $edit['vod_isend'] = $vod['vod_isend'];
        }
        if ($vod['vod_diantai'] && empty($vod_old['vod_diantai'])) {
            $edit['vod_diantai'] = $vod['vod_diantai'];
            model('Vodtv')->tv_update($vod_old['vod_id'], $vod["vod_diantai"], 1);
        }
        if ($vod['vod_isfilm'] && empty($vod_old['vod_isfilm'])) {
            $edit['vod_isfilm'] = $vod['vod_isfilm'];
        }
        if ($vod['vod_weekday'] && empty($vod_old['vod_weekday'])) {
            $edit['vod_weekday'] = $vod['vod_weekday'];
        }
        if ($vod['vod_tvcont'] && empty($vod_old['vod_tvcont'])) {
            $edit['vod_tvcont'] = $vod['vod_tvcont'];
        }
        if ($vod['vod_director'] && empty($vod_old['vod_director'])) {
            $edit['vod_director'] = $vod['vod_director'];
            model('Actors')->actors_update($vod_old['vod_id'], $vod['vod_director'], 2);
        }
        if ($vod['vod_actor'] && empty($vod_old['vod_actor'])) {
            $edit['vod_actor'] = $vod['vod_actor'];
            model('Actors')->actors_update($vod_old['vod_id'], $vod['vod_actor'], 1);
        }
        if ($vod['vod_filmtime'] && empty($vod_old['vod_filmtime'])) {
            $edit['vod_filmtime'] = $vod['vod_filmtime'];
        }
        if ($vod['vod_mcid'] && empty($vod_old['vod_mcid'])) {
            $edit['vod_mcid'] = $vod['vod_mcid'];
            model('Mcid')->mcid_update($vod_old['vod_id'], $vod["vod_mcid"], 1);
        }
        if ($vod['vod_content'] && empty($vod_old['vod_content'])) {
            $edit['vod_content'] = $vod['vod_content'];
        }
        //是否强制更新远程图片
        if (config('caiji_pic') && strstr($vod_old['vod_pic'], "http://")) {
            $edit['vod_pic'] = $img->down_load(trim($vod['vod_pic']), 'vod');
        }
        // 是否为强制更新资料图片等参数
        if ($mustup) {
            $edit['vod_pic'] = $img->down_load(trim($vod['vod_pic']), 'vod');
            if ($vod['vod_area']) {
                $edit['vod_area'] = $vod['vod_area'];
            }
            if ($vod['vod_year']) {
                $edit['vod_year'] = $vod['vod_year'];
            }
            if ($vod['vod_aliases']) {
                $edit['vod_aliases'] = $vod['vod_aliases'];
            }
            if ($vod['vod_doubanid']) {
                $edit['vod_doubanid'] = $vod['vod_doubanid'];
            }
            if ($vod['vod_language']) {
                $edit['vod_language'] = $vod['vod_language'];
            }
            if ($vod['vod_total']) {
                $edit['vod_total'] = $vod['vod_total'];
            }
            if ($vod['vod_isend']) {
                $edit['vod_isend'] = $vod['vod_isend'];
            }
            if ($vod['vod_diantai']) {
                $edit['vod_diantai'] = $vod['vod_diantai'];
            }
            if ($vod['vod_isfilm']) {
                $edit['vod_isfilm'] = $vod['vod_isfilm'];
            }
            if ($vod['vod_weekday']) {
                $edit['vod_weekday'] = $vod['vod_weekday'];
            }
            if ($vod['vod_tvcont']) {
                $edit['vod_tvcont'] = $vod['vod_tvcont'];
            }
            if ($vod['vod_director']) {
                $edit['vod_director'] = trim($vod['vod_director'], ',');
                model('Actors')->actors_update($vod_old['vod_id'], $vod['vod_director'], 2);
            }
            if ($vod['vod_actor']) {
                $edit['vod_actor'] = trim($vod['vod_actor'], ',');
                model('Actors')->actors_update($vod_old['vod_id'], $vod['vod_actor'], 1);
            }
            if ($vod['vod_filmtime']) {
                $edit['vod_filmtime'] = $vod['vod_filmtime'];
            }
            if ($vod['vod_mcid']) {
                $edit['vod_mcid'] = $vod['vod_mcid'];
                model('Mcid')->mcid_update($vod_old['vod_id'], $vod["vod_mcid"], 1);
            }
        }
        //需要更新的字段
        if ($vod['vod_title']) {
            $edit['vod_title'] = $vod['vod_title'];
        }
        if ($vod['vod_continu'] > $vod_old['vod_continu'] || $vod['vod_continu'] != $vod_old['vod_continu']) {
            $edit['vod_continu'] = $vod['vod_continu'];
        }
        //更新来源地址
        model('Urls')->urls_update($vod_old['vod_id'], $edit['vod_reurl']);
        $result = db('vod')->update($edit);
        $readurl = xianyu_data_url('home/vod/read', array('id' => $vod_old['vod_id'], 'pinyin' => $vod_old['vod_letters'], 'cid' => $vod_old['vod_cid'], 'dir' => getlistname($vod_old['vod_cid'], 'list_dir'), 'jumpurl' => $vod_old['vod_jumpurl']), true, true);
        if ($result !== false) {
            $return['code'] = $vod_old['vod_id'];
            $return['msg'] = $edit['vod_update_info'] . baidutui($readurl, 'update', 2);
            return $return;
        } else {
            $return['msg'] = "数据处理失败,跳过";
            return $return;
        }

    }

    // 格式化播放地址
    private function vod_url_array($vod_play, $vod_url)
    {
        $old_play = explode('$$$', $vod_play);
        $old_url = explode('$$$', $vod_url);
        $old_array = array();
        $sid = array();
        foreach ($old_play as $key => $value) {
            $url_one = explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $old_url[$key]));
            foreach ($url_one as $key_son => $value_son) {
                $old_array[$value . '-' . intval($sid[$value]) . '-' . $key_son] = $value_son;
            }
            $sid[$value] = +1;
        }
        return $old_array;
    }

    //重组播放地址
    private function vod_array_url($play_list_merge)
    {
        foreach ($play_list_merge as $key => $value) {
            list($play_name, $play_sid, $play_pid) = explode('-', $key);
            $play[$play_name . $play_sid] = $play_name;
            $array_url[$play_name . $play_sid][$play_pid] = $value;
        }

        //还原单组播放地址
        foreach ($array_url as $key => $value) {
            $url[$key] = implode(chr(13), array_unique($value));
        }
        //过滤本地不存在的播放组
        if (config('contrast_play')) {
            $array_player = $this->xml_status_play();
            foreach ($play as $key => $value) {
                if (!$array_player[$value]) {
                    unset($play[$key]);
                    unset($url[$key]);
                }
            }
        }
        return array('vod_play' => implode('$$$', $play), 'vod_url' => implode('$$$', $url));
    }

    function xml_status_play()
    {
        $array_allplay = F('_data/statusplay');
        foreach ($array_allplay as $key => $value) {
            $array_newurl[$value] = $value;
        }
        return $array_newurl;
    }

    // 重生成某一组的播放地址 返回新的地址(string)
    public function xml_update_urlone($vodurlold, $vodurlnew)
    {
        $arrayold = explode(chr(13), trim($vodurlold));
        $arraynew = explode(chr(13), trim($vodurlnew));
        foreach ($arraynew as $key => $value) {
            unset($arrayold[$key]);
        }
        if ($arrayold) {
            return implode(chr(13), array_merge($arrayold, $arraynew));
        } else {
            return implode(chr(13), $arraynew);
        }
    }

    //播放组名替换
    public function xml_play_replace($play)
    {
        if (config('replace_play')) {
            $replace_play = config('replace_play');
            $replace_key = array_keys($replace_play);
            $replace_values = array_values($replace_play);
            $play = str_replace($replace_key, $replace_values, $play);
        }
        return $play;
    }

    //过滤本地不存在的播放组
    function xml_all_play($play, $url)
    {
        $array_allplay = F('_data/statusplay');
        $array_play = explode('$$$', $play);
        $array_url = explode('$$$', $url);
        $array_newplay = array_intersect($array_play, $array_allplay);
        if ($array_newplay) {
            foreach ($array_newplay as $key => $value) {
                $array_newurl[] = $array_url[$key];
            }
            $new['vod_play'] = implode('$$$', $array_newplay);
            $new['vod_url'] = implode('$$$', $array_newurl);
            return $new;
        } else {
            return false;
        }
    }

    //组合播放名与播放地址为二维数组
    public function xml_play_group($play, $url)
    {
        if ($play && $url) {
            foreach ($play as $key => $value) {
                $arrayold[$value][] = $url[$key];
            }
            return array_filter($arrayold);
        } else {
            return false;
        }
    }

    //重新组合播放组
    public function xml_play_reform($newarray)
    {
        if ($newarray) {
            foreach ($newarray as $key => $value) {
                $new['vod_play'][] = strtolower($key);
                $new['vod_url'][] = $value;
            }
            return $new;
        } else {
            return false;
        }
    }

    //去除重复的播放地址
    public function removal_vod_url($vod_url)
    {
        if ($vod_url) {
            $array_vod_url = explode('$$$', $vod_url);
            foreach ($array_vod_url as $key => $value) {
                $arraynew[$key] = implode(chr(13), array_unique(explode(chr(13), trim($value))));
            }
            $newurl = implode('$$$', $arraynew);
            return $newurl;
        } else {
            return false;
        }
    }


}