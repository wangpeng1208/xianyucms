var xianyucms_player_fun = {
	'weburl' : unescape(window.location.href),
	'isinstall': true,
	'height': xianyucms_player.height,
	'$':function(id){
		return document.getElementById(id);
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
		xianyucms_player_fun.$('jjvod_iframe').src = "//cdn.97bike.com/player/setup/jjvod.html";
		xianyucms_player_fun.$('jjvod_player').style.display = 'none';
	},
	'buffer': function(){
		xianyucms_player_fun.$('jjvod_iframe').src = xianyucms_player.adurl;
		xianyucms_player_fun.$('jjvod_iframe').style.height = (xianyucms_player_fun.height-50)+'px';
	},
	'status': function(){
		if(xianyucms_player_fun.$('jjvod_player').PlayState == 3){
    	xianyucms_player_fun.$('jjvod_iframe').style.display = 'none';
    }else if(xianyucms_player_fun.$('jjvod_player').PlayState == 2 || xianyucms_player_fun.$('jjvod_player').PlayState == 4){
    	xianyucms_player_fun.$('jjvod_iframe').style.display = 'block';
    }
	},
	'play' : function(){
		xianyucms_player_fun.height = xianyucms_player_fun.$('xianyucms_player').offsetHeight;
		document.write('<div><iframe id="jjvod_iframe" class="xianyucms-play-iframe" src="" frameBorder="0" width="100%" height="100%" scrolling="no"></iframe>');
		if( xianyucms_player_fun.isie() ){
			document.write('<object id="jjvod_player" classid="clsid:C56A576C-CC4F-4414-8CB1-9AAC2F535837" width="100%" height="100%" onerror="xianyucms_player_fun.install();"><PARAM NAME="URL" VALUE="'+xianyucms_player.url+'"><PARAM NAME="WEB_URL" VALUE="'+xianyucms_player_fun.weburl+'"><param name="Autoplay" value="1"></object></div>');
			if(xianyucms_player_fun.isinstall){
				xianyucms_player_fun.buffer();
				setInterval('xianyucms_player_fun.status()','1000');
			}
		}else{
			if (navigator.plugins) {
				xianyucms_player_fun.isinstall = false;
				for (var i=0; i<navigator.plugins.length; i++) {
					if(navigator.plugins[i].name == 'JJvod Plugin'){
						xianyucms_player_fun.isinstall = true;
						break;
					}
				}
				if(xianyucms_player_fun.isinstall){
					document.write('<object id="jjvod_player" name="jjvod_player" type="application/x-itst-activex" width="100%" height="100%" progid="WEBPLAYER.WebPlayerCtrl.2" param_URL="'+xianyucms_player.url+'" param_WEB_URL="'+xianyucms_player_fun.weburl+'"></object></div>');
					xianyucms_player_fun.buffer();
					setInterval('xianyucms_player_fun.status()','1000');
				}else{
					document.write('<div id="jjvod_player" name="jjvod_player">请安装JJVOD播放器</div></div>');
					xianyucms_player_fun.install();
				}
			}else{
				alert('不支持该浏览器点播，推荐使用Goolge Chrome');
			}
		}
	}
};
xianyucms_player_fun.play();