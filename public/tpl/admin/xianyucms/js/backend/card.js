define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													  
    var Controller = {
        index: function () {	
		    Table.api.bindevent();
			 $(document).on('click', ".copy_num", function () {										 
		       		$html = new Array();
		            $(".copy_number").each(function(){
				       $html.push($(this).html());
		            });
					layer.open({
  type: 1,
  anim: 2,
  title: '卡密列表:请手动选择 CTRL+C复制',
  area: ['auto', 'auto'],
  skin: 'layui-layer-rim', //加上边框
  content: '<div style="padding:30px;font-size:14px;">'+$html.join('<br/>')+'</div>'
});


           })

			
        },
        error: function () {	
		    Table.api.bindevent();	
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