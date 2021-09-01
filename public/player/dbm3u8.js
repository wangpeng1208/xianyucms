document.write('<link rel="stylesheet" href="https://cdn.bootcss.com/dplayer/1.22.2/DPlayer.min.css" type="text/css">');
document.write('<div id="dplayer" style="width:100%; height:100%;position:absolute;"></div>');
var playurl = xianyucms_player.url;
var getScript = function(url, callback){
  var script = document.createElement("script");
  script.type = "text/javascript";
  if(typeof(callback) != "undefined"){
    if (script.readyState) {
      script.onreadystatechange = function () {
        if (script.readyState == "loaded" || script.readyState == "complete") {
          script.onreadystatechange = null;
          callback();
        }
      };
    } else {
      script.onload = function () {
        callback();
      };
    }
  }
  script.src = url;
  document.body.appendChild(script);
}
getScript("https://cdn.bootcss.com/hls.js/0.9.1/hls.js", function() {
getScript("https://cdn.bootcss.com/dplayer/1.22.2/DPlayer.min.js", function() {

		const dp = new DPlayer({
			container: document.getElementById("dplayer"),
			autoplay: true,
			screenshot: true,
			video: {
				quality: [{
					url: playurl
				}],
				defaultQuality: 0
			}
		});
		
});
		
});
