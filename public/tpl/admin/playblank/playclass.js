islogin=0;
syndomain='';
function checkcookie(){
	if(document.cookie.indexOf('user_auth_sign=')>=0){
	islogin=1;
	return true;
	}
	return false;
}
checkcookie();
function PlayHistoryClass() {
	var cookieStr, nameArray, urlArray, allVideoArray;
	this.getPlayArray = function() {
		cookieStr = document.cookie;
		var start = cookieStr.indexOf("xianyu_vod_v=") + "xianyu_vod_v=".length, end = cookieStr
				.indexOf("_$_|", start), allCookieStr = unescape(cookieStr
				.substring(start, end))
		if (end == -1) {
			allCookieStr = "";
			return;
		}
		allVideoArray = allCookieStr.split("_$_");
		nameArray = new Array(), urlArray = new Array();
		for ( var i = 0; i < allVideoArray.length; i++) {
			var singleVideoArray = allVideoArray[i].split("^");
			nameArray[i] = singleVideoArray[0];
			urlArray[i] = singleVideoArray[1];
		}
	}
	this.addPlayHistory = function(json,vod_readurl,vod_palyurl) {
		var count = 10; // 播放历史列表调用条数
		if(checkcookie()){
			$.post(Root + "index.php?s=user-comm-addplaylog",json);
		}
		var name = json.vod_name+"|" + vod_readurl ;
		var url = json.url_name  +"|" + vod_palyurl ;
		
		var code_name = escape(name) + "^", code_url = escape(url) + "_$_",
		expireTime = new Date(new Date().setDate(new Date().getDate() + 30)), timeAndPathStr = "|; expires="+ expireTime.toGMTString() + "; path=/";
		
		
		if ((cookieStr.indexOf("xianyu_vod_v=") != -1 || cookieStr.indexOf("_$_|") != -1) && allVideoArray != undefined) {
			var newCookieStr = "";
			if (allVideoArray.length < count) {
				for (i in allVideoArray) {
					if(nameArray[i] != name) {
						newCookieStr += escape(nameArray[i]) + "^" + escape(urlArray[i]) + "_$_";
					}
				}
			} else {
				for ( var i = 1; i < count; i++) {
					if (nameArray[i] != name) {
						newCookieStr += escape(nameArray[i]) + "^" + escape(urlArray[i]) + "_$_";
					}
				}
			}
			document.cookie = "xianyu_vod_v=" + newCookieStr + code_name + code_url + timeAndPathStr;
		} else {
			document.cookie = "xianyu_vod_v=" + code_name + code_url + timeAndPathStr;
		}
	}
	
	
}
var PlayHistoryObj = new PlayHistoryClass()
PlayHistoryObj.getPlayArray();
function killErrors() {
	return true;
}