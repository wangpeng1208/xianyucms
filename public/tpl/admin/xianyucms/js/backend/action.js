define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            Controller.api.bindevent();
        },
        logs: function () {
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
				Table.api.bindevent();
                Form.api.bindevent($("form[role=form]"));
            }

        }
    };
    return Controller;
});