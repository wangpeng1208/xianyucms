define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
  function geturl(tab, data) {
		$.get(data.url).success(function(e) {
			if (e.code) {
				tab.html(e.msg);
				geturl(tab, e);
			} else {
				Toastr.error(e.msg ? e.msg : '操作失败');
				$(".play-del").parent().children().removeClass("disabled");
				$(".play-del").html("删除数据");
			}
		}, "json");

	}
	var Controller = {
		index: function() {
			Controller.api.bindevent();
			$(document).on("click", ".play-del", function() {
				var target;
				var that = this;
				var thistab = $(this);
				if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
					layer.confirm('确认要执行该操作吗,确定后将删除该视频中该播放器数据?', {
						icon: 3,
						title: '温馨提醒',
						btn: ['确定', '取消']
					}, function(index, layero) {
						layer.close(index);
						$(".play-del").parent().children().addClass("disabled");
						thistab.html("正在发请求...");
						$.get(target).success(function(data) {
							if (data.code) {
								Toastr.success('操作成功,开始执行');
								thistab.html(data.msg);
								geturl(thistab, data);
								window.onbeforeunload = function() {
									return "正在执行删除播放器数据，请不要关闭！"
								}
							} else {
								Toastr.error(data.msg ? data.msg : '操作失败');
								$(".play-del").parent().children().removeClass("disabled");
								$(".play-del").html("删除数据");
							}
						}, "json");
						return false;
					});
				};

			});
			Table.api.bindevent();
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
			}
		}
	};
	return Controller;
});