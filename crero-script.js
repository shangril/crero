// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0


function cr_document_getElementById_overload_splash__style_display(display){
	document.getElementById(overload_splash).style.display=display;
}
function cr_document_getElementById_player__volume(){
		return document.getElementById(player).volume;
}
function cr_document_menu_getElementById(menu_item){
	return document.getElementById(menu_item);
	
}
var infoselected=null;
var isplaying=-1;
var currenttarget='';
var currentclewn;
function play(target, id, isclewn, isautoplay=false){
	if (isclewn) {
			target=dl_audiourl+encodeURI(target);
	}
	else  {
			target=str_audiourl+encodeURI(target);
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
	if (isclewn){
		if (dlformats[0]=='ogg'||dlformats[1]=='ogg'||dlformats[2]=='ogg'){
			player.innerHTML+='<source type="application/ogg" src="'+target+'.ogg"/>';
		}
		if (dlformats[0]=='mp3'||dlformats[1]=='mp3'||dlformats[2]=='mp3')
		
		{
			player.innerHTML+='<source type="audio/mpeg" src="'+target+'.mp3"/>';
		}
	}
	else {
		if (strformats[0]=='ogg'||strformats[1]=='ogg'||strformats[2]=='mp3'){
			player.innerHTML+='<source type="application/ogg" src="'+target+'.ogg"/>';
		}
		if (strformats[0]=='mp3'||strformats[1]=='mp3'||strformats[2]=='mp3')
		
		{
			player.innerHTML+='<source type="audio/mpeg" src="'+target+'.mp3"/>';
		}
		
	}
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

// @license-end
