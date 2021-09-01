define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													  
    var Controller = {
        index: function () {	
		    $total=$(".total").attr("total");
			if($total){
			Backend.api.sidebar({'admin/channel/index': $total});
			}			
		Table.api.bindevent();
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            Controller.api.bindevent();
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            Controller.api.bindevent();
        },	
        api: {
            bindevent: function () {
			 $(document).on("change", "select[name='list_sid']", function () {
                    var id = $(this).val();
					url="channel/show/id/"+id;
					$.get(url,function(data,status){				
					$("#contents").html(data);
	                });	
                });	
			 	$(document).on("change", "select[name='list_vipplay']", function() {
				if($(this).val()==1 || $(this).val()==3){
					$("#trysee").show();
				}else{
					$("#trysee").hide();
				}
				
			});
			// $("select[name='list_sid']").trigger("change");
            }
        }
    };
    return Controller;
});