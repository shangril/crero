<?php
chdir('./..');
require_once('./config.php');
chdir('./script.js');


$supported_formats_remote=explode("\n", file_get_contents($clewnapiurl.'?listformats=true'));
$supported_formats_local=explode("\n", file_get_contents($serverapi.'?listformats=true'));



header('Content-Type: text/javascript');
?>
var infoselected=null;
var isplaying=-1;
var currenttarget='';
var currentclewn;
function play(target, id, isclewn, isautoplay = false){
	if (isclewn) {
			target='<?php echo $clewnaudiourl;?>'+target;
	}
	else  {
			target='<?php echo 'http://'.$server.'/z/';?>'+target;
	}
	currenttarget=target;
	currentclewn=isclewn;
	var player=document.getElementById('player');
	
	if (isplaying!=id){
		if (!isautoplay) 
		{	
			document.getElementById(id).innerHTML='■';	
	
		}
	isplaying=id;
	player.pause();
	player.innerHTML='';
	<?php if (in_array('ogg', $supported_formats_local)&&in_array('ogg', $supported_formats_local)) { ?>
	player.innerHTML+='<source type="application/ogg" src="'+target+'.ogg"/>';
	<?php }  if (in_array('mp3', $supported_formats_remote)&&in_array('mp3', $supported_formats_remote)) {?>
	player.innerHTML+='<source type="audio/mpeg" src="'+target+'.mp3"/>';
	<?php } ?>
	player.load();
	player.autoplay='autoplay';
	player.play();
	}
	
	else {
	document.getElementById(id).innerHTML='▶';	
	player.pause();
	player.currentTime=0;
	player.innerHTML=null;
	isplaying=-1;	
		
	}
}
function playNext() {
	document.getElementById(isplaying).innerHTML='▶';
	if (document.getElementById(isplaying+1)!=null){
		player.pause();
		player.innerHTML='';	
		document.getElementById(isplaying+1).click();
		
	}
	else {
		if (document.getElementById('digolder')!=null){
		
			document.location.href=document.getElementById('digolder').href+'&autoplay=true';//'./?offset='+(offset+1)+'&autoplay=true';
		}
		else {
			document.location.href='./?offset='+(offset+1)+'&autoplay=true';
		}

	}
	
}
function loadInfo(track) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
	 infoselected.style.display='block';
     infoselected.innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "./?getinfo="+encodeURI(track), true);
  xhttp.send();
} 

