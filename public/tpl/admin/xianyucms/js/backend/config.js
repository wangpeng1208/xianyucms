define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
	function showtab(mid, no, n) {
		if (no == 1) {
			for (var i = 0; i <= n; i++) {
				$('#' + mid + i).show();
			}
		} else {
			for (var i = 0; i <= n; i++) {
				$('#' + mid + i).hide();
			}
		}
	}
	var Controller = {
		index: function() {
			if($("select[name='config[data_cache_type]'] option:selected").val() == 'redis') {
				showtab('redis_', 1, 3);
			}
			$(document).on("change", "select[name='config[data_cache_type]']", function() {
				if($(this).val()!='file'){
					 $.post("config/check",{name: $(this).val()},function(data){
						if (data.code !=1) {
                         alert(data.msg ? data.msg : '该缓存运行不正常，请慎重设置')
						}
                     });
				}																		
				if ($(this).val() == 'redis') {
					showtab('redis_', 1, 3);
				} else {
					showtab('redis_', 0, 3);
				}
			});	
			$(document).on("change", "select[name='config[player_http]']", function() {
				if($(this).val()==0){
                         alert('使用站内播放,存在版权问题，一切后果自负。')
				}																		
			});				
			if ($("select[name='config[upload_water]'] option:selected").val() == 1) {
				showtab('water', 1, 3);
			}			
			$(document).on("change", "select[name='config[upload_water]']", function() {
				if ($(this).val() == 1) {
					showtab('water', 1, 3);
				} else {
					showtab('water', 0, 3);
				}
			});
			if ($("select[name='config[upload_ftp]'] option:selected").val() == 1) {
				showtab('ftptab', 1, 6);
			}
			$(document).on("change", "select[name='config[upload_ftp]']", function() {
				if ($(this).val() == 1) {
					showtab('ftptab', 1, 6);
				} else {
					showtab('ftptab', 0, 6);
				}
			});
			if ($("select[name='config[upload_thumb]'] option:selected").val() == 1) {
				showtab('thumb', 1, 2);
			}
			$(document).on("change", "select[name='config[upload_thumb]']", function() {
				if ($(this).val() == 1) {
					showtab('thumb', 1, 2);
				} else {
					showtab('thumb', 0, 2);
				}
			});
			if ($("select[name='config[url_rewrite]'] option:selected").val() == 1) {
				showtab('rewrite', 1, 2);
			}
			$(document).on("change", "select[name='config[url_rewrite]']", function() {
				if ($(this).val() == 1) {
					showtab('rewrite', 1, 2);
				} else {
					showtab('rewrite', 0, 2);
				}
			});
			if ($("select[name='config[html_cache_on]'] option:selected").val() == 1) {
				showtab('html_cache_', 1, 24);
			}
			$(document).on("change", "select[name='config[html_cache_on]']", function() {
				if ($(this).val() == 1) {
					showtab('html_cache_', 1, 24);
				} else {
					showtab('html_cache_', 0, 24);
				}
			});

			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
		},
		show: function() {
			Table.api.bindevent();
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