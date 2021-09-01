define(['jquery', 'bootstrap', 'backend', 'table', 'form','bigcolorpicker'], function ($, undefined, Backend, Table, Form) {													  
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
				$(document).ready(function(){
				$("#color").bigColorpicker("color");
                $("#c5").bigColorpicker("c5", "L", 3);
				});
				
				
				
            }
        }
    };
    return Controller;
});