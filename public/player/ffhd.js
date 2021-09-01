var xianyucms_player_fun = {
	'weburl' : unescape(window.location.href),
	'isinstall': true,
	'height': xianyucms_player.height,
	'next_weburl': '',
	'$':function(id){
		return document.getElementById(id);
	},
	'_height':function(){
		this.height = this.$('xianyucms_player').offsetHeight;
	},
	'isie' : function(){
		if (!!window.ActiveXObject || "ActiveXObject" in window){
			return true;
		}else{
			return false;
		}
	},
	'install': function(){
		xianyucms_player_fun.isinstall = false;
		xianyucms_player_fun.$('ffhd_iframe').src = "//cdn.97bike.com/player/setup/ffhd.html";
		xianyucms_player_fun.$('ffhd_player').style.display = 'none';
	},
	'buffer': function(){
		if(xianyucms_player.adtime){
			xianyucms_player_fun.$('ffhd_iframe').src = xianyucms_player.adurl;
			xianyucms_player_fun.$('ffhd_iframe').style.height = (xianyucms_player_fun.height-50)+'px';
			setTimeout('xianyucms_player_fun.status()', xianyucms_player.adtime*1000);
		}else{
			xianyucms_player_fun.status();
		}
	},
	'status': function(){
		xianyucms_player_fun.$('ffhd_iframe').style.display = 'none';
	},
	'play' : function(){
		xianyucms_player_fun._height();
		document.write('<div><iframe id="ffhd_iframe" class="xianyucms-play-iframe" src="" frameBorder="0" width="100%" height="100%" scrolling="no"></iframe>');
		if( xianyucms_player_fun.isie() ){
			document.write('<object id="ffhd_player" name="ffhd_player" classid="clsid:D154C77B-73C3-4096-ABC4-4AFAE87AB206" width="100%" height="100%" onerror="xianyucms_player_fun.install();"><param name="url" value="'+xianyucms_player.url+'"/><param name="CurWebPage" value="'+xianyucms_player_fun.weburl+'"/></object></div>');
			if(xianyucms_player_fun.isinstall){
				xianyucms_player_fun.buffer();
			}
		}else{
			if (navigator.plugins) {
				xianyucms_player_fun.isinstall = false;
				for (var i=0; i<navigator.plugins.length; i++) {
					if(navigator.plugins[i].name == 'FFPlayer Plug-In'){
						xianyucms_player_fun.isinstall = true;
						break;
					}
				}
				if(xianyucms_player_fun.isinstall){
					document.write('<object id="ffhd_player" name="ffhd_player" type="application/npFFPlayer" width="100%" height="100%" progid="XLIB.FFPlayer.1" url="'+xianyucms_player.url+'" CurWebPage="'+xianyucms_player_fun.weburl+'"></object></div>');
					xianyucms_player_fun.buffer();
				}else{
					document.write('<div id="ffhd_player" name="ffhd_player">请安装非凡影音播放器</div></div>');
					xianyucms_player_fun.install();
				}
			}else{
				alert('不支持该浏览器点播，推荐使用Goolge Chrome');
			}
		}
	}
};
xianyucms_player_fun.play();