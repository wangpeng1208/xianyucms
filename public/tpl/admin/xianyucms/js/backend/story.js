define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
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
				$(document).on("click", "#getkeywords", function() {
					if ($("#keywords").val() != '') {
						var name = $("#keywords").val();
					} else if ($("#vod_name").val() != '') {
						var name = $("#vod_name").val() + '剧情';
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
			}
		}
	};
	return Controller;
});