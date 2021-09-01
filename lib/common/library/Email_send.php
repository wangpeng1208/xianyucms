<?php
function remindset($data)
{
	$userconfig = F("_data/userconfig_cache");
    $data["vod_readurl"] = rtrim(rtrim(config("site_url"), "/") . config("site_path"), "/") . xianyu_data_url("home/vod/read", array("id" => $data["vod_id"], "pinyin" => $data["vod_letters"], "cid" => $data["vod_cid"], "dir" => getlistname($data["vod_cid"], "list_dir"), "jumpurl" => $data["vod_jumpurl"]));
    $data["vod_picurl"] = xianyu_img_url($data["vod_pic"]);
	$where["remind_vid"] = $data["vod_id"];
    $where["isRemind"] = 1;
	$result = db("remind")->alias("r")->join("user u", "u.userid=r.remind_uid")->where($where)->select();
	if ($result) {
		foreach ($result as $key => $value) {
			$url = url("user/home/index", array("id" => $value["userid"]), true, true);
			$content = $userconfig["remindset_message"];
			$title = $userconfig["remindset_title"];
			$arr[$key]["title"] = str_replace(array("{siteurl}", "{sittitle}", "{sitemail}", "{vod_url}", "{vod_name}", "{vod_pic}", "{vod_title}"), array(config("site_url"), config("site_name"), $userconfig["mail_from"], $data["vod_readurl"], $data["vod_name"], $data["vod_picurl"], $data["vod_title"]), $title);

            $arr[$key]["content"] = str_replace(array("{userurl}", "{siteurl}", "{sittitle}", "{sitemail}", "{vod_url}", "{vod_name}", "{vod_pic}", "{vod_title}", "{nickname}", "{vodactor}", "{voddirector}", "{vodarea}", "{vodyear}", "{vodlanguage}", "{vodcontent}", "{vodaddtime}", "{usercenterindex}", "{usercenterremind}", "{usercenterremindset}", "{usercenterlove}"), array($url, config("site_url"), config("site_name"), $userconfig["mail_from"], $data["vod_readurl"], $data["vod_name"], $data["vod_picurl"], $data["vod_title"], $value["nickname"], $data["vod_actor"], $data["vod_director"], $data["vod_area"], $data["vod_year"], $data["vod_language"], mb_substr($data["vod_content"], 0, 100, "gbk"), $data["vod_addtime"], rtrim(config("site_url") . config("site_path"), "/") . xianyu_user_url("user/center/index"), rtrim(config("site_url") . config("site_path"), "/") . xianyu_user_url("user/center/remind"), rtrim(config("site_url") . config("site_path"), "/") . xianyu_user_url("user/center/email"), rtrim(config("site_url") . config("site_path"), "/") . xianyu_user_url("user/center/love")), $content);

            $arr[$key]["email"] = $value["iemail"];
		}
		return $arr;
	}
}
function regsendemail($_arg_0 = false)
{
    $userconfig = F("_data/userconfig_cache");
	$_var_3 = session("user_auth_temp");
	if (!empty($userconfig["user_email_auth"]) && $_var_3["groupid"] == 1 || !empty($_arg_0)) {
		$_var_4 = !empty($userconfig["user_key"]) ? $userconfig["user_key"] : "xianyucms";
		$_var_5 = md5($_var_4);
		$_var_6 = sys_auth($_var_3["userid"] . "|" . $_var_5 . "|" . time(), "ENCODE", $_var_5);
		cache("user_regverify_" . $_var_3["userid"], $_var_6, 3600);
		$_var_7 = url("user/home/index", array("id" => $_var_3["userid"]), true, true);
		$_var_8 = url("user/reg/code", array("verify" => $_var_6), true, true);
		$_var_9 = str_replace(array("{click}", "{url}", "{userid}", "{username}", "{email}", "{siteurl}", "{sitename}", "{nickname}", "{userurl}"), array("<a href=\"" . $_var_8 . "\">请点击</a>", $_var_8, $_var_3["userid"], $_var_3["username"], $_var_3["useremail"], rtrim(config("site_url"), "/") . config("site_path"), config("site_name"), $_var_3["nickname"], $_var_7), $userconfig["user_registermessage"]);
		$_var_10["email"] = $_var_3["useremail"];
		$_var_10["title"] = config("site_name") . "邮件验证";
		$_var_10["content"] =  $_var_9;
		return $_var_10;
	}
}
function forgetpwdemail($_arg_0)
{

    $userconfig = F("_data/userconfig_cache");
	$_var_3 = !empty($userconfig["user_key"]) ? $userconfig["user_key"] : "xianyucms";
	$_var_4 = md5($_var_3);
	$_var_5 = sys_auth($_arg_0["userid"] . "#" . time() . "#" . $_var_4, "ENCODE", $_var_4);
	cache("user_forgetpwd_" . $_arg_0["userid"], $_var_5, 3600);
	$_var_6 = url("user/login/repass", array("code" => $_var_5), true, true);
	$_var_7 = url("user/home/index", array("id" => $_arg_0["userid"]), true, true);
	$_var_8 = str_replace(array("{click}", "{url}", "{sitename}", "{siteurl}", "{email}", "{nickname}", "{userurl}"), array("<a href=\"" . $_var_6 . "\" target=\"_blank\">点击此处找回密码</a>", $_var_6, config("site_name"), rtrim(config("site_url"), "/") . config("site_path"), $_arg_0["email"], $_arg_0["nickname"], $_var_7), $userconfig["user_passwordmessage"]);
	$_var_9["content"] =  $_var_8;
	$_var_9["title"] = $_var_9["title"] =  "亲爱的会员：" . $_arg_0["nickname"] . "您在" . config("site_name") . "密码找回邮件内容";
	$_var_9["email"] = $_arg_0["email"];
	return $_var_9;
}