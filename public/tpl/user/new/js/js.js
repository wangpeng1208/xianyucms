$(document).ready(function(){
//订单付款界面						   
$('body').on("click","#user-score-payment",function(){	
	 payment();
});
//购买VIP界面
$('body').on("click","#ispay-vip",function(){
    $.colorbox({
        href: Root+'index.php?s=user-buy-index',
    });
});
//支付VIP影币
$('body').on("click","#pay_vip",function(){								 
   $(".form-horizontal").qiresub({
			curobj: $("#pay_vip"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if($.hidediv(a),parseInt(a["rcode"])  > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
				$("#cboxClose").trigger("click")
				 location.reload();
				}
				if($.hidediv(a),parseInt(a["rcode"]) == -2){
				 payment();
				}
				else - 3 == parseInt(a["rcode"])
			}
		}).post({
			url: Root + "index.php?s=user-buy-index"
		}), !1;
	
});
//购买VIP界面
$('body').on("click","#pay_card",function(){
     payment_card();
});
$('body').on("click","#payment_card",function(){								 
   $(".form-horizontal").qiresub({
			curobj: $("#payment_card"),
			txt: "数据提交中,请稍后...",
			onsucc: function(a) {
				if($.hidediv(a),parseInt(a["code"])  > 0) {
					qr.gu({
						ubox: "unm",
						rbox: "innermsg",
						h3: "h3",
						logo: "userlogo"
					});
				$("#cboxClose").trigger("click")
				 location.reload();
				}
				if($.hidediv(a),parseInt(a["code"]) == -2){
				 payment();
				}
				else - 3 == parseInt(a["code"])
			}
		}).post({
			url: Root + "index.php?s=user-payment-card"
		}), !1;
	
});

})
function payment(){
	 $.colorbox({
        href: Root+'index.php?s=user-payment-index',
    });
}

function player_iframe(){
		if($("#xianyucms-player-vip").length>0){
			self.location.reload();
		}
}
function payment_card(){
	 $.colorbox({
        href: Root+'index.php?s=user-payment-card',
    });
}