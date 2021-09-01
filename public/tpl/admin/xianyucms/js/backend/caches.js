define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													  
    var Controller = {
        index: function () {	
		    Controller.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        editpwd: function () {
            Controller.api.bindevent();
        },		
        api: {
            bindevent: function () {
            Form.api.bindevent($("form[role=form]"));
			Table.api.bindevent();	
				$(document).on("click", ".post-cache", function(e) {
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
						if ($(this).hasClass('confirm')) {
							layer.confirm('确认要执行该操作吗?', {
								icon: 3,
								title: '温馨提醒',
								btn: ['确定', '取消']
							}, function(index, layero) {
								layer.close(index);
								$.post(target, query).success(function(data) {
									if (data.code == 1) {
										if (data.url) {
											Toastr.success(data.msg ? data.msg : '操作成功');
										} else {
											Toastr.success(data.msg ? data.msg : '操作成功');
										}
									} else {
										Toastr.error(data.msg ? data.msg : '操作失败');
									}
								});
							});
						} else {
							$.post(target, query).success(function(data) {
								if (data.code == 1) {
									if (data.url) {
										Toastr.success(data.msg ? data.msg : '操作成功');
									} else {
										Toastr.success(data.msg ? data.msg : '操作成功');
									}
								} else {
									Toastr.error(data.msg ? data.msg : '操作失败');
								}
							});

						}
					}
					return false;
				});			
            //清除缓存
            $(document).on('click', ".ajax-cache", function () {
                $.ajax({
                    url: $(this).attr('url'),
                    dataType: 'json',
                    cache: false,
                    success: function (ret) {
                        if (ret.hasOwnProperty("code")) {
                            var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                            if (ret.code === 1) {
                                Toastr.success(msg ? msg : __('Wipe cache completed'));
                            } else {
                                Toastr.error(msg ? msg : __('Wipe cache failed'));
                            }
                        } else {
                            Toastr.error(__('Unknown data format'));
                        }
                    }, error: function () {
                        Toastr.error(__('Network error'));
                    }
                });
            });				
				
            }
        }
    };
    return Controller;
});