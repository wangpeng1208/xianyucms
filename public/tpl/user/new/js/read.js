islogin=0;
function checkcookie(){
	if(document.cookie.indexOf('auth=')>=0){
		islogin=1;
		return true;
	}
	return false;
}
checkcookie();
function login_form() {
    $.colorbox({
        inline: true,
        href: "#login-dialog",
        width: '570px'

    });

};
// 全站通栏模块切换
function setTab(name,cursel,n){
	for(i=1;i<=n;i++){
		var menu=document.getElementById(name+i);
		var con=document.getElementById("con_"+name+"_"+i);
		menu.className=i==cursel?"current":"";
		con.style.display=i==cursel?"block":"none";
	}
}
$(".promsg-btn").click(function (){	
	if(!checkcookie()) {
	login_form();
	return false;
	} else {
	$.colorbox({
		inline:true, 
		width: "500px",
		href:"#promsg-dialog"
	});}});
$("#comm_txt").focus(function(e) {	
	if (!checkcookie()) {
		login_form();
		return false;
	}
});
$("#loginbarx").click(function(e) {
	if (!checkcookie()) {
		login_form();
		return false;
	}
});	
$("#loginbt").click(function(e) {
	userlogin();
});
$("#register").click(function(e) {
	userreg();
});
$("#login2").click(function(){								
		$.colorbox({
        inline: true,
        href: "#login-dialog",
        width: '570px',
		height: '415px'

    });});
	$("#login1").click(function(){								
		$.colorbox({
        inline: true,
        href: "#login-dialog",
        width: '570px',
		height: '400px'

    });});	
//注册	
function userreg() {
	if ("Email" == $("#email").val()) $.showfloatdiv({
		txt: "请输入正确的Emial"
	}), $("#email").focus(), $.hidediv({});
	else {
		if ("" != $("#pwd").val()) return $("#regform").qiresub({
			curobj: $("#register"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if ($.hidediv(a), parseInt(a["rcode"]) > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
					try {
						PlayHistoryObj.viewPlayHistory("playhistory")
					} catch (b) {}
					$("#cboxClose").trigger("click")
				} else - 3 == parseInt(a["rcode"])
			}
		}).post({
			url: Root + "index.php?s=user-reg-index"
		}), !1;
		$.showfloatdiv({
			txt: "请输入密码"
		}), $("#pwd").focus(), $.hidediv({})
	}
}
//登陆
function userlogin() {
	if ("用户名" == $("#username").val()) $.showfloatdiv({
		txt: "请输入正确的用户名"
	}), $("#username").focus(), $.hidediv({});
	else {
		if ("" != $("#password").val()) return $("#loginform").qiresub({
			curobj: $("#loginbt"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if ($.hidediv(a), parseInt(a["rcode"]) > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
					$("#cboxClose").trigger("click")
				} else - 3 == parseInt(a["rcode"])
			}
		}).post({
			url: Root + "index.php?s=user-login-index"
		}), !1;
		$.showfloatdiv({
			txt: "请输入密码"
		}), $("#password").focus(), $.hidediv({})
	}
}
$("#indexlogin,#indexreg").click(function() {
	$("#indexlogin,#indexreg").attr('src', "index.php?s=user-validate-verify-" + Math.random());
});
if ($(".emotion").length > 0) {
        $(".emotion").on('click', function(){
            var left = $(this).offset().left;
            var top = $(this).offset().top;
            var id = $(this).attr("data-id");
            $("#smileBoxOuter").css({
                "left": left,
                "top": top + 20
            }).show().attr("data-id", id)
        });
        $("#smileBoxOuter,.emotion").hover(function() {
            $("#smileBoxOuter").attr("is-hover", 1)
        },
                function() {
                    $("#smileBoxOuter").attr("is-hover", 0)
                });
        $(".emotion,#smileBoxOuter").blur(function() {
            var is_hover = $("#smileBoxOuter").attr("is-hover");
            if (is_hover != 1) {
                $("#smileBoxOuter").hide()
            }
        });
        $(".smileBox").find("a").click(function() {
            var textarea_id = $("#smileBoxOuter").attr("data-id");
            var textarea_obj = $("#reply_" + textarea_id).find("textarea");
            var textarea_val = textarea_obj.val();
            if (textarea_val == "发布评论") {
                textarea_obj.val("")
            }
            var title = "[" + $(this).attr("title") + "]";
            textarea_obj.val(textarea_obj.val() + title).focus();
            $("#smileBoxOuter").hide()
        });
        $("#smileBoxOuter").find(".smilePage").children("a").click(function() {
            $(this).addClass("current").siblings("a").removeClass("current");
            var index = $(this).index();
            $("#smileBoxOuter").find(".smileBox").eq(index).show().siblings(".smileBox").hide()
        });
        $(".comment_blockquote").hover(function() {
            $(".comment_action_sub").css({
                "visibility": "hidden"
            });
            $(this).find(".comment_action_sub").css({
                "visibility": "visible"
            })
        }, function() {
            $(".comment_action_sub").css({
                "visibility": "hidden"
            })
        })
    }	
function pagegoo(url) {
	$.ajax({
		url : url,
		success : function(data) {
			if(data.ajaxtxt != '') {
				if ($('#commbox ul').length > 3)
					$("html,body").animate({
						scrollTop : $("#commbox").offset().top - 130
					}, 1000);
					$("#commbox").empty().html(data.ajaxtxt);
					$(".digg a").click(function(e) {
						opp($(this).attr('data'), $(this));
						return false ;
					});
     $(".replyt").click(function(e) {	
     var curid = $(this).attr('data-id');
	 var curpid = $(this).attr('data-pid');
	 var curtuid = $(this).attr('data-tuid');
	 var cursid = $(this).attr('data-sid');
	 var curvid = $(this).attr('data-vid');
	 if (!checkcookie()) {
		login_form();
		return false;
		}else{
		  if ($("#reps" + curid).html() != '') {
			$("#reps" + curid).html('');
		} else {
		$(".commss").html('');
		$("#reps" + curid).html($("#hidcommformt").html());
        $(".emotion").on('click', function(){
        var left = $(this).offset().left;
        var top = $(this).offset().top;
        var id = $(this).attr("data-id");
        $("#smileBoxOuter").css({
         "left": left,
         "top": top + 20
         }).show().attr("data-id", id)
        });		
        $("#reps"+ curid+ " #comm_pid").val(curpid); //顶级ID
		$("#reps"+ curid+ " #comm_id").val(curid); //回贴ID
		$("#reps"+ curid+ " #comm_tuid").val(curtuid); //回贴用户ID
		$("#reps"+ curid+ " #comm_sid").val(cursid); //回贴SID
		$("#reps"+ curid+ " #comm_vid").val(curvid); //回贴VID
		$("#reps"+ curid+ " #row_id").attr("data-id", curid)
		$("#reps"+ curid+ " .recm_id").attr("id",'reply_'+curid)
		$("#reps" + curid + " .sub").unbind();
		$("#reps" + curid + " .sub").click(function(e) {
		$("#reps"+ curid+ " #mcommformt").qiresub({
		curobj : $("#reps"+ curid+ " .sub"),
		txt : '数据提交中,请稍后...',
		onsucc : function(result) {
		$.hidediv(result);
		if (parseInt(result['rcode']) > 0) {
		pagegoo(url) ;
		}
		}
		}).post({
		url : Root + 'index.php?s=user-center-addrecomm'
		});
		});
		}
	}
});
			
			$("#pagebox").html(data.pages);
			$("#pageboxx").html(data.pagesx);
			} else {
				$("#pagebox").html('');
			    $("#pageboxx").html('');
				$("#commbox").html('<div style="height:40px;line-height:40px;text-align:center; font-size:15px;">当前没有评论，赶紧抢个沙发！</div>');
			};
			if (data.star != undefined && data.star != null) {
				stars(data.star);
			};	
			$(".commpage a,.pages ul a").click(function(e) {
				var pagegourl = $(this).attr('href');
				pagegoo(pagegourl);
				return false;
			});
			
		},dataType : 'json'
	});
	return false;
};

if (Id != undefined && Id != null && Id != '') {
    pagegoo(Root +"index.php?s=user-comm-getcomm-id-" + Id + '')
};

function opp(url, thisobj) {
	$.showfloatdiv({
		txt : '数据提交中...',
		cssname : 'loading'
	});
	$.get(url, function(r) {
		$.hidediv(r);
		if (parseInt(r.rcode) > 0) {
			thisobj.children('strong').html(parseInt(thisobj.children('strong').html()) + 1)
		}
	}, 'json');
};
function delcomm(url) {
	$.showfloatdiv({
		txt : '数据提交中...',
		cssname : 'loading'
	});
	$.get(url, function(r) {
		$.hidediv(r);
		if (parseInt(r.rcode) > 0) {
			$("#li" + r.id).remove()
		}
	}, 'json')
}