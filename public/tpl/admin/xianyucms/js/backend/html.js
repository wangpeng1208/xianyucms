define(['jquery', 'bootstrap', 'backend', 'table', 'form','summernote'], function ($, undefined, Backend, Table, Form) {
if ($("#close").length > 0) {
			setTimeout(function() {
            var index=parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
			}, 1000);
}
(function($){
	$.fn.extend({
		"insert":function(value){
			//默认参数
			value=$.extend({
				"text":"123"
			},value);
			
			var dthis = $(this)[0]; //将jQuery对象转换为DOM元素
			
			//IE下
			if(document.selection){
				
				$(dthis).focus();		//输入元素textara获取焦点
				var fus = document.selection.createRange();//获取光标位置
				fus.text = value.text;	//在光标位置插入值
				$(dthis).focus();	///输入元素textara获取焦点
				
			
			}
			//火狐下标准	
			else if(dthis.selectionStart || dthis.selectionStart == '0'){
				
				var start = dthis.selectionStart; 
				var end = dthis.selectionEnd;
				var top = dthis.scrollTop;
				
				//以下这句，应该是在焦点之前，和焦点之后的位置，中间插入我们传入的值
				dthis.value = dthis.value.substring(0, start) + value.text + dthis.value.substring(end, dthis.value.length);
			}
			
			//在输入元素textara没有定位光标的情况
			else{
				this.value += value.text;
				this.focus();	
			};
			
			return $(this);
		}
	})
})(jQuery)																				   
    var Controller = {
        index: function () {
			$(document).on("change", "select[name='dir_html']", function() {
				var id = $(this).attr('data-id');
				var value = $(this).val();
				if(value){
	                $('#'+id).val(value);
	            }
			});	
			$('body').on("click", "#dir_html_add", function() {
				var name = $(this).attr('data-name');
				var value = $(this).attr('data-id');
				$('#'+name).focus();
				$('#'+name).insert({"text":value});
				return false;
			});	
			if ($("select[name='config[url_html_list]'] option:selected").val() == 0) {
				$("#list_div_vod,#list_div_news,#list_div_star,#list_div_story,#list_div_actor,#list_div_role,#list_div_tv,#list_div_special").hide();  
			}	
			$(document).on("change", "select[name='config[url_html_list]']", function() {
				if($(this).val()==1){
                    $("#list_div_vod,#list_div_news,#list_div_star,#list_div_story,#list_div_actor,#list_div_role,#list_div_tv,#list_div_special").show();     
				}else{
					$("#list_div_vod,#list_div_news,#list_div_star,#list_div_story,#list_div_actor,#list_div_role,#list_div_tv,#list_div_special").hide();  
					}																		
			});				
		    Controller.api.bindevent();
        },
        show: function () {
       $('body').on("click", "#html-post", function(){
		   var url = $(this).attr("data-url");									
	       $('#form').attr('action',url);
	       $('#form').submit();
       })			
			
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
        api: {
            bindevent: function () {
				Table.api.bindevent();
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});