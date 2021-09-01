document.write('<iframe class="xianyucms-play-iframe" id="buffer" src="'+xianyucms_player.adurl+'" width="100%" height="100%" frameborder="0" scrolling="no" style="position:absolute;z-index:9;display:none"></iframe>');
document.write('<object wmode="Opaque" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="100%" height="100%" id="mdediaplayer"><param name="URL" value="'+xianyucms_player.url+'"><param name="stretchToFit" value="-1"><embed filename="'+xianyucms_player.url+'" ShowStatusBar="1" type="application/x-mplayer2" width="100%" height="100%"></object><div align="right" style="margin-right:69px;margin-top:-30px"><input type="submit" value="ȫ���ۿ�" onclick="setfullscreen()"></div>');
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