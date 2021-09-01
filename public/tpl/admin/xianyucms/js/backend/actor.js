define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
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
		   $(document).on('click', ".btn-success", function () {										 
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
				if ($("#contents").size()>0 && $("input[name=actor_vid]").val()){
					vid=$("input[name=actor_vid]").val();
					url= 'role/actor/vid/'+vid+'/hidden/1/';
					pagegooo(url);
				}
				$(document).on("click", "#getkeywords", function() {
					if ($("#keywords").val() != '') {
						var name = $("#keywords").val();
					} else if ($("#vod_name").val() != '') {
						var name = $("#vod_name").val() + '演员';
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
				$(document).on("click", "#getrole", function() {
					if($("#actor_reurl").val()!='' && $("#actor_vid").val()!=''){
						$.ajax({
							type: 'post',
							url: 'getactor/role',
							data: {vid:$("#actor_vid").val(), url:$("#actor_reurl").val()},
							dataType: 'json',
							success: function(data) {
								if(data.code == 1) {			   
								Toastr.success(data.msg ? data.msg : '操作成功');
								pagegooo(data.url)
								}else{
								Toastr.error(data.msg ? data.msg : '操作失败');	
								}

							}
						});
					}
				});					
				
			}
		}
	};
	return Controller;
});