define(['jquery','backend'], function ($, Backend) {												
    var Controller = {
        index: function () {
              $(document).on("click", ".login", function(e) {
					var target, query, form;
					var target_form = $(this).attr('target-form');
					var that = this;
					var nead_confirm = false;
					if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('url'))) {
						form = $('.' + target_form);
						if ($(this).attr('hide-data') === 'true') { //无数据时也可以使用的功能
							form = $('.hide-data');
							query = form.serialize();
						} else if (form.get(0) == undefined) {
							return false;
						} else if (form.get(0).nodeName == 'FORM') {
							if ($(this).attr('url') !== undefined) {
								target = $(this).attr('url');
							} else {
								target = form.get(0).action;
							}
							query = form.serialize();

						} else if (form.get(0).nodeName == 'INPUT' || form.get(0).nodeName == 'SELECT' || form.get(0).nodeName == 'TEXTAREA') {
							form.each(function(k, v) {
								if (v.type == 'checkbox' && v.checked == true) {
									nead_confirm = true;
								}
							})
							query = form.serialize();
						} else {
							query = form.find('input,select,textarea').serialize();
						}
						$.post(target, query).success(function(data) {
								if (data.code == 1) {
									if (data.url) {
										Toastr.success(data.msg ? data.msg : '登录成功');
										setTimeout(function() {
							               location.href = data.url;
					                    }, 1500);
									} else {
										Toastr.success(data.msg ? data.msg : '登录成功');
									}
								} else {
									    $("#verify").attr("src",$("#verify").attr('src')+'?tm='+Math.random());  
									    Toastr.error(data.msg ? data.msg : '登录失败');
										
								}
							});
					}
					return false;
				});	
			  $(document).on("click", "#verify", function(e) {
				$("#verify").attr("src",$("#verify").attr('src')+'?tm='+Math.random()); 										 
			  });												 
        },	 	
    };
    return Controller;
});