define(['jquery', 'bootstrap', 'backend', 'form', 'table'], function ($, undefined, Backend, Form, Table) {

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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                var refreshkey = function (data) {
                    $("input[name='auto_eventkey']").val(data[1]).trigger("change");
                    Layer.closeAll();
                    var keytitle = data[0];
                    var cont = $(".clickbox .create-click:first");
                    $(".keytitle", cont).remove();
                    if (keytitle) {
                        cont.append('<div class="keytitle">' + __('Event key') + ':' + keytitle + '</div>');
                    }
                };
                $(document).on('click', "#select-resources", function () {
                    var key = $("input[name='auto_eventkey']").val();
                    parent.Backend.api.open($(this).attr("href") + "?key=" + key,$(this).attr("title"), {callback: refreshkey});
                    return false;
                });
                $(document).on('click', "#add-resources", function () {
                    parent.Backend.api.open($(this).attr("href") + "?key=",$(this).attr("title"), {callback: refreshkey},'','95%');
                    return false;
                });
            }
        }

    };
    return Controller;
});