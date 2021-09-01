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
	'_next_weburl':function(){
		var url = '';
		if(xianyucms_player.nexturl){
			var a = this.weburl.match(/(\d+)/g);
			var len = a.length;
			var i = 0;
			var url = this.weburl.replace(/(\d+)/g,function(){
				if (a[i]){
					if((i+1)==len){
						return a[len-1]*1+1;
					}else{
						return a[i++];
					}
				}
			});
		}
		this.next_weburl = url;
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
		xianyucms_player_fun.$('xigua_iframe').src = "//cdn.97bike.com/player/setup/xigua.html";
		xianyucms_player_fun.$('xigua_player').style.display = 'none';
	},
	'buffer': function(){
		xianyucms_player_fun.$('xigua_iframe').src = xianyucms_player.adurl;
		xianyucms_player_fun.$('xigua_iframe').style.height = (xianyucms_player_fun.height-48)+'px';
	},
	'status': function(){
		if( xigua_player.IsPlaying() ){
    	xianyucms_player_fun.$('xigua_iframe').style.display = 'none';
    }else if( xigua_player.IsBuffing() ){
    	xianyucms_player_fun.$('xigua_iframe').style.display = 'block';
    }else if( xigua_player.IsPause() ){
    	xianyucms_player_fun.$('xigua_iframe').style.display = 'block';
    }
	},
	'play' : function(){
		xianyucms_player_fun._next_weburl();
		xianyucms_player_fun._height();
		document.write('<div><iframe class="xianyucms-play-iframe" id="xigua_iframe" src="" frameBorder="0" width="100%" height="100%" scrolling="no"></iframe>');
		if( xianyucms_player_fun.isie() ){
			document.write('<object id="xigua_player" name="xigua_player" classid="clsid:BEF1C903-057D-435E-8223-8EC337C7D3D0" width="100%" height="100%" onerror="xianyucms_player_fun.install();"><param name="URL" value="'+xianyucms_player.url+'"/><param name="NextCacheUrl" value="'+xianyucms_player.next_url+'"><param name="NextWebPage" value="'+xianyucms_player_fun.next_weburl+'"><param name="Autoplay" value="1"/></object></div>');
			if(xianyucms_player_fun.isinstall){
				xianyucms_player_fun.buffer();
				setInterval('xianyucms_player_fun.status()','1000');
			}
		}else{
			if (navigator.plugins) {
				xianyucms_player_fun.isinstall = false;
				for (var i=0; i<navigator.plugins.length; i++) {
					if(navigator.plugins[i].name == 'XiGua Yingshi Plugin'){
						xianyucms_player_fun.isinstall = true;
						break;
					}
				}
				if(xianyucms_player_fun.isinstall){
					document.write('<object id="xigua_player" name="xigua_player" type="application/xgyingshi-activex" progid="xgax.player.1" width="100%" height="100%" progid="Xbdyy.PlayCtrl.1" param_URL="'+xianyucms_player.url+'" param_NextCacheUrl="'+xianyucms_player.nexturl+'" param_NextWebPage="'+xianyucms_player_fun.next_weburl+'" param_Autoplay="1"></object></div>');
					xianyucms_player_fun.buffer();
					setInterval('xianyucms_player_fun.status()','1000');
				}else{
					document.write('<div id="xigua_player" name="xigua_player">请安装西瓜影音播放器</div></div>');
					xianyucms_player_fun.install();
				}
			}else{
				alert('不支持该浏览器点播，推荐使用Goolge Chrome');
			}
		}
	}
};
xianyucms_player_fun.play();