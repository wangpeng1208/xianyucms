define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'sortable'], function ($, undefined, Backend, Table, Form, Sortable) {
    var Controller = {
        index: function () {
            String.prototype.subByte = function (start, bytes) {
                for (var i = start; bytes > 0; i++) {
                    var code = this.charCodeAt(i);
                    bytes -= code < 256 ? 1 : 2;
                }
                return this.slice(start, i + bytes)
            };
            var init_menu = function (menu) {
                var str = "";
                var items = menu;
                var type = action = "";
                for (i in items) {
                    if (items[i]['sub_button'] != undefined) {
                        type = action = "";
                    } else {
                        type = items[i]['type'];
                        if (items[i]['url'] != undefined)
                            action = "url|" + items[i]['url'];
                        if (items[i]['key'] != undefined)
                            action = "key|" + items[i]['key'];
                    }
                    str += '<li id="menu-' + i + '" class="menu-item" data-type="' + type + '" data-action="' + action + '" data-name="' + items[i]['name'] + '"> <a href="javascript:;" class="menu-link"> <i class="icon-menu-dot"></i> <i class="weixin-icon sort-gray"></i> <span class="title">' + items[i]['name'] + '</span> </a>';
                    var tem = '';
                    if (items[i]['sub_button'] != undefined) {
                        var sub_menu = items[i]['sub_button'];
                        for (j in sub_menu) {
                            type = sub_menu[j]['type'];
                            if (sub_menu[j]['url'] != undefined)
                                action = "url|" + sub_menu[j]['url'];
                            if (sub_menu[j]['key'] != undefined)
                                action = "key|" + sub_menu[j]['key'];
                            tem += '<li id="sub-menu-' + j + '" class="sub-menu-item" data-type="' + type + '" data-action="' + action + '" data-name="' + sub_menu[j]['name'] + '"> <a href="javascript:;"> <i class="weixin-icon sort-gray"></i><span class="sub-title">' + sub_menu[j]['name'] + '</span></a> </li>';
                        }
                    }
                    str += '<div class="sub-menu-box" style="' + (i != 0 ? 'display:none;' : '') + '"> <ul class="sub-menu-list">' + tem + '<li class=" add-sub-item"><a href="javascript:;" title="???????????????"><span class=" "><i class="weixin-icon add-gray"></i></span></a></li> </ul> <i class="arrow arrow-out"></i> <i class="arrow arrow-in"></i></div>';
                    str += '</li>';
                }
                $("#add-item").before(str);
            };
            var refresh_type = function () {
                if ($('input[name=type]:checked').val() == 'view') {
                    $(".is-view").show();
                    $(".is-click").hide();
                } else {
                    $(".is-view").hide();
                    $(".is-click").show();
                }
            };
            //???????????????
            init_menu(menu);
            //????????????
            new Sortable($("#menu-list")[0], {draggable: 'li.menu-item'});
            $(".sub-menu-list").each(function () {
                new Sortable(this, {draggable: 'li.sub-menu-item'});
            });
            //???????????????
            $(document).on('click', '#add-item', function () {
                var menu_item_total = $(".menu-item").size();
                if (menu_item_total < 3) {
                    var item = '<li class="menu-item" data-type="click" data-action="key|" data-name="????????????" > <a href="javascript:;" class="menu-link"> <i class="icon-menu-dot"></i> <i class="weixin-icon sort-gray"></i> <span class="title">????????????</span> </a> <div class="sub-menu-box" style=""> <ul class="sub-menu-list"><li class=" add-sub-item"><a href="javascript:;" title="???????????????"><span class=" "><i class="weixin-icon add-gray"></i></span></a></li> </ul> <i class="arrow arrow-out"></i> <i class="arrow arrow-in"></i> </div></li>';
                    var itemDom = $(item);
                    itemDom.insertBefore(this);
                    itemDom.trigger("click");
                    $(".sub-menu-box", itemDom).show();
                    new Sortable($(".sub-menu-list", itemDom)[0], {draggable: 'li.sub-menu-item'});
                }
            });
            $(document).on('change', 'input[name=type]', function () {
                refresh_type();
            });
            $(document).on('click', '#item_delete', function () {
                var current = $("#menu-list li.current");
                var prev = current.prev("li[data-type]");
                var next = current.next("li[data-type]");

                if (prev.size() == 0 && next.size() == 0 && $(".sub-menu-box", current).size() == 0) {
                    last = current.closest(".menu-item");
                } else if (prev.size() > 0 || next.size() > 0) {
                    last = prev.size() > 0 ? prev : next;
                } else {
                    last = null;
                    $(".weixin-content").hide();
                    $(".no-weixin-content").show();
                }
                $("#menu-list li.current").remove();
                if (last) {
                    last.trigger('click');
                } else {
                    $("input[name='item-title']").val('');
                }
                updateChangeMenu();
            });

            //?????????????????????
            var updateChangeMenu = function () {
                var title = $("input[name='item-title']").val();
                var type = $("input[name='type']:checked").val();
                var key = value = '';
                if (type == 'view') {
                    key = 'url';
                } else {
                    key = 'key';
                }
                value = $("input[name='" + key + "']").val();

                if (key == 'key') {
                    var keytitle = typeof responselist[value] != 'undefined' ? responselist[value] : '';
                    var cont = $(".is-click .create-click:first");
                    $(".keytitle", cont).remove();
                    cont.append('<div class="keytitle">?????????:' + keytitle + '</div>');
                }
                var currentItem = $("#menu-list li.current");
                if (currentItem.size() > 0) {
                    currentItem.attr('data-type', type);
                    currentItem.attr('data-action', key + "|" + value);
                    if (currentItem.siblings().size() == 4) {
                        $(".add-sub-item").show();
                    } else if (false) {

                    }
                    currentItem.children("a").find("span").text(title.subByte(0, 16));
                    $("input[name='item-title']").val(title);
                    currentItem.attr('data-name', title);
                    $('#current-item-name').text(title);
                }
                menuUpdate();
            }
            //??????????????????
            var menuUpdate = function () {
                $.post("wechat.menu/edit", {menu: JSON.stringify(getMenuList())}, function (data) {
                    if (data['code'] == 1) {
                    } else {
                        Toastr.error(__('Operation failed'));
                    }
                }, 'json');
            };
            //??????????????????
            var getMenuList = function () {
                var menus = new Array();
                var sub_button = new Array();
                var menu_i = 0;
                var sub_menu_i = 0;
                var item;
                $("#menu-list li").each(function (i) {
                    item = $(this);
                    var name = item.attr('data-name');
                    var type = item.attr('data-type');
                    var action = item.attr('data-action');
                    if (name != null) {
                        actions = action.split('|');
                        if (item.hasClass('menu-item')) {
                            sub_menu_i = 0;
                            if (item.find('.sub-menu-item').size() > 0) {
                                menus[menu_i] = {"name": name, "sub_button": "sub_button"}
                            } else {
                                if (actions[0] == 'url')
                                    menus[menu_i] = {"name": name, "type": type, "url": actions[1]};
                                else
                                    menus[menu_i] = {"name": name, "type": type, "key": actions[1]};
                            }
                            if (menu_i > 0) {
                                if (menus[menu_i - 1]['sub_button'] == "sub_button")
                                    menus[menu_i - 1]['sub_button'] = sub_button;
                                else
                                    menus[menu_i - 1]['sub_button'];
                            }
                            sub_button = new Array();
                            menu_i++;
                        } else {
                            if (actions[0] == 'url')
                                sub_button[sub_menu_i++] = {"name": name, "type": type, "url": actions[1]};
                            else
                                sub_button[sub_menu_i++] = {"name": name, "type": type, "key": actions[1]};
                        }
                    }
                });
                if (sub_button.length > 0) {
                    var len = menus.length;
                    menus[len - 1]['sub_button'] = sub_button;
                }
                return menus;
            }
            //???????????????
            $(document).on('click', ".add-sub-item", function () {
                var sub_menu_item_total = $(this).parent().find(".sub-menu-item").size();
                if (sub_menu_item_total < 5) {
                    var item = '<li class="sub-menu-item" data-type="click" data-action="key|" data-name="???????????????"><a href="javascript:;"><span class=" "><i class="weixin-icon sort-gray"></i><span class="sub-title">???????????????</span></span></a></li>';
                    var itemDom = $(item);
                    itemDom.insertBefore(this);
                    itemDom.trigger("click");
                    if (sub_menu_item_total == 4) {
                        $(this).hide();
                    }
                }
                return false;
            });
            //??????????????????????????????
            $(document).on('click', ".menu-item, .sub-menu-item", function () {
                if ($(this).hasClass("sub-menu-item")) {
                    $("#menu-list li").removeClass('current');
                    $(".is-item").show();
                    $(".is-sub-item").show();
                } else {
                    $("#menu-list li").removeClass('current');
                    $("#menu-list > li").not(this).find(".sub-menu-box").hide();
                    $(".sub-menu-box", this).toggle();
                    //??????????????????????????????
                    if ($(".sub-menu-item", this).size() == 0) {
                        $(".is-item").show();
                        $(".is-sub-item").show();
                    } else {
                        $(".is-item").hide();
                        $(".is-sub-item").hide();
                    }
                }
                $(this).addClass('current');
                var type = $(this).attr('data-type');
                var action = $(this).attr('data-action');
                var title = $(this).attr('data-name');

                actions = action.split('|');
                $("input[name=type][value='" + type + "']").prop("checked", true);
                $("input[name='item-title']").val(title);
                $('#current-item-name').text(title);
                if (actions[0] == 'url') {
                    $('input[name=key]').val('');
                } else {
                    $('input[name=url]').val('');
                }
                $("input[name='" + actions[0] + "']").val(actions[1]);
                if (actions[0] == 'key') {
                    var keytitle = typeof responselist[actions[1]] != 'undefined' ? responselist[actions[1]] : '';
                    var cont = $(".is-click .create-click:first");
                    $(".keytitle", cont).remove();
                    if (keytitle) {
                        cont.append('<div class="keytitle">?????????:' + keytitle + '</div>');
                    }
                } else {

                }

                $(".weixin-content").show();
                $(".no-weixin-content").hide();

                refresh_type();

                return false;
            });
            $("form").on('change', "input,textarea", function () {
                updateChangeMenu();
            });
            $(document).on('click', "#menuSyn", function () {
                $.post("wechat.menu/sync", {}, function (ret) {
                    var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                    if (ret.code == 1) {
                        Backend.api.toastr.success('????????????????????????????????????????????????????????????????????????????????????????????????');
                    } else {
                        Backend.api.toastr.error(msg ? msg : __('Operation failed'));
                    }
                }, 'json');
            });
             var refreshkey = function (data) {
                    $("input[name=key]").val(data[1]).trigger("change");
					 responselist[data.eventkey] = data[0];
                    Layer.closeAll();
             };			
            $(document).on('click', "#select-resources", function () {
                    var key = $("input[name='auto_eventkey']").val();
                    parent.Backend.api.open($(this).attr("href") + "?key=" + key,$(this).attr("title"), {callback: refreshkey});
                    return false;
             });			
            $(document).on('click', "#add-resources", function () {
                Backend.api.open($(this).attr("href") + "?key=" + key,$(this).attr("title"), {callback: refreshkey},'','95%');
                return false;
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});