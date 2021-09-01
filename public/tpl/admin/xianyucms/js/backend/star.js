define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
		    $total=$(".pagination-info").attr("total");
			if($total){
			Backend.api.sidebar({'admin/star/index': $total});	
			}
			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		addrole: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
		},
		editrole: function() {
			Controller.api.bindevent();
			$(document).on('click', ".btn-success", function() {
				Fast.api.close(arr1);
			})
		},
		editpwd: function() {
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
				$(document).on("click", "#getinfo", function() {
					if ($("#star_reurl").val() != '') {
						var $data = "url=" + $("#star_reurl").val();
						$.ajax({
							type: 'post',
							url: 'get/star',
							data: $data,
							dataType: 'json',
							success: function($string) {
								if ($string.name) {
									$("#star_name").val($string.name);
									$("#star_bm").val($string.bm);
									$("#star_wwm").val($string.wwm);
									$("#star_tz").val($string.tz);
									$("#star_mz").val($string.mz);
									$("#star_sg").val($string.sg);
									$("#star_gj").val($string.gj);
									$("#star_area").val($string.area);
									$("#star_csd").val($string.csd);
									$("#star_school").val($string.scool);
									$("#star_gs").val($string.gs);
									$("#star_weibo").val($string.weibo);
									$("#star_work").val($string.work);
									$("#star_guanxi").val($string.guanxi);
									$("#star_xb").val($string.xb);
									$("#star_bgcolor").val($string.color);
									$("#star_pig").val($string.pig);
									$("#star_xx").val($string.xx);
									$("#star_caiji").val($string.cai);
									$("#star_xz").val($string.xz);
									$("#star_zy").val($string.zy);
									$("#star_pic").val($string.im);
									$("#star_bigpic").val($string.pic);
									$("#star_data_info").summernote('code', $string.info);
									$("#star_cstime").val($string.cstime);
									$('#star_content').summernote('code', $string.content);
									if ($string.info) {
										$("input[name='star_caiji']").val("1");
									}
									Toastr.success('获取明星资料成功');
								} else {
									$("input[name='star_caiji']").val("");
									Toastr.error('获取明星资料失败');
								}

							}
						});
					}
				});
				$(document).on("click", ".delinfo", function() {
					var k = $(this).attr('id');
					var i = $("#stardata #star_data_info").length;
					if (i != 1) {
						$("#data_" + k).remove();
						Toastr.success('删除成功');
					} else {
						Toastr.error('已经最后一个鸟');
					}
				});
				$(document).on("click", "#getkeywords", function() {
					if ($("#keywords").val() != '') {
						var name = $("#keywords").val();
					} else if ($("#star_name").val() != '') {
						var name = $("#star_name").val();
					}
					$.post("get/keywords", {
						name: name,
					}, function(data) {
						var o = eval("(" + data + ")");
						if (o.keywords) {
							Toastr.success('获取关键词成功');
							$("#keywords").val(o.keywords);
						} else {
							Toastr.error('获取关键词失败');
						}
					});
				});
				$(document).on('click', ".addstar", function() {
					$urln = $("#stardata>stardata").length;
					str = '<stardata id="data_' + ($urln + 1) + '"><label for="c-keywords" class="control-label col-xs-12 col-sm-1">信息' + ($urln + 1) + '</label><div class="col-xs-12 col-sm-11"><div class="col-xs-12 col-sm-11" style="padding-left:0px;"><label for="c-keywords" class="control-label col-xs-12" style=" width:auto;padding-left:0px;">标题：</label><div class="col-xs-12" style=" width:auto"><input class="form-control"  name="star_data_title[]" id="star_data_title" value=""></div><a href="javascript:;"  class="btn btn-danger btn-del delinfo" id="' + ($urln + 1) + '"><i class="fa fa-minus"></i> 删除</a><div class="col-xs-12 col-sm-11" style=" margin-top:10px;padding-left:0px;"><textarea name="star_data_info[]" id="star_data_info" class="form-control summernote"></textarea></div></div></div></stardata>';
					$("#stardata>stardata:last-child").after('<stardata>' + str + '</stardata>');
					Form.events.summernote();
				})

				if ($("#specialhtml").size() > 0) {
					tid = $("#specialhtml").attr("tid");
					nid = $("#specialhtml").attr("nid");
					relation_ajax(nid, tid, 0, 'show', 0, 'special', 3);
					$(document).on("click", ".addajax", function() {
						arr = $(this).attr("id").split("_");
						relation_ajax(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6]);
						$(this).addClass('disabled').prop('disabled', false);
					})
				}
				if ($("#newshtml").size() > 0) {
					tid = $("#newshtml").attr("tid");
					nid = $("#newshtml").attr("nid");
					relation_ajax(nid, tid, 0, 'show', 0, 'news', 3);
					$(document).on("click", ".addajax", function() {
						arr = $(this).attr("id").split("_");
						relation_ajax(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6]);
						$(this).addClass('disabled').prop('disabled', false);
					})
				}
				$(document).on("click", ".add-star-name", function() {												  
				arr = $(this).attr("data").split("_");	
			    var val = parent.$('#news_star').val();
		        if(val!=''){
			        val = val+','+arr[0];
		        }else{
			       val = arr[0];
		        }
                parent.$('#news_star').val(val);	
				$(this).addClass('disabled').prop('disabled', false);
				Toastr.success('添加成功');
				});					
			}
		}
	};
	return Controller;
});