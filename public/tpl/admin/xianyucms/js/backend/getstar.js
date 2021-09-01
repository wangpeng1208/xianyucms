define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function($, undefined, Backend, Table, Form) {
	var Controller = {
		index: function() {
		  Table.api.bindevent();
          $(document).on("click", ".collect-post", function(e) {
				var target, query, form;
				var target_form = $(this).attr('target-form');
				var that = this;
				var nead_confirm = false;
				if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('url'))) {
					$('#form').attr('action', target);
					$('#form').submit();
				}
				return false;
			});			
			},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
		},
		data: function() {
		},
		api: {
			bindevent: function() {
				Form.api.bindevent($("form[role=form]"));
			}
		}
	};
	return Controller;
});