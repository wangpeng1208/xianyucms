define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'bigcolorpicker'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
		    $total=$(".pagination-info").attr("total");
			if($total){
			Backend.api.sidebar({'admin/news/index': $total});
			}			
			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
		},
		editpwd: function() {
			Controller.api.bindevent();
		},
		api: {
			bindevent: function() {
				Form.api.bindevent($("form[role=form]"));
				Table.api.bindevent();
				$(document).ready(function() {
					$("#color").bigColorpicker("color");
					$("#c5").bigColorpicker("c5", "L", 3);
				});
				if ($("#specialhtml").size()>0) {
					      tid=$("#specialhtml").attr("tid");
						  nid=$("#specialhtml").attr("nid");
					      relation_ajax(nid,tid,0,'show',0,'special',2);
						  $(document).on("click", ".addajax", function() {
							arr=$(this).attr("id").split("_");										   
							relation_ajax(arr[0],arr[1],arr[2],arr[3],arr[4],arr[5],arr[6]);	
							$(this).addClass('disabled').prop('disabled', false);
						  })
				}
				if ($("#starhtml").size()>0) {
						  did=$("#starhtml").attr("did");
					      relation_ajax(0,0,did,'show',0,'star',3);
						  $(document).on("click", ".addajax", function() {
							arr=$(this).attr("id").split("_");										   
							relation_ajax(arr[0],arr[1],arr[2],arr[3],arr[4],arr[5],arr[6]);	
							$(this).addClass('disabled').prop('disabled', false);
						  })
				}
				if ($("#vodhtml").size()>0) {
						  did=$("#vodhtml").attr("did");
					      relation_ajax(0,0,did,'show',0,'vod',1);
						  $(document).on("click", ".addajax", function() {
							arr=$(this).attr("id").split("_");										   
							relation_ajax(arr[0],arr[1],arr[2],arr[3],arr[4],arr[5],arr[6]);	
							$(this).addClass('disabled').prop('disabled', false);
						  })
				}				
				
				
			}
		}
	};
	return Controller;
});