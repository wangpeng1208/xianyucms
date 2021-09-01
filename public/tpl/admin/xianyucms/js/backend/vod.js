define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	function vod_ajax($type, $sid, $uid, $vid, $cid, $lastdid) {
		$.ajax({
			type: 'get',
			cache: false,
			url: 'user/ajax-type-' + $type + '-sid-' + $sid + '-uid-' + $uid + '-vid-' + $vid + '-cid-' + $cid + '-lastdid-' + $lastdid + ".html",
			success: function($html) {
				$('#userhtml').html($html);
			}
		});
	}
	String.prototype.replaceAll = function(s1, s2) {
		return this.replace(new RegExp(s1, "gm"), s2);
	}

	function getPatName(n, l, s) {
		var res = "";
		var rc = false;
		if (s.indexOf("qvod:") > -1 || s.indexOf("bdhd:") > -1 || s.indexOf("cool:") > -1) {
			var arr = s.split('|');
			if (arr.length >= 2) {
				res = arr[2].replace(/[^0-9]/ig, "");
				rc = true;
				if (res != "") {
					if (res.length > 3) {
						res += "期";
					} else if (l == 1) {
						res = "全集";
					} else {
						res = '第' + res + '集';
					}
				} else {
					res = FindNote(s);
					if (s == "") {
						if (l == 1) {
							res = "全集";
						} else {
							rc = false;
						}
					}
				}
			}
		}
		if (!rc) {
			res = '第' + (n < 9 ? '0' : '') + (n + 1) + '集';
		}
		return res;
	}
	var Controller = {
		index: function() {
		    $total=$(".pagination-info").attr("total");
			if($total){
			Backend.api.sidebar({'admin/vod/index': $total});
			}
			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
		   $(document).on("change", "select[name='vod_vipplay']", function() {
				if($(this).val()!=0){
					$("#trysee").show();
				}else{
					$("#trysee").hide();
				}
				if($(this).val()==1 || $(this).val()==2){
					$("#pay").show();
				}else{
					$("#pay").hide();
				}				
			});
			Controller.api.bindevent();
		},
		addrole: function() {
			Controller.api.bindevent();
		},
		ajaxmcid: function() {
			Controller.api.bindevent();
		},
		api: {
			bindevent: function() {
				Form.api.bindevent($("form[role=form]"));
				Table.api.bindevent();
				$(document).ready(function() {
					$("#color").bigColorpicker("color");
					$("#c5").bigColorpicker("c5", "L", 3);
				});
				if ($("#specialhtml").size() > 0) {
					tid = $("#specialhtml").attr("tid");
					nid = $("#specialhtml").attr("nid");
					relation_ajax(nid, tid, 0, 'show', 0, 'special', 1);
					$(document).on("click", ".addajax", function() {
						arr = $(this).attr("id").split("_");
						relation_ajax(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6]);
						$(this).addClass('disabled').prop('disabled', false);
					})
				}
				if ($("#newshtml").size() > 0) {
					tid = $("#newshtml").attr("tid");
					nid = $("#newshtml").attr("nid");
					relation_ajax(nid, tid, 0, 'show', 0, 'news', 1);
					$(document).on("click", ".addajax", function() {
						arr = $(this).attr("id").split("_");
						relation_ajax(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6]);
						$(this).addClass('disabled').prop('disabled', false);
					})
				}
				if ($("#userhtml").size() > 0) {
					sid = $("#userhtml").attr("sid");
					uid = $("#userhtml").attr("uid");
					vid = $("#userhtml").attr("vid");
					cid = $("#userhtml").attr("cid");
					vod_ajax('show', sid, uid, 0, 0, 0);
					$(document).on("click", ".addajax", function() {
						arr = $(this).attr("id").split("_");
						vod_ajax(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5]);
						$(this).addClass('disabled').prop('disabled', false);
					})
				}
				$(document).on("change", "select[name='vod_cid']", function() {
					var id = $(this).val();
					url = "vod/ajaxcat/id/" + id;
					$.get(url, function(data, status) {
						$("#vod_cat_list").html(data);
					});
				});
				$(document).on("click", ".clearurl", function() {
					arr = $(this).parent().parent().parent().attr("id").split("_");
					$("#data_" + arr[1] + " #vod_url").val('');

				});
				$(document).on("click", ".sorturl", function() {
					arr = $(this).parent().parent().parent().attr("id").split("_");
					s1 = $("#data_" + arr[1] + " #vod_url").val();
					s2 = "";
					if (s1.length == 0) {
						Toastr.error('请填写地址');
						return false;
					}
					s1 = s1.replaceAll("\r", "");
					arr1 = s1.split("\n");
					for (j = arr1.length - 1; j >= 0; j--) {
						if (arr1[j].length > 0) {
							s2 += arr1[j] + "\r\n";
						}
					}
					$("#data_" + arr[1] + " #vod_url").val(s2.trim());
					Toastr.success('排序成功');
				});
				$(document).on("click", ".removeurl", function() {
					arr = $(this).parent().parent().parent().attr("id").split("_");
					s1 = $("#data_" + arr[1] + " #vod_url").val();
					s2 = "";
					if (s1.length == 0) {
						Toastr.error('请填写地址');
						return false;
					}
					s1 = s1.replaceAll("\r", "");
					arr1 = s1.split("\n");
					for (j = 0; j < arr1.length; j++) {
						if (arr1[j].length > 0) {
							urlarr = arr1[j].split('$');
							urlarrcount = urlarr.length - 1;
							if (urlarrcount == 0) {
								arr1[j] = arr1[j];
							} else {
								arr1[j] = urlarr[1];
							}
							s2 += arr1[j] + "\r\n";
						}
					}
					$("#data_" + arr[1] + " #vod_url").val(s2.trim());
					Toastr.success('去前缀成功');
				});

				$(document).on("click", ".checkurl", function() {
					arr = $(this).parent().parent().parent().attr("id").split("_");
					s1 = $("#data_" + arr[1] + " #vod_url").val();
					s2 = "";
					if (s1.length == 0) {
						Toastr.error('请填写地址');
						return false;
					}
					s1 = s1.replaceAll("\r", "");
					arr1 = s1.split("\n");
					arr1len = arr1.length;
					for (j = 0; j < arr1len; j++) {
						if (arr1[j].length > 0) {
							urlarr = arr1[j].split('$');
							urlarrcount = urlarr.length - 1;
							if (urlarrcount == 0) {
								arr1[j] = getPatName(j, arr1len, arr1[j]) + '$' + arr1[j];
							}
							s2 += arr1[j] + "\r\n";
						}
					}
					$("#data_" + arr[1] + " #vod_url").val(s2.trim());
					Toastr.success('验校成功');
				});

				$(document).on("click", ".delurl", function() {
					arr = $(this).parent().parent().parent().attr("id").split("_");
					var i = $("#urldata #vod_url").length;
					if (i != 1) {
						$("#data_" + arr[1]).remove();
						Toastr.success('删除成功');
					} else {
						Toastr.error('已经最后一个鸟~~');
					}
				});
				$(document).on("click", ".upurl", function() {
					var objParentTR = $(this).parent().parent().parent();
					var prevTR = objParentTR.prev();
					if (prevTR.length > 0) {
						prevTR.insertAfter(objParentTR);
						Toastr.info('上移成功');
					} else {
						Toastr.error('已经是开头鸟~~');
					}
				});
				$(document).on("click", ".downurl", function() {
					var objParentTR = $(this).parent().parent().parent();
					var nextTR = objParentTR.next();
					if (nextTR.length > 0) {
						nextTR.insertBefore(objParentTR);
						Toastr.info('下移成功');
					} else {
						Toastr.error('已经是最后鸟~~');
					}
				});

				$(document).on('click', ".addurl", function() {
					var $old = $("#urldata>.urldata:last").html();
					$urln = $("#urldata>.urldata").length;
					$old = $old.replace("播放地址" + $urln, "播放地址" + ($urln + 1));
					$("#urldata>.urldata:last-child").after('<div class="urldata" id="data_' + ($urln + 1) + '" style="background:#f9f9f9; overflow:hidden; padding:10px 0px;">' + $old + '</div>');
					$("#urldata>.urldata:last #vod_play").val($("#vod_play:last option").val());
					$("#urldata>.urldata:last textarea").val('');
				})
				$(document).on("click", "#getkeywords", function() {
					if ($("#vod_skeywords").val() != '') {
						var name = $("#vod_skeywords").val();
					} else if ($("#vod_name").val() != '') {
						var name = $("#vod_name").val();
					}
					$.post("get/keywords", {
						name: name,
					}, function(data) {
						var o = eval("(" + data + ")");
						if (o.keywords) {
							Toastr.success('获取关键词成功');
							$("#vod_skeywords").val(o.keywords);
						} else {
							Toastr.error('获取关键词失败');
						}
					});
				});	
				$(document).on("click", "#getstorykeywords", function() {
					if ($("#story_keywords").val() != '') {
						var name = $("#story_keywords").val();
					} else if ($("#vod_name").val() != '') {
						var name = $("#vod_name").val() + '剧情';
					}
					$.post("get/keywords", {
						name: name,
					}, function(data) {
						var o = eval("(" + data + ")");
						if (o.keywords) {
							Toastr.success('获取关键词成功');
							$("#story_keywords").val(o.keywords);
						} else {
							Toastr.error('获取关键词失败');
						}
					});
				});		
				$(document).on("click", "#getactorkeywords", function() {
					if ($("#actor_keywords").val() != '') {
						var name = $("#actor_keywords").val();
					} else if ($("#vod_name").val() != '') {
						var name = $("#vod_name").val() + '演员';
					}
					$.post("get/keywords", {
						name: name,
					}, function(data) {
						var o = eval("(" + data + ")");
						if (o.keywords) {
							Toastr.success('获取关键词成功');
							$("#actor_keywords").val(o.keywords);
						} else {
							Toastr.error('获取关键词失败');
						}
					});
				});					
				if ($("#contents").size()>0 && $("input[name=vod_id]").val()){
					vid=$("input[name=vod_id]").val();
					url= 'role/actor/vid/'+vid+'/hidden/1/';
					pagegooo(url);
				}	
				$(document).on("click", "#getdoubaninfo", function() {
					if ($("#vod_doubanid").val() != '') {
						var $data = "url="+$("#vod_doubanid").val();
						$.ajax({
							type: 'post',
							url: 'get/vod',
							data: $data,
							dataType: 'json',
							success: function($string) {
								if ($string.name) {
									$("#vod_name").val($string.name);
					                $("#vod_aliases").val($string.stitle);
					                $("#vod_filmtime").val($string.filmtime);
					                $("#vod_language").val($string.language);
					                $("#vod_length").val($string.length);
					                $("#vod_area").val($string.area);
					                $("#vod_total").val($string.total);
					                $("#vod_year").val($string.year);
					                $("#vod_director").val($string.director);
					                $("#vod_actor").val($string.actor);
					                $("#vod_pic").val($string.pic);
					                $("#vod_keywords").val($string.tags);
					                $("#vod_gold").val($string.gold);
					                $("#vod_golder").val($string.golder);
					                $("#vod_hits").val($string.golder);
					                $("#vod_diantai").val($string.diantai);
					                $("#vod_content").summernote('code', $string.content);
									Toastr.success('获取豆瓣资料成功');
								} else {
									Toastr.error('获取豆瓣资料失败');
								}

							}
						});
					}
				});	
				$(document).on("click", "#getbaikeinfo", function() {
					if ($("#vod_baike").val() != '') {
						var $data = "url="+$("#vod_baike").val();
						$.ajax({
							type: 'post',
							url: 'get/vod',
							data: $data,
							dataType: 'json',
							success: function($string) {
								if ($string.name) {
									$("#vod_name").val($string.name);
					                $("#vod_aliases").val($string.stitle);
					                $("#vod_filmtime").val($string.filmtime);
					                $("#vod_language").val($string.language);
					                $("#vod_length").val($string.length);
					                $("#vod_area").val($string.area);
					                $("#vod_total").val($string.total);
					                $("#vod_year").val($string.year);
					                $("#vod_director").val($string.director);
					                $("#vod_actor").val($string.actor);
					                $("#vod_pic").val($string.pic);
					                $("#vod_keywords").val($string.tags);
					                $("#vod_gold").val($string.gold);
					                $("#vod_golder").val($string.golder);
					                $("#vod_hits").val($string.golder);
					                $("#vod_diantai").val($string.diantai);
					                $("#vod_content").summernote('code', $string.content);
									Toastr.success('获取资料成功');
								} else {
									Toastr.error('获取资料失败');
								}

							}
						});
					}
				});	
				$(document).on("click", "#getrole", function() {
					if($("#actor_reurl").val()!='' && $("#vod_id").val()!=''){
						$.ajax({
							type: 'post',
							url: 'getactor/role',
							data: {vid:$("#vod_id").val(), url:$("#actor_reurl").val()},
							dataType: 'json',
							success: function(data) {
								if(data.code == 1) {			   
								Toastr.success(data.msg ? data.msg : '操作成功');
								pagegooo(data.url)
								}else{
								Toastr.error(data.msg ? data.msg : '操作失败');	
								}

							}
						});
					}
				});					
                     $(document).on("click", "#getdoubancm", function() {
			           if($("#vod_doubanid").val()!='' && $("#vod_id").val()!=''){
						  var url = 'getcm/getdouban/vid/'+$("#vod_id").val()+'/id/'+$("#vod_doubanid").val();
						  $.get( url).success(function(data) {
								if (data.code == 1) {			   
								Toastr.success(data.msg ? data.msg : '操作成功');
								}else{
								Toastr.error(data.msg ? data.msg : '操作失败');	
								}
							}); 
			           }																			
                     });		 
				$(document).on("click", "#getstory", function() {
					if ($("#story_url").val() != '') {
						var $data = "url=" + $("#story_url").val();
						$.ajax({
							type: 'post',
							url: 'get/story',
							data: $data,
							dataType: 'json',
							success: function($string) {
								if ($string.story_content) {
									$("#story_title").val($string.story_title);
									$("#story_page").val($string.story_page);
									$("#story_cont").val($string.story_cont);
									$("#story_continu").val($string.story_continu);
									$("#story_url").val($string.story_url);
									$('#story_content').summernote('code', $string.story_content);
									Toastr.success('获取剧情成功');
								} else {
									Toastr.error('获取剧情失败');
								}
							}

						});
					}
				});	
				$(document).on("click", ".add-vod-name", function() {												  
				arr = $(this).attr("data").split("_");	
			    var val = parent.$('#news_vod').val();
		        if(val!=''){
			        val = val+','+arr[0];
		        }else{
			       val = arr[0];
		        }
                parent.$('#news_vod').val(val);	
				$(this).addClass('disabled').prop('disabled', false);
				Toastr.success('添加成功');
				});	
			}
		}
	};
	return Controller;
});