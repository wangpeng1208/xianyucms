define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'messager', 'droppable', 'board'], function($, undefined, Backend, Table, Form){
	var Controller = {
		index: function() {
			Table.api.bindevent();
			Controller.api.bindevent();
		},
		add: function() {
			Controller.api.bindevent();
		},
		edit: function() {
			Controller.api.bindevent();
			$(document).ready(function() {
				$('.form-group #field_sort').boards();
				$('.form-group #field_group_sort').boards({
					drop: function(e) {
						var group = e.target.closest('.board').find('.board-list').attr('data-group');
						e.element.find('input').attr('name', 'field_sort[' + group + '][]')
					}
				})
			})
		},
		editpwd: function() {
			Controller.api.bindevent();
		},
		api: {
			bindevent: function() {
				Form.api.bindevent($("form[role=form]"));
			}
		}
	};
	return Controller;
});