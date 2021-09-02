<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

use mailer\lib\Config as MailerConfig;
use mailer\lib\Mailer;
use think\Cache;
use think\captcha\Captcha;
use think\Db;

think\Url::root('/index.php?s=');
/*-------------------------------------------------老函数兼容开始------------------------------------------------------------------*/
function getlistdir($value)
{
    return getlistname($value, 'list_dir');
}

function ff_story_url($value, $id)
{
    $array['tpl_id'] = config('tpl_id');
    return getlistname($array['tpl_id'][8], 'list_url');
}

function ff_star_url($value)
{
    $array['tpl_id'] = config('tpl_id');
    return getlistname($array['tpl_id'][7], 'list_url');
}

function ff_actor_list_url($value)
{
    $array['tpl_id'] = config('tpl_id');
    return getlistname($array['tpl_id'][10], 'list_url');
}

function ff_mcat_name($value, $id, $k)
{
    return mcat_name($value, $id, $k);
}

function ff_xml_vodactor($value)
{
    return format_vodname($value);
}

/*-------------------------------------------------统栏目相关函数开始------------------------------------------------------------------*/
/*** 把返回的数据集转换成Tree
 * +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**----------------------------------------------------------
 * 在数据列表中搜索
 * +----------------------------------------------------------
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 * @return array
 */
function list_search($list, $condition)
{
    if (is_string($condition))
        parse_str($condition, $condition);
    // 返回的结果集合
    $resultSet = array();
    foreach ($list as $key => $data) {
        $find = true;// fixed
        foreach ($condition as $field => $value) {
            if (isset($data[$field])) {
                if (0 === strpos($value, '/')) {
                    $find = $find && preg_match($value, $data[$field]);// fixed
                } else {
                    $find = $find && $data[$field] == $value;// fixed
                }
            } else {
                $find = false;// fixed
            }
        }
        if ($find)
            $resultSet[] =   &$list[$key];
    }
    return $resultSet;
}

//通过栏目ID返回其它值按数组方式
function getlistall($cid)
{
    if (!empty($cid)) {
        $tree = list_search(F('_data/listtree'), 'list_id=' . $cid);
        if (!empty($tree[0]['son'])) {
            foreach ($tree[0]['son'] as $val) {
                $param[] = $val;
            }
            return $param;
        } else {
            return false;
        }
    }
}

//通过栏目参数获取对应参数
function getlist($str, $seach, $type)
{
    if (!empty($str)) {
        $arr = list_search(F('_data/list'), $seach . '=' . $str);
        if (empty($arr)) {
            return false;
        } else {
            return $arr[0][$type];
        }
    }
}

//通过栏目ID获取栏目PID
function getlistpid($cid)
{
    if (!empty($cid)) {
        $arr = list_search(F('_data/list'), 'list_id=' . $cid);
        if (is_array($arr) && !empty($arr[0]['list_pid'])) {
            return $arr[0]['list_pid'];
        } else {
            return $cid;
        }
    }
}

//通过栏目ID获取对应的栏目名称/别名等
function getlistname($cid, $type = 'list_name')
{
    if (!empty($cid)) {
        $arr = list_search(F('_data/list'), 'list_id=' . $cid);

        if (is_array($arr) && !empty($arr)) {
            return $arr[0][$type];
        } else {
            return $cid;
        }
    }
}

//通过栏目ID返回小分类其它值按数组方式
function getlistmcat($cid)
{
    if (!empty($cid)) {
        $pid = getlistname($cid, 'list_pid');
        $cid = !empty($pid) ? $pid : $cid;
        $sid = getlistname($cid, 'list_sid');
        $tree = list_search(F('_data/mcat'), 'list_id=' . $cid);
        if (!empty($tree[0]['son'])) {
            foreach ($tree[0]['son'] as $val) {
                $param[] = $val;
            }
            return $param;
        } elseif ($sid == 1) {
            $catlist = model('Mcat')->list_cat($cid);
            return $catlist;
        }
    }
}

function get_mcat_ename($ename, $cid, $type = 'm_cid')
{
    if (!empty($cid)) {
        $pid = getlistname($cid, 'list_pid');
        $cid = !empty($pid) ? $pid : $cid;
        $data = list_search(F('_data/mcid'), array('m_ename' => $ename, 'm_list_id' => $cid));
        return $data[0][$type];
    } else {
        return false;
    }
}

function get_mcat_id($id, $type = 'm_ename')
{
    if (!empty($id)) {
        $data = list_search(F('_data/mcid'), array('m_cid' => $id));
        return $data[0][$type];
    } else {
        return false;
    }
}

//通过SID获取模型名
function get_area($name, $seach = 'name', $type = 'ename')
{
    $data = list_search(F('_data/area'), $seach . '=' . $name);
    if (empty($data)) {
        return false;
    } else {
        return $data[0][$type];
    }
}

function mcat_title($str, $cid = "", $k = "")
{
    if (empty($str)) {
        return "";
    }
    $mcids = explode(',', $str);
    $html = '';
    foreach ($mcids as $v) {
        $arr = list_search(F('_data/mcid'), 'm_cid=' . $v);
        $html .= $arr[0]['m_name'];
    }
    return $html;
}

function mcat_name($str, $cid = "", $k = "")
{
    if (empty($str)) {
        return "未知";
    }
    $mcids = explode(',', $str);
    $html = '';
    foreach ($mcids as $key => $v) {
        if (!empty($k) && $key == $k) {
            break;
        }
        $arr = list_search(F('_data/mcid'), 'm_cid=' . $v);
        if (is_array($arr) && !empty($arr)) {
            $html .= $arr[0]['m_name'] . " ";
        }
    }
    return $html;
}

function mcat_url($str, $cid, $k = "")
{
    if (empty($str)) {
        return "";
    }
    $mcids = array_filter(explode(',', $str));
    $html = '';
    foreach ($mcids as $key => $v) {
        if (!empty($k) && $key == $k) {
            break;
        }
        $url = xianyu_type_url('home/vod/type', array('id' => $cid, 'dir' => getlistname($cid, 'list_dir'), 'mcid' => $v, 'mename' => get_mcat_id($v), 'area' => "", 'earea' => "", 'year' => "", 'letter' => "", 'order' => ""));
        $arr = list_search(F('_data/mcid'), 'm_cid=' . $v);
        $html .= "<a href='" . $url . "' target='_blank'>{$arr[0]['m_name']}</a> ";
    }
    return $html;
}

//通过SID获取模型名
function getmodeid($sid, $type)
{
    $mode = list_search(F('_data/modellist'), 'id=' . $sid);
    if (empty($mode)) {
        return false;
    } else {
        return $mode[0][$type];
    }
}

//通过视频字段取对应的相关信息
function get_vod_info($var, $seach = 'vod_id', $type = 'vod_name')
{
    $arr = Db::name('vod')->where($seach, $var)->value($type);
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过视频字段取对应的所有信息
function get_vod_find($var, $seach = 'vod_id', $find = "*")
{
    $arr = Db::name('vod')->field($find)->where($seach, $var)->find();
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过视频字段取对应的相关信息
function get_news_info($var, $seach = 'news_id', $type = 'vod_name')
{
    $arr = Db::name('news')->where($seach, $var)->value($type);
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过视频字段取对应的所有信息
function get_news_find($var, $seach = 'news_id', $find = "*")
{
    $arr = Db::name('news')->field($find)->where($seach, $var)->find();
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过明星ID获取对应的相关信息
function get_star_info($var, $seach = 'star_id', $type = 'star_name')
{
    $arr = Db::name('star')->where($seach, $var)->value($type);
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过明星ID获取对应的相关信息
function get_star_find($var, $seach = 'star_id', $find = "*")
{
    $arr = Db::name('star')->field($find)->where($seach, $var)->find();
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过视频ID获取演员表信息
function get_actor_info($var, $seach = 'actor_id', $type = 'actor_vid')
{
    $info = Db::name('actor')->where($seach, $var)->value($type);
    if ($info) {
        return $info;
    } else {
        return false;
    }

}

//通过演员表相关数据获取对应的相关信息
function get_actor_find($var, $seach = 'actor_id', $find = "*")
{
    $arr = Db::name('actor')->field($find)->where($seach, $var)->find();
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过相关字段获取剧情相关字段
function get_story_info($var, $seach = 'story_id', $type = 'story_vid')
{
    $arr = Db::name('story')->where($seach, $var)->value($type);
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过剧情相关数据获取对应的相关信息
function get_story_find($var, $seach = 'story_id', $find = "*")
{
    $arr = Db::name('story')->field($find)->where($seach, $var)->find();
    if (!empty($arr)) {
        return $arr;
    } else {
        return false;
    }
}

//通过角色ID获取角色相关信息
function get_role_info($var, $seach = 'role_id', $type = 'role_vid')
{
    $info = Db::name('role')->where($seach, $var)->value($type);
    if ($info) {
        return $info;
    } else {
        return false;
    }

}

//通过电台信息获取电台相关信息
function get_tv_info($var, $seach = 'tv_id', $type = 'tv_letters')
{
    $info = Db::name('tv')->where($seach, $var)->value($type);
    if ($info) {
        return $info;
    } else {
        return false;
    }

}

//通过专题信息获取专题相关信息
function get_special_info($var, $seach = 'special_id', $type = 'special_letters')
{
    $info = Db::name('special')->where($seach, $var)->value($type);
    if ($info) {
        return $info;
    } else {
        return false;
    }

}

//通过会员ID获取会员相关信息
function get_user_info($var, $seach = 'userid', $type = 'nickname')
{
    $info = Db::name('user')->where($seach, $var)->value($type);
    if ($info) {
        return $info;
    } else {
        return false;
    }

}

//通过内容ID和模型ID获取获取内容信息
function getinfo($vid = "", $sid = "", $field = "")
{
    $name = getmodeid($sid, 'name');
    if ($name) {
        $info = Db::name($name)->field($field)->where(array($name . '_id' => $vid))->find();
    }
    if ($info) {
        return $info;
    } else {
        return "";
    }
}

// 查询当前栏目是否存在下级分类
function getlistson($pid)
{
    $tree = list_search(F('_data/listtree'), 'list_id=' . $pid);
    if (!empty($tree[0]['son'])) {
        return false;
    } else {
        return true;
    }
}

//去重后的模板栏目ID参数 $cids = array(1,2,3,...)
function getlistarr_tag($cids)
{
    foreach ($cids as $key => $value) {
        if (getlistson($value)) {
            $cid .= ',' . $value;
        } else {
            $cidin = getlistsqlin($value);
            $cid .= ',' . $cidin[1];
        }
    }
    $cidarr = explode(',', $cid);
    unset($cidarr[0]);
    $cidarr = array_unique($cidarr);
    return $cidarr;
}

//生成栏目sql查询语句范围
function getlistsqlin($cid)
{
    $tree = list_search(F('_data/listtree'), 'list_id=' . $cid);
    if (!empty($tree[0]['son'])) {
        foreach ($tree[0]['son'] as $val) {
            if ($val['list_id'] == 69) {

            } else {

                $arr['vod_cid'][] = $val['list_id'];
            }

        }

        $channel = $cid . ',' . implode(',', $arr['vod_cid']);
        return array('IN', '' . $channel . '');
    }
    if ($cid == 69) {
        $cid = 690;
    }
    return $cid;
}

function gettypelistcid($vid, $sid)
{
    $cid = get_vod_info($vid, 'vod_id', 'vod_cid');
    $list_name = getlistname($cid, 'list_name_big');
    $mode = list_search(F('_data/modellist'), 'id=' . $sid);
    if ($sid == 4) {
        $list_name = getlistname($cid);
    }
    $cidarray = config($mode[0]['name'] . "_cidarray");
    foreach ($cidarray as $key => $val) {
        $cidarry = explode("=", $val);
        $keys[$cidarry[0]] = $cidarry[1];
    }
    if (!empty($cidarray[0]) && $cidarray[0] == "all") {
        $where['list_sid'] = $sid;
        $where['list_pid'] = 0;
        $id = db('list')->where($where)->value('list_id');
    } elseif (!empty($cidarray[0])) {
        if ($keys[$cid]) {
            $id = $keys[$cid];
        } else {
            $where['list_sid'] = $sid;
            $where['list_pid'] = 0;
            $id = Db::name('list')->where($where)->value('list_id');
        }
    } else {
        $where['list_sid'] = $sid;
        $where['list_name'] = $list_name;
        $id = Db::name('list')->where($where)->value('list_id');
        if (empty($id)) {
            unset($where);
            $where['list_sid'] = $sid;
            $where['list_pid'] = 0;
            $pid = Db::name('list')->where($where)->value('list_id');
            $data['list_skin'] = $mode[0]['name'] . "_list";
            $data['list_skin_detail'] = $mode[0]['name'] . "_detail";
            $data['list_sid'] = $sid;
            $data['list_status'] = 1;
            if (empty($pid)) {
                $data['list_name'] = $mode[0]['title'];
                $data['list_pid'] = 0;
                $data['list_dir'] = getletters(trim($data['list_name']), 'list');
                $pid = Db::name('list')->insertGetId($data);
            }
            $data['list_name'] = $list_name;
            $data['list_pid'] = $pid;
            $data['list_dir'] = getletters(trim($data['list_name']), 'list');
            $id = Db::name('list')->insertGetId($data);
        }
    }
    if ($id) {
        return $id;
    } else {
        return $pid;
    }
}

// 获取栏目相关统计
function getcount($cid, $sid, $time = "")
{
    $where = array();
    $mode = list_search(F('_data/modellist'), 'id=' . $sid);
    if (!empty($mode[0]['name']) && $mode[0]['id'] != 11 && $mode[0]['id'] != 9 && $mode[0]['id'] != 8) {
        if ($time == 999) {
            $where[$mode[0]['name'] . '_cid'] = getlistsqlin($cid);
            $where[$mode[0]['name'] . '_status'] = 1;
            $where[$mode[0]['name'] . '_addtime'] = array('gt', getxtime(1));
            $count = db($mode[0]['name'])->where($where)->cache($mode[0]['name'] . '_daycount_' . $cid, 3600, 'count')->count();
        } elseif ($time == 1) {
            $where[$mode[0]['name'] . '_cid'] = array('gt', 0);
            $count = db($mode[0]['name'])->where($where)->cache($mode[0]['name'] . '_countid', 3600, 'count')->count();
        } else {
            $where[$mode[0]['name'] . '_cid'] = getlistsqlin($cid);
            $where[$mode[0]['name'] . '_status'] = 1;
            $count = db($mode[0]['name'])->where($where)->cache($mode[0]['name'] . '_countid_' . $cid, 3600, 'count')->count();
        }
        return $count + 0;
    }
}

// 返回下一篇或上一篇的内容的信息
function detail_array($module = 'vod', $type = 'next', $id, $cid, $field = 'vod_id,vod_cid,vod_status,vod_name,vod_ename,vod_jumpurl')
{
    //优先读取缓存数据
    $cache_key = 'cache_' . $module . '_' . $type . '_' . $cid . '_' . $id;
    if (config('data_cache_' . $module)) {
        $array = cache($cache_key);
        if ($array) {
            return $array;
        }
    }
    $where = array();
    $where[$module . '_cid'] = $cid;
    $where[$module . '_status'] = 1;
    if ($type == 'next') {
        $where[$module . '_id'] = array('gt', $id);
        $order = $module . '_id asc';
    } else {
        $where[$module . '_id'] = array('lt', $id);
        $order = $module . '_id desc';
    }
    if ($module != 'vod') {
        $field = str_replace('vod_', $module . '_', $field);
    }
    $array = Db::name((ucfirst($module)))->field($field)->where($where)->limit(1)->order($order)->find();
    // 是否写入缓存
    if (config('data_cache_' . $module)) {
        cache($cache_key, $array, intval(config('data_cache_' . $module)));
    }
    return $array;
}

function getnewtime($type = 'Y-m-d H:i:s', $time, $color = 'red', $new = '', $miao = '86400')
{
    if (empty($new)) {
        $site_cssjsurl = config('site_cssjsurl');
        $public = !empty($site_cssjsurl) ? $site_cssjsurl : config('site_path') . PUBLIC_PATH . 'tpl/';
        $new = "<img src='" . $public . "admin/new.gif'>";
    }
    if ((time() - $time) > $miao) {
        return date($type, $time);
    } else {
        return '<i><font color="' . $color . '">' . date($type, $time) . '</font></i>' . $new . '';
    }
}

//关键字高亮颜色
function get_hilight_ex($string, $keyword, $arr = 'span', $color = "black")
{
    return str_replace($keyword, '<' . $arr . ' color="' . $color . '">' . $keyword . '</' . $arr . '>', $string);
}

//获取当前地址栏URL
function get_http_url()
{
    $http = $_SERVER['HTTPS'] !== 'on' ? 'http' : 'https';
    return htmlspecialchars($http . "://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
}

/*-------------------------------------------------赞片CMS视频相关函数------------------------------------------------------------------*/
function getletters($name, $sid = 0, $pid = 0)
{
    $tree = list_search(F('_data/modellist'), 'id=' . $sid);
    $pinyin = new \com\Hzpy();
    $name = preg_replace("/\s|\:|(|\~|\`|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\-|\+|\=|\{|\}|\[|\]|\||\\|\:|\;|\"|\'|\<|\,|\>|\.|\?|\/)/is", "", $name);
    if (!empty($tree[0]['name']) || $sid == 'list' || $sid == 'mcat') {
        if ($sid == 'list') {
            $dbname = 'list';
            $wherename = 'list_dir';
        } elseif ($sid == 'mcat' && !empty($pid)) {
            $dbname = 'mcat';
            $wherename = 'm_ename';
        } else {
            $dbname = $tree[0]['name'];
            $wherename = $tree[0]['name'] . '_letters';
        }
        $rs = Db::name($dbname);
        $letter = $pinyin->pinyin(trim($name));
        $where[$wherename] = $letter;
        if ($sid == 'mcat' && !empty($pid)) {
            $where['m_list_id'] = $pid;
        }
        if ($rs->where($where)->count() > 0) {
            $letter = $pinyin->pinyin(trim($name));
            $where[$wherename] = $letter;
            if ($sid == 'mcat' && !empty($pid)) {
                $where['m_list_id'] = $pid;
            }
            $i = 1;
            while ($rs->where($where)->count() > 0) {
                $letter = $pinyin->pinyin(trim($name)) . $i;
                $where[$wherename] = $letter;
                if ($sid == 'mcat' && !empty($pid)) {
                    $where['m_list_id'] = $pid;
                }
                $i++;
            }
        }
        return $letter;
    } else {
        if ($name) {
            return $pinyin->pinyin(trim($name));
        } else {
            return false;
        }
    }
}

function getletter($s0)
{
    $s0 = preg_replace("/\s|\:|(|\~|\`|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\-|\+|\=|\{|\}|\[|\]|\||\\|\:|\;|\"|\'|\<|\,|\>|\.|\?|\/)/is", "", $s0);
    $firstchar_ord = ord(strtoupper($s0[0]));
    if (($firstchar_ord >= 65 and $firstchar_ord <= 91) or ($firstchar_ord >= 48 and $firstchar_ord <= 57)) return $s0[0];
    $s = iconv("UTF-8", "gb2312", $s0);
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    if ($asc >= -20319 and $asc <= -20284) return "A";
    if ($asc >= -20283 and $asc <= -19776) return "B";
    if ($asc >= -19775 and $asc <= -19219) return "C";
    if ($asc >= -19218 and $asc <= -18711) return "D";
    if ($asc >= -18710 and $asc <= -18527) return "E";
    if ($asc >= -18526 and $asc <= -18240) return "F";
    if ($asc >= -18239 and $asc <= -17923) return "G";
    if ($asc >= -17922 and $asc <= -17418) return "H";
    if ($asc >= -17417 and $asc <= -16475) return "J";
    if ($asc >= -16474 and $asc <= -16213) return "K";
    if ($asc >= -16212 and $asc <= -15641) return "L";
    if ($asc >= -15640 and $asc <= -15166) return "M";
    if ($asc >= -15165 and $asc <= -14923) return "N";
    if ($asc >= -14922 and $asc <= -14915) return "O";
    if ($asc >= -14914 and $asc <= -14631) return "P";
    if ($asc >= -14630 and $asc <= -14150) return "Q";
    if ($asc >= -14149 and $asc <= -14091) return "R";
    if ($asc >= -14090 and $asc <= -13319) return "S";
    if ($asc >= -13318 and $asc <= -12839) return "T";
    if ($asc >= -12838 and $asc <= -12557) return "W";
    if ($asc >= -12556 and $asc <= -11848) return "X";
    if ($asc >= -11847 and $asc <= -11056) return "Y";
    if ($asc >= -11055 and $asc <= -10247) return "Z";
    return 0;//null
}

// 获取标题颜色
function getcolor($str, $color)
{
    if (empty($color)) {
        return $str;
    } else {
        return '<font color="' . $color . '">' . $str . '</font>';
    }
}

//获得某天前的时间戳
function getxtime($day)
{
    $day = intval($day);
    return mktime(23, 59, 59, date("m"), date("d") - $day, date("y"));
}

// 播放地址3天内更新在最后一集增加NEW
function gettimevodnew($type = 'Y-m-d H:i:s', $time, $color = 'red', $new = '<span class="new"></span>')
{
    if ((time() - $time) > 86400) {
        return;
    } else {
        return '' . $new . '';
    }
}

//
function get_addons_state($name)
{
    $config = get_addon_info($name);
    if ($config) {
        return $config['state'];
    } else {
        return 0;
    }
}

function get_addons_config($name)
{
    $config = get_addon_config($name);
    if ($config) {
        return true;
    } else {
        return false;
    }
}

function get_addons_status($name)
{
    $config = get_addon_config($name);
    if ($config) {
        return !empty($config[$name . '_status']) ? $config[$name . '_status'] : false;
    } else {
        return false;
    }
}

function special_count($id, $sid = 1)
{
    if ($id) {
        $where = array();
        $where['topic_tid'] = $id;
        $where['topic_sid'] = $sid;
        return db('topic')->where($where)->count('topic_did');
    } else {
        return 0;
    }
}

/*-------------------------------------------------字符串处理开始------------------------------------------------------------------*/
// 去掉换行
function nr($str)
{
    $str = str_replace(array("<nr/>", "<rr/>"), array("\n", "\r"), $str);
    return trim($str);
}

// 格式化采集影片名称
function format_vodname($vodname)
{
    $vodname = str_replace(array('【', '】', '（', '）', '(', ')', '{', '}'), array('[', ']', '[', ']', '[', ']', '[', ']'), $vodname);
    $vodname = preg_replace('/\[([a-z][A-Z])\]|([a-z][A-Z])版/i', '', $vodname);
    $vodname = preg_replace('/TS清晰版|枪版|抢先版|HD|BD|TV|DVD|VCD|TS|\/版|\[\]/i', '', $vodname);
    return trim($vodname);
}

// 格式化采集影片主演
function format_vodactor($vodactor)
{
    return str_replace(',,', ',', str_replace(array('/', '，', '|', '、', ' ', '，', ',,'), ',', $vodactor));
}

//去掉连续空白
function nb($str)
{
    $find = ['~>\s+<~', '~>(\s+\n|\r)~'];
    $replace = ['><', '>'];
    $str = preg_replace($find, $replace, $str);
    return trim($str);
}

//字符串截取(同时去掉HTML与空白)
function msubstr($str, $start = 0, $length, $suffix = false)
{
    return xianyu_msubstr(preg_replace('/<[^>]+>/', '', preg_replace("/[\r\n\t ]{1,}/", ' ', nb($str))), $start, $length, 'utf-8', $suffix);
}

//输出安全的html
function h($text, $tags = null)
{
    $text = trim($text);
    //完全过滤注释
    $text = preg_replace('/<!--?.*-->/', '', $text);
    //完全过滤动态代码
    $text = preg_replace('/<\?|\?' . '>/', '', $text);
    //完全过滤js
    $text = preg_replace('/<script?.*\/script>/', '', $text);

    $text = str_replace('[', '&#091;', $text);
    $text = str_replace(']', '&#093;', $text);
    $text = str_replace('|', '&#124;', $text);
    //过滤换行符
    $text = preg_replace('/\r?\n/', '', $text);
    //br
    $text = preg_replace('/<br(\s\/)?' . '>/i', '[br]', $text);
    $text = preg_replace('/(\[br\]\s*){10,}/i', '[br]', $text);
    //过滤危险的属性，如：过滤on事件lang js
    while (preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1], $text);
    }
    while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
    }
    if (empty($tags)) {
        $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
    }
    //允许的HTML标签
    $text = preg_replace('/<(' . $tags . ')( [^><\[\]]*)>/i', '[\1\2]', $text);
    //过滤多余html
    $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i', '', $text);
    //过滤合法的html标签
    while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)) {
        $text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
    }
    //转换引号
    while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
    }
    //过滤错误的单个引号
    while (preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat)) {
        $text = str_replace($mat[0], str_replace($mat[1], '', $mat[0]), $text);
    }
    //转换其它所有不合法的 < >
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);
    $text = str_replace('"', '&quot;', $text);
    //反转换
    $text = str_replace('[', '<', $text);
    $text = str_replace(']', '>', $text);
    $text = str_replace('|', '"', $text);
    //过滤多余空格
    $text = str_replace('  ', ' ', $text);
    return $text;
}

function xianyu_msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $length_new = $length;
    for ($i = $start; $i < $length; $i++) {
        if (ord($match[0][$i]) > 0xa0) {
            //中文
        } else {
            $length_new++;
            $length_chi++;
        }
    }
    if ($length_chi < $length) {
        $length_new = $length + ($length_chi / 2);
    }
    $slice = join("", array_slice($match[0], $start, $length_new));
    if ($suffix && $slice != $str) {
        return $slice . "…";
    }
    return $slice;
}

//XSS漏洞过滤
function remove_xss($val)
{
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);
    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}

function post_curl($url, $data)
{
    $co = new \com\Curl();
    $data = $co->post($url, $data);
    return $data;
}

//写入文件
function write_file($l1, $l2 = '')
{
    $dir = dirname($l1);
    if (!is_dir($dir)) {
        mkdirss($dir);
    }
    return @file_put_contents($l1, $l2);
}

//递归创建文件
function mkdirss($dirs, $mode = 0777)
{
    if (!is_dir($dirs)) {
        mkdirss(dirname($dirs), $mode);
        return @mkdir($dirs, $mode);
    }
    return true;
}

// 数组保存到文件
function arr2file($filename, $arr = '')
{
    if (is_array($arr)) {
        $con = var_export($arr, true);
    } else {
        $con = $arr;
    }
    $con = "<?php\nreturn $con;\n?>";//\n!defined('IN_MP') && die();\nreturn $con;\n
    write_file($filename, $con);
}

/**
 * 删除文件夹
 * @param string $dirname
 * @return boolean
 */
function rmdirs($dirname)
{
    if (!is_dir($dirname))
        return false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    @rmdir($dirname);
    return true;
}

/**
 * 复制文件夹
 * @param string $source 源文件夹
 * @param string $dest 目标文件夹
 */
function copydirs($source, $dest)
{
    if (!is_dir($dest)) {
        mkdir($dest, 0755);
    }
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            $sontDir = $dest . DS . $iterator->getSubPathName();
            if (!is_dir($sontDir)) {
                mkdir($sontDir);
            }
        } else {
            copy($item, $dest . DS . $iterator->getSubPathName());
        }
    }
}

function deldir($path, $delDir = true)
{
    if (is_dir($path) == false) {
        return FALSE;
    }
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? deldir("$path/$item", $delDir) : @unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return @rmdir($path);
    } else {
        if (file_exists($path)) {
            return @unlink($path);
        } else {
            return FALSE;
        }
    }
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value = '', $path = "")
{
    if (!$path) {
        $path = RUNTIME_PATH . 'data/';
    }
    $file = new \com\File();
    static $_cache = array();
    $filename = $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            if (false !== strpos($name, '*')) {
                return false; // TODO
            } else {
                unset($_cache[$name]);
                return $file->unlink($filename, 'F');
            }
        } else {
            $file->put($filename, serialize($value), 'F');
            // 缓存数据
            $_cache[$name] = $value;
            return;
        }
    }
    // 获取缓存数据
    if (isset($_cache[$name]))
        return $_cache[$name];
    if ($file->has($filename, 'F')) {
        $value = unserialize($file->read($filename, 'F'));
        $_cache[$name] = $value;
    } else {
        $value = false;
    }
    return $value;
}

// 获取热门关键词
function hot_keywords($string)
{
    $url_html = F('_data/url_html_config');
    if (config('site_hotkeywords')) {
        if ($url_html['url_html']) {
            return '<script type="text/javascript" src="' . config('site_path') . 'runtime/js/hotkey.js" charset="utf-8"></script>';
        } else {
            $hotkeywords = F('_data/hotkeywords');
            return $hotkeywords;
        }
    } else {
        return "";
    }
}

// 获取广告调用地址
function getadsurl($str, $charset = "utf-8")
{
    $url = str_replace(array('//', '/./'), '/', config('site_path') . config('admin_ads_file') . '/' . $str . '.js');
    return '<script type="text/javascript" src="' . $url . '" charset="' . $charset . '"></script>';
}

// 获取与处理人气值
function gethits($sid, $type = 'hits', $array, $js = true)
{
    $url_html = F('_data/url_html_config');
    $mode = list_search(F('_data/modellist'), 'id=' . $sid);
    if (($url_html['url_html'] && $js) || $type == 'insert') {
        return '<span class="detail-hits" data-sid="' . $sid . '" data-id="' . $array[$mode[0]['name'] . '_id'] . '" data-type="' . $type . '"></span>';
    } else {
        return $array[$type];
    }
}

/**
 * +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * +----------------------------------------------------------
 * @return string
 * +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default:
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {
        //位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}

//TAG分词自动获取
function xianyu_tag_auto($title, $content)
{
    $data = xianyu_get_url('http://keyword.discuz.com/related_kw.html?ics=utf-8&ocs=utf-8&title=' . rawurlencode($title) . '&content=' . rawurlencode(msubstr($content, 0, 500)));
    if ($data) {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $data, $values, $index);
        xml_parser_free($parser);
        $kws = array();
        foreach ($values as $valuearray) {
            if ($valuearray['tag'] == 'kw') {
                if (strlen($valuearray['value']) > 3) {
                    $kws[] = trim($valuearray['value']);
                }
            } elseif ($valuearray['tag'] == 'ekw') {
                $kws[] = trim($valuearray['value']);
            }
        }
        return implode(',', $kws);
    }
    return false;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = true)
{
    return request()->ip($type, $adv);
}

// 采集内核
function xianyu_get_url($url, $timeout = 10, $referer = "")
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR:127.0.0.1"));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        $content = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($content && $status == 200) {
            return $content;
        }
    }
    $ctx = stream_context_create(array('http' => array('timeout' => $timeout)));
    $content = @file_get_contents($url, 0, $ctx);

    if (strlen($content) == 0) {
        return false;
    } elseif ($content) {
        return $content;
    }
    return false;
}

// 获取某图片的访问地址
function xianyu_img_url($file, $default = "")
{
    if (empty($file)) {
        $site_cssjsurl = config('site_cssjsurl');
        $public = !empty($site_cssjsurl) ? $site_cssjsurl : config('site_path') . PUBLIC_PATH . 'tpl/';
        return !empty($default) ? $default : $public . "admin/no.jpg";
    }
    if (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 2) == '//') {
        return $file;
    }
    $prefix = config('upload_http_prefix');
    if (!empty($prefix)) {
        return $prefix . $file;
    } else {
        return config('site_path') . config('upload_path') . '/' . $file;
    }
}

// 获取某图片缩略图访问地址
function xianyu_img_smallurl($file, $content = "", $number = 1)
{
    if (empty($file)) {
        $site_cssjsurl = config('site_cssjsurl');
        $public = !empty($site_cssjsurl) ? $site_cssjsurl : config('site_path') . PUBLIC_PATH . 'tpl/';
        return $public . "admin/no.jpg";
    }
    if (!$file) {
        return xianyu_img_url_preg($file, $content, $number);
    }
    if (substr($file, 0, 7) == 'http://' || substr($file, 0, 8) == 'https://' || substr($file, 0, 2) == '//') {
        return $file;
    }
    $prefix = config('upload_http_prefix');
    if (!empty($prefix)) {
        return $prefix . $file;
    } else {
        return config('site_path') . config('upload_path') . '-s/' . $file;
    }
}

//正则提取正文里指定的第几张图片地址
function xianyu_img_url_preg($file, $content, $number = 1)
{
    preg_match_all('/<img(.*?)src="(.*?)(?=")/si', $content, $imgarr);///(?<=img.src=").*?(?=")/si
    preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
    $countimg = count($imgarr);
    if ($number > $countimg) {
        $number = $countimg;
    }
    return $imgarr[0][($number - 1)];
}

//获取正文内容中的远程图片路径并返回
function xianyu_http_pic($contents)
{
    if ($contents == null) return false;
    preg_match_all('/<img(.*?)src="(.*?)(?=")/si', $contents, $imgarr);///(?<=img.src=").*?(?=")/si
    preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
    if (is_array($imgarr[0])) {
        return $imgarr[0]; //返回远程图片路径
    }
    return false;
}

function xianyu_news_img_array($content, $type = "")
{
    $prefix = config('upload_http_prefix');
    $upload_path = config('site_path') . config('upload_path') . '/';
    if ($content == null) return false;
    preg_match_all('/<img(.*?)src="(.*?)(?=")/si', $content, $imgarr);///(?<=img.src=").*?(?=")/si
    preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
    if (is_array($imgarr[0])) {
        $picarray = $imgarr[0];
    }
    $pathArr = array();
    foreach ($picarray as $key => $value) {
        if (substr($value, 0, 7) == 'http://' || substr($value, 0, 8) == 'https://' || substr($value, 0, 2) == '//') {
            if (!empty($prefix)) {
                $pathArr[] = str_replace($prefix, "", $value);    //开启远程附件替换远程地址替换为空
            } else {
                $pathArr[] = $value;
            }
        } else {
            if ($type == 1) {
                $pathArr[] = str_replace($upload_path, "", $value);
            } elseif (!empty($prefix)) {
                $pathArr[] = $prefix . $value;
            } else {
                $pathArr[] = $upload_path . $value;
            }
        }
    }
    if (is_array($pathArr)) {
        return str_ireplace($picarray, $pathArr, $content);
    }
    return $content;
}

function xianyu_news_images_array($content)
{
    $content = preg_replace('/<img(.*?)>/si', "", $content);
    return $content;
}

//保存内容图片
function xianyu_content_images($value, $sid)
{
    if (!empty($value)) {
        $content = xianyu_news_img_array($value, 1);
        if (config('upload_http') && !empty($content) || config('upload_http_news') && $sid == "news" && !empty($content)) {
            $img = model('Img');
            if (!!$path = xianyu_http_pic($content)) { //正文内的图片远程路径数组
                $savePath = $img->down_load($path, $sid, ''); //保存图片并获取本地保存绝对路径
                $contents1 = str_ireplace($path, $savePath, $content); //远程图片路径替换为本地绝对路径
                return $contents1;
            } else {
                return $value;
            }
        } else {
            return $content;
        }
    } else {
        return $value;
    }
}

// 提取内容中图片为缩略图
function xianyu_content_pic($content, $w = 0, $sid = "news")
{
    preg_match_all('/<img(.*?)src="(.*?)(?=")/si', $content, $imgarr);///(?<=img.src=").*?(?=")/si
    preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
    $countimg = count($imgarr[0]);
    //只有一张图片提取第一张
    if ($countimg == 1) {
        return $imgarr[0][0];
    }
    if (!$w) {
        return $imgarr[0][0];
    }
    foreach ($imgarr[0] as $key => $val) {
        //图片为外链图片
        if (substr($val, 0, 7) == 'http://' || substr($val, 0, 8) == 'https://' || substr($val, 0, 2) == '//') {
            $val = $val;
        } else {
            $val = './' . config('upload_path') . '/' . $val;
        }
        $pic = getimagesize($val);
        if ($w == 1 && $pic[0] > $pic[1]) {
            return $val;
        } elseif ($w == 2 && $pic[0] < $pic[1]) {
            return $val;
        }
    }
    return $imgarr[0][0];
}

//正则提取正文里图片为数组
function xianyu_img_url_array($content)
{
    //import('phpQuery.phpQuery', EXTEND_PATH);
    \phpQuery::newDocumentHTML($content);
    $pq = pq(null);
    $images = $pq->find("img");
    $imagesData = [];
    if ($images->length) {
        foreach ($images as $img) {
            $img = pq($img);
            $image = [];
            $image['src'] = $img->attr("src");
            $image['title'] = $img->attr("title");
            $image['alt'] = $img->attr("alt");
            array_push($imagesData, $image);
        }
    }
    \phpQuery::$documents = null;
    return $imagesData;
}

//正则提取正文里本地图片为数组
function xianyu_img_file_array($content)
{
    preg_match_all('/<img(.*?)(?=>)/si', $content, $imgarr);
    foreach ($imgarr[0] as $key => $value) {
        preg_match('/(?<=src=").*?(?=")/si', $value, $imgurl);
        if (substr($imgurl[0], 0, 7) == 'http://' || substr($imgurl[0], 0, 8) == 'https://' || substr($imgurl[0], 0, 2) == '//') {
            $array[$key] = $imgurl[0];
        }
    }
    $array = !empty($array) ? array_values($array) : "";
    return $array;
}

function graph_domain($url)
{
    $upload_graph_domain = config('upload_graph_domain');
    if ($upload_graph_domain[0]) {
        foreach ($upload_graph_domain as $key => $value) {
            if (strpos($url, $value) > 0) {
                return true;
            }
        }
    }
    return false;
}

//分页相关类
function getpage($model, $params, $currentPage, $totalPages, $showrow, $halfPer = 2)
{
    $url = xianyu_list_url($model, $params, true, false);
    unset($params['p']);
    $first_url = xianyu_list_url($model, $params, true, false);
    $page = new \com\Page($totalPages, $showrow, $currentPage, $url, $first_url, $halfPer);
    return $page->myde_write();
}

function gettoppage($model, $params, $currentPage, $totalPages, $halfPer = 3, $url, $pagego = "")
{
    if ($currentPage == 2) {
        unset($params['p']);
        $first_url = xianyu_data_url($model, $params, true, false);
        $linkPage .= '<li><a href="' . $first_url . '" class="prev" data="p-' . ($currentPage - 1) . '">上一页</a></li>';
    } elseif ($currentPage > 1) {
        $linkPage .= '<li><a href="' . str_replace('xianyupage', ($currentPage - 1), $url) . '" class="prev" data="p-' . ($currentPage - 1) . '">上一页</a></li>';
    } else {
        $linkPage .= '<li class="disabled"><span>上一页</span></li>';

    }
    if ($currentPage < $totalPages) {
        $linkPage .= '<li><a href="' . str_replace('xianyupage', ($currentPage + 1), $url) . '" class="next pagegbk" data="p-' . ($currentPage + 1) . '">下一页</a></li>';
    } else {
        $linkPage .= '<li class="disabled"><span>下一页</span></li>';

    }
    return $linkPage;
}

function getpageprev($model, $params, $currentPage, $totalPages, $halfPer = 5, $url = "")
{
    $url = xianyu_list_url($model, $params, true, false);
    unset($params['p']);
    $first_url = xianyu_list_url($model, $params, true, false);
    if ($currentPage > 1) {
        $linkPage = str_replace('xianyupage', ($currentPage - 1), $url);
    }
    if ($currentPage == 2) {
        $linkPage = $first_url;
    }
    return $linkPage;
}

function getpagenext($model, $params, $currentPage, $totalPages, $halfPer = 5, $url = "")
{
    $url = xianyu_list_url($model, $params, true, false);
    $linkPage .= ($currentPage < $totalPages)
        ? str_replace('xianyupage', ($currentPage + 1), $url)
        : '';
    return $linkPage;
}

//新闻分页相关
function getnewspage($model, $params, $currentPage, $totalPages, $halfPer = 5, $pagego = "", $url = "")
{
    $url = xianyu_list_url($model, $params, true, false);
    unset($params['p']);
    $first_url = xianyu_list_url($model, $params, true, false);
    if ($currentPage == 2) {
        $linkPage .= '<li class="visible-xs"><a href="' . $first_url . '" class="prev" data="p-1">首页</a></li><li><a href="' . $first_url . '" class="prev" data="p-' . ($currentPage - 1) . '">上一页</a></li>';
    } elseif ($currentPage > 1) {
        $linkPage .= '<li class="visible-xs"><a href="' . $first_url . '" class="prev" data="p-1">首页</a></li><li><a href="' . str_replace('xianyupage', ($currentPage - 1), $url) . '" class="prev" data="p-' . ($currentPage - 1) . '">上一页</a></li>';
    } else {
        $linkPage .= '<li class="disabled visible-xs"><span>首页</span></li><li class="disabled"><span>上一页</span></li>';
    }
    $linkPage .= '<li class="visible-xs active"><span class="num">' . $currentPage . '/' . $totalPages . '</span></li>';
    for ($i = $currentPage - $halfPer, $i > 1 || $i = 1, $j = $currentPage + $halfPer, $j < $totalPages || $j = $totalPages; $i < $j + 1; $i++) {
        //格式化第一页
        if ($i == 1) {
            $linkPage .= ($i == $currentPage) ? '<li class="hidden-xs  active"><span>' . $i . '</span></li>' : '<li class="hidden-xs"><a href="' . str_replace('xianyupage', $i, $first_url) . '" data="p-' . $i . '">' . $i . '</a></li>';
        } else {
            $linkPage .= ($i == $currentPage) ? '<li class="hidden-xs active"><span>' . $i . '</span></li>' : '<li class="hidden-xs"><a href="' . str_replace('xianyupage', $i, $url) . '" data="p-' . $i . '">' . $i . '</a></li>';
        }
    }
    if ($currentPage < $totalPages) {
        $linkPage .= '<li><a href="' . str_replace('xianyupage', ($currentPage + 1), $url) . '" class="next pagegbk" data="p-' . ($currentPage + 1) . '">下一页</a></li><li class="visible-xs"><a href="' . str_replace('xianyupage', $totalPages, $url) . '" class="prev" data="p-' . $totalPages . '">尾页</a></li>';
    } else {
        $linkPage .= '<li class="disabled"><span>下一页</span></li><li class="visible-xs disabled"><span>尾页</span></li>';
    }

    return $linkPage;
}

// 根据静态规则生成保存路径
function xianyu_url_html($module, $params)
{
    $url_html = F('_data/url_html_config');
    $old = array('{listid}', '{listdir}', '{vodid}', '{pinyin}', '{id}', '{md5}', '{page}', '{sid}', '{pid}');
    if (strpos($module, 'show') !== false) {
        $new = array($params['id'], $params['dir'], $params['vodid'], $params['pinyin'], $params['id'], md5($params['id']), $params['p'], $params['sid'], $params['pid']);
    } else {
        $new = array($params['cid'], $params['dir'], $params['vodid'], $params['pinyin'], $params['id'], md5($params['id']), $params['p'], $params['sid'], $params['pid']);
    }
    if ('home/vod/read' == $module) {
        $html_path = $url_html['url_vod_data'];
    } else if ('home/vod/play' == $module) {
        $html_path = $url_html['url_vod_play'];
    } else if ('home/vod/filmtime' == $module) {
        $html_path = $url_html['url_vod_filmtime'];
    } else if ('home/vod/show' == $module) {
        $html_path = $url_html['url_vod_list'];
    } else if ('home/news/read' == $module) {
        $html_path = $url_html['url_news_data'];
    } else if ('home/news/show' == $module) {
        $html_path = $url_html['url_news_list'];
    } else if ('home/star/read' == $module) {
        $html_path = $url_html['url_star_data'];
    } else if ('home/star/show' == $module) {
        $html_path = $url_html['url_star_list'];
    } else if ('home/story/read' == $module) {
        $html_path = $url_html['url_story_data'];
    } else if ('home/story/show' == $module) {
        $html_path = $url_html['url_story_list'];
    } else if ('home/actor/read' == $module) {
        $html_path = $url_html['url_actor_data'];
    } else if ('home/actor/show' == $module) {
        $html_path = $url_html['url_actor_list'];
    } else if ('home/role/read' == $module) {
        $html_path = $url_html['url_role_data'];
    } else if ('home/role/show' == $module) {
        $html_path = $url_html['url_role_list'];
    } else if ('home/special/read' == $module) {
        $html_path = $url_html['url_special_data'];
    } else if ('home/special/show' == $module) {
        $html_path = $url_html['url_special_list'];
    } else if ('home/my/show' == $module) {
        $html_path = $url_html['url_my_list'];
    } else {
        return false;
    }
    if (!$html_path) {
        return false;
    }
    $html_path = str_replace($old, $new, $html_path);
    //第一页去除页码规则
    if ($params['p'] == 1 || $module != 'home/vod/play') {
        $html_path .= '#PAGE#';
        $old = array('/1#PAGE#', '-1#PAGE#', '_1#PAGE#', '-#PAGE#', '_#PAGE#');
        $new = array('/index#PAGE#', '#PAGE#', '#PAGE#', '#PAGE#', '#PAGE#');
        $html_path = str_replace('#PAGE#', '', str_replace($old, $new, $html_path));
    }
    //首页index处理
    $suffix = strrchr($html_path, '/');
    if ($suffix == '/') {
        $html_path .= 'index';
    }
    return config('site_path') . $html_path;


}

//静态模式 格式化页码为1的首个链接
function xianyu_url_replace_html($model, $url)
{
    $url_html = F('_data/url_html_config');
    if ($model != 'home/vod/play') {
        $old = array('/index.' . config('url_html_suffix'), '-1.' . config('url_html_suffix'), '_1.' . config('url_html_suffix'));
        $new = array('/', '.' . config('url_html_suffix'), '.' . config('url_html_suffix'));
        $url = str_replace($old, $new, $url);
    }
    return $url;
}

// 动态模式 自定义路由反向生成对应的链接URL
function url_user_route($model, $params, $redirect = true, $suffix = false)
{
    $suffix = !empty($suffix) ? config('site_url') . config('site_path') : config('site_path');
    $url_rules = config('route_rules');
    $array_key = explode('/', $model);
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            array_push($array_key, $key);
        }
    }
    //根据U参数生成KEY值
    $key = implode('/', $array_key);
    //将对应的URL网址按对应规则替换
    if (isset($url_rules[$key])) {
        //判读有无参数没有参数直接替换
        if ($url_rules[$key]['find'] == 1) {
            $url = $url_rules[$key]['replace'];
        } else {
            $url = str_replace('', '', str_replace($url_rules[$key]['find'], $params, $url_rules[$key]['replace']));
        }
        if (!empty($url)) {
            //规则结尾有/不增加后缀
            if (substr($url_rules[$key]['replace'], -1) == "/") {
                return $suffix . $url;
            } else {
                if ($model == 'home/map/show') {
                    return $suffix . $url . ".xml";
                } else {
                    return $suffix . $url . "." . config('url_html_suffix');
                }
            }
        }
    }
}

// 重写动态路径
function zp_url($model, $params = "", $redirect = true, $suffix = false)
{
    return str_replace('index.php?s=/', 'index.php?s=', url($model, $params, $redirect, $suffix));
}

// 重写动态路径
function xianyu_url($model, $params = "", $redirect = true, $suffix = false)
{
    $model = strtolower($model);
    $config = config();
    $url_html = F('_data/url_html_config');
    $reurl = "";
    // 自定义伪静态规则替换
    if (!empty($config['url_rewrite'])) {
        if (!empty($config['user_rewrite'])) {
            $reurl = url_user_route($model, $params, $redirect, $suffix);
        }
        //伪静态配置正确
        if (!empty($reurl)) {
            return $reurl;
        } else {
            if (is_array($params)) {
                unset($params['pinyin']);
                unset($params['cid']);
                unset($params['dir']);
                unset($params['mename']);
                unset($params['earea']);
            }
            $reurl = str_replace('index.php?s=/', 'index.php?s=', url($model, $params, $redirect, $suffix));
            if (!$url_html['url_html']) {
                return str_replace(array('index.php'), '', urldecode($reurl));
            }
            return $reurl;
        }
    } else {
        $reurl = str_replace('index.php?s=/', 'index.php?s=', url($model, $params, $redirect, $suffix));
        if (!$url_html['url_html']) {
            return str_replace(array('index.php'), '', urldecode($reurl));
        }
        return $reurl;
    }

}

// 重写会员动态
function xianyu_user_url($model, $params = "", $redirect = true, $suffix = false)
{
    return xianyu_url($model, $params, $redirect, $suffix);
}

function xianyu_list_url($model = "home/vod/read", $arrurl = "", $redirect = true, $suffix = false)
{
    return xianyu_data_url($model, $arrurl, $redirect, $suffix);
}

function xianyu_type_url($model = "home/vod/type", $arrurl = "", $redirect = true, $suffix = false)
{
    return xianyu_data_url($model, $arrurl, $redirect, $suffix);
}

function xianyu_data_url($model = "home/vod/read", $arrurl = "", $redirect = true, $suffix = false)
{
    $url_html = F('_data/url_html_config');
    $config = config();
    //有跳转地址
    if (!empty($arrurl['jumpurl'])) {
        return $arrurl['jumpurl'];
    }
    if ($url_html['url_html']) {
        $url = xianyu_url_html($model, $arrurl);
        if ($url) {
            $url = $url . '.' . config('url_html_suffix');
            return xianyu_url_replace_html($model, $url);
        }
    }
    if (empty($config['url_rewrite']) || !empty($config['url_rewrite']) && empty($config['user_rewrite'])) {
        unset($arrurl['pinyin']);
        unset($arrurl['cid']);
        unset($arrurl['dir']);
        unset($arrurl['mename']);
        unset($arrurl['earea']);
    }
    unset($arrurl['jumpurl']);
    if ($arrurl['p'] > 1) {
        $arrurl['p'] = 'xianyupage';
    } elseif ($arrurl['p'] == 1) {
        unset($arrurl['p']);
    }
    return xianyu_url($model, $arrurl, $redirect, $suffix);
}

function xianyu_play_url($arrurl, $redirect = true, $suffix = false)
{
    $url_html = F('_data/url_html_config');
    $config = config();
    if ($url_html['url_html']) {
        $url = xianyu_url_html('home/vod/play', $arrurl);
        if ($url) {
            $url = $url . '.' . config('url_html_suffix');
            return xianyu_url_replace_html('home/vod/play', $url);
        }
    }
    //动态模式销毁变量
    if (empty($config['url_rewrite']) || !empty($config['url_rewrite']) && empty($config['user_rewrite'])) {
        unset($arrurl['pinyin']);
        unset($arrurl['cid']);
        unset($arrurl['dir']);
    }
    return xianyu_url('home/vod/play', $arrurl, $redirect, $suffix);
}

function seokeywords($name, $key = 3)
{
    $urlarray = config('http_api');
    $rand = array_rand($urlarray, 1);
    $apiurl = "http://" . $urlarray[$rand] . "/xianyucms/seo.php?name=" . $name . "&k=" . $key;
    $data = xianyu_get_url($apiurl, 15);
    $d = json_decode($data, true);
    $c = explode(',', $d['keywords']);
    if (!empty($d) && count($c) > 1) {
        return $d['keywords'];
    } else {
        return false;
    }
}

//路径参数处理函数
function param_url()
{
    $where = array();
    $where['sid'] = input('sid/d', '');
    $where['pid'] = input('pid/d', '');
    $where['id'] = input('id/d', '');
    $where['op'] = input('op/d', '');
    $where['vid'] = input('vid/d', '');
    $where['vcid'] = input('vcid/d', '');
    $where['uid'] = input('uid/d', '');
    $where['pinyin'] = htmlspecialchars(input('pinyin/s', ''));
    $where['cid'] = input('cid/d', '');
    $where['dir'] = htmlspecialchars(input('dir/s', ''));
    $where['listdir'] = htmlspecialchars(input('listdir/s', ''));
    $where['vdir'] = htmlspecialchars(input('vdir/s', ''));
    $where['letter'] = htmlspecialchars(input('letter/s', ''));
    $where['tag'] = htmlspecialchars(urldecode(trim(input('tag/s', ''))));
    $where['wd'] = htmlspecialchars(urldecode(trim(input('wd/s', ''))));
    $where['play'] = htmlspecialchars(urldecode(trim(input('play/s', ''))));
    $where['year'] = input('year/d', '');
    $where['month'] = input('month/d', '');
    $where['language'] = htmlspecialchars(urldecode(trim(input('language/s', ''))));
    $where['area'] = htmlspecialchars(urldecode(trim(input('area/s', ''))));
    $where['earea'] = htmlspecialchars(urldecode(trim(input('earea/s', ''))));
    $where['actor'] = htmlspecialchars(urldecode(trim(input('actor/s', ''))));
    $where['director'] = htmlspecialchars(urldecode(trim(input('director/s', ''))));
    $where['stars'] = input('stars/d', '');
    $where['zy'] = htmlspecialchars(urldecode(trim(input('zy/s', ''))));
    $where['xb'] = htmlspecialchars(urldecode(trim(input('xb/s', ''))));
    $where['sex'] = htmlspecialchars(urldecode(trim(input('sex/s', ''))));
    $where['type'] = htmlspecialchars(urldecode(trim(input('type/s', ''))));
    $where['filmtime'] = htmlspecialchars(input('filmtime/s', 'up'));
    $where['mcid'] = input('mcid/d', '');
    $where['mename'] = htmlspecialchars(input('mename/s', ''));
    $where['lz'] = input('lz/d', '');
    $where['picm'] = input('picm/d', '');
    $where['limit'] = input('limit/d', '');
    $where['page'] = input('p/d', 1);
    $where['order'] = order_by(input('order/s', ""));
    $where['p'] = input('p/d', 1);
    return $where;
}

//分页跳转参数处理
function param_jump($where)
{
    if (!empty($where['sid'])) {
        $jumpurl['sid'] = $where['sid'];
    }
    if (!empty($where['id'])) {
        $jumpurl['id'] = $where['id'];
    }
    if (!empty($where['pid'])) {
        $jumpurl['pid'] = $where['pid'];
    }
    if (!empty($where['op'])) {
        $jumpurl['op'] = $where['op'];
    }
    if (!empty($where['pinyin'])) {
        $jumpurl['pinyin'] = $where['pinyin'];
    }
    if (!empty($where['cid'])) {
        $jumpurl['cid'] = $where['cid'];
    }
    if (!empty($where['listdir'])) {
        $jumpurl['listdir'] = $where['listdir'];
    }
    if (!empty($where['letter'])) {
        $jumpurl['letter'] = $where['letter'];
    }
    if (!empty($where['name'])) {
        $jumpurl['name'] = urlencode($where['name']);
    }
    if (!empty($where['aliases'])) {
        $jumpurl['aliases'] = urlencode($where['aliases']);
    }
    if (!empty($where['tag'])) {
        $jumpurl['tag'] = urlencode($where['tag']);
    }
    if (!empty($where['wd'])) {
        $jumpurl['wd'] = urlencode($where['wd']);
    }
    if (!empty($where['play'])) {
        $jumpurl['play'] = $where['play'];
    }
    if (!empty($where['year'])) {
        $jumpurl['year'] = $where['year'];
    }
    if (!empty($where['language'])) {
        $jumpurl['language'] = urlencode($where['language']);
    }
    if (!empty($where['area'])) {
        $jumpurl['area'] = urlencode($where['area']);
    }
    if (!empty($where['earea'])) {
        $jumpurl['earea'] = urlencode($where['earea']);
    }
    if (!empty($where['actor'])) {
        $jumpurl['actor'] = urlencode($where['actor']);
    }
    if (!empty($where['director'])) {
        $jumpurl['director'] = urlencode($where['director']);
    }
    if (!empty($where['stars'])) {
        $jumpurl['stars'] = $where['stars'];
    }
    if (!empty($where['zy'])) {
        $jumpurl['zy'] = urlencode($where['zy']);
    }
    if (!empty($where['xb'])) {
        $jumpurl['xb'] = urlencode($where['xb']);
    }
    if (!empty($where['type'])) {
        $jumpurl['type'] = $where['type'];
    }
    if (!empty($where['mcid'])) {
        $jumpurl['mcid'] = $where['mcid'];
    }
    if (!empty($where['meanme'])) {
        $jumpurl['meanme'] = $where['meanme'];
    }
    if (!empty($where['lz'])) {
        $jumpurl['lz'] = $where['lz'];
    }
    if (!empty($where['picm'])) {
        $jumpurl['picm'] = $where['picm'];
    }
    if (!empty($where['limit'])) {
        $jumpurl['limit'] = $where['limit'];
    }
    if ($where['order'] != 'addtime' && $where['order']) {
        $jumpurl['order'] = $where['order'];
    }
    $jumpurl['p'] = '';
    return $jumpurl;
}

//返回安全的orderby
function order_by($order = 'addtime')
{
    if (empty($order)) {
        return 'addtime';
    }
    $array = array();
    $array['addtime'] = 'addtime';
    $array['id'] = 'id';
    $array['hits'] = 'hits';
    $array['hits_month'] = 'hits_month';
    $array['hits_week'] = 'hits_week';
    $array['stars'] = 'stars';
    $array['up'] = 'up';
    $array['down'] = 'down';
    $array['gold'] = 'gold';
    $array['golder'] = 'golder';
    $array['year'] = 'year';
    $array['letter'] = 'letter';
    $array['filmtime'] = 'filmtime';
    $array['oid'] = 'oid';
    return $array[trim($order)];
}

//生成参数列表,以数组形式返回
function param_lable($tag = '')
{
    $param = array();
    $array = explode(';', str_replace('num:', 'limit:', $tag));
    foreach ($array as $v) {
        list($key, $val) = explode(':', trim($v));
        $param[trim($key)] = trim($val);
    }
    return $param;
}

// 循环标签查询参数格式化
function mysql_param($tag)
{
    $params = array();
    // 查询条数
    $params['limit'] = !empty($tag['limit']) ? $tag['limit'] : '10';
    // 排序字段
    $params['order'] = !empty($tag['order']) ? $tag['order'] : '';
    // 分组参数
    $params['page'] = !empty($tag['page']) ? $tag['page'] : false;
    $params['p'] = !empty($tag['p']) ? $tag['p'] : '';
    // 缓存参数
    if (!empty($tag['cahce_name']) == 'default' || empty($tag['cahce_name'])) {
        $params['cache_name'] = md5(config('cache_prefix') . implode('_', $tag));
    } else {
        $params['cache_name'] = !empty($tag['cache_name']) ? md5(config('cache_prefix') . '_' . $tag['cache_name']) : '';
    }
    // 缓存时间
    $params['cache_time'] = !empty($tag['cahce_time']) ? intval($tag['cahce_time']) : intval(config('data_cache_foreach'));
    return $params;
}

//循环查询相关标签视频
function xianyu_mysql_vod($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_color,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_hits_lasttime,vod_stars,vod_up,vod_down,vod_play,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_weekday,vod_letters,vod_bigpic,vod_jumpurl';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if ($params['cache_name'] && $params['cache_time']) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    if (!empty($tag['ids'])) {
        $where['vod_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['vod_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['vod_cid'] = getlistsqlin($tag['cid']);
        }
    } else {
        $where['vod_cid'] = array('neq', 69);
    }
    // $where['vod_cid'] = array('neq',69);
    if (!empty($tag['names'])) {
        $where['vod_name'] = array('in', $tag['names']);
    }
    if (!empty($tag['day'])) {
        $where['vod_addtime'] = array('gt', getxtime($tag['day']));
    }
    if (!empty($tag['filmtime'])) {
        if ($tag['filmtime'] == 'ls') {
            $where['vod_filmtime'] = array('between', array(strtotime("-2 month", time()), time()));
            //正在热映的视频desc
        } else {
            $where['vod_filmtime'] = array('gt', time());
            //即将上映的视频asc
        }
    }
    if (!empty($tag['name'])) {
        $where['vod_name|vod_aliases'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['play'])) {
        $where['vod_play'] = array('like', '%' . $tag['play'] . '%');
    }
    if (!empty($tag['likename'])) {
        $where['vod_name'] = array('like', $tag['vname'] . '%');
    }
    if (!empty($tag['title'])) {
        $where['vod_title'] = array('like', '%' . $tag['title'] . '%');
    }
    if (!empty($tag['tj'])) {
        $tjinfo = explode('|', trim($tag['tj']));
        if ($tjinfo[3]) {
            $where[$tjinfo[3]] = array($tjinfo[1], $tjinfo[2]);
        } else {
            $where[$tjinfo[0]] = array($tjinfo[1], $tjinfo[2]);
        }
    }
    if (!empty($tag['no'])) {
        $where['vod_id'] = array('neq', $tag['no']);
    }
    if (!empty($tag['play'])) {
        $where['vod_play'] = array('like', '%' . $tag['play'] . '%');
    }
    if (!empty($tag['inputer'])) {
        $where['vod_inputer'] = array('eq', $tag['inputer']);
    }
    if (!empty($tag['wd'])) {
        $where['vod_name|vod_actor|vod_director|vod_aliases'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['yanyuan'])) {
        $where['vod_actor|vod_director'] = array('like', '%' . $tag['yanyuan'] . '%');
    }
    if (!empty($tag['stars'])) {
        $where['vod_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['isfiml'])) {
        $where['vod_isfiml'] = array('eq', $tag['isfiml']);
    }
    if (!empty($tag['letter'])) {
        $letter = explode(',', $tag['letter']);
        if (count($letter) > 1) {
            $where['vod_letter'] = array('in', $tag['letter']);
        } else {
            $where['vod_letter'] = array('eq', $letter[0]);
        }
    }
    if (!empty($tag['area'])) {
        $where['vod_area'] = array('eq', '' . $tag['area'] . '');
    }
    if (!empty($tag['language'])) {
        $where['vod_language'] = array('eq', '' . $tag['language'] . '');
    }
    if (!empty($tag['lz']) == 1) {
        $where['vod_continu'] = array('neq', '0');
    } elseif (!empty($tag['lz']) == 2) {
        $where['vod_continu'] = 0;
    }
    if (!empty($tag['year'])) {
        $year = explode(',', $tag['year']);
        if (count($year) > 1) {
            $where['vod_year'] = array('between', $year[0] . ',' . $year[1]);
        } else {
            $where['vod_year'] = array('eq', $tag['year']);
        }
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['vod_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['vod_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['gold'])) {
        $gold = explode(',', $tag['gold']);
        if (count($gold) > 1) {
            $where['vod_gold'] = array('between', $gold[0] . ',' . $gold[1]);
        } else {
            $where['vod_gold'] = array('gt', $gold[0]);
        }
    }
    if (!empty($tag['golder'])) {
        $golder = explode(',', $tag['golder']);
        if (count($golder) > 1) {
            $where['vod_golder'] = array('between', $golder[0] . ',' . $golder[1]);
        } else {
            $where['vod_golder'] = array('gt', $golder[0]);
        }
    }
    if (!empty($tag['up'])) {
        $up = explode(',', $tag['up']);
        if (count($up) > 1) {
            $where['vod_up'] = array('between', $up[0] . ',' . $up[1]);
        } else {
            $where['vod_up'] = array('gt', $up[0]);
        }
    }
    if (!empty($tag['down'])) {
        $down = explode(',', $tag['down']);
        if (count($down) > 1) {
            $where['vod_down'] = array('between', $down[0] . ',' . $down[1]);
        } else {
            $where['vod_down'] = array('gt', $down[0]);
        }
    }
    if (!empty($tag['group'])) {
        $group = $tag['group'];
    } else {
        $group = FALSE;
    }
    $where['vod_status'] = array('eq', 1);
    //视图查询
    if (!empty($tag['tag'])) {
        $where['tag_name'] = array('eq', $tag['tag']);
        $where['tag_sid'] = 1;
        if ($group) {
            $rs = db('tag')->alias('t')->join('vod v', 'v.vod_id = t.tag_id', 'RIGHT')->group($group);
        } else {
            $rs = db('tag')->alias('t')->join('vod v', 'v.vod_id = t.tag_id', 'RIGHT');
        }
    } elseif (!empty($tag['mcid'])) {
        $mcid = explode(',', $tag['mcid']);
        if (count($mcid) > 1) {
            $where['mcid_mid'] = array('in', $tag['mcid']);
        } else {
            $where['mcid_mid'] = array('eq', $tag['mcid']);
        }
        if ($group) {
            $rs = db('mcid')->alias('m')->join('vod v', 'v.vod_id = m.mcid_id', 'RIGHT')->group($group);
        } else {
            $rs = db('mcid')->alias('m')->join('vod v', 'v.vod_id = m.mcid_id', 'RIGHT');
        }
    } elseif (!empty($tag['weekday'])) {
        $week = explode(',', $tag['weekday']);
        if (count($week) > 1) {
            $where['weekday_cid'] = array('in', $tag['weekday']);
        } else {
            $where['weekday_cid'] = array('eq', $tag['weekday']);
        }
        $where['weekday_sid'] = 1;
        $rs = db('weekday')->alias('w')->join('vod v', 'v.vod_id = w.weekday_id', 'RIGHT')->group('vod_id');
    } elseif (!empty($tag['diantai'])) {
        $week = explode(',', $tag['diantai']);
        if (count($week) > 1) {
            $where['vodtv_name'] = array('in', $tag['diantai']);
        } else {
            $where['vodtv_name'] = array('eq', $tag['diantai']);
        }
        $where['vodtv_sid'] = 1;
        $rs = db('vodtv')->alias('t')->join('vod v', 'v.vod_id = t.vodtv_id', 'RIGHT')->group('vod_id');
    } elseif (!empty($tag['prty'])) {
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $where['prty_sid'] = 1;
        $rs = db('prty')->alias('p')->join('vod v', 'v.vod_id = p.prty_id', 'RIGHT')->group('vod_id');
    } elseif (!empty($tag['actor'])) {
        $actor = explode(',', $tag['actor']);
        if (count($actor) > 1) {
            $where['actors_name'] = array('in', $tag['actor']);
            $where['actors_type'] = 1;
        } else {
            $where['actors_name'] = $tag['actor'];
            $where['actors_type'] = 1;
        }
        $rs = db('actors')->alias('a')->join('vod v', 'v.vod_id = a.actors_id', 'RIGHT')->group('actors_id');
    } elseif (!empty($tag['director'])) {
        $actor = explode(',', $tag['director']);
        if (count($actor) > 1) {
            $where['actors_name'] = array('in', $tag['director']);
            $where['actors_type'] = 2;
        } else {
            $where['actors_name'] = $tag['director'];
            $where['actors_type'] = 2;
        }
        $rs = db('actors')->alias('a')->join('vod v', 'v.vod_id = a.actors_id', 'RIGHT')->group('actors_id');
    } else {
        $rs = db('vod');
    }
    if (!empty($params['page'])) {
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['vod_cid'];
        $data[$key]['list_name'] = getlistname($val['vod_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['vod_cid'], 'list_url');
        $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
        $data[$key]['vod_playurl'] = xianyu_play_url(array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'sid' => 0, 'pid' => 1));
        $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
//        $data[$key]['vod_bigpicurl'] = !empty($val['vod_bigpic'])?xianyu_img_url($val['vod_bigpic']):NULL;
//        $data[$key]['vod_picurl_small'] = xianyu_img_smallurl($val['vod_pic']);

    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_vod')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询相关标签新闻

function xianyu_mysql_news($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'news_id,news_cid,news_name,news_mid,news_color,news_pic,news_remark,news_hits,news_hits_day,news_hits_week,news_hits_month,news_stars,news_up,news_down,news_gold,news_golder,news_addtime,news_jumpurl';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['news_status'] = array('eq', 1);
    if (!empty($tag['ids'])) {
        $where['news_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['news_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['news_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['day'])) {
        $where['news_addtime'] = array('gt', getxtime($tag['day']));
    }
    if (!empty($tag['stars'])) {
        $where['news_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['letter'])) {
        $letter = explode(',', $tag['letter']);
        if (count($letter) > 1) {
            $where['news_letter'] = array('in', $tag['letter']);
        } else {
            $where['news_letter'] = array('eq', $letter[0]);
        }
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['news_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['news_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['name'])) {
        $where['news_name'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['wd'])) {
        $where['news_name|news_remark'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['no'])) {
        $where['news_id'] = array('neq', $tag['no']);
    }
    if (!empty($tag['gold'])) {
        $gold = explode(',', $tag['gold']);
        if (count($gold) > 1) {
            $where['news_gold'] = array('between', $gold[0] . ',' . $gold[1]);
        } else {
            $where['news_gold'] = array('gt', $gold[0]);
        }
    }
    if (!empty($tag['golder'])) {
        $golder = explode(',', $tag['golder']);
        if (count($golder) > 1) {
            $where['news_golder'] = array('between', $golder[0] . ',' . $golder[1]);
        } else {
            $where['news_golder'] = array('gt', $golder[0]);
        }
    }
    if (!empty($tag['up'])) {
        $up = explode(',', $tag['up']);
        if (count($up) > 1) {
            $where['news_up'] = array('between', $up[0] . ',' . $up[1]);
        } else {
            $where['news_up'] = array('gt', $up[0]);
        }
    }
    if (!empty($tag['down'])) {
        $down = explode(',', $tag['down']);
        if (count($down) > 1) {
            $where['news_down'] = array('between', $down[0] . ',' . $down[1]);
        } else {
            $where['news_down'] = array('gt', $down[0]);
        }
    }
    //视图查询
    if (!empty($tag['tag'])) {
        $where['tag_name'] = array('eq', $tag['tag']);
        $where['tag_sid'] = 2;
        $rs = db('tag')->alias('t')->join('news n', 'n.news_id = t.tag_id', 'RIGHT');
    } elseif (!empty($tag['news'])) {
        if (!empty($tag['sid'])) {
            $where['newsrel_sid'] = $tag['sid'];
        }
        if (!empty($tag['did'])) {
            $where['newsrel_did'] = $tag['did'];
            $whereor['newsrel_name'] = $tag['news'];
        } else {
            $where['newsrel_name'] = $tag['news'];
        }
        $rs = db('newsrel')->alias('s')->join('news n', 'n.news_id = s.newsrel_nid', 'RIGHT')->group('news_id');
    } elseif (!empty($tag['prty'])) {
        $where['prty_sid'] = 2;
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $rs = db('prty')->alias('p')->join('news n', 'n.news_id = p.prty_id', 'RIGHT');
    } else {
        $rs = db('news');
    }
    if (!empty($params['page'])) {
        $pageurl = urldecode(config('jumpurl'));
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['news_cid'];
        $data[$key]['list_name'] = getlistname($val['news_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['news_cid'], 'list_url');
        $data[$key]['news_readurl'] = xianyu_data_url('home/news/read', array('id' => $val['news_id'], 'cid' => $val['news_cid'], 'dir' => getlistname($val['news_cid'], 'list_dir'), 'jumpurl' => $val['news_jumpurl']));
        $data[$key]['news_picurl'] = xianyu_img_url($val['news_pic']);
        $data[$key]['news_picurl_small'] = xianyu_img_smallurl($val['news_pic']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_news')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询相关标签明星

function xianyu_mysql_star($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'star_id,star_cid,star_name,star_bm,star_wwm,star_letters,star_tag,star_letter,star_color,star_bgcolor,star_pig,star_pic,star_bigpic,star_xb,star_sg,star_tz,star_mz,star_cstime,star_school,star_gs,star_weibo,star_work,star_guanxi,star_zy,star_xz,star_xx,star_area,star_gj,star_csd,star_content,star_jumpurl,star_hits,star_hits_day,star_hits_week,star_hits_month,star_addtime,star_stars,star_up,star_down';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    if (!empty($tag['ids'])) {
        $where['star_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['star_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['star_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['day'])) {
        $where['star_addtime'] = array('gt', getxtime($tag['day']));
    }
    if (!empty($tag['stars'])) {
        $where['star_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['letter'])) {
        $letter = explode(',', $tag['letter']);
        if (count($letter) > 1) {
            $where['star_letter'] = array('in', $tag['letter']);
        } else {
            $where['star_letter'] = array('eq', $letter[0]);
        }
    }
    if (!empty($tag['xb'])) {
        $where['star_xb'] = array('eq', $tag['xb']);
    }
    if (!empty($tag['sex'])) {
        $where['star_xb'] = array('eq', $tag['sex']);
    }
    if (!empty($tag['area'])) {
        $area = explode(',', $tag['area']);
        if (count($area) > 1) {
            $where['star_area'] = array('in', $tag['area']);
        } else {
            $where['star_area'] = array('like', '%' . $tag['area'] . '%');
        }
    }
    if (!empty($tag['zy'])) {
        $zy = explode(',', $tag['zy']);
        if (count($zy) > 1) {
            $where['star_zy'] = array('in', $tag['zy']);
        } else {
            $where['star_zy'] = array('like', '%' . $tag['zy'] . '%');
        }
    }
    if (!empty($tag['guanxi'])) {
        $where['star_guanxi'] = array('like', '%' . $tag['guanxi'] . '%');
    }
    if (!empty($tag['work'])) {
        $where['star_work'] = array('like', '%' . $tag['work'] . '%');
    }
    if (!empty($tag['stararea'])) {
        $where['star_area'] = array('in', $tag['stararea']);
    }
    if (!empty($tag['name'])) {
        $where['star_name'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['starname'])) {
        $where['star_name'] = array('in', $tag['starname']);
    }
    if (!empty($tag['wd'])) {
        $where['star_name|star_area|star_zy|star_csd|star_work|star_gx|star_content|star_info'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['no'])) {
        $where['star_id'] = array('neq', $tag['no']);
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['star_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['star_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['up'])) {
        $up = explode(',', $tag['up']);
        if (count($up) > 1) {
            $where['star_up'] = array('between', $up[0] . ',' . $up[1]);
        } else {
            $where['star_up'] = array('gt', $up[0]);
        }
    }
    if (!empty($tag['down'])) {
        $down = explode(',', $tag['down']);
        if (count($down) > 1) {
            $where['star_down'] = array('between', $down[0] . ',' . $down[1]);
        } else {
            $where['star_down'] = array('gt', $down[0]);
        }
    }
    if (!empty($tag['tj'])) {
        $tjinfo = explode('|', trim($tag['tj']));
        if ($tjinfo[3]) {
            $where[$tjinfo[3]] = array($tjinfo[1], $tjinfo[2]);
        } else {
            $where[$tjinfo[0]] = array($tjinfo[1], $tjinfo[2]);
        }
    }
    //视图查询
    if (!empty($tag['tag'])) {
        $where['tag_name'] = array('eq', $tag['tag']);
        $where['tag_sid'] = 3;
        $rs = db('tag')->alias('t')->join('star s', 's.star_id = t.tag_id', 'RIGHT');
    } elseif (!empty($tag['prty'])) {
        $where['prty_sid'] = 3;
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $rs = db('prty')->alias('p')->join('stat s', 's.star_id = p.prty_id', 'RIGHT');
    } else {
        $rs = db('star');
    }
    $where['star_status'] = array('eq', 1);
    if (!empty($params['page'])) {
        $pageurl = urldecode(config('jumpurl'));
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['star_cid'];
        $data[$key]['list_name'] = getlistname($val['star_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['star_cid'], 'list_url');
        $data[$key]['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $val['star_id'], 'pinyin' => $val['star_letters'], 'cid' => $val['star_cid'], 'dir' => getlistname($val['star_cid'], 'list_dir'), 'jumpurl' => $val['star_jumpurl']));
        $data[$key]['star_picurl'] = xianyu_img_url($val['star_pic']);
        $data[$key]['star_bigpicurl'] = xianyu_img_url($val['star_bigpic']);
        $data[$key]['star_picurl_small'] = xianyu_img_smallurl($val['star_pic']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_star')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//明星合作
function xianyu_mysql_starhz($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $tag['order'] = !empty($tag['order']) ? $tag['order'] : 'star_addtime desc';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['star_status'] = array('eq', 1);
    if (!empty($tag['star'])) {
        $vodid = db('actors')->field('actors_id')->where('actors_name', $tag['star'])->select();
        foreach ($vodid as $val) {
            $arrid[] = $val['actors_id'];
        }
        $arrayvid = @implode(',', $arrid);
        $stararray = db('actors')->field('actors_name')->where('actors_id', 'in', $arrayvid)->select();
        foreach ($stararray as $key => $val) {
            if ($val['actors_name'] != $tag['star']) {
                $arrstar[$val['actors_name']] = $val['actors_name'];
            }
        }
        if (!empty($tag['guanxi'])) {
            $guanxi = @explode(',', $tag['guanxi']);
            $arrstar = @array_merge($guanxi, $arrstar);
        }
        $arraystar = @implode(',', @array_unique($arrstar));
        $where['star_name'] = array('neq', $tag['star']);
    }
    $where['star_name'] = array('in', $arraystar);
    $rs = db('star');
    if (!empty($params['page'])) {
        $pageurl = urldecode(config('jumpurl'));
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['star_cid'];
        $data[$key]['list_name'] = getlistname($val['star_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['star_cid'], 'list_url');
        $data[$key]['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $val['star_id'], 'pinyin' => $val['star_letters'], 'cid' => $val['star_cid'], 'dir' => getlistname($val['star_cid'], 'list_dir'), 'jumpurl' => $val['star_jumpurl']));
        $data[$key]['star_picurl'] = xianyu_img_url($val['star_pic']);
        $data[$key]['star_bigpicurl'] = xianyu_img_url($val['star_bigpic']);
        $data[$key]['star_picurl_small'] = xianyu_img_smallurl($val['star_pic']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_starhz')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;

}

//循环查询相关标签剧情
function xianyu_mysql_story($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'story_id,story_cid,story_vid,story_addtime,story_hits,story_hits_day,story_hits_week,story_hits_month,story_continu,story_cont,story_title,story_page,story_stars,vod_id,vod_cid,vod_letters,vod_name,vod_mcid,vod_content,vod_pic,vod_director,vod_actor,vod_year,vod_gold,vod_hits,vod_addtime,vod_jumpurl';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    if (!empty($tag['ids'])) {
        $where['story_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['story_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['story_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['vcid'])) {
        $cids = explode(',', trim($tag['vcid']));
        if (count($cids) > 1) {
            $where['vod_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['vod_cid'] = getlistsqlin($tag['vcid']);
        }
    }
    if (!empty($tag['vid'])) {
        $where['story_vid'] = array('in', $tag['vid']);
    }
    if (!empty($tag['name'])) {
        $where['vod_name'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['day'])) {
        $where['story_addtime'] = array('gt', getxtime($tag['day']));
    }

    if (!empty($tag['tj'])) {
        $tjinfo = explode('|', trim($tag['tj']));
        if ($tjinfo[3]) {
            $where[$tjinfo[3]] = array($tjinfo[1], $tjinfo[2]);
        } else {
            $where[$tjinfo[0]] = array($tjinfo[1], $tjinfo[2]);
        }
    }
    if (!empty($tag['stars'])) {
        $where['story_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['lz']) == 1) {
        $where['story_cont'] = array('neq', '0');
    } elseif (!empty($tag['lz']) == 2) {
        $where['story_cont'] = 0;
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['story_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['story_hits'] = array('gt', $hits[0]);
        }
    }
    //视图查询
    if (!empty($tag['prty'])) {
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $where['prty_sid'] = 4;
        $rs = db('prty')->alias('p')->join('story s', 's.story_id = p.prty_id', 'RIGHT')->join('vod v', 'v.vod_id = s.story_vid', 'LEFT');
    } else {
        $rs = db('story')->alias('s')->join('vod v', 'v.vod_id = s.story_vid', 'LEFT');
    }
    $where['story_status'] = array('eq', 1);
    if (!empty($params['page'])) {
        $count = model('story')->where($where)->count('story_id');
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], $count, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['story_cid'];
        $data[$key]['list_name'] = getlistname($val['story_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['story_cid'], 'list_url');
        $data[$key]['story_readurl'] = xianyu_data_url('home/story/read', array('id' => $val['story_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['story_cid'], 'dir' => getlistname($val['story_cid'], 'list_dir')));
        $data[$key]['story_endurl'] = str_replace('xianyupage', $val['story_page'], xianyu_data_url('home/story/read', array('id' => $val['story_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['story_cid'], 'dir' => getlistname($val['story_cid'], 'list_dir'), 'p' => $val['story_page'])));
        $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
        $data[$key]['vod_playurl'] = xianyu_play_url(array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'sid' => 0, 'pid' => 1));
        $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
        $data[$key]['vod_bigpicurl'] = xianyu_img_url($val['vod_bigpic']);
        $data[$key]['vod_picurl_small'] = xianyu_img_smallurl($val['vod_pic']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_story')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

function xianyu_mysql_vod_story($vid = "", $letters = "")
{
    $where = array();
    $where['story_vid'] = $vid;
    $where['story_status'] = array('eq', 1);
    $array = db('story')->where($where)->find();
    if (!empty($array)) {
        $arrays['story_readurl'] = xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $letters, 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir')));
        $listarray = explode('||', $array['story_content']); //将每一集分组
        foreach ($listarray as $key => $jiinfo) {
            $jiinfoarray = explode('@@', $jiinfo);
            $arrays['story_list'][$key + 1]['story_name'] = $jiinfoarray[0];
            $arrays['story_list'][$key + 1]['story_title'] = $jiinfoarray[1];
            $arrays['story_list'][$key + 1]['story_info'] = $jiinfoarray[2];
            if ($key == 0) {
                $arrays['story_list'][$key + 1]['story_url'] = xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $letters, 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir')));
            } else {
                $arrays['story_list'][$key + 1]['story_url'] = str_replace('xianyupage', $key + 1, xianyu_data_url('home/story/read', array('id' => $array['story_id'], 'pinyin' => $letters, 'cid' => $array['story_cid'], 'dir' => getlistname($array['story_cid'], 'list_dir'), 'p' => $key + 1)));
            }
        }
        return $arrays;
    } else {
        return false;
    }
}

//循环查询标签演员表
function xianyu_mysql_actor($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'actor_id,actor_cid,actor_vid,actor_addtime,actor_hits,actor_hits_day,actor_hits_week,actor_hits_month,vod_id,vod_cid,vod_letters,vod_name,vod_mcid,vod_content,vod_pic,vod_director,vod_actor,vod_year,vod_gold,vod_hits,vod_addtime,vod_jumpurl';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['actor_status'] = array('eq', 1);
    if (!empty($tag['ids'])) {
        $where['actor_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['actor_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['actor_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['vcid'])) {
        $cids = explode(',', trim($tag['vcid']));
        if (count($cids) > 1) {
            $where['vod_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['vod_cid'] = getlistsqlin($tag['vcid']);
        }
    }
    if (!empty($tag['vid'])) {
        $where['actor_vid'] = array('in', $tag['vid']);
    }
    if (!empty($tag['name'])) {
        $where['vod_name'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['day'])) {
        $where['actor_addtime'] = array('gt', getxtime($tag['day']));
    }

    if (!empty($tag['tj'])) {
        $tjinfo = explode('|', trim($tag['tj']));
        if ($tjinfo[3]) {
            $where[$tjinfo[3]] = array($tjinfo[1], $tjinfo[2]);
        } else {
            $where[$tjinfo[0]] = array($tjinfo[1], $tjinfo[2]);
        }
    }
    if (!empty($tag['stars'])) {
        $where['actor_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['actor_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['actor_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['prty'])) {
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $where['prty_sid'] = 6;
        $rs = db('prty')->alias('p')->join('actor a', 'a.actor_id = p.prty_id', 'LEFT')->join('vod v', 'v.vod_id = a.actor_vid', 'LEFT');
    } else {
        $rs = db('actor')->alias('a')->join('vod v', 'v.vod_id = a.actor_vid', 'LEFT');
    }
    if (!empty($params['page'])) {
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['actor_cid'];
        $data[$key]['list_name'] = getlistname($val['actor_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['actor_cid'], 'list_url');
        $data[$key]['actor_readurl'] = xianyu_data_url('home/actor/read', array('id' => $val['actor_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['actor_cid'], 'dir' => getlistname($val['actor_cid'], 'list_dir')));
        $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
        $data[$key]['vod_playurl'] = xianyu_play_url(array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'sid' => 0, 'pid' => 1));
        $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
        $data[$key]['vod_bigpicurl'] = xianyu_img_url($val['vod_bigpic']);
        $data[$key]['vod_picurl_small'] = xianyu_img_smallurl($val['vod_pic']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_actor')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询标签演员表
function xianyu_mysql_role($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 'a.*,r.*,s.*,vod_id,vod_cid,vod_mcid,vod_name,vod_aliases,vod_title,vod_keywords,vod_actor,vod_director,vod_content,vod_pic,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_continu,vod_total,vod_isend,vod_addtime,vod_hits,vod_hits_day,vod_hits_week,vod_hits_month,vod_stars,vod_jumpurl,vod_letter,vod_gold,vod_golder,vod_isfilm,vod_filmtime,vod_length,vod_letters';
    $tag['order'] = !empty($tag['order']) ? $tag['order'] : 'role_oid asc,role_addtime desc';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['role_status'] = array('eq', 1);
    if (!empty($tag['ids'])) {
        $where['role_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['role_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['role_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['vcid'])) {
        $cids = explode(',', trim($tag['vcid']));
        if (count($cids) > 1) {
            $where['vod_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['vod_cid'] = getlistsqlin($tag['vcid']);
        }
    }
    if (!empty($tag['vid'])) {
        $where['role_vid'] = $tag['vid'];
    }
    if (!empty($tag['name'])) {
        $where['role_name'] = array('eq', $tag['name']);
    }
    if (!empty($tag['starname'])) {
        $where['role_star'] = array('eq', $tag['starname']);
    }
    if (!empty($tag['tj'])) {
        $tjinfo = explode('|', trim($tag['tj']));
        if ($tjinfo[3]) {
            $where[$tjinfo[3]] = array($tjinfo[1], $tjinfo[2]);
        } else {
            $where[$tjinfo[0]] = array($tjinfo[1], $tjinfo[2]);
        }
    }
    if (!empty($tag['stars'])) {
        $where['role_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['role_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['role_hits'] = array('gt', $hits[0]);
        }
    }
    $rs = db('role')->alias('r')->join('vod v', 'v.vod_id = r.role_vid', 'LEFT')->join('actor a', 'a.actor_vid = r.role_vid', 'LEFT')->join('star s', 's.star_name = r.role_star', 'LEFT');
    if (!empty($params['page'])) {
        $count = model('role')->where($where)->count('role_id');
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], $count, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['role_cid'];
        $data[$key]['list_name'] = getlistname($val['role_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['role_cid'], 'list_url');
        $data[$key]['role_readurl'] = xianyu_data_url('home/role/read', array('id' => $val['role_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['role_cid'], 'dir' => getlistname($val['role_cid'], 'list_dir')));
        $data[$key]['role_picurl'] = xianyu_img_url($val['role_pic']);
        $data[$key]['actor_readurl'] = xianyu_data_url('home/actor/read', array('id' => $val['actor_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['actor_cid'], 'dir' => getlistname($val['actor_cid'], 'list_dir')));
        $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
        $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
        $data[$key]['vod_bigpicurl'] = xianyu_img_url($val['vod_bigpic']);
        $data[$key]['vod_picurl_small'] = xianyu_img_smallurl($val['vod_pic']);
        if (!empty($val['star_id'])) {
            $data[$key]['star_readurl'] = xianyu_data_url('home/star/read', array('id' => $val['star_id'], 'pinyin' => $val['star_letters'], 'cid' => $val['star_cid'], 'dir' => getlistname($val['star_cid'], 'list_dir'), 'jumpurl' => $val['star_jumpurl']));
            $data[$key]['star_picurl'] = xianyu_img_url($val['star_pic']);
        } else {
            $data[$key]['star_picurl'] = xianyu_img_url(false);
            $data[$key]['star_readurl'] = xianyu_data_url('home/search/index', array('wd' => urlencode(trim($val['role_star']))));
        }
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_role')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询相关标签节目
function xianyu_mysql_tv($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['tv_status'] = array('eq', 1);
    if (!empty($tag['ids'])) {
        $where['tv_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['tv_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['tv_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['day'])) {
        $where['tv_addtime'] = array('gt', getxtime($tag['day']));
    }
    if (!empty($tag['stars'])) {
        $where['tv_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['letter'])) {
        $letter = explode(',', $tag['letter']);
        if (count($letter) > 1) {
            $where['tv_letter'] = array('in', $tag['letter']);
        } else {
            $where['tv_letter'] = array('eq', $letter[0]);
        }
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['tv_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['tv_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['no'])) {
        $where['tv_id'] = array('neq', $tag['no']);
    }
    if (!empty($tag['name'])) {
        $where['tv_name'] = array('like', '%' . $tag['name'] . '%');
    }
    if (!empty($tag['wd'])) {
        $where['tv_name|tv_content|tv_data'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['gold'])) {
        $gold = explode(',', $tag['gold']);
        if (count($gold) > 1) {
            $where['tv_gold'] = array('between', $gold[0] . ',' . $gold[1]);
        } else {
            $where['tv_gold'] = array('gt', $gold[0]);
        }
    }
    if (!empty($tag['golder'])) {
        $golder = explode(',', $tag['golder']);
        if (count($golder) > 1) {
            $where['tv_golder'] = array('between', $golder[0] . ',' . $golder[1]);
        } else {
            $where['tv_golder'] = array('gt', $golder[0]);
        }
    }
    if (!empty($tag['prty'])) {
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $where['prty_sid'] = 7;
        $rs = db('prty')->alias('p')->join('tv t', 't.tv_id = p.prty_id', 'LEFT');
    } else {
        $rs = db('tv');
    }
    if (!empty($params['page'])) {
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    $week = str_replace('0', '7', date("w"));
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['tv_cid'];
        $data[$key]['list_name'] = getlistname($val['tv_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['tv_cid'], 'list_url');
        $data[$key]['tv_readurl'] = xianyu_data_url('home/tv/read', array('id' => $val['tv_id'], 'pinyin' => $val['tv_letters'], 'cid' => $val['tv_cid'], 'dir' => getlistname($val['tv_cid'], 'list_dir')));
        $data[$key]['tv_picurl'] = xianyu_img_url($val['tv_pic']);
        $data[$key]['tv_icourl'] = xianyu_img_url($val['tv_pic']);
        if (!empty($tag['data'])) {
            $data[$key]['tv_datalist'] = model('tv')->tv_week($val['tv_data'], $week, $tag['zhibo']);
            unset($data[$key]['tv_data']);
        }
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_tv')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

function xianyu_mysql_vod_tv($tag)
{
    $tag = param_lable($tag);
    return model('tv')->vod_tv($tag);
}

//循环查询相关标签幻灯片
function xianyu_mysql_slide($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : 's.*,vod_id,vod_letters,vod_cid,vod_title,vod_continu,vod_content,vod_pic,vod_aliases,vod_mcid,vod_actor,vod_director,vod_bigpic,vod_diantai,vod_tvcont,vod_tvexp,vod_area,vod_language,vod_year,vod_total,vod_addtime,vod_filmtime,vod_golder,vod_jumpurl';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {

            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['slide_status'] = array('eq', 1);
    if (!empty($tag['cid'])) {
        $where['slide_cid'] = array('in', $tag['cid']);
    }
    if (!empty($tag['nocid'])) {
        $where['slide_cid'] = array('neq', $tag['nocid']);
    }
    $rs = db('slide')->alias('s')->join('vod v', 'v.vod_id = s.slide_vid', 'LEFT');
    $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();
    foreach ($data as $key => $val) {
        $data[$key]['slide_picurl'] = xianyu_img_url($val['slide_pic']);
        $data[$key]['slide_logourl'] = xianyu_img_url($val['slide_logo']);
        if ($data[$key]['vod_id']) {
            $data[$key]['vod_readurl'] = xianyu_data_url('home/vod/read', array('id' => $val['vod_id'], 'pinyin' => $val['vod_letters'], 'cid' => $val['vod_cid'], 'dir' => getlistname($val['vod_cid'], 'list_dir'), 'jumpurl' => $val['vod_jumpurl']));
            $data[$key]['vod_picurl'] = xianyu_img_url($val['vod_pic']);
        }
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_slide')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询相关标签友情连接
function xianyu_mysql_link($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['link_status'] = array('eq', 1);
    if (!empty($tag['type'])) {
        $where['link_type'] = array('in', $tag['type']);
    }
    $rs = db('link')->field($tag['field']);
    $data = $rs->where($where)->limit($params['limit'])->order(trim($params['order']))->select();
    foreach ($data as $key => $val) {
        $data[$key]['link_logo'] = xianyu_img_url($data[$key]['link_logo']);
    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_link')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

//循环查询相关标签专题
function xianyu_mysql_special($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $params = mysql_param($tag);
    //优先从缓存调用数据及分页变量
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        $data_cache_content = Cache::get($params['cache_name']);
        if ($data_cache_content) {
            return $data_cache_content;
        }
    }
    $where = array();
    //根据参数生成查询条件
    $where['special_status'] = array('eq', 1);
    if (!empty($tag['ids'])) {
        $where['special_id'] = array('in', $tag['ids']);
    }
    if (!empty($tag['cid'])) {
        $cids = explode(',', trim($tag['cid']));
        if (count($cids) > 1) {
            $where['special_cid'] = array('in', getlistarr_tag($cids));
        } else {
            $where['special_cid'] = getlistsqlin($tag['cid']);
        }
    }
    if (!empty($tag['day'])) {
        $where['special_addtime'] = array('gt', getxtime($tag['day']));
    }
    if (!empty($tag['stars'])) {
        $where['special_stars'] = array('in', $tag['stars']);
    }
    if (!empty($tag['hits'])) {
        $hits = explode(',', $tag['hits']);
        if (count($hits) > 1) {
            $where['special_hits'] = array('between', $hits[0] . ',' . $hits[1]);
        } else {
            $where['special_hits'] = array('gt', $hits[0]);
        }
    }
    if (!empty($tag['name'])) {
        $where['special_name'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['wd'])) {
        $where['special_name|special_content'] = array('like', '%' . $tag['wd'] . '%');
    }
    if (!empty($tag['gold'])) {
        $gold = explode(',', $tag['gold']);
        if (count($gold) > 1) {
            $where['special_gold'] = array('between', $gold[0] . ',' . $gold[1]);
        } else {
            $where['special_gold'] = array('gt', $gold[0]);
        }
    }
    if (!empty($tag['golder'])) {
        $golder = explode(',', $tag['golder']);
        if (count($golder) > 1) {
            $where['special_golder'] = array('between', $golder[0] . ',' . $golder[1]);
        } else {
            $where['special_golder'] = array('gt', $golder[0]);
        }
    }
    if (!empty($tag['prty'])) {
        $week = explode(',', $tag['prty']);
        if (count($week) > 1) {
            $where['prty_cid'] = array('in', $tag['prty']);
        } else {
            $where['prty_cid'] = array('eq', $tag['prty']);
        }
        $where['prty_sid'] = 10;
        $rs = db('prty')->alias('p')->join('special s', 's.special_id = p.prty_id', 'LEFT');
    } else {
        $rs = db('special');
    }
    if (!empty($params['page'])) {
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        $data[$key]['list_id'] = $val['special_cid'];
        $data[$key]['list_name'] = getlistname($val['special_cid'], 'list_name');
        $data[$key]['list_url'] = getlistname($val['special_cid'], 'list_url');
        $data[$key]['special_readurl'] = xianyu_data_url('home/special/read', array('id' => $val['special_id'], 'pinyin' => $val['special_letters'], 'cid' => $val['special_cid'], 'dir' => getlistname($val['special_cid'], 'list_dir')));
        $data[$key]['special_logo'] = xianyu_img_url($val['special_logo']);
        $data[$key]['special_banner'] = xianyu_img_url($val['special_banner']);

    }
    if (!empty($params['cache_name']) && !empty($params['cache_time'])) {
        Cache::tag('foreach_special')->set($params['cache_name'], $data, intval($params['cache_time']));
    }
    return $data;
}

function xianyu_mysql_visitors($tag)
{
    $tag = param_lable($tag);
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $params = mysql_param($tag);
    $where = array();
    //$where['islock'] = array('neq',1);
    if ($tag['userid']) {
        $where['visitors_userid'] = array('eq', $tag['userid']);
    }
    if ($tag['uid']) {
        $where['visitors_uid'] = array('eq', $tag['uid']);
    }
    return model('Visitors')->GetVisitors($params, $where);
}

//循环查询相关标签留言
function xianyu_mysql_gb($tag)
{
    $tag = param_lable($tag);
    // 查询字段
    $tag['field'] = !empty($tag['field']) ? $tag['field'] : '*';
    $params = mysql_param($tag);
    $where = array();
    //根据参数生成查询条件
    $where['gb_status'] = array('eq', 1);
    if (!empty($tag['uid'])) {
        $where['gb_uid'] = array('in', $tag['uid']);
    }
    if (!empty($tag['cid'])) {
        $where['gb_cid'] = array('in', $tag['cid']);
    }
    if (!empty($tag['vid'])) {
        $where['gb_vid'] = array('in', $tag['vid']);
    }
    $rs = db('gb')->alias('g')->join('user u', 'u.userid = g.gb_uid', 'LEFT');
    if (!empty($params['page'])) {
        $list = $rs->field($tag['field'])->where($where)->order(trim($params['order']))->paginate($params['limit'], false, ['page' => config('currentpage')]);
        $data = $list->all();
        if (!empty($data[0])) {
            $data[0]['page']['pagecount'] = $list->total();
            $data[0]['page']['totalpage'] = $list->lastPage();
            $data[0]['page']['currentpage'] = $list->currentPage();
            $data[0]['page']['pageprevurl'] = getpageprev(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//上一页连接地址
            $data[0]['page']['pagenexturl'] = getpagenext(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['totalpage'], config('home_pagenum'));//下一页连接地址
            $data[0]['page']['pageurl'] = getpage(config('model'), config('params'), $data[0]['page']['currentpage'], $data[0]['page']['pagecount'], $params['limit'], config('home_pagenum'));//数字分页
        }
    } else {
        $data = $rs->field($tag['field'])->where($where)->limit($params['limit'])->order(trim($params['order']))->select();

    }
    foreach ($data as $key => $val) {
        if ($val['userid']) {
            $userapi = db('user_api')->where('uid', $val['userid'])->find();
            $data[$key]['user_homeurl'] = xianyu_user_url('user/home/index', array('id' => $val['userid']));
            $data[$key]['user_imageurl'] = ps_getavatar($val['userid'], $val['pic'], $userapi['avatar']);
        }
    }
    return $data;
}

//循环查询相关标签会员
function xianyu_mysql_user()
{
    $field = 'userid,avatar,nickname';
    $limit = 9;
    $where['nickname'] = ['NEQ', ''];
    // 查询字段
    $data = Db::name('user')->field($field)->where($where)->limit($limit)->order('last_login_time desc')->select();
    return $data;
}

/*-------------------------------------------------相关连接获取函数开始------------------------------------------------------------------*/


// 获取影片最后一集array(sid,pid,jiname,jipath)
function play_url_end($vod_url, $vod_play)
{
    $player = F('_data/player');
    $arr_server = explode('$$$', trim($vod_url));
    $arr_player = explode('$$$', trim($vod_play));
    foreach ($arr_player as $key => $value) {
        //过滤不存在以及禁用的播放器
        if (empty($player[$value]) || $player[$value]['play_display'] == 0 || $value == "down") {
            unset($arr_player[$key]);    //不存在就销毁
            unset($arr_server[$key]);    //不存在就销毁
        } else {
            $arr_player[$key] = $arr_server[$key];
        }
    }
    if ($arr_player) {
        foreach ($arr_player as $key => $value) {
            $array[$key] = array(count(explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $value))), $key);
        }
        $max_key = max($array);
        $array = explode(chr(13), str_replace(array("\r\n", "\n", "\r"), chr(13), $arr_player[$max_key[1]]));
        $arr_url = explode('$', end($array));
        if ($arr_url[1]) {
            return array($max_key[1], $max_key[0], $arr_url[0], $arr_url[1]);
        } else {
            return array($max_key[1], $max_key[0], '第' . $max_key[0] . '集', $arr_url[0]);
        }
    } else {
        return false;
    }

}

//循环演员名称获取相关明星连接
function get_star_url($actor, $label = 'span', $k = '', $keyword = '')
{
    if (is_array($actor)) {
        $star_arr = $actor;
    } else {
        $star_arr = explode(',', trim(str_replace(array(',', '/', '，', '|', '、', ' '), ',', $actor)));
    }
    $list = db('star')->field('star_id,star_cid,star_name,star_letters,star_jumpurl,star_hits')->where('star_name', 'in', $star_arr)->select();
    if ($list) {
        foreach ($list as $key => $value) {
            $stararray[] = $value['star_name'];
        }
        $nostar = array_diff($star_arr, $stararray);
        $hestar = array_merge($stararray, $nostar);
    } else {
        $hestar = $star_arr;
    }
    if (!empty($hestar)) {
        foreach ($hestar as $key => $value) {
            if (!empty($k) && $key == $k) {
                break;
            }
            if ($keyword) {
                $value = get_hilight_ex($value, $keyword, 'font', 'red');
            }
            if (!empty($list[$key]['star_id'])) {
                if ($label == 'no') {
                    $urlarr[$key] = '<a href="' . xianyu_data_url('home/star/read', array('id' => $list[$key]['star_id'], 'pinyin' => $list[$key]['star_letters'], 'cid' => $list[$key]['star_cid'], 'dir' => getlistname($list[$key]['star_cid'], 'list_dir'), 'jumpurl' => $list[$key]['star_jumpurl'])) . '" target="_blank">' . trim($value) . '</a>';
                } elseif ($label == 'a') {
                    $urlarr[$key] = xianyu_data_url('home/star/read', array('id' => $list[$key]['star_id'], 'pinyin' => $list[$key]['star_letters'], 'cid' => $list[$key]['star_cid'], 'dir' => getlistname($list[$key]['star_cid'], 'list_dir'), 'jumpurl' => $list[$key]['star_jumpurl']));
                } elseif ($label == 'n') {
                    $urlarr[$key] = $value;
                } else {
                    $urlarr[$key] = '<' . $label . '><a href="' . xianyu_data_url('home/star/read', array('id' => $list[$key]['star_id'], 'pinyin' => $list[$key]['star_letters'], 'cid' => $list[$key]['star_cid'], 'dir' => getlistname($list[$key]['star_cid'], 'list_dir'), 'jumpurl' => $list[$key]['star_jumpurl'])) . '" target="_blank">' . trim($value) . '</a></' . $label . '>';
                }
            } else {
                if ($label == 'no') {
                    $urlarr[$key] = '<a rel="nofollow" href="' . xianyu_url('home/search/index', array('wd' => urlencode(trim($value))), true, false) . '" target="_blank">' . trim($value) . '</a>';
                } elseif ($label == 'a') {
                    $urlarr[$key] = xianyu_url('home/search/index', array('wd' => urlencode(trim($value))), true, false);
                } else {
                    $urlarr[$key] = '<' . $label . '><a rel="nofollow" href="' . xianyu_url('home/search/index', array('wd' => urlencode(trim($value))), true, false) . '" target="_blank">' . trim($value) . '</a></' . $label . '>';
                }
            }
        }
        return implode('', $urlarr);
    }
}

//通过电视名获取连接
function get_vod_url($name, $type, $tvname = "")
{
    $where['vod_name'] = $name;
    $where['vod_status'] = array('eq', 1);
    $list = db('vod')->field('vod_id,vod_cid,vod_letters,vod_name,vod_jumpurl')->where($where)->limit(1)->order('vod_id desc')->cache(600)->find();
    if (empty($list)) {
        $where['vod_aliases'] = $name;
        $list = db('vod')->field('vod_id,vod_cid,vod_letters,vod_name,vod_jumpurl')->where($where)->limit(1)->order('vod_id desc')->cache(600)->find();
    }
    if (empty($list) && !empty($type)) {
        unset($where);
        $where['vod_name'] = array('like', $name . '%');
        $where['vod_status'] = array('eq', 1);
        $list = db('vod')->field('vod_id,vod_cid,vod_letters,vod_name,vod_jumpurl')->where($where)->limit(1)->order('vod_id desc')->cache(600)->find();
    }
    if (!empty($list)) {
        if (!empty($tvname)) {
            model('Vodtv')->tv_add($list['vod_id'], $tvname, 1);
        }
        return xianyu_data_url('home/vod/read', array('id' => $list['vod_id'], 'pinyin' => $list['vod_letters'], 'cid' => $list['vod_cid'], 'dir' => getlistname($list['vod_cid'], 'list_dir'), 'jumpurl' => $list['vod_jumpurl']));
    }
    return "";
}

//通过电视名获取连接
function get_tv_url($name, $label = 'span', $k = "")
{
    if (is_array($name)) {
        $names = $name;
    } else {
        $names = explode(',', trim($name));
    }
    $where['tv_name'] = array('in', $names);
    $list = db('tv')->field('tv_id,tv_cid,tv_letters,tv_name')->where($where)->select();
    $html = '';
    if (!empty($list)) {
        foreach ($list as $key => $value) {
            $url = xianyu_data_url('home/tv/read', array('id' => $list[$key]['tv_id'], 'pinyin' => $list[$key]['tv_letters'], 'cid' => $list[$key]['tv_cid'], 'dir' => getlistname($list[$key]['tv_cid'], 'list_dir')));
            if ($label == "no") {
                $html .= "<a href='" . $url . "' target='_blank'>{$list[$key]['tv_name']}</a>";
            } elseif ($label == "url") {
                $html .= $url;
            } else {
                $html .= "<" . $label . "><a href='" . $url . "' target='_blank'>{$list[$key]['tv_name']}</a></" . $label . ">";
            }
        }
        return $html;
    } else {
        return $name;
    }

}

function tag_url($str, $sid, $k = "")
{
    if (is_array($str)) {
        $array = $str;
    } else {
        $str = explode(',', trim($str));
    }
    $mode = list_search(F('_data/modellist'), 'id=' . $sid);
    $html = '';
    foreach ($str as $key => $v) {
        if (!empty($k) && $key == $k) {
            break;
        }
        $url = xianyu_list_url('home/tag/show', array('id' => $sid, 'dir' => $mode[0]['name'], 'tag' => urlencode($v)));
        $html .= "<a href='" . $url . "' target='_blank'>{$v}</a> ";
    }
    return $html;
}

// 内容页MCID链接
function xianyu_mcid_replace($content, $array_tag, $cid, $sid = 1)
{
    $mcatarray = getlistmcat($cid);
    if (is_array($array_tag)) {
        $array_tag = $array_tag;
    } else {
        $array_tag = explode(',', trim($array_tag));
    }
    if ($array_tag) {
        foreach ($array_tag as $key => $value) {
            $arr = list_search($mcatarray, 'm_name=' . $value);
            $preg = '/' . str_replace('/', '\/', $value) . '/';
            $revalue = '<a href="' . xianyu_type_url('home/vod/type', array('id' => $cid, 'dir' => getlistname($cid, 'list_dir'), 'mcid' => $arr[0]['m_cid'], 'mename' => get_mcat_id($arr[0]['m_cid']), 'area' => "", 'earea' => "", 'year' => "", 'letter' => "", 'order' => "")) . '" target="_blank">' . $value . '</a>';
        }
        $content = preg_replace($preg, $revalue, $content, 1);
    }
    return $content;
}

// 小分类替换
function xianyu_tags_replace($content, $array_tag, $sid = 1)
{
    if (is_array($array_tag)) {
        $array_tag = $array_tag;
    } else {
        $array_tag = explode(',', trim($array_tag));
    }
    if ($array_tag) {
        foreach ($array_tag as $key => $value) {
            $preg = '/' . str_replace('/', '\/', $value) . '/';
            $revalue = tag_url($value, $sid);
        }
        $content = preg_replace($preg, $revalue, $content, 1);
    }
    return $content;
}

//演员页替换
function xianyu_actor_replace($content, $actor)
{
    if (empty($content) || empty($actor)) {
        return $content;
    }
    $array_actor = explode(',', $actor);
    if ($array_actor) {
        foreach ($array_actor as $key => $value) {
            $preg = '/' . str_replace('/', '\/', $value) . '/';
            $revalue = get_star_url($value, 'no');
            $content = preg_replace($preg, $revalue, $content, 1);
        }
        $content = preg_replace($preg, $revalue, $content, 1);
    }
    return $content;
}

//剧情页替换
function xianyu_role_replace($content, $vid, $pinyin)
{
    if (empty($content) || empty($vid)) {
        return $content;
    }
    $role = db('role')->where('role_vid', $vid)->field('role_id,role_name,role_star,role_cid')->select();
    if ($role) {
        foreach ($role as $key => $value) {
            $roleurl = xianyu_data_url('home/role/read', array('id' => $value['role_id'], 'pinyin' => $pinyin, 'cid' => $value['role_cid'], 'dir' => getlistname($value['role_cid'], 'list_dir')));
            $role[$key] = '<role><a target="_blank" href="' . $roleurl . '" title="' . $value['role_star'] . '饰' . $value['role_name'] . '">' . $value['role_name'] . '</a></role><star>(' . get_star_url($value['role_star'], 'no') . '饰)</star>';
            // $star='<star>('.get_star_url($value['role_star'],'no').'饰)</star>';
            $pregs[$key] = '/' . str_replace(array('/', '（', '）', '(', ')'), array('\/', '', '', '', ''), $value['role_name']) . '/';

        }
        $content = preg_replace($pregs, $role, $content, 1);
    }
    return $content;
}

function get_emots($content = false)
{
    if (!$content) {
        return false;
    }
    $ture = preg_split("/\[.*?\]/", $content);
    if (count($ture) > 0) {
        $emots = F('_data/list_emots');
        $site_cssjsurl = config('site_cssjsurl');
        $public = !empty($site_cssjsurl) ? $site_cssjsurl : config('site_path') . PUBLIC_PATH . 'tpl/';
        foreach ($emots as $v) {
            $content = str_replace("[" . $v['emot_name'] . "]", "<img alt='" . $v['emot_name'] . "' src='" . $public . "admin/emot/" . ($v['emot_id'] - 1) . ".gif'>", $content);
        }
    }
    return $content;
}


//获取用户头像
function ps_getavatar($uid, $pic = "", $avatar = "", $size = "")
{
    $session = session('user_auth');
    if (!is_numeric($uid)) {
        $uid = $session['userid'];
        if (empty($uid)) {
            return '';
        }
    }
    if ($pic) {
        //判断 是否有图像
        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        $typeadd = $type == 'real' ? '_real' : '';
        $url = config('site_path') . config('upload_path') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . $typeadd . "_avatar_";
        $rand = "?random=" . time();
        $avatar_array = array('big' => $url . 'big.jpg' . $rand, 'middle' => $url . 'middle.jpg' . $rand, 'small' => $url . 'small.jpg' . $rand);
    } elseif ($avatar) {
        $avatar_array = array('big' => $avatar . $rand, 'middle' => $avatar . $rand, 'small' => $avatar . $rand);
    } else {
        $avatar_array = array('big' => config('site_path') . PUBLIC_PATH . 'tpl/admin/noavatar_big.gif', 'middle' => config('site_path') . PUBLIC_PATH . 'tpl/admin/noavatar_middle.gif', 'small' => config('site_path') . PUBLIC_PATH . 'tpl/admin/noavatar_small.gif');
    }
    if ($size) {
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
        return $avatar_array[$size];
    } else {
        return $avatar_array;
    }
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function user_islogin()
{

    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['userid'] : 0;
    }
}

/**
 * 数据签名认证
 * @param array $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

function get_small_id($id)
{
    if ($id) {
        return floor($id / 1000);
    }
}

function get_small_vod_id($name)
{
    if ($name) {
        $id = get_vod_info($name, 'vod_letters', 'vod_id');
        return get_small_id($id);
    }
}

function get_vod_id($name)
{
    if ($name) {
        $id = get_vod_info($name, 'vod_letters', 'vod_id');
        return $id;
    }
}

function get_list_id($name)
{
    if ($name) {
        $id = getlist($name, 'list_dir', 'list_id');
        return $id;
    }
}

function get_small_list_id($name)
{
    if ($name) {
        $id = getlist($name, 'list_dir', 'list_id');
        return get_small_id($id);
    }
}

function get_small_story_id($name)
{
    if ($name) {
        $id = get_story_info($name, 'story_id', 'story_vid');
        return get_small_id($id);
    }
}

function get_story_id($name)
{
    if ($name) {
        $id = get_story_info($name, 'story_id', 'story_vid');
        return $id;
    }
}

function get_small_actor_id($name)
{
    if ($name) {
        $id = get_actor_info($name, 'actor_id', 'actor_vid');
        return get_small_id($id);
    }
}

function get_actor_id($name)
{
    if ($name) {
        $id = get_actor_info($name, 'actor_id', 'actor_vid');
        return $id;
    }
}

function get_small_role_id($name)
{
    if ($name) {
        $id = get_role_info($name, 'role_id', 'role_vid');
        return get_small_id($id);
    }
}

function get_role_id($name)
{
    if ($name) {
        $id = get_role_info($name, 'role_id', 'role_vid');
        return $id;
    }
}

function get_small_star_id($name)
{
    if ($name) {
        $id = get_star_info($name, 'star_letters', 'star_id');
        return get_small_id($id);
    }
}

function get_star_id($name)
{
    if ($name) {
        $id = get_star_info($name, 'star_letters', 'star_id');
        return $id;
    }
}

function get_small_tv_id($name)
{
    if ($name) {
        $id = get_tv_info($name, 'tv_letters', 'tv_id');
        return get_small_id($id);
    }
}

function get_tv_id($name)
{
    if ($name) {
        $id = get_tv_info($name, 'tv_letters', 'tv_id');
        return $id;
    }
}

function get_small_special_id($name)
{
    if ($name) {
        $id = get_special_info($name, 'special_letters', 'special_id');
        return get_small_id($id);
    }
}

function get_special_id($name)
{
    if ($name) {
        $id = get_special_info($name, 'special_letters', 'special_id');
        return $id;
    }
}

function adminTreeMenu($array, $pid, $itemtpl, $selectedids = '', $disabledids = '', $wraptag = 'ul', $wrapattr = '', $deeplevel = 0)
{
    $str = '';
    $childs = getMenuid($array, $pid);
    if ($childs) {
        foreach ($childs as $value) {
            $id = $value['id'];
            unset($value['child']);
            $selected = in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ? 'selected' : '';
            $disabled = in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ? 'disabled' : '';
            $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
            $value = array_combine(array_map(function ($k) {
                return '@' . $k;
            }, array_keys($value)), $value);
            $bakvalue = array_intersect_key($value, array_flip(['@url', '@caret', '@class']));
            $value = array_diff_key($value, $bakvalue);
            $nstr = strtr($itemtpl, $value);
            $value = array_merge($value, $bakvalue);
            $childdata = adminTreeMenu($array, $id, $itemtpl, $selectedids, $disabledids, $wraptag, $wrapattr, $deeplevel + 1);
            $childlist = $childdata ? "<{$wraptag} {$wrapattr}>" . $childdata . "</{$wraptag}>" : "";
            $childlist = strtr($childlist, array('@class' => $childdata ? 'last' : ''));
            $value = array(
                '@childlist' => $childlist,
                '@url' => $childdata || !isset($value['@url']) ? "javascript:;" : $value['@url'],
                '@caret' => ($childdata && (!isset($value['@badge']) || !$value['@badge']) ? '<i class="fa fa-angle-left"></i>' : ''),
                '@badge' => isset($value['@badge']) ? $value['@badge'] : '',
                '@class' => ($selected ? ' active' : '') . ($disabled ? ' disabled' : '') . ($childdata ? ' treeview' : ''),
            );
            $str .= strtr($nstr, $value);
        }
    }
    return $str;
}

/**
 * 字符串加密、解密函数
 *
 *
 * @param string $txt 字符串
 * @param string $operation ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
 * @param string $key 密钥：数字、字母、下划线
 * @param string $expiry 过期时间
 * @return    string
 */
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
{
    $key_length = 4;
    //$key = md5($key != '' ? $key : pc_base::load_config('system', 'auth_key'));
    $fixedkey = md5($key);
    $egiskeys = md5(substr($fixedkey, 16, 16));
    $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
    $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
    $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));

    $i = 0;
    $result = '';
    $string_length = strlen($string);
    for ($i = 0; $i < $string_length; $i++) {
        $result .= chr(ord($string[$i]) ^ ord($keys[$i % 32]));
    }
    if ($operation == 'ENCODE') {
        return $runtokey . str_replace('=', '', base64_encode($result));
    } else {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}

function checkRule($rule, $type = AuthRule::rule_url, $mode = 'url')
{
    static $Auth = null;
    if (!$Auth) {
        $Auth = new \com\Auth();
    }
    if (!$Auth->check($rule, session('member_auth.uid'), $type, $mode)) {
        return false;
    }
    return true;
}

function parse_field_attr($string)
{
    if (is_array($string)) {
        return $string;
    }
    if (0 === strpos($string, ':')) {
        // 采用函数定义
        return eval('return' . substr($string, 1) . ';');
    } elseif (0 === strpos($string, '[')) {
        // 支持读取配置参数（必须是数组类型）
        return config(substr($string, 1, -1));
    }

    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

function baidutui($urls, $type = 'add', $data = 1)
{
    $config = F('_data/sendurl');
    if (empty($config['sendurl']['baidu_token']) && empty($config['sendurl']['xiongzhang_appid']) && empty($config['sendurl']['xiongzhang_token'])) {
        return false;
    }
    if (empty($config['sendurl']['sendurl_data']) && $data == 1) {
        return false;
    }
    if (empty($config['sendurl']['sendurl_caiji']) && $data == 2) {
        return false;
    }
    if (is_array($urls)) {
        $urls = $urls;
    } else {
        $urls = array($urls);
    }
    if ($type == 'add') {
        $types = "添加";
        $bidutui = "http://data.zz.baidu.com/urls?site=" . config('site_url') . "&token=" . $config['sendurl']['baidu_token'];
        $xiongzhang = "http://data.zz.baidu.com/urls?appid=" . $config['sendurl']['xiongzhang_appid'] . "&token=" . $config['sendurl']['xiongzhang_token'] . "&type=realtime";
    } else {
        $types = "更新";
        $bidutui = "http://data.zz.baidu.com/urls?site=" . config('site_url') . "&token=" . $config['sendurl']['baidu_token'];
        $xiongzhang = "http://data.zz.baidu.com/urls?appid=" . $config['sendurl']['xiongzhang_appid'] . "&token=" . $config['sendurl']['xiongzhang_token'] . "&type=batch";
    }
    if ($config['sendurl']['sendurl_type'] == 3 || $config['sendurl']['sendurl_type'] == 1) {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $bidutui,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $success = json_decode($result, true);
        curl_close($ch);
        if ($success['error']) {
            $msg .= "百度" . $types . "失败" . $success['message'] . "&nbsp;&nbsp;";
        } else {
            $msg .= "百度推送" . $types . "<font color=red>" . $success['success'] . "</font>条成功&nbsp;&nbsp;剩余<font color=red>" . $success['remain'] . "</font>条";
        }
    }
    if ($config['sendurl']['sendurl_type'] == 3 || $config['sendurl']['sendurl_type'] == 2) {
        $chs = curl_init();
        $optionss = array(
            CURLOPT_URL => $xiongzhang,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($chs, $optionss);
        $results = curl_exec($chs);
        $successs = json_decode($results, true);
        curl_close($chs);
        if ($type == 'add') {
            if ($successs['error']) {
                $msg .= "熊掌推送失败" . $successs['message'] . "&nbsp;&nbsp;";
            } else {
                $msg .= "熊掌推送新增<font color=red>" . $successs['success_realtime'] . "</font>条成功&nbsp;&nbsp;剩余<font color=red>" . $successs['remain_realtime'] . "</font>条";
            }
        } else {
            if ($successs['error']) {
                $msg .= "熊掌推送历史内容失败" . $successs['message'] . "&nbsp;&nbsp;";
            } else {
                $msg .= "熊掌推送历史内容<font color=red>" . $successs['success_batch'] . "</font>条成功&nbsp;&nbsp;剩余<font color=red>" . $successs['remain_batch'] . "</font>条";
            }
        }
    }
    return $msg;
}

/*    function content_replace($data)
{

	//优先读取缓存数据
	$array = db('keylink')->where("status=1")->limit(10)->select();
	foreach($array as $k=>$v){
			$str = $v['keyword'];
			$newStr = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $str);
			$zifu = mb_strlen($newStr,"utf-8");

			if($zifu > 2 ){
				$data = str_replace($v['keyword'],$v['link'],$data);
                $data = preg_replace("/title=\"<a[^>]*>(.*?)<\/a>(.*?)\"/is", "title=\"$1.$2\"", $data);
				$data = preg_replace("/alt=\"<a[^>]*>(.*?)<\/a>(.*?)\"/is", "alt=\"$1.$2\"", $data);
			}
		}

		return $data;
}*/

// 生成token
function createToken($str)
{
    $tokenSalt = md5(uniqid(md5(microtime(true))), true);
    return sha1($tokenSalt . $str);
}

// 生成盐
function salt($len)
{
    //盐字符集
    $chars = 'abcdefghijklmnopqrstuvwxsyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567898';
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}


/**
 * 生成用户 token
 * @param $userId
 * @param $deviceType
 * @return string 用户 token
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function cmf_generate_user_token($userId, $deviceType)
{
    $userTokenQuery = Db::name("user_token")
        ->where('user_id', $userId)
        ->where('device_type', $deviceType);
    $findUserToken = $userTokenQuery->find();
    $currentTime = time();
    $expireTime = $currentTime + 24 * 3600 * 180;
    $token = md5(uniqid()) . md5(uniqid());
    if (empty($findUserToken)) {
        Db::name("user_token")->insert([
            'token' => $token,
            'user_id' => $userId,
            'expire_time' => $expireTime,
            'create_time' => $currentTime,
            'device_type' => $deviceType
        ]);
    } else {
        if ($findUserToken['expire_time'] > time() && !empty($findUserToken['token'])) {
            $token = $findUserToken['token'];
        } else {
            Db::name("user_token")
                ->where('user_id', $userId)
                ->where('device_type', $deviceType)
                ->update([
                    'token' => $token,
                    'expire_time' => $expireTime,
                    'create_time' => $currentTime
                ]);
        }

    }

    return $token;
}

/**
 * 更新当前登录前台用户的信息
 * @param array $user 前台用户的信息
 */
function cmf_update_current_user($user)
{
    session('user', $user);
}

/**
 * 获取当前登录前台用户id
 * @return int
 */
function cmf_get_current_user_id()
{
    $sessionUserId = session('user.userid');
    if (empty($sessionUserId)) {
        return 0;
    }

    return $sessionUserId;
}

/**
 * 获取当前登录的前台用户的信息，未登录时，返回false
 * @return array|boolean
 */
function cmf_get_current_user()
{
    $sessionUser = session('user');
    if (!empty($sessionUser)) {
        unset($sessionUser['password']); // 销毁敏感数据
        return $sessionUser;
    } else {
        return false;
    }
}

/**
 * 判断前台用户是否登录
 * @return boolean
 */
function cmf_is_user_login()
{
    $sessionUser = session('user');
    return !empty($sessionUser);
}

/**
 * 根据会员id获取用户头像
 * @return boolean
 */
function cmf_get_user_avatar($id)
{
    return Db::name('user')->where('userid', $id)->value('avatar');
}

/**
 * 验证码检查，验证完后销毁验证码
 * @param string $value 要验证的字符串
 * @param string $id 验证码的ID
 * @param bool $reset 验证成功后是否重置
 * @return bool
 */
function cmf_captcha_check($value, $id = "", $reset = true)
{
    $captcha = new Captcha();
    return $captcha->check($value, $id);
}

/**
 * 检查手机格式，中国手机不带国家代码，国际手机号格式为：国家代码-手机号
 * @param $mobile
 * @return bool
 */
function cmf_check_mobile($mobile)
{
    if (preg_match('/(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$/', $mobile)) {
        return true;
    } else {
        if (preg_match('/^\d{1,4}-\d{5,11}$/', $mobile)) {
            if (preg_match('/^\d{1,4}-0+/', $mobile)) {
                //不能以0开头
                return false;
            }

            return true;
        }

        return false;
    }
}

/**
 * CMF密码加密方法
 * @param string $pw 要加密的原始密码
 * @param string $authCode 加密字符串
 * @return string
 */
function cmf_password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = 'XV8nBx1Awgf9FdfVfm';
    }
    $result = "###" . md5(md5($authCode . $pw));
    return $result;
}

/**
 * CMF密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $passwordInDb 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function cmf_compare_password($password, $passwordInDb)
{

    return cmf_password($password) == $passwordInDb;

}

/**
 * 添加钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */

/**
 * 添加钩子,只执行一个
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return mixed
 */
function hook_one($hook, $params = null)
{
    return \think\Hook::listen($hook, $params, true);
}


/**
 * 用户操作记录
 * @param string $action 用户操作
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function cmf_user_action($action)
{
    $userId = cmf_get_current_user_id();

    if (empty($userId)) {
        return;
    }

    $findUserAction = Db::name('user_action')->where('action', $action)->find();

    if (empty($findUserAction)) {
        return;
    }

    $changeScore = false;

    if ($findUserAction['cycle_type'] == 0) {
        $changeScore = true;
    } elseif ($findUserAction['reward_number'] > 0) {
        $findUserScoreLog = Db::name('user_score_log')->order('create_time DESC')->find();
        if (!empty($findUserScoreLog)) {
            $cycleType = intval($findUserAction['cycle_type']);
            $cycleTime = intval($findUserAction['cycle_time']);
            switch ($cycleType) {//1:按天;2:按小时;3:永久
                case 1:
                    $firstDayStartTime = strtotime(date('Y-m-d', $findUserScoreLog['create_time']));
                    $endDayEndTime = strtotime(date('Y-m-d', strtotime("+{$cycleTime} day", $firstDayStartTime)));
//                    $todayStartTime        = strtotime(date('Y-m-d'));
//                    $todayEndTime          = strtotime(date('Y-m-d', strtotime('+1 day')));
                    $findUserScoreLogCount = Db::name('user_score_log')->where([
                        'user_id' => $userId,
                        'create_time' => [['gt', $firstDayStartTime], ['lt', $endDayEndTime]]
                    ])->count();
                    if ($findUserScoreLogCount < $findUserAction['reward_number']) {
                        $changeScore = true;
                    }
                    break;
                case 2:
                    if (($findUserScoreLog['create_time'] + $cycleTime * 3600) < time()) {
                        $changeScore = true;
                    }
                    break;
                case 3:

                    break;
            }
        } else {
            $changeScore = true;
        }
    }

    if ($changeScore) {
        Db::name('user_score_log')->insert([
            'user_id' => $userId,
            'create_time' => time(),
            'action' => $action,
            'score' => $findUserAction['score'],
            'coin' => $findUserAction['coin'],
        ]);

        $data = [];
        if ($findUserAction['score'] > 0) {
            $data['score'] = Db::raw('score+' . $findUserAction['score']);
        }

        if ($findUserAction['score'] < 0) {
            $data['score'] = Db::raw('score-' . abs($findUserAction['score']));
        }

        if ($findUserAction['coin'] > 0) {
            $data['coin'] = Db::raw('coin+' . $findUserAction['coin']);
        }

        if ($findUserAction['coin'] < 0) {
            $data['coin'] = Db::raw('coin-' . abs($findUserAction['coin']));
        }

        Db::name('user')->where('userid', $userId)->update($data);

    }


}

/**
 * 检查手机或邮箱是否还可以发送验证码,并返回生成的验证码
 * @param string $account 手机或邮箱
 * @param integer $length 验证码位数,支持4,6,8
 * @return string 数字验证码
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function cmf_get_verification_code($account, $length = 6)
{
    if (empty($account)) return false;
    $verificationCodeQuery = Db::name('verification_code');
    $currentTime = time();
    $maxCount = 5;
    $findVerificationCode = $verificationCodeQuery->where('account', $account)->find();
    $result = false;
    if (empty($findVerificationCode)) {
        $result = true;
    } else {
        $sendTime = $findVerificationCode['send_time'];
        $todayStartTime = strtotime(date('Y-m-d', $currentTime));
        if ($sendTime < $todayStartTime) {
            $result = true;
        } else if ($findVerificationCode['count'] < $maxCount) {
            $result = true;
        }
    }

    if ($result) {
        switch ($length) {
            case 4:
                $result = rand(1000, 9999);
                break;
            case 6:
                $result = rand(100000, 999999);
                break;
            case 8:
                $result = rand(10000000, 99999999);
                break;
            default:
                $result = rand(100000, 999999);
        }
    }

    return $result;
}

/**
 * 更新手机或邮箱验证码发送日志
 * @param string $account 手机或邮箱
 * @param string $code 验证码
 * @param int $expireTime 过期时间
 * @return int|string
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function cmf_verification_code_log($account, $code, $expireTime = 0)
{
    $currentTime = time();
    $expireTime = $expireTime > $currentTime ? $expireTime : $currentTime + 30 * 60;

    $findVerificationCode = Db::name('verification_code')->where('account', $account)->find();

    if ($findVerificationCode) {
        $todayStartTime = strtotime(date("Y-m-d"));//当天0点
        if ($findVerificationCode['send_time'] <= $todayStartTime) {
            $count = 1;
        } else {
            $count = Db::raw('count+1');
        }
        $result = Db::name('verification_code')
            ->where('account', $account)
            ->update([
                'send_time' => $currentTime,
                'expire_time' => $expireTime,
                'code' => $code,
                'count' => $count
            ]);
    } else {
        $result = Db::name('verification_code')
            ->insert([
                'account' => $account,
                'send_time' => $currentTime,
                'code' => $code,
                'count' => 1,
                'expire_time' => $expireTime
            ]);
    }

    return $result;
}

/**
 * 手机或邮箱验证码检查，验证完后销毁验证码增加安全性,返回true验证码正确，false验证码错误
 * @param string $account 手机或邮箱
 * @param string $code 验证码
 * @param boolean $clear 是否验证后销毁验证码
 * @return string  错误消息,空字符串代码验证码正确
 * @return string
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function cmf_check_verification_code($account, $code, $clear = false)
{

    $findVerificationCode = Db::name('verification_code')->where('account', $account)->find();
    if ($findVerificationCode) {
        if ($findVerificationCode['expire_time'] > time()) {

            if ($code == $findVerificationCode['code']) {
                if ($clear) {
                    Db::name('verification_code')->where('account', $account)->update(['code' => '']);
                }
            } else {
                return "验证码不正确!";
            }
        } else {
            return "验证码已经过期,请先获取验证码!";
        }

    } else {
        return "请先获取验证码!";
    }

    return "";
}

/**
 * 清除某个手机或邮箱的数字验证码,一般在验证码验证正确完成后
 * @param string $account 手机或邮箱
 * @return boolean true：手机验证码正确，false：手机验证码错误
 * @throws \think\Exception
 * @throws \think\exception\PDOException
 */
function cmf_clear_verification_code($account)
{
    $verificationCodeQuery = Db::name('verification_code');
    $result = $verificationCodeQuery->where('account', $account)->update(['code' => '']);
    return $result;
}


/**
 * 发送邮件
 * @param string $address 收件人邮箱
 * @param string $subject 邮件标题
 * @param string $message 邮件内容
 */
function cmf_send_email($address = '', $subject = '', $message = '')
{
    $userMailConfig = F('_data/userconfig_cache');
    $config = [
        'driver' => 'smtp', // 邮件驱动, 支持 smtp|sendmail|mail 三种驱动
        'host' => $userMailConfig['mail_smtp_host'], // SMTP服务器地址
        'port' => $userMailConfig['mail_smtp_port'], // SMTP服务器端口号,一般为25
        'addr' => $userMailConfig['mail_smtp_user'], // 发件邮箱地址
        'pass' => $userMailConfig['mail_smtp_pass'], // 发件邮箱密码
        'name' => 'i7', // 发件邮箱名称
        'content_type' => 'text/html', // 默认文本内容 text/html|text/plain
        'charset' => 'utf-8', // 默认字符集
        'security' => 'ssl', // 加密方式 null|ssl|tls, QQ邮箱必须使用ssl
        'sendmail' => '/usr/sbin/sendmail -bs', // 不适用 sendmail 驱动不需要配置
        'debug' => true, // 开启debug模式会直接抛出异常, 记录邮件发送日志
        'left_delimiter' => '{', // 模板变量替换左定界符, 可选, 默认为 {
        'right_delimiter' => '}', // 模板变量替换右定界符, 可选, 默认为 }
        'log_driver' => '', // 日志驱动类, 可选, 如果启用必须实现静态 public static function write($content, $level = 'debug') 方法
        'log_path' => '', // 日志路径, 可选, 不配置日志驱动时启用默认日志驱动, 默认路径是 /path/to/think-mailer/log, 要保证该目录有可写权限, 最好配置自己的日志路径
        'embed' => 'embed:', // 邮件中嵌入图片元数据标记
    ];
    MailerConfig::init($config);
    $mailer = Mailer::instance();
    $data = $mailer->from($userMailConfig['mail_smtp_user'], 'service')
        ->to($address)
        ->subject($subject)
        ->text($message)
        ->send();
    if (!$data) {
        return ["code" => 0, "message" => '发送失败'];
    } else {
        return ["code" => 1, "message" => "发送成功"];
    }
}

/**
 * 获取系统配置，通用
 * @param string $key 配置键值,都小写
 * @return array
 */
function cmf_get_option($key)
{
    if (!is_string($key) || empty($key)) {
        return [];
    }

    if (PHP_SAPI != 'cli') {
        static $cmfGetOption;

        if (empty($cmfGetOption)) {
            $cmfGetOption = [];
        } else {
            if (!empty($cmfGetOption[$key])) {
                return $cmfGetOption[$key];
            }
        }
    }

    $optionValue = cache('cmf_options_' . $key);

    if (empty($optionValue)) {
        $optionValue = Db::name('option')->where('option_name', $key)->value('option_value');
        if (!empty($optionValue)) {
            $optionValue = json_decode($optionValue, true);

            cache('cmf_options_' . $key, $optionValue);
        }
    }

    $cmfGetOption[$key] = $optionValue;

    return $optionValue;
}
