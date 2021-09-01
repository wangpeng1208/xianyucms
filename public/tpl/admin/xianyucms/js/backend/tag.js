define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													  
    var Controller = {
        index: function () {	
		 Table.api.bindevent();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
		showajax: function () {
			$(document).on("click", ".addtag", function(){
				sid=$(this).attr("sid");
				tag=$(this).attr("name");
                var val = parent.$('.tag').val();
		        if(val!=''){
			        val = val+','+tag;
		        }else{
			       val = tag;
		        }
               parent.$('.tag').val(val);												  
			});											  
            Controller.api.bindevent();
        },
        editpwd: function () {
            Controller.api.bindevent();
        },		
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});