define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'adminlte'], function ($, undefined, Backend, Table, Form, Adminlte) {

    var Controller = {
        index: function () {
           Table.api.bindevent();
		   $('div').find('ul').each(function(){
											 
		   })
		   $(document).on('click', ".btn-chooseone", function () {
			   var arr1 = [$(this).attr('title'),$(this).attr('eventkey')];											 
		       Fast.api.close(arr1);
           })
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"), function (data) {
                Fast.api.close(data);
            });
            Controller.api.bindevent();
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {				
                $(document).on("change", "select[name='response_type']", function () {
					var app=$(this).val();														   
                    var id = $('[name=response_id]').val();
					url="wechat.response/select/app/"+app+"/id/"+id;
					$.get(url,function(data,status){				
					$("#expand").html(data);
	                });	
                });			
                $(document).on('click', ".btn-insertlink", function () {
                    var textarea = $("textarea[name='row[content][content]']");
                    var cursorPos = textarea.prop('selectionStart');
                    var v = textarea.val();
                    var textBefore = v.substring(0, cursorPos);
                    var textAfter = v.substring(cursorPos, v.length);

                    Layer.prompt({title: '请输入显示的文字', formType: 3}, function (text, index) {
                        Layer.close(index);
                        Layer.prompt({title: '请输入跳转的链接URL(包含http)', formType: 3}, function (link, index) {
                            text = text == '' ? link : text;
                            textarea.val(textBefore + '<a href="' + link + '">' + text + '</a>' + textAfter);
                            Layer.close(index);
                        });
                    });
                });
                $("input[name='row[type]']:checked").trigger("click");
            }
        }
    };
    return Controller;
});