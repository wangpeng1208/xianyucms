define(['jquery', 'bootstrap', 'backend', 'table', 'form','summernote'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {	
		    Controller.api.bindevent();
        },
        show: function () {
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
            }
        }
    };
    return Controller;
});