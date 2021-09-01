<?php
    
    $arr['url'] = base64_decode($_GET['url']);
    if(strstr($arr['url'], 'http://')){
    	$arr['url'] = str_replace('http://', 'https://', $arr['url']);
    }
    $arr['name'] = $_GET['name'];
    $arr['copyright'] = $_GET['co'];
    $arr['apiurl'] = base64_decode($_GET['api']);
    $arr['adtime'] = $_GET['time'];
    $arr['adurl'] = base64_decode($_GET['ad']);
    $arr['nexturl'] = base64_decode($_GET['next']);
    $p = json_encode($arr);
?>
var player = <?php  echo $p; ?>;
document.write('<ifr'+'ame class="xianyucms-play-iframe" id="buffer" src="<?php  echo $arr['apiurl'].$arr['adurl'];?>" width="100%" height="100%" frameborder="0" scrolling="no" style="display:none;position:absolute;z-index:9;"></ifr'+'ame>');
document.write('<ifr'+'ame class="xianyucms-play-iframe" src="<?php  echo $arr['apiurl'].$arr['url'];?>" allowFullscreen="true" width="100%" height="100%"  frameborder="0" scrolling="no"></ifr'+'ame>');
document.write('<div  style="display:none"></div>'); 
ads_show();
function ads_show(){
  try{
       if(player.adurl!=null && player.adtime>0){
         document.getElementById("buffer").style.display = "block";
         setTimeout("document.getElementById(\"buffer\").style.display=\"none\"",player.adtime*1000);    
       }else{
         setTimeout(function(){ads_show();},200);
       }
    }catch(e){}
}