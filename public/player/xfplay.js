var newuri = '';
var Player = {
    'Url': newuri,
    'Play': function () {
    }
}
Player.Play();

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
	'install': function () {
		xianyucms_player_fun.isinstall = false;
		xianyucms_player_fun.$('xf_iframe').src = "//error.xfplay.com/error.htm";
		xianyucms_player_fun.$('xf_player').style.display = 'none';
	},
	'buffer': function(){
		if(xianyucms_player.adtime){
			xianyucms_player_fun.$('xf_iframe').src = xianyucms_player.adurl;
			xianyucms_player_fun.$('xf_iframe').style.height = (xianyucms_player_fun.height-40)+'px';
			setTimeout('xianyucms_player_fun.status()', xianyucms_player.adtime*1000);
		}else{
			xianyucms_player_fun.status();
		}
	},
	'status': function(){
		xianyucms_player_fun.$('xf_iframe').style.display = 'none';
	},
	'play' : function(){

        Player.Url = xianyucms_player.url;
 
		xianyucms_player_fun._height();

		document.write('<div><iframe class="xianyucms-play-iframe" id="xf_iframe"  src="" frameBorder="0" width="100%" height="100%" scrolling="no"></iframe>');

		if( xianyucms_player_fun.isie() ){
			document.write('<object id="xf_player" name="xf_player" classid="clsid:E38F2429-07FE-464A-9DF6-C14EF88117DD" width="100%" height="100%" onerror="xianyucms_player_fun.install();"><param name="URL" value="'+xianyucms_player.url+'"/><param name="Status" value="1"/></object></div>');
			if(xianyucms_player_fun.isinstall){
				xianyucms_player_fun.buffer();
			}
		}else{
			if (navigator.plugins) {
				xianyucms_player_fun.isinstall = false;
				for (i=0; i < navigator.plugins.length; i++ ) {
					var n = navigator.plugins[i].name;
					if( navigator.plugins[n][0]['type'] == 'application/xfplay-plugin'){
						xianyucms_player_fun.isinstall = true;
						break;
					}
				} 
				if(xianyucms_player_fun.isinstall){
					document.write('<embed id="xf_player" name="xf_player" type="application/xfplay-plugin" PARAM_URL="'+xianyucms_player.url+'" PARAM_Status="1" width="100%" height="100%"></embed></div>');
					xianyucms_player_fun.buffer();
				}else{

					document.write('<div id="xf_player" name="xf_player">�밲װӰ���ȷ沥����</div></div>');
					xianyucms_player_fun.install();
				}
			} else {

				document.write('<div id="xf_player" name="xf_player">�밲װӰ���ȷ沥����</div></div>');
				xianyucms_player_fun.install();
			}
		}
	}
};
xianyucms_player_fun.play();