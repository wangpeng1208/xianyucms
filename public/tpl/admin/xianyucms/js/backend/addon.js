define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function($, undefined, Backend, Table, Form, Template) {
	var Controller = {
		index: function() {
		    $total=$(".row-flex").attr("total");
			if($total){
			Backend.api.sidebar({'admin/addon/index': $total});
			}	
			Template.helper("Moment", Moment);
			Template.helper("addons", Config['addons']);
			require(['upload'], function(Upload) {
				Upload.api.plupload("#plupload-addon", function(data, ret) {
					//alert(JSON.stringify(ret.msg));
					Toastr.success(ret.msg);
					Config['addons'][data.addon.name] = ret.data.addon;	
					$('.refurbish').trigger('click');
				});
			});
			//刷新列表
			$(document).on("click", ".refurbish", function(e) {
				var target;
				var that = this;
				var index = layer.load();
				if ((target = $(this).attr('url'))) {
					$.get(target, function(data, status) {	
						Backend.api.layer.close(index);
						var value = jQuery('#contents', data).html();
						$("#contents").html(value);
					});
				}
			});
			//本地列表
			$(document).on("click", ".downloaded", function(e) {
				var target;
				var that = this;
				var index = layer.load();
				if ((target = $(this).attr('url'))) {
					$.get(target, function(data, status) {	
						Backend.api.layer.close(index);
						var value = jQuery('#contents', data).html();
						$("#contents").html(value);
					});
				}
			});			
			//刷新列表
			$(document).on("click", ".btn-ajax", function(e) {
				var target;
				var that = this;
				if ((target = $(this).attr('url'))) {
					$.get(target, function(data, status) {	
						   if (data.code == 1) {
								Toastr.success(data.msg ? data.msg : '刷新成功');
								$('.refurbish').trigger('click');
						   }else{
							    Toastr.error(data.msg ? data.msg : '刷新失败');  
						}
						return false;
					});
				}
			});			
			//点击安装
			$(document).on("click", ".btn-install", function() {
				var name = $(this).closest(".operate").data("name");
				var install = function(name, force) {
						Fast.api.ajax({
							url: 'addon/install',
							data: {
								name: name,
								force: force ? 1 : 0
							}
						}, function(data, ret) {
							Layer.closeAll();
							Config['addons'][data.addon.name] = ret.data.addon;
                        Layer.alert('插件安装成功！清除缓存刷新页面后生效！', {
                            btn: ['确定'],
                            title: '温馨提示',
                            icon: 1,
                            btn2: function () {
                                //打赏
                                Layer.open({
                                    content: Template("paytpl", {payimg: $(that).data("donateimage")}),
                                    shade: 0.8,
                                    area: ['800px', '600px'],
                                    skin: 'layui-layer-msg layui-layer-pay',
                                    title: false,
                                    closeBtn: true,
                                    btn: false,
                                    resize: false,
                                });
                            }
                        });
                        $('.btn-refresh').trigger('click');
                    },function(data, ret) {
							//如果是需要购买的插件则弹出二维码提示
							if (ret && ret.code === -1) {
								//扫码支付
								Layer.open({
									content: Template("paytpl", ret.data),
									shade: 0.8,
									area: ['800px', '500px'],
									skin: 'layui-layer-msg layui-layer-pay',
									title: false,
									closeBtn: true,
									btn: false,
									resize: false,
									end: function() {
										Layer.alert("");
									}
								});
							} else if (ret && ret.code === -2) {
								//跳转支付
								Layer.alert('请点击这里在新窗口中进行支付！', {
									btn: ['立即支付', '取消'],
									icon: 0,
									success: function(layero) {
										$(".layui-layer-btn0", layero).attr("href", ret.data.payurl).attr("target", "_blank");
									}
								}, function() {
									Layer.alert("请在新弹出的窗口中进行支付，支付完成后再重新点击安装按钮进行安装！", {
										icon: 0
									});
								});

							} else if (ret && ret.code === -3) {
								//插件目录发现影响全局的文件
								Layer.open({
									content: Template("conflicttpl", ret.data),
									shade: 0.8,
									area: ['800px', '500px'],
									title: "温馨提示",
									btn: ['继续安装', '取消'],
									end: function() {

									},
									yes: function() {
										install(name, true);
									}
								});

							} else {
								Layer.alert(ret.msg);
							}
							return false;
						});
					};
				install(name, false);
			});

			//点击卸载
			$(document).on("click", ".btn-uninstall", function() {
				var name = $(this).closest(".operate").data("name");
				var uninstall = function(name, force) {
						Fast.api.ajax({
							url: 'addon/uninstall',
							data: {
								name: name,
								force: force ? 1 : 0
							}
						}, function(data, ret) {
							delete Config['addons'][name];
							Layer.closeAll();
							$('.refurbish').trigger('click');
						}, function(data, ret) {
							if (ret && ret.code === -3) {
								//插件目录发现影响全局的文件
								Layer.open({
									content: Template("conflicttpl", ret.data),
									shade: 0.8,
									area: ['800px', '500px'],
									title: "温馨提示",
									btn: ['继续卸载', '取消'],
									end: function() {

									},
									yes: function() {
										uninstall(name, true);
									}
								});

							} else {
								Layer.alert(ret.msg);
							}
							return false;
						});
					};
				Layer.confirm("确认卸载插件？<p class='text-danger'>卸载将会删除所有插件文件且不可找回!!! 插件如果有创建数据库表请手动删除!!!</p>如有重要数据请备份后再操作！", function() {
					uninstall(name, false);
				});
			});

			//点击配置
			$(document).on("click", ".btn-config", function() {
				var name = $(this).closest(".operate").data("name");
				Fast.api.open("addon/configs-name-" + name, "修改配置");
			});

			//点击启用/禁用
			$(document).on("click", ".btn-enable,.btn-disable", function() {
				var name = $(this).closest(".operate").data("name");
				var action = $(this).data("action");
				var operate = function(name, action, force) {
						Fast.api.ajax({
							url: 'addon/state',
							data: {
								name: name,
								action: action,
								force: force ? 1 : 0
							}
						}, function(data, ret) {
							var addon = Config['addons'][name];
							addon.state = action === 'enable' ? 1 : 0;
							Layer.closeAll();
							$('.refurbish').trigger('click');
						}, function(data, ret) {
							if (ret && ret.code === -3) {
								//插件目录发现影响全局的文件
								Layer.open({
									content: Template("conflicttpl", ret.data),
									shade: 0.8,
									area: ['800px', '500px'],
									title: "温馨提示",
									btn: ['继续操作', '取消'],
									end: function() {},
									yes: function() {
										operate(name, action, true);
									}
								});

							} else {
								Layer.alert(ret.msg);
							}
							return false;
						});
					};
				operate(name, action, false);
			});
		Controller.api.bindevent();			
		},
		add: function() {
			Controller.api.bindevent();
		},
		configs: function() {
			Controller.api.bindevent();
		},
		api: {
			bindevent: function() {
				Table.api.bindevent();
				Form.api.bindevent($("form[role=form]"));
			}
		}
	};
	return Controller;
});