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
				$(document).on('click', ".addtv", function() {
					var $old = $("#tvdata>tvdata:last").html();
					$urln = $("#tvdata>tvdata").length;
					$old = $old.replace("周期"+$urln,"周期"+($urln + 1));
					$("#tvdata>tvdata:last-child").after('<tvdata>' + $old + '</tvdata>');
					$("#tvdata>tvdata:last textarea").val('');
				})
				$(document).on("click", "#getkeywords", function() {
					if ($("#keywords").val() != '') {
						var name = $("#keywords").val();
					} else if ($("#tv_name").val() != '') {
						var name = $("#tv_name").val();
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