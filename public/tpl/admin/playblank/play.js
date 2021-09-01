$(function(){ 
   $(".jump").click(function() {
    if("" != url){	
    window.location = url;
    }	
	});
	 var redirectTip=getCookie("redirectTip");
	 if(redirectTip!=undefined&&redirectTip=="1"){
     if("" != url){
		window.location = url;	
	}	
    }
   var rate;
   function animate(){ 
		if($('span.loading').width()>0){
			rate = 1-$('span.loading').width()/$('p.loading').width();
		} else {
			rate = 1;
		}
		$('span.loading').animate({ 
		 width: 100+"%" 
		},5000*rate); 
	} 
	animate();
	var timer = setTimeout(function() {
		location.href = url;},5000);
	var jumb = 0;
	$('.clearjumb').click(function(){
		if(jumb==0){
			jumb = 1;
			$('span.loading').stop();
			clearTimeout(timer);
			$(this).addClass('jxjumb').html('继续跳转');
		} else {
			$(this).removeClass('jxjumb').html('取消跳转');;
			jumb = 0;
			animate();
			timer = setTimeout(function() {
				location.href = url;},5000*rate);
		}
	})				
  $("#notip").live("click",function(){								  
    $(this).attr('_clicklog','nt:'.concat($(this).is(":checked")?'0':'1'));
    if($(this).is(":checked")){
		setCookie("redirectTip","1",24*7);
    } else {
		setCookie("redirectTip","0",24*7);
    }
	});
function setCookie(name,value,hours,path,domain,secure){
          var cdata = name + "=" + value;
          if(hours){
              var d = new Date();
              d.setHours(d.getHours() + hours);
              cdata += "; expires=" + d.toGMTString();
          }
          cdata +=path ? ("; path=" + path) : "" ;
          cdata +=domain ? ("; domain=" + domain) : "" ;
          cdata +=secure ? ("; secure=" + secure) : "" ;
          document.cookie = cdata;
      }
       
function getCookie(name){
          var reg = eval("/(?:^|;\\s*)" + name + "=([^=]+)(?:;|$)/"); 
          return reg.test(document.cookie) ? RegExp.$1 : "";
      }
       
function removeCookie(name,path,domain){
          this.setCookie(name,"",-1,path,domain);
      }
})
