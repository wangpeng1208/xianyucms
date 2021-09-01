define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													
    var Controller = {
        index: function () {	
		Table.api.bindevent();
            //显示隐藏子节点
            $(document.body).on("click", ".btn-node-sub", function (e) {
                    var status = $(this).data("shown") ? true : false;
                    $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
                        $(this).closest("tr").toggle(!status);
                    });
                    $(this).data("shown", !status);
                    return false;
                });													 
            //展开隐藏一级
            $(document.body).on("click", ".btn-toggle", function (e) {												 
                $("a.btn[data-id][data-pid][data-pid!=0].disabled").closest("tr").show();
                var that = this;
                var show = $("i", that).hasClass("fa-chevron-up");
                $("i", that).toggleClass("fa-chevron-up", !show);
                $("i", that).toggleClass("fa-chevron-down", show);
                $("a.btn[data-id][data-pid][data-pid!=0]").closest("tr").toggle(show);
                $(".btn-node-sub[data-pid=!0]").data("shown", show);
            });		
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
            }
        }
    };
    return Controller;
});