<?php

function vod($_arg_0)
{
	$_arg_0["vod_play"] = trim($_arg_0["vod_play"], "\$\$\$");
	$_arg_0["vod_url"] = implode(chr(13), explode("%0d%0a", trim($_arg_0["vod_url"])));
	return model("Collect")->vod_insert($_arg_0, config("force_collect"));
}
function news($_arg_0)
{
	if (empty($_arg_0["news_keywords"]) && config("auto_tag")) {
		$_arg_0["news_keywords"] = xianyu_tag_auto($_arg_0["news_name"], $_arg_0["news_content"]);
	}
	if (!empty($_arg_0["news_name"])) {
		if (model("news")->get(array("news_name" => $_arg_0["news_name"]))) {
			$_arg_0["msg"] = "标题存在跳过";
			return $_arg_0;
		}
	} else {
		$_arg_0["msg"] = "标题为空跳过";
		return $_arg_0;
	}
	if (empty($_arg_0["news_content"])) {
		$_arg_0["msg"] = "内容为空跳过";
		return $_arg_0;
	}
	if (empty($_arg_0["news_cid"])) {
		$_arg_0["msg"] = "分类为空跳过";
		return $_arg_0;
	}
	if (!empty($_arg_0["news_content"]) && config("api_pic")) {
		$_arg_0["news_content"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["news_content"]);
	}
	if (!empty($_arg_0["news_pic"]) && config("api_pic")) {
		$_arg_0["news_pic"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["news_pic"]);
	}
	$_arg_0["checktime"] = 1;
	unset($_arg_0["news_id"]);
	$_var_2 = model("news")->setAttr("news_id", NULL)->isUpdate(false)->save($_arg_0);
	if (false === $_var_2) {
		return model("news")->getError();
	}
	if ($_arg_0["news_vod"]) {
		model("Newsrel")->newsrel_update(model("news")->news_id, $_arg_0["news_vod"], 1);
	}
	if ($_arg_0["news_star"]) {
		model("Newsrel")->newsrel_update(model("news")->news_id, $_arg_0["news_star"], 3);
	}
	if ($_arg_0["news_keywords"]) {
		model("Tag")->tag_update(model("news")->news_id, $_arg_0["news_keywords"], 2);
	}
	$_var_3 = xianyu_data_url("home/news/read", array("id" => model("news")->news_id, "cid" => model("news")->news_cid, "dir" => getlistname(model("news")->news_cid, "list_dir")), true, true);
	$_arg_0["code"] = model("news")->news_id;
	$_arg_0["msg"] = "添加成功" . baidutui($_var_3, "add", 2);
	return $_arg_0;
}
function star($_arg_0)
{
	unset($_arg_0["star_id"]);
	$_var_2 = model("star");
	$_var_3 = model("Img");
	$_var_4 = $_var_2->where("star_name", $_arg_0["star_name"])->find();
	if (!empty($_var_4["star_id"])) {
		if ($_arg_0["star_bigpic"] && empty($_var_4["star_bigpic"])) {
			if (config("api_pic")) {
				$_arg_0["star_bigpic"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["star_bigpic"]);
			}
			$_var_5["star_bigpic"] = $_var_3->down_load(trim($_arg_0["star_bigpic"]), "star");
			$_var_5["star_pig"] = $_arg_0["star_pig"];
			$_var_5["star_bgcolor"] = $_arg_0["star_bgcolor"];
		}
		if ($_arg_0["star_pic"] && empty($_var_4["star_pic"])) {
			if (config("api_pic")) {
				$_arg_0["star_pic"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["star_pic"]);
			}
			$_var_5["star_pic"] = $_var_3->down_load(trim($_arg_0["star_pic"]), "star");
		}
		if ($_arg_0["star_xb"] && empty($_var_4["star_xb"])) {
			$_var_5["star_xb"] = $_arg_0["star_xb"];
		}
		if ($_arg_0["star_info"] && empty($_var_4["star_info"])) {
			$_var_5["star_info"] = $_arg_0["star_info"];
		}
		if ($_arg_0["star_reurl"] && empty($_var_4["star_reurl"])) {
			$_var_5["star_reurl"] = $_arg_0["star_reurl"];
		}
		if ($_arg_0["star_reurl"] && empty($_var_4["star_reurl"])) {
			$_var_5["star_reurl"] = $_arg_0["star_reurl"];
		}
		if ($_arg_0["star_weibo"] && empty($_var_4["star_weibo"])) {
			$_var_5["star_weibo"] = $_arg_0["star_weibo"];
		}
		if ($_arg_0["star_content"] && empty($_var_4["star_content"])) {
			$_var_5["star_content"] = xianyu_content_images(trim($_arg_0["star_content"]), "star");
		}
		if ($_arg_0["star_csd"] && empty($_var_4["star_csd"])) {
			$_var_5["star_csd"] = $_arg_0["star_csd"];
		}
		if ($_arg_0["star_area"] && empty($_var_4["star_area"])) {
			$_var_5["star_area"] = $_arg_0["star_area"];
		}
		if ($_arg_0["star_guanxi"] && empty($_var_4["star_guanxi"])) {
			$_var_5["star_guanxi"] = $_arg_0["star_guanxi"];
		}
		if ($_arg_0["star_work"] && empty($_var_4["star_work"])) {
			$_var_5["star_work"] = $_arg_0["star_work"];
		}
		if ($_arg_0["star_gs"] && empty($_var_4["star_gs"])) {
			$_var_5["star_gs"] = $_arg_0["star_gs"];
		}
		if ($_arg_0["star_bm"] && empty($_var_4["star_bm"])) {
			$_var_5["star_bm"] = $_arg_0["star_bm"];
		}
		if ($_var_5) {
			$_var_5["star_addtime"] = time();
			$_var_6 = db("Star")->where("star_id", $_var_4["star_id"])->update($_var_5);
			if ($_var_6 !== false) {
				$_var_7["code"] = $_var_4["star_id"];
				$_var_7["msg"] = "更新成功";
				return $_var_7;
			}
			$_var_7["msg"] = "更新失败";
			return $_var_7;
		}
		$_var_7["code"] = $_var_4["star_id"];
		$_var_7["msg"] = "不需要更新";
		return $_var_7;
	}
	if ($_arg_0["star_pic"]) {
		if (config("api_pic")) {
			$_arg_0["star_pic"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["star_pic"]);
		}
		$_arg_0["star_pic"] = $_var_3->down_load(trim($_arg_0["star_pic"]), "star");
	}
	if ($_arg_0["star_bigpic"]) {
		if (config("api_pic")) {
			$_arg_0["star_bigpic"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["star_bigpic"]);
		}
		$_arg_0["star_bigpic"] = $_var_3->down_load(trim($_arg_0["star_bigpic"]), "star");
		$_arg_0["star_stars"] = 5;
	}
	$_arg_0["star_letter"] = getletter($_arg_0["star_name"]);
	$_arg_0["star_letters"] = getletters(trim($_arg_0["star_name"]), 3);
	if ($_arg_0["star_content"]) {
		$_arg_0["star_content"] = xianyu_content_images(trim($_arg_0["star_content"]), "star");
	}
	$_arg_0["star_hits"] = mt_rand(0, config("rand_hits"));
	$_arg_0["star_up"] = mt_rand(0, config("rand_updown"));
	$_arg_0["star_down"] = mt_rand(0, config("rand_updown"));
	$_arg_0["star_caiji"] = 1;
	$_arg_0["star_addtime"] = time();
	if ($_arg_0["star_name"]) {
		$_var_8 = db("Star")->insertGetId($_arg_0);
		if ($_var_8) {
			$_var_9 = xianyu_data_url("home/star/read", array("id" => $_var_8, "pinyin" => $_arg_0["star_letters"], "cid" => $_arg_0["star_cid"], "dir" => getlistname($_arg_0["star_cid"], "list_dir"), "jumpurl" => $_arg_0["star_jumpurl"]), true, true);
			$_var_7["code"] = $_var_8;
			$_var_7["msg"] = "添加成功" . baidutui($_var_9, "add", 2);
			return $_var_7;
		}
	} else {
		$_var_7["msg"] = "名字为空跳过";
		return $_var_7;
	}
}
function story($_arg_0)
{
	$_var_2 = model("Story");
	if (empty($_arg_0["vod_name"])) {
		$_var_3["msg"] = "视频名称为空跳过";
		return $_var_3;
	}
	if (empty($_arg_0["story_content"])) {
		$_var_3["msg"] = "剧情内容为空跳过";
		return $_var_3;
	}
	$_var_4 = $_var_2->where("story_url", $_arg_0["story_url"])->value("story_vid");
	if (!empty($_var_4)) {
		$_var_5 = $_var_4;
	} else {
		$_var_6 = db("vod")->field("vod_name,vod_id,vod_cid,vod_actor,vod_year")->limit(2)->where("vod_name", "like", $_arg_0["vod_name"] . "%")->select();
		if (empty($_var_6)) {
			$_var_3["msg"] = "没有相关视频跳过";
			return $_var_3;
		}
		$_var_7 = explode(",", $_arg_0["vod_actor"]);
		$_var_8 = $_arg_0["vod_year"];
		foreach ($_var_6 as $_var_9 => $_var_10) {
			$_var_11 = explode(",", $_var_10["vod_actor"]);
			$_var_12 = array_intersect($_var_11, $_var_7);
			if (count($_var_12) >= 4 && $_var_10["vod_year"] == $_var_8) {
				$_var_5 = $_var_10["vod_id"];
				break;
			}
			if (count($_var_12) >= 3 && $_var_10["vod_year"] == $_var_8) {
				$_var_5 = $_var_10["vod_id"];
				break;
			}
			if (count($_var_12) >= 2 && $_var_10["vod_year"] == $_var_8) {
				$_var_5 = $_var_10["vod_id"];
				break;
			}
			if ($_var_10["vod_year"] == $_var_8) {
				$_var_5 = $_var_10["vod_id"];
				break;
			}
		}
		$_var_5 = !empty($_var_5) ? $_var_5 : $_var_6[0]["vod_id"];
	}
	return story_add($_arg_0, $_var_5);
}
function story_add($_arg_0, $_arg_1)
{
	unset($_arg_0["story_id"]);
	$_arg_0["story_content"] = trim($_arg_0["story_content"], "||");
	if (empty($_arg_0["story_content"])) {
		$_var_3["msg"] = "剧情内容为空跳过";
		return $_var_3;
	}
	$_var_4 = explode("||", $_arg_0["story_content"]);
	$_var_5 = count($_var_4);
	if (strstr($_var_4[$_var_5 - 1], "大结局") || strstr($_var_4[$_var_5 - 1], "全剧终") || strstr($_var_4[$_var_5 - 1], "全集")) {
		$_arg_0["story_cont"] = 1;
		$_arg_0["story_title"] = "第" . $_var_5 . "集大结局";
	}
	if (isset($_arg_0["vod_total"]) && $_arg_0["vod_total"] == $_var_5) {
		$_arg_0["story_cont"] = 1;
	} else {
		if (isset($_arg_0["story_total"]) && $_arg_0["story_total"] == $_var_5) {
			$_arg_0["story_cont"] = 1;
		}
	}
	$_var_6 = model("Story");
	$_var_7 = $_var_6->where("story_vid", $_arg_1)->find();
	$_arg_0["story_addtime"] = time();
	$_arg_0["checktime"] = 1;
	$_arg_0["story_vid"] = $_arg_1;
	if (!empty($_arg_0["story_content"]) && config("api_pic")) {
		$_arg_0["story_content"] = preg_replace("/http[^.]*[^A]+pic\\.php\\?url=/is", config("api_pic"), $_arg_0["story_content"]);
	}
	if (!empty($_var_7["story_id"])) {
		$_arg_0["story_id"] = $_var_7["story_id"];
		$_var_8 = explode("||", $_var_7["story_content"]);
		$_var_9 = explode("||", $_arg_0["story_content"]);
		if ($_var_7["story_cont"] == 1) {
			$_var_3["msg"] = "剧情以完结跳过";
			return $_var_3;
		}
		if (count($_var_8) >= count($_var_9)) {
			$_var_3["msg"] = "剧情未更新跳过";
			return $_var_3;
		}
		$_var_6->data($_arg_0, true)->isUpdate(true)->save();
		if ($_var_6) {
			$_var_3["code"] = $_var_6->story_id;
			$_var_3["msg"] = "剧情更新成功";
			return $_var_3;
		}
		$_var_3["msg"] = "剧情更新失败";
		return $_var_3;
	}
	$_arg_0["story_cid"] = !empty($_arg_0["story_cid"]) ? $_arg_0["story_cid"] : gettypelistcid($_arg_1, 4);
	$_arg_0["story_hits"] = mt_rand(0, config("rand_hits"));
	$_var_6->data($_arg_0, true)->isUpdate(false)->save();
	if ($_var_6) {
		$_var_10 = get_vod_info($_arg_0["story_vid"], "vod_id", "vod_letters");
		$_var_11 = xianyu_data_url("home/story/read", array("id" => $_var_6->story_id, "pinyin" => $_var_10, "cid" => $_arg_0["story_cid"], "dir" => getlistname($_arg_0["story_cid"], "list_dir")), true, true);
		$_var_3["code"] = $_var_6->story_id;
		$_var_3["msg"] = "剧情添加成功" . baidutui($_var_11, "add", 2);
		return $_var_3;
	}
	$_var_3["msg"] = "剧情添加失败";
	return $_var_3;
}
function actor($_arg_0)
{
	$_var_2 = model("role");
	if (empty($_arg_0["vod_name"])) {
		$_var_3["msg"] = "视频名为空跳过";
		return $_var_3;
	}
	if (empty($_arg_0["actor_role"])) {
		$_var_3["msg"] = "角色为空跳过";
		return $_var_3;
	}
	$_var_4 = db("vod")->field("vod_name,vod_id,vod_cid,vod_actor,vod_year")->limit(2)->where("vod_name", "like", $_arg_0["vod_name"] . "%")->select();
	if (empty($_var_4)) {
		$_var_3["msg"] = "没有相关视频跳过";
		return $_var_3;
	}
	$_var_5 = explode(",", $_arg_0["vod_actor"]);
	$_var_6 = $_arg_0["vod_year"];
	foreach ($_var_4 as $_var_7 => $_var_8) {
		$_var_9 = explode(",", $_var_8["vod_actor"]);
		$_var_10 = array_intersect($_var_9, $_var_5);
		if (count($_var_10) >= 4 && $_var_8["vod_year"] == $_var_6) {
			$_var_11 = $_var_8["vod_id"];
			break;
		}
		if (count($_var_10) >= 3 && $_var_8["vod_year"] == $_var_6) {
			$_var_11 = $_var_8["vod_id"];
			break;
		}
		if (count($_var_10) >= 2 && $_var_8["vod_year"] == $_var_6) {
			$_var_11 = $_var_8["vod_id"];
			break;
		}
		if ($_var_8["vod_year"] == $_var_6) {
			$_var_11 = $_var_8["vod_id"];
			break;
		}
	}
	$_var_11 = !empty($_var_11) ? $_var_11 : $_var_4[0]["vod_id"];
	return actor_add($_arg_0["actor_role"], $_var_11);
}
function actor_add($_arg_0, $_arg_1)
{
	$_arg_0 = is_array($_arg_0) ? $_arg_0 : explode("|||", $_arg_0);
	if (isset($_arg_0)) {
		$_var_3 = db("Role")->where("role_vid", $_arg_1)->value("role_vid");
		foreach ($_arg_0 as $_var_4 => $_var_5) {
			$_var_6 = explode("@@", $_var_5);
			if ($_var_3) {
				$_var_7["role_vid"] = $_arg_1;
				$_var_7["role_star"] = trim($_var_6[1]);
				$_var_8 = db("Role")->where($_var_7)->value("role_id");
				if (empty($_var_8) && !empty($_var_6[1])) {
					$_var_9[$_var_4]["role_cid"] = gettypelistcid($_arg_1, 5);
					$_var_9[$_var_4]["role_name"] = trim($_var_6[0]);
					$_var_9[$_var_4]["role_star"] = trim($_var_6[1]);
					$_var_9[$_var_4]["role_pic"] = preg_replace(array("/http[^.]*[^A]+pic\\.php\\?url=/is", "/http:\\/\\/.*?no.jpg/"), array(config("api_pic"), ''), trim($_var_6[2]));
					$_var_9[$_var_4]["role_content"] = trim($_var_6[3]);
					$_var_9[$_var_4]["role_vid"] = $_arg_1;
					$_var_9[$_var_4]["role_addtime"] = time();
					$_var_9[$_var_4]["role_oid"] = $_var_4;
					$_var_9[$_var_4]["role_hits"] = mt_rand(0, config("rand_hits"));
				} else {
					$_var_10[] = $_var_4;
				}
			} else {
				if (!empty($_var_6[1])) {
					$_var_9[$_var_4]["role_cid"] = gettypelistcid($_arg_1, 5);
					$_var_9[$_var_4]["role_name"] = trim($_var_6[0]);
					$_var_9[$_var_4]["role_star"] = trim($_var_6[1]);
					$_var_9[$_var_4]["role_pic"] = preg_replace(array("/http[^.]*[^A]+pic\\.php\\?url=/is", "/http:\\/\\/.*?no.jpg/"), array(config("api_pic"), ''), trim($_var_6[2]));
					$_var_9[$_var_4]["role_content"] = trim($_var_6[3]);
					$_var_9[$_var_4]["role_vid"] = $_arg_1;
					$_var_9[$_var_4]["role_addtime"] = time();
					$_var_9[$_var_4]["role_oid"] = $_var_4;
					$_var_9[$_var_4]["role_hits"] = mt_rand(0, config("rand_hits"));
				}
			}
		}
		if ($_var_9) {
			$_var_11 = db("Role")->insertAll($_var_9);
			$_var_12 = db("Actor")->where("actor_vid", $_arg_1)->value("actor_id");
			if (!$_var_12) {
				$_var_13["actor_vid"] = $_arg_1;
				$_var_13["actor_cid"] = !empty($_arg_0["actor_cid"]) ? $_arg_0["actor_cid"] : gettypelistcid($_arg_1, 6);
				$_var_13["actor_addtime"] = time();
				$_var_13["actor_hits"] = mt_rand(0, config("rand_hits"));
				$_var_14 = db("Actor")->insertGetId($_var_13);
				$_var_15 = get_vod_info($_var_13["actor_vid"], "vod_id", "vod_letters");
				$_var_16 = xianyu_data_url("home/actor/read", array("id" => $_var_14, "pinyin" => $_var_15, "cid" => $_var_13["actor_cid"], "dir" => getlistname($_var_13["actor_cid"], "list_dir")), true, true);
			}
			if ($_var_10) {
				$_var_17["code"] = $_arg_1;
				$_var_17["msg"] = "成功采集入库<font color='green'>" . $_var_11 . "</font>个角色,已存在角色<font color='red'>" . count($_var_10) . "</font>个跳过" . baidutui($_var_16, "add", 2);
				return $_var_17;
			}
			$_var_17["code"] = $_arg_1;
			$_var_17["msg"] = "成功采集入库<font color='green'>" . $_var_11 . "</font>个角色" . baidutui($_var_16, "add", 2);
			return $_var_17;
		}
		$_var_17["msg"] = "所有角色已经存在跳过";
		return $_var_17;
	}
	$_var_17["msg"] = "没有采集到角色跳过";
	return $_var_17;
}
function Cm($_arg_0)
{
	if (empty($_arg_0["vod_name"])) {
		$_var_2["msg"] = "视频名为空跳过";
		return $_var_2;
	}
	if (empty($_arg_0["vod_cm"])) {
		$_var_2["msg"] = "评论为空跳过";
		return $_var_2;
	}
	$_var_3 = db("vod")->field("vod_name,vod_id,vod_cid,vod_actor,vod_year")->limit(2)->where("vod_name", "like", $_arg_0["vod_name"] . "%")->select();
	if (empty($_var_3)) {
		$_var_2["msg"] = "没有相关视频跳过";
		return $_var_2;
	}
	$_var_4 = explode(",", $_arg_0["vod_actor"]);
	$_var_5 = $_arg_0["vod_year"];
	foreach ($_var_3 as $_var_6 => $_var_7) {
		$_var_8 = explode(",", $_var_7["vod_actor"]);
		$_var_9 = array_intersect($_var_8, $_var_4);
		if (count($_var_9) >= 4 && $_var_7["vod_year"] == $_var_5) {
			$_var_10 = $_var_7["vod_id"];
			break;
		}
		if (count($_var_9) >= 3 && $_var_7["vod_year"] == $_var_5) {
			$_var_10 = $_var_7["vod_id"];
			break;
		}
		if (count($_var_9) >= 2 && $_var_7["vod_year"] == $_var_5) {
			$_var_10 = $_var_7["vod_id"];
			break;
		}
		if ($_var_7["vod_year"] == $_var_5) {
			$_var_10 = $_var_7["vod_id"];
			break;
		}
	}
	$_var_10 = !empty($_var_10) ? $_var_10 : $_var_3[0]["vod_id"];
	return cm_add($_arg_0["vod_cm"], $_var_10);
}
function cm_add($_arg_0, $_arg_1)
{
	if (isset($_arg_0)) {
		$_arg_0 = is_array($_arg_0) ? $_arg_0 : explode("|||", $_arg_0);
		$_var_3 = db("Cm");
		$_var_4 = db("User")->where($_var_5)->count("userid");
		$_var_6 = db("Cm")->where("cm_vid", $_arg_1)->value("cm_id");
		foreach ($_arg_0 as $_var_7 => $_var_8) {
			$_var_8 = explode("||", $_var_8);
			$_var_9 = $_var_8[0];
			$_var_10 = strtotime($_var_8[1]) ? strtotime($_var_8[1]) : $_var_8[1];
			$_var_11 = $_var_8[2];
			$_var_12 = $_var_8[3];
			if ($_var_6) {
				$_var_5["cm_rcid"] = $_var_9;
				$_var_5["cm_vid"] = $_arg_1;
				$_var_13 = db("Cm")->where($_var_5)->value("cm_id");
				if (!$_var_13) {
					$_var_14[$_var_7]["cm_vid"] = $_arg_1;
					$_var_14[$_var_7]["cm_pid"] = 0;
					$_var_14[$_var_7]["cm_uid"] = mt_rand(0, $_var_4);
					$_var_14[$_var_7]["cm_content"] = $_var_12;
					$_var_14[$_var_7]["cm_support"] = 0;
					$_var_14[$_var_7]["cm_oppose"] = 0;
					$_var_14[$_var_7]["cm_ip"] = ip2long($_var_11);
					$_var_14[$_var_7]["cm_addtime"] = $_var_10;
					$_var_14[$_var_7]["cm_status"] = config("user_status");
					$_var_14[$_var_7]["cm_rcid"] = $_var_9;
				} else {
					$_var_15[] = $_var_7;
				}
			} else {
				$_var_14[$_var_7]["cm_vid"] = $_arg_1;
				$_var_14[$_var_7]["cm_pid"] = 0;
				$_var_14[$_var_7]["cm_uid"] = mt_rand(0, $_var_4);
				$_var_14[$_var_7]["cm_content"] = $_var_12;
				$_var_14[$_var_7]["cm_support"] = 0;
				$_var_14[$_var_7]["cm_oppose"] = 0;
				$_var_14[$_var_7]["cm_ip"] = ip2long($_var_11);
				$_var_14[$_var_7]["cm_addtime"] = $_var_10;
				$_var_14[$_var_7]["cm_status"] = config("user_status");
				$_var_14[$_var_7]["cm_rcid"] = $_var_9;
			}
		}
		if ($_var_14) {
			$_var_16 = $_var_3->insertAll($_var_14);
			if ($_var_15) {
				$_var_17["code"] = $_arg_1;
				$_var_17["msg"] = "已存在评论 <font color='red'>" . count($_var_15) . "</font>条未入库,成功采集入库<font color='green'>" . $_var_16 . "</font>条评论";
				return $_var_17;
			}
			$_var_17["code"] = $_arg_1;
			$_var_17["msg"] = "成功采集入库<font color='green'>" . $_var_16 . "</font>条评论";
			return $_var_17;
		}
		$_var_17["msg"] = "没有入库任何评论，采集的评论没有新的数据跳过";
		return $_var_17;
	}
	$_var_17["msg"] = "没有采集到任何评论跳过";
	return $_var_17;
}