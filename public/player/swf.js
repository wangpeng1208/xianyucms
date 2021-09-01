if(!xianyucms_player.apiurl){
xianyucms_player.apiurl = '//cdn.97bike.com/api/?type='+xianyucms_player.name+'&url=';
};
document.write('<iframe class="xianyucms-play-iframe" id="buffer" src="'+xianyucms_player.adurl+'" width="100%" height="100%" frameborder="0" scrolling="no" style="position:absolute;z-index:9;display:none"></iframe>');
document.write('<iframe class="xianyucms-play-iframe" src="'+xianyucms_player.apiurl+xianyucms_player.url+'" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>');
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