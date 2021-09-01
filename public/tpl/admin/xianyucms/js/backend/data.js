define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
	var $form = $("#export-form"),
		$export = $("#export"),
		tables
		$optimize = $("#optimize"),
		$repair = $("#repair");

	function backup(tab, status) {
		status && showmsg(tab.id, "开始备份...(0%)");
		$.get($form.attr("action"), tab, function(data) {
			if (data.code) {
				var info = data.data;
				showmsg(tab.id, data.msg);
				if (!$.isPlainObject(info.tab)) {
					$("#export").parent().children().removeClass("disabled");
					$("#export").html("备份完成，点击重新备份");
					window.onbeforeunload = function() {
						return null
					}
					return;
				}
				backup(info.tab, tab.id != info.tab.id);
			} else {
				Toastr.error(data.msg ? data.msg : '操作失败');
				$("#export").parent().children().removeClass("disabled");
				$("#export").html("立即备份");
				setTimeout(function() {
					$('#top-alert').find('button').click();
					$(that).removeClass('disabled').prop('disabled', false);
				}, 1500);
			}
		}, "json");

	}
	function showmsg(id, msg) {
		$form.find("input[value=" + tables[id] + "]").closest("tr").find(".info").html(msg);
	}
	var Controller = {
		index: function() {
			Table.api.bindevent();
			//数据库备份
			$(document).on("click", "#export", function() {
				$("#export").parent().children().addClass("disabled");
				$("#export").html("正在发送备份请求...");
				$.post(
				$form.attr("action"), $form.serialize(), function(data) {
					if (data.code) {
						Toastr.success('操作成功,开始备份');
						tables = data.data.tables;
						$("#export").html(data.msg + "开始备份，请不要关闭本页面！");
						backup(data.data.tab);
						window.onbeforeunload = function() {
							return "正在备份数据库，请不要关闭！"
						}
					} else {
						Toastr.error(data.msg ? data.msg : '操作失败');
						$("#export").parent().children().removeClass("disabled");
						$("#export").html("立即备份");
						setTimeout(function() {
							$('#top-alert').find('button').click();
							$(that).removeClass('disabled').prop('disabled', false);
						}, 1500);
					}
				}, "json");
				return false;
			});
			//数据库还原
			$(document).on("click", "#db-import", function() {
				var self = this,
					status = ".";
				if ($(this).hasClass('confirm')) {
					layer.confirm('确认要执行该操作吗?', {
						icon: 3,
						title: '温馨提醒',
						btn: ['确定', '取消']
					}, function(index, layero) {
						layer.close(index);
						$.get(self.href, success, "json");
						window.onbeforeunload = function() {
							return "正在还原数据库，请不要关闭！"
						}
						return false;
						function success(data) {
							if (data.code) {
								if (data.data.gz) {
									data.msg += status;
									if (status.length === 5) {
										status = ".";
									} else {
										status += ".";
									}
								}
								$(self).parent().prev().text(data.msg);
								if (data.data.part) {
									$.get(self.href, {
										"part": data.data.part,
										"start": data.data.start
									}, success, "json");
								} else {
									window.onbeforeunload = function() {
										return null;
									}
								}
							} else {
								Toastr.error(data.msg ? data.msg : '操作失败');
							}
						}
					})
					return false;
				};
			});
		},
	replace: function() {
	Form.api.bindevent($("form[role=form]"));	
	//字段加载
    $(document).on("click", "#field", function () {
		$('#rpfield').val($(this).attr('title')); 
	});														  
	$(document).on("change", "select[name='exptable']", function () {
                    var id = $(this).val();
                    $.ajax({
                        url: "data/ajaxfields",
                        type: 'post',
                        dataType: 'json',
                        data: {id:id},
                        success: function (data) {
                    if (data.code == 1) {
					Toastr.success(data.msg ? data.msg : '获取成功');
					$("#fields").html(data.data);
				} else {
					Toastr.error(data.msg ? data.msg : '获取失败');
				}							
						}
                    });
                });			
		},
	};
	return Controller;
});