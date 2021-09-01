define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
		    Table.api.bindevent();		
			
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
		},
		data: function() {
			Table.api.bindevent();	
			$(document).on("click", ".collect-post", function(e) {
				var target, query, form;
				var target_form = $(this).attr('target-form');
				var that = this;
				var nead_confirm = false;
				if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('url'))) {
					$('#form').attr('action', target);
					$('#form').submit();
				}
				return false;
			});
			//分类绑定
			$(document.body).on("click", ".bind", function(e) {
				var offset = $(this).offset();
				var left = offset.left - 40;
				var top = offset.top + 15;
				if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
					$.ajax({
						url: target,
						cache: false,
						async: false,
						success: function(res) {
							$("#setbind").css({
								left: left,
								top: top,
								display: ""
							});
							$("#setbind").html(res);
						}
					});
				}
			});
			$(document.body).on("click", 'button[type="submitbind"]', function(e) {
				target = $(this).attr('url');
				bind = $(this).attr("id");
				$.ajax({
					url: target,
					data: {
						cid: $('#cid').val(),
						bind: bind
					},
					success: function(res) {
						if (res.code == 1) {
							$("#bind_" + bind).html("<font color='#f39c12'> 已绑定</font");
							Toastr.success(res.msg ? res.msg : '绑定成功');
						} else if (res.code == 2) {
							$("#bind_" + bind).html("<font color='#e74c3c'> 未绑定</font>");
							Toastr.success(res.msg ? res.msg : '取消成功');
						} else {
							Toastr.error(res.msg ? res.msg : '绑定失败');
						}
						$('#showbg').css({
							width: 0,
							height: 0
						});
						$('#setbind').hide();
					}
				});
			});
			$(document.body).on("click", 'button[type="hidebind"]', function(e) {
				$('#showbg').css({
					width: 0,
					height: 0
				});
				$('#setbind').hide();
			});
		},
		api: {
			bindevent: function() {
				Form.api.bindevent($("form[role=form]"));
			}
		}
	};
	return Controller;
});