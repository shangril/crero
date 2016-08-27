<?php
chdir('./..');
require_once('./config.php');
chdir('./script.js');


header('Content-Type: text/javascript');
?>

var isplaying=-1;
var currenttarget='';
var currentclewn;
function play(target, id, isclewn){
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
	document.getElementById(id).innerHTML='■';	
	isplaying=id;
	player.pause();
	player.innerHTML=null;
	
	player.innerHTML='<source type="application/ogg" src="'+target+'.ogg"/><source type="audio/mpeg" src="'+target+'.mp3"/>';
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
