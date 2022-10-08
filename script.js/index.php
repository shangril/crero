<?php
chdir('./..');
require_once('./config.php');
chdir('./script.js');


$supported_formats_remote=explode("\n", file_get_contents($clewnapiurl.'?listformats=true'));
$supported_formats_local=explode("\n", file_get_contents($serverapi.'?listformats=true'));



header('Content-Type: text/javascript');
?>
/*********************************
* Please note that this script.js, while available under a free license, namely AGPL Version 3 or later
* has been built specifically for the following CreRo instance, that is to find at
* <?php echo str_replace('*/', '', $server); ?>;
* and contains hardcoded data in it
* that are specific for this very instance
* and in fact this code as-is will not be easily useful
* but
* it is quite easy to get the *generic* version of this script
* that will hardcode site-specific data according to CreRo instance specific configuration
* by visiting the original Crero code repository, https://github.com/shangril/crero
* or one of its possibly existing, and mandatory AGPL'ed as well, subsequent forks
* and take a look at ./script.js/index.php
* and more generally at CreRo as a whole
* Thanks for reading
*/





var infoselected=null;
var isplaying=-1;
var currenttarget='';
var currentclewn;
function play(target, id, isclewn, isautoplay = false){
	if (isclewn) {
			target='<?php echo $clewnaudiourl;?>'+encodeURI(target);
	}
	else  {
			target='<?php echo '//'.$server.'/z/';?>'+encodeURI(target);
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
		if (document.getElementById('digolder')!=null&&embed){
		
			document.location.href=document.getElementById('digolder').href+'&autoplay=true';//'./?offset='+(offset+1)+'&autoplay=true';
		}
		else if(target_album!=null){
			//document.location.href='./?album='+target_album+'&autoplay=true';
		}

		else
		
		{
			//	document.location.href='./?offset='+(offset+1)+'&autoplay=true';
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

