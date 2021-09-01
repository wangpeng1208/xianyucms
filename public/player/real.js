document.write('<iframe class="xianyucms-play-iframe" id="buffer" src="'+xianyucms_player.adurl+'" width="100%" height="100%" frameborder="0" scrolling="no" style="position:absolute;z-index:9;display:none"></iframe>');
document.write('<object wmode="Opaque" id="realObj" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="100%"><param name="CONTROLS" value="ImageWindow"><param name="CONSOLE" value="Clip1"><param name="AUTOSTART" value="-1"><param name="src" value="'+xianyucms_player.url+'"></object><br><object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="60"><param name="CONTROLS" value="ControlPanel,StatusBar"><param name="CONSOLE" value="Clip1"></object><div style="text-align:center; margin-top:50px;"><a href="#" onClick="document.realObj.SetFullScreen();">点击这里全屏收看 按ESC键退出</a></div>');
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