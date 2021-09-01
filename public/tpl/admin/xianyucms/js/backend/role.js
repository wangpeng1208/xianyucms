define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {		
			Controller.api.bindevent();
		},
		actor: function() {
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
				if ($("#specialhtml").size()>0) {
					      tid=$("#specialhtml").attr("tid");
					      news_ajax(tid,0,'show',0,'special',2);
						  $(document).on("click", ".addajax", function() {
							arr=$(this).attr("id").split("_");										   
							news_ajax(arr[0],arr[1],arr[2],arr[3],arr[4],arr[5]);									 
						  })
				}
				$(document).on("click", "#getrole", function() {
					if($("#actor_reurl").val()!='' && $("#vod_id").val()!=''){
						$.ajax({
							type: 'post',
							url: 'getactor/role',
							data: {vid:$("#vod_id").val(), url:$("#actor_reurl").val()},
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