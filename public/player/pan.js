document.write('<iframe class="xianyucms-play-iframe" id="buffer" src="'+xianyucms_player.adurl+'" width="100%" height="100%" frameborder="0" scrolling="no" style="position:absolute;z-index:9;display:none"></iframe>');
document.write('<html style="margin:0;padding:0;"><body style="margin:0;padding:0;"><div class="PanBox" style="background-color:#eee;width:100%;float:left;font-weight:900;font-size:20px;height:100%"><p style="text-align:center;line-height:40px;margin-top:150px;">下面是网盘下载地址，请直接点击进入网盘界面</p><p style="text-align:center;"><a target="_blank" href="'+xianyucms_player.url+'">'+xianyucms_player.url+'</a></p></div></body></html>');
ads_show(); 
function ads_show(){
	try{
		if(xianyucms_player.adurl!=null && xianyucms_player.adtime>0){
			document.getElementById("buffer").style.display = "block";
			setTimeout("document.getElementById(\"buffer\").style.display=\"none\"",xianyucms_player.adtime*1000);
		}else{
			setTimeout(function(){ads_show();},200);
		}
		}catch(e){}
}