define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {													  
    var Controller = {
        index: function () {	
		    Table.api.bindevent();
 $(document).on('click', ".liunx_url", function () {
												 
		       		$content=$(this).attr('data-content');
					layer.open({
  type: 1,
  anim: 2,
  offset: '50px',
  title: 'liunx定时地址',
  area: ['auto', 'auto'],
  skin: 'layui-layer-rim', //加上边框
  content: '<div style="padding:30px 50px;font-size:14px;"><p style="text-align:center">将下列地址分段复制后添加到计划任务</p><p style="text-align:center">'+$content+'</p></div>'
});


           })			
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
				if ($("select[name='timming_type'] option:selected").val() == 2) {
				  $("#collect_list,#collect_action,#timming_apitime").hide();
			    }else{
				  $("#collect_list,#timming_htmlaction,#timming_apitime").show();
				}
			    $(document).on("change", "select[name='timming_type']", function() {
				if ($(this).val() == 1) {
					$("#collect_list,#collect_action,#timming_apitime").show();
				} else {
					$("#collect_list,#collect_action,#timming_apitime").hide();
				}
			   });							
			    $('#collect_action').change(function(){
				$('#timming_apipar').val($("select[name='collect_action'] option:selected").val());
			    })					
            }
        }
    };
    return Controller;
});