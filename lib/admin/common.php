<?php


function getdomain($_arg_0)
{
    $_var_1 = (require APP_PATH . "common/library/Domain.php");
    $_var_2 = explode(".", $_arg_0);
    $_var_3 = '';
    $_var_4 = 0;
    $_var_5 = count($_var_2) - 1;
    while ($_var_5 >= 0) {
        if ($_var_5 == 0) {
            break;
        }
        if (in_array($_var_2[$_var_5], $_var_1)) {
            $_var_4 = $_var_4 + 1;
            $_var_3 = "." . $_var_2[$_var_5] . $_var_3;
            if ($_var_4 >= 2) {
                break;
            }
        }
        $_var_5 = $_var_5 - 1;
    }
    $_var_3 = $_var_2[count($_var_2) - $_var_4 - 1] . $_var_3;
    return $_var_3;
}

function setMenu()
{
    $request = request();
//    print_r($request);
    $url = strtolower($request->module() . "/" . $request->controller() . "/" . $request->action());
    $param = $request->param();
    $re_ssl = "http";
    if (!empty($param)) {
        foreach ($param as $key => $value) {
            $param[$key] = $key . "=" . $value;
        }
        $param = $url . "?" . implode("&", $param);
    }
    $where["type"] = 2;
    $where["status"] = 1;
    if (!config("develop_mode")) {
        $where["isdev"] = 0;
    }
    $authRuleData = db("AuthRule")->field("id,pid,condition,icon,name,title,type")->where($where)->order("sort asc,id asc")->select();
    $menuArray = [];
    foreach ($authRuleData as $key => $value) {
        $url_arr = explode("/", $value["name"]);
        if (!IS_ROOT && !checkRule($value["name"] . "," . $url_arr[0] . "/" . $url_arr[1], 2, NULL)) {
            unset($menuArray["main"][$value["id"]]);
        } else {
            $menuArray[$value["id"]] = $value;
            $menuArray[$value["id"]]["url"] = str_replace($re_ssl . "://" . $_SERVER["HTTP_HOST"], '', admin_url($value["name"]));
        }
    }
    $menu = adminTreeMenu($menuArray, 0, "<li class=\"@class\"><a href=\"@url\" addtabs=\"@id\" url=\"@url\" data=\"@name\"><i class=\"@icon\"></i> <span>@title</span> <span class=\"pull-right-container\">@caret @badge</span></a> @childlist</li>", '', '', "ul", "class=\"treeview-menu\"");
    return $menu;
}

function admin_url($model, $params = "", $redirect = true, $suffix = false)
{
    return url($model, $params, $redirect, $suffix);
}

function xianyu_del_img_file($_arg_0)
{
    $_var_1 = xianyu_img_file_array($_arg_0);
    if (!empty($_var_1)) {
        foreach ($_var_1 as $_var_2) {
            @unlink("./" . config("upload_path") . "/" . $_var_2);
        }
    }
}

function is_login()
{
    $member_auth = session("member_auth");
    if (empty($member_auth)) {
        return 0;
    }
    return session("member_auth_sign") == data_auth_sign($member_auth) ? $member_auth["uid"] : 0;
}

function is_administrator($id)
{
    $id = is_null($id) ? is_login() : $id;
    return $id && intval($id) === config("user_administrator");
}

function in_array_case($_arg_0, $_arg_1)
{
    return in_array(strtolower($_arg_0), array_map("strtolower", $_arg_1));
}

function action_log($_arg_0, $_arg_1, $_arg_2 = 0, $_arg_3 = NULL, $_arg_4 = NULL)
{
    if (empty($_arg_0) || empty($_arg_1)) {
        return "参数不能为空";
    }
    if (empty($_var_5)) {
        $_var_5 = is_login();
    }
    $_var_6 = db("Action")->getByName($_arg_0);
    if ($_var_6["status"] != 1) {
        return "该行为被禁用或删除";
    }
    $_var_7["action_id"] = $_var_6["id"];
    $_var_7["user_id"] = session("member_auth.uid");
    $_var_7["action_ip"] = ip2long(get_client_ip());
    $_var_7["model"] = $_arg_1;
    $_var_7["record_id"] = $_arg_2;
    $_var_7["info"] = $_arg_4;
    $_var_7["status"] = $_arg_3;
    $_var_7["create_time"] = time();
    if (!empty($_var_6["log"])) {
        if (preg_match_all("/\\[(\\S+?)\\]/", $_var_6["log"], $_var_8)) {
            $_var_9["user"] = session("member_auth.uid");
            $_var_9["record"] = $_arg_2;
            $_var_9["model"] = $_arg_1;
            $_var_9["status"] = $_arg_3;
            $_var_9["info"] = $_arg_4;
            $_var_9["time"] = time();
            $_var_9["data"] = array("user" => session("member_auth.uid"), "model" => $_arg_1, "record" => $_arg_2, "time" => time());
            foreach ($_var_8[1] as $_var_10) {
                $_var_11 = explode("|", $_var_10);
                if (isset($_var_11[1])) {
                    $_var_12[] = call_user_func($_var_11[1], $_var_9[$_var_11[0]]);
                } else {
                    $_var_12[] = $_var_9[$_var_11[0]];
                }
            }
            $_var_7["remark"] = str_replace($_var_8[0], $_var_12, $_var_6["log"]);
        } else {
            $_var_7["remark"] = $_var_6["log"];
        }
    } else {
        $_var_7["remark"] = "操作url：" . $_SERVER["REQUEST_URI"];
    }
    db("ActionLog")->insert($_var_7);
    if (!empty($_var_6["rule"])) {
        $_var_13 = parse_action($_arg_0, $_var_5);
        $_var_14 = execute_action($_var_13, $_var_6["id"], $_var_5);
    }
}

function parse_action($_arg_0, $_arg_1)
{
    if (empty($_arg_0)) {
        return false;
    }
    if (is_numeric($_arg_0)) {
        $_var_2 = array("id" => $_arg_0);
    } else {
        $_var_2 = array("name" => $_arg_0);
    }
    $_var_3 = db("Action")->where($_var_2)->find();
    if (!$_var_3 || $_var_3["status"] != 1) {
        return false;
    }
    $_var_4 = $_var_3["rule"];
    $_var_4 = str_replace("{\$self}", $_arg_1, $_var_4);
    $_var_4 = explode(";", $_var_4);
    $_var_5 = array();
    foreach ($_var_4 as $_var_6 => $_var_7) {
        $_var_7 = explode("|", $_var_7);
        foreach ($_var_7 as $_var_8 => $_var_9) {
            $_var_10 = empty($_var_9) ? array() : explode(":", $_var_9);
            if (!empty($_var_10)) {
                $_var_5[$_var_6][$_var_10[0]] = $_var_10[1];
            }
        }
        if (!array_key_exists("cycle", $_var_5[$_var_6]) || !array_key_exists("max", $_var_5[$_var_6])) {
            unset($_var_5[$_var_6]["cycle"]);
            unset($_var_5[$_var_6]["max"]);
        }
    }
    return $_var_5;
}

function execute_action($_arg_0 = false, $_arg_1 = NULL, $_arg_2 = NULL)
{
    if (!$_arg_0 || empty($_arg_1) || empty($_arg_2)) {
        return false;
    }
    $_var_3 = true;
    foreach ($_arg_0 as $_var_4) {
        $_var_5 = array("action_id" => $_arg_1, "user_id" => $_arg_2);
        $_var_5["create_time"] = array("gt", NOW_TIME - intval($_var_4["cycle"]) * 3600);
        $_var_6 = db("ActionLog")->where($_var_5)->count();
        if ($_var_6 <= $_var_4["max"]) {
            $_var_7 = $_var_4["field"];
            $_var_8 = db(ucfirst($_var_4["table"]))->where($_var_4["condition"])->setField($_var_7, array("exp", $_var_4["rule"]));
            if (!$_var_8) {
                $_var_3 = false;
            }
        }
    }
    return $_var_3;
}

function format_bytes($_arg_0, $_arg_1 = '')
{
    $_var_2 = array("B", "KB", "MB", "GB", "TB", "PB");
    $_var_3 = 0;
    while ($_arg_0 >= 1024 && $_var_3 < 5) {
        $_arg_0 = $_arg_0 / 1024;
        $_var_3 = $_var_3 + 1;
    }
    return round($_arg_0, 2) . $_arg_1 . $_var_2[$_var_3];
}

function admin_star_arr($_arg_0)
{
    $_var_1 = 1;
    while ($_var_1 <= 5) {
        if ($_var_1 <= $_arg_0) {
            $_var_2[$_var_1] = 1;
        } else {
            $_var_2[$_var_1] = 0;
        }
        $_var_1 = $_var_1 + 1;
    }
    return $_var_2;
}

function Dostripslashes($_arg_0)
{
    if (!is_array($_arg_0)) {
        return stripslashes($_arg_0);
    }
    foreach ($_arg_0 as $_var_1 => $_var_2) {
        $_arg_0[$_var_1] = Dostripslashes($_var_2);
    }
    return $_arg_0;
}

function url_repalce($_arg_0, $_arg_1 = "asc")
{
    if ($_arg_1 == "asc") {
        return str_replace(array("|", "@", "#", "||"), array("/", "=", "&", "//"), $_arg_0);
    }
    return str_replace(array("/", "=", "&", "||"), array("|", "@", "#", "//"), $_arg_0);
}

function collect_bind_id($_arg_0)
{
    $_var_1 = F("_collect/bind");
    return $_var_1[$_arg_0];
}

function adminpage($_arg_0, $_arg_1, $_arg_2, $_arg_3, $_arg_4)
{
    if (empty($_arg_2)) {
        $_arg_2 = 3;
    }
    $_var_5 = '';
    $_var_5 = $_var_5 . ($_arg_0 > 1 ? "<li><a href=\"" . str_replace("xianyupage", 1, $_arg_3) . "\" class=\"prev disabled ajax-url\" data=\"p-0\">首页</a></li><li><a href=\"" . str_replace("xianyupage", $_arg_0 - 1, $_arg_3) . "\" class=\"prev ajax-url\" data=\"p-" . ($_arg_0 - 1) . "\">上一页</a></li>" : "<li class=\"disabled\"><span>首页</span></li><li class=\"disabled\"><span>上一页</span></li>");
    $_var_6 = $_arg_0 - $_arg_2;
    $_var_6 > 1 || ($_var_6 = 1);
    $_var_7 = $_arg_0 + $_arg_2;
    $_var_7 < $_arg_1 || ($_var_7 = $_arg_1);
    while ($_var_6 < $_var_7 + 1) {
        $_var_5 = $_var_5 . ($_var_6 == $_arg_0 ? "<li class=\"active\"><span>" . $_var_6 . "</span></li>" : "<li><a href=\"" . str_replace("xianyupage", $_var_6, $_arg_3) . "\" data=\"p-" . $_var_6 . "\" class=\"ajax-url\">" . $_var_6 . "</a></li>");
        $_var_6 = $_var_6 + 1;
    }
    $_var_5 = $_var_5 . ($_arg_0 < $_arg_1 ? "<li><a href=\"" . str_replace("xianyupage", $_arg_0 + 1, $_arg_3) . "\" class=\"next pagegbk ajax-url\" data=\"p-" . ($_arg_0 + 1) . "\">下一页</a></li><li><a href=\"" . str_replace("xianyupage", $_arg_1, $_arg_3) . "\" class=\"next pagegbk ajax-url\" data=\"p-" . $_arg_1 . "\">尾页</a></li>" : "<li class=\"disabled\"><span>下一页</span></li><li class=\"disabled\"><span>尾页</span></li>");
    if (!empty($_arg_4)) {
        $_var_5 = $_var_5 . ("<input type=\"input\" name=\"page\" id=\"page\" size=4 class=\"pagego\"/><input type=\"button\" value=\"跳 转\" onclick=\"" . $_arg_4 . "\" class=\"pagebtn\" />");
    }
    return $_var_5;
}

function get_mcid($_arg_0, $_arg_1)
{
    $_var_2 = getlistname($_arg_0, "list_pid");
    $_var_3 = !empty($_var_2) ? $_var_2 : $_arg_0;
    $_var_4 = array();
    $_var_5 = array_unique(explode(",", trim($_arg_1)));
    $_var_6["m_list_id"] = $_var_3;
    $_var_6["m_name"] = array("IN", $_var_5);
    $_arg_1 = db("mcat", array(), false)->field("m_cid")->where($_var_6)->select();
    if ($_arg_1) {
        foreach ($_arg_1 as $_var_7 => $_var_8) {
            $_var_4[] = $_var_8["m_cid"];
        }
        return implode(",", $_var_4);
    }
    return '';
}

function mcid_update($_arg_0, $_arg_1, $_arg_2)
{
    $_var_3 = db("mcid", array(), false);
    $_var_4["mcid_id"] = $_arg_0;
    $_var_4["mcid_sid"] = $_arg_2;
    $_var_3->where($_var_4)->delete();
    if (is_array($_arg_1)) {
        $_var_5 = $_arg_1;
    } else {
        $_var_5 = explode(",", $_arg_1);
        $_var_5 = array_filter(array_unique($_var_5));
    }
    foreach ($_var_5 as $_var_6 => $_var_7) {
        $_var_8[$_var_6]["mcid_mid"] = $_var_7;
        $_var_8[$_var_6]["mcid_sid"] = $_arg_2;
        $_var_8[$_var_6]["mcid_id"] = $_arg_0;
    }
    if (isset($_var_8)) {
        $_var_3->insertAll($_var_8);
    }
}

function tv_update($_arg_0, $_arg_1, $_arg_2)
{
    $_var_3 = db("vodtv", array(), false);
    $_var_4["vodtv_id"] = $_arg_0;
    $_var_4["vodtv_name"] = $_arg_1;
    $_var_4["vodtv_sid"] = $_arg_2;
    $_var_3->where($_var_4)->delete();
    if (is_array($_arg_1)) {
        $_var_5 = $_arg_1;
    } else {
        $_var_5 = explode(",", $_arg_1);
        $_var_5 = array_filter(array_unique($_var_5));
    }
    foreach ($_var_5 as $_var_6 => $_var_7) {
        if ($_var_7 != "未知") {
            $_var_8[$_var_6]["vodtv_id"] = $_arg_0;
            $_var_8[$_var_6]["vodtv_sid"] = $_arg_2;
            $_var_8[$_var_6]["vodtv_name"] = $_var_7;
        }
    }
    if (isset($_var_8)) {
        $_var_3->insertAll($_var_8);
    }
}

function getcountrole($_arg_0)
{
    if (!empty($_arg_0)) {
        $_var_1 = db("role")->where("role_vid", $_arg_0)->count();
        return $_var_1 + 0;
    }
}

function t2js($_arg_0, $_arg_1 = 1)
{
    $_var_2 = str_replace(array("\r", "\n"), array('', "\\n"), addslashes($_arg_0));
    return $_arg_1 ? "document.write(\"" . $_var_2 . "\");" : $_var_2;
}

function is_really_writable($_arg_0)
{
    if (DIRECTORY_SEPARATOR === "/") {
        return is_writable($_arg_0);
    }
    if (is_dir($_arg_0)) {
        $_arg_0 = rtrim($_arg_0, "/") . "/" . md5(mt_rand());
        if (($_var_1 = @fopen($_arg_0, "ab")) === false) {
            return false;
        }
        fclose($_var_1);
        @chmod($_arg_0, 511);
        @unlink($_arg_0);
        return true;
    }
    if (!is_file($_arg_0) || ($_var_1 = @fopen($_arg_0, "ab")) === false) {
        return false;
    }
    fclose($_var_1);
    return true;
}

function parse_config_attr($_arg_0)
{
    if (is_array($_arg_0)) {
        return $_arg_0;
    }
    $_var_1 = preg_split("/[,;\\r\\n]+/", trim($_arg_0, ",;\r\n"));
    if (strpos($_arg_0, ":")) {
        $_var_2 = array();
        foreach ($_var_1 as $_var_3) {
            list($_var_4, $_var_5) = explode(":", $_var_3);
            $_var_2[$_var_4] = $_var_5;
        }
    } else {
        $_var_2 = $_var_1;
    }
    return $_var_2;
}

function admin_config()
{
    $_var_0 = request();
    $_var_1 = config("site_path") . PUBLIC_PATH . "tpl/";
    $_var_2 = config("xianyucms.api_url");
    $_var_3 = array("site" => array("name" => config("xianyucms.name"), "title" => config("xianyucms.title"), "version" => lang("xianyucms_version"), "apiurl" => str_replace(array("https:", "http:"), '', $_var_2), "cdnurl" => $_var_1), "upload" => admin_upload(), "modulename" => strtolower($_var_0->module()), "controllername" => strtolower($_var_0->controller()), "actionname" => strtolower($_var_0->action()), "jsname" => "backend/" . str_replace(".", "/", strtolower($_var_0->controller())), "moduleurl" => url("/" . $_var_0->module() . '', false, false), "referer" => session("referer"));
    return $_var_3;
}

function admin_upload()
{
    $_var_0 = request();
    $_var_1 = strtolower($_var_0->controller());
    $_var_2 = config("upload_http_prefix");
    if (!empty($_var_2)) {
        $_var_3 = $_var_2;
    } else {
        $_var_3 = config("site_path") . config("upload_path") . "/";
    }
    $_var_4 = array("cdnurl" => $_var_3, "uploadurl" => "upload/ajax", "bucket" => "local", "mimetype" => config("upload_class"), "multipart" => array("sid" => $_var_1), "multiple" => false);
    return $_var_4;
}

function getParents($_arg_0, $_arg_1 = false)
{
    $_var_2 = F("_data/rules");
    $_var_3 = 0;
    $_var_4 = array();
    foreach ($_var_2 as $_var_5 => $_var_6) {
        if (isset($_var_6["id"])) {
            if ($_var_6["id"] == $_arg_0) {
                if ($_arg_1) {
                    $_var_4[$_var_5] = $_var_6;
                    $_var_7 = list_search(F("_data/rules"), "id=" . $_var_6["pid"]);
                    if ($_var_7['0']) {
                        $_var_4[$_var_5]["child"] = 1;
                    }
                }
                $_var_3 = $_var_6["pid"];
                break;
            }
        }
    }
    if ($_var_3) {
        $_var_8 = getParents($_var_3, true);
        $_var_4 = array_merge($_var_8, $_var_4);
    }
    return $_var_4;
}

function getMenuid($_arg_0, $_arg_1 = 0)
{
    $_var_2 = array();
    foreach ($_arg_0 as $_var_3) {
        if (isset($_var_3["id"])) {
            if ($_var_3["pid"] == $_arg_1) {
                $_var_2[$_var_3["id"]] = $_var_3;
            }
        }
    }
    return $_var_2;
}

function config_cache($arr = '')
{
    if (!$arr) {
        $arr = model("Config")->lists();
    }
    if ($arr["data_cache_type"] == "redis") {
        $arr["cache"] = array("host" => $arr["data_cache_host"], "port" => $arr["data_cache_port"], "password" => $arr["data_cache_password"]);
    }
    $arr["site_status"] = !empty($arr["site_status"]);
    $arr["app_debug"] = !empty($arr["config_app_debug"]);
    $arr["app_trace"] = !empty($arr["config_app_trace"]);
    $arr["show_error_msg"] = !empty($arr["show_error_msg"]);
    $arr["tpl_cache"] = !empty($arr["tpl_cache"]);
    $arr["strip_space"] = !empty($arr["strip_space"]);
    $arr["show_error_msg"] = !empty($arr["show_error_msg"]);
    $config = include RUNTIME_PATH . "conf/config.php";
    // 如果自定义了伪静态
    if (config("user_rewrite")) {
        $rewrite_rules = [];
        $route_rules = [];
        $i = 1;
        foreach ($arr["user_rewrite_route"] as $key => $val) {
            $rewrite_rules[$i]['rewrite'] = $val;
            $rewrite_rules[$i]['url'] = $key;
            $i++;
            $k = preg_replace('/\(.*?\)/', '', $key);
            $k = str_replace(array('--', '-'), '/', $k);
            $k = preg_replace('/\/$/', '', $k);
            $kArr = [];
            $find = '' || [];
            $kArr = explode('/', $k);
            if ($kArr) {
                unset($kArr[0]);
                unset($kArr[1]);
                unset($kArr[2]);
                $j = 0;
                if (empty($kArr)) {
                    $find = 1;
                } else {
                    foreach ($kArr as $a => $b) {
                        if (!is_int($b)) {
                            $find[$k][$j] = '$' . $b;
                            $j++;
                        }
                    }
                }
            }
            unset($kArr);
            $route_rules[$k]['find'] = $find[$k];
            $val = preg_replace(array('/\(/', '/\)/'), '', $val);
            $route_rules[$k]['replace'] = $val;
        }
        $arr["rewrite_rules"] = $rewrite_rules;
        $arr["route_rules"] = $route_rules;
    }
    @unlink(RUNTIME_PATH . "/init.php");
    @unlink(RUNTIME_PATH . "/route.php");
    arr2file(RUNTIME_PATH . "/conf/config.php", $arr);
}

function hotkeywords_cache()
{
    $_var_0 = config("site_hotkeywords");
    if ($_var_0) {
        $_var_1 = array();
        foreach (explode(chr(13), trim($_var_0)) as $_var_2 => $_var_3) {
            $_var_4 = explode("|", $_var_3);
            if ($_var_4[1]) {
                $_var_1[$_var_2] = "<a href=\"" . $_var_4[1] . "\" target=\"_blank\">" . trim($_var_4[0]) . "</a>";
            } else {
                $_var_5 = db("Vod")->field("vod_id,vod_letters,vod_cid,vod_jumpurl")->where("vod_name", trim($_var_3))->find();
                if ($_var_5["vod_id"]) {
                    $_var_6 = xianyu_data_url("home/vod/read", array("id" => $_var_5["vod_id"], "pinyin" => $_var_5["vod_letters"], "cid" => $_var_5["vod_cid"], "dir" => getlistname($_var_5["vod_cid"], "list_dir"), "jumpurl" => $_var_5["vod_jumpurl"]));
                    $_var_1[$_var_2] = "<a href=\"" . $_var_6 . "\">" . trim($_var_3) . "</a>";
                } else {
                    $_var_1[$_var_2] = "<a href=\"" . xianyu_url("home/vod/search", array("wd" => urlencode(trim($_var_3))), true, false) . "\">" . trim($_var_3) . "</a>";
                }
            }
        }
        $_var_7 = implode(" ", $_var_1);
        F("_data/hotkeywords", $_var_7);
        $_var_7 = "document.write('" . $_var_7 . "');";
        write_file("./runtime/js/hotkey.js", $_var_7);
    }
}

function ad_cache()
{
    $data = db("ads")->order("update_time desc")->select();
    foreach ($data as $key => $value) {
        write_file(ROOT_PATH . DS . config("admin_ads_file") . DS . $value["ads_name"] . ".js", t2js(stripslashes(trim($value["ads_content"]))));
    }
}

function userconfig_cache()
{
    $module = db("module")->where("module", "member")->find();
    F("_data/userconfig_cache", json_decode($module["setting"], true));
}

function usernav_cache()
{
    $usernav = db("Usernav")->order("oid asc")->select();
    foreach ($usernav as $value) {
        $arr[$value["cid"]][] = $value;
    }
    F("_data/usernavindex", $arr[1]);
    F("_data/usernavcenter", $arr[2]);
}

function play_cache()
{
    $play = db("Play")->order("play_oid asc")->select();
    foreach ($play as $key => $value) {
        $playArr[$value["play_name"]]["play_id"] = $value["play_id"];
        $playArr[$value["play_name"]]["play_name"] = $value["play_name"];
        $playArr[$value["play_name"]]["play_title"] = $value["play_title"];
        $playArr[$value["play_name"]]["play_oid"] = sprintf("%02d", $value["play_oid"]);
        $playArr[$value["play_name"]]["play_key"] = $value["play_key"];
        $playArr[$value["play_name"]]["play_keytime"] = $value["play_keytime"];
        $playArr[$value["play_name"]]["play_status"] = $value["play_status"];
        $playArr[$value["play_name"]]["play_display"] = $value["play_display"];
        $playArr[$value["play_name"]]["play_apiurl"] = $value["play_apiurl"];
        $playArr[$value["play_name"]]["play_copyright"] = $value["play_copyright"];
        $playArr[$value["play_name"]]["play_cloud"] = $value["play_cloud"];
        if ($value["play_status"] == 1) {
            $playNameArr[$key] = $value["play_name"];
        }
    }
    F("_data/statusplay", $playNameArr);
    F("_data/player", $playArr);
}

function list_limit($_arg_0, $_arg_1)
{
    $_var_2 = config("template.view_path", ROOT_PATH . "tpl" . DS . "home" . DS) . config("theme.pc_theme") . DS;
    $_var_3 = stream_context_create(array("http" => array("timeout" => 5)));
    $_var_4 = @file_get_contents($_var_2 . trim($_arg_1) . ".html", 0, $_var_3);
    preg_match_all("/" . $_arg_0 . "/", $_var_4, $_var_5);
    foreach ($_var_5[1] as $_var_6 => $_var_7) {
        if (strpos($_var_7, "page:true") > 0) {
            $_var_8 = explode(";", str_replace("num", "limit", $_var_7));
            foreach ($_var_8 as $_var_9) {
                list($_var_6, $_var_7) = explode(":", trim($_var_9));
                $_var_10[trim($_var_6)] = trim($_var_7);
            }
            return $_var_10["limit"];
        }
    }
    return 0;
}

function list_cache()
{
    $_var_0 = "menu";
    $_var_1["list_status"] = array("eq", 1);
    $_var_2 = db("List")->alias("l")->field("l.*,m.id,m.name,m.title,m.icon,m.template_list,m.template_add,m.template_edit,m.template_list_skin,m.template_list_type,m.template_list_detail,m.template_list_play")->join("model m", "m.id=l.list_sid", "LEFT")->where($_var_1)->order("list_oid asc")->select();
    foreach ($_var_2 as $_var_3 => $_var_4) {
        if ($_var_2[$_var_3]["list_sid"] == 9) {
            $_var_2[$_var_3]["list_url"] = $_var_2[$_var_3]["list_jumpurl"];
            $_var_2[$_var_3]["list_url_big"] = $_var_2[$_var_3]["list_jumpurl"];
        } else {
            if (config("url_rewrite")) {
                $_var_2[$_var_3]["list_url"] = xianyu_list_url("home/" . $_var_2[$_var_3]["name"] . "/show", array("id" => $_var_2[$_var_3]["list_id"], "dir" => $_var_2[$_var_3]["list_dir"]));
                $_var_2[$_var_3]["list_url_big"] = xianyu_list_url("home/" . $_var_2[$_var_3]["name"] . "/show", array("id" => $_var_2[$_var_3]["list_pid"], "dir" => getlistname($_var_2[$_var_3]["list_pid"], "list_dir")));
            } else {
                $_var_2[$_var_3]["list_url"] = xianyu_list_url("home/" . $_var_2[$_var_3]["name"] . "/show", array("id" => $_var_2[$_var_3]["list_id"], "dir" => $_var_2[$_var_3]["list_dir"]));
                if (!empty($_var_2[$_var_3]["list_pid"])) {
                    $_var_2[$_var_3]["list_url_big"] = xianyu_list_url("home/" . $_var_2[$_var_3]["name"] . "/show", array("id" => $_var_2[$_var_3]["list_pid"], "dir" => getlistname($_var_2[$_var_3]["list_pid"], "list_dir")));
                } else {
                    $_var_2[$_var_3]["list_url_big"] = xianyu_list_url("home/" . $_var_2[$_var_3]["name"] . "/show", array("id" => $_var_2[$_var_3]["list_id"], "dir" => $_var_2[$_var_3]["list_dir"]));
                }
            }
            if (!empty($_var_2[$_var_3]["list_pid"])) {
                $_var_2[$_var_3]["list_name_big"] = getlistname($_var_2[$_var_3]["list_pid"]);
            } else {
                $_var_5 = db("List")->where("list_id", $_var_2[$_var_3]["list_id"])->value("list_name");
                $_var_2[$_var_3]["list_name_big"] = $_var_5;
            }
            $_var_2[$_var_3]["list_limit"] = list_limit("xianyu_mysql_" . $_var_2[$_var_3]["name"] . "\\('(.*)'\\)", $_var_2[$_var_3]["list_skin"]);
        }
    }
    $_var_6 = array("list_pid" => 0, "list_sid" => 1);
    $_var_7 = db("List")->where($_var_6)->field("list_id,list_name,list_oid")->order("list_oid asc")->select();
    foreach ($_var_7 as $_var_8 => $_var_9) {
        $_var_7[$_var_8]["son"] = model("Mcat")->list_cat($_var_9["list_id"]);
        $_var_7[$_var_8]["total"] = $_var_7[$_var_8]["son"] == NULL ? 0 : count($_var_7[$_var_8]["son"]);
    }
    $_var_10 = db("Mcat")->order("m_cid asc")->select();
    foreach ($_var_7 as $_var_8 => $_var_9) {
        $_var_10[$_var_8]["son"] = $_var_10[0]["m_cid"];
    }
    $_var_11 = db("Emot")->order("emot_oid asc")->select();
    F("_data/list_emots", $_var_11);
    F("_data/mcat", $_var_7);
    F("_data/mcid", $_var_10);
    F("_data/list", $_var_2);
    F("_data/listtree", list_to_tree($_var_2, "list_id", "list_pid", "son", 0));
    $_var_1 = NULL;
    $_var_12 = db("Model")->order("id asc")->select();
    F("_data/modellist", $_var_12);
    foreach ($_var_12 as $_var_8 => $_var_9) {
        F("_data/list" . $_var_9["name"], list_search(F("_data/listtree"), "list_sid=" . $_var_9["id"]));
        $_var_1 = NULL;
        $_var_2 = NULL;
    }
}

function _bootstrap_3ce0ea82ed7e1b85da862c42ef0d106c()
{
    //版本缓存
    $data = cache("xianyucms_version");
    // 如果不存在则写入v8变量缓存 时长259200s
    if (empty($data)) {
        $data = '{"xianyu_name":"","xianyucms_name":"XianYuCms","xianyu_version":"v8.20180310","xianyu_qq":"12234@qq.com","xianyu_document":"https:\/\/www.baidu.com\/","xianyu_copyright":"<div class=\"copyright\" style=\"display:block;text-align:center\">Powered by xianyuCms \u00a92017-2018 All Rights Reserved <\/div>\r\n<\/body>","xianyu_api":"","caiji_api":{"1":"v.97bike.com","2":"v1.97bike.com","3":"v2.97bike.com","4":"v3.97bike.com","5":"v4.97bike.com"},"xianyu_ad":null,"xianyu_apiurl":"https:\/\/yanzheng.97bike.com\/","xianyu_head":"<script type=\"text\/javascript\" charset=\"utf-8\" src=\"\/\/yanzheng.97bike.com\/zp\/v8index.js?time\"><\/script>","xianyu_caiji":"<script type=\"text\/javascript\" charset=\"utf-8\" src=\"\/\/yanzheng.97bike.com\/caiji\/caiji.js?time\"><\/script>","xianyu_caijids":"<script type=\"text\/javascript\" charset=\"utf-8\" src=\"\/\/yanzheng.97bike.com\/caiji\/ds.js?time\"><\/script> ","xianyu_timming":"<script type=\"text\/javascript\" charset=\"utf-8\" src=\"\/\/yanzheng.97bike.com\/caiji\/timming.js?time\"><\/script> ","xianyu_body":"<div class=\"copyright\" style=\"display:block;text-align:center\">Powered by ZanPianCms \u00a92018-2019 All Rights Reserved <\/div>"}';
        $data = json_decode($data, true);
        cache("xianyucms_version", $data, 259200);

    }

    config("xianyucms", array(
        "version" => $data["xianyu_version"],
        "name" => $data["xianyu_name"],
        "title" => $data["xianyucms_name"],
        "qq" => $data["xianyu_qq"],
        "copyright" => $data["xianyu_copyright"],
        "ad" => $data["xianyu_ad"],
        "api_url" => $data["xianyu_apiurl"]
    ));
}

_bootstrap_3ce0ea82ed7e1b85da862c42ef0d106c();