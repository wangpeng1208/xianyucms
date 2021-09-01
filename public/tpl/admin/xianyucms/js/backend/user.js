define(['jquery', 'bootstrap', 'backend', 'table', 'form','summernote'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {	
		    Controller.api.bindevent();
        },
        show: function () {
		    $total=$(".pagination-info").attr("total");
			if($total){
			Backend.api.sidebar({'admin/user/show': $total});
			}				
		   Controller.api.bindevent();
        },		
        nav: function () {		
		   Table.api.bindevent();
        },		
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
			$(document).on("click", "#addscore", function (){										   
				$('.addscore').toggleClass("hidden");
            });	
			$(document).on("click", ".ajax-add-score", function() {
					if($("#add-score").val()!='' && $("#userid").val()!=''){
						$.ajax({
							type: 'post',
							url: 'user/addscore',
							data: {score:$("#add-score").val(), userid:$("#userid").val()},
							dataType: 'json',
							success: function(data){
								if(data.code == 1) {			   
								Toastr.success(data.msg ? data.msg : '操作成功');
								$("#score").val(data.data);
								}else{
								Toastr.error(data.msg ? data.msg : '操作失败');	
								}

							}
						});
					}
				});	
				$(document).on("click", "#addviptime", function (){										   
				$('.addvip').toggleClass("hidden");
                });	
				$(document).on("click", ".ajax-add-day", function() {
					if($("#add-day").val()!='' && $("#userid").val()!=''){
						$.ajax({
							type: 'post',
							url: 'user/addvip',
							data: {day:$("#add-day").val(), userid:$("#userid").val(),viptime:$("#viptime").val(),score:$("#score").val()},
							dataType: 'json',
							success: function(data){
								if(data.rcode >= 1) {			   
								Toastr.success(data.msg ? data.msg : '操作成功');
								$("#viptime").val(data.data);
								$("#score").val(data.score);
								}else{
								Toastr.error(data.msg ? data.msg : '操作失败');	
								}

							}
						});
					}
				});		
        },
        navedit: function () {
            Controller.api.bindevent();
        },
        navadd: function () {
            Controller.api.bindevent();
        },			
        api: {
            bindevent: function () {
				Table.api.bindevent();
                Form.api.bindevent($("form[role=form]"));
				 $(document).on("click", ".testmail", function (){										   
				 Backend.api.ajax({url: "user/testmail", data: {receiver: $('input[name="info[mail_test]"]').val()}});
                });	 
            }
        }
    };
    return Controller;
});