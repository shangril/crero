// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0
var myoverloadtimer=null
function set_overloadtimer(tim){
	myoverloadtimer=tim;
}
function get_overloadtimer(){
	return myoverloadtimer;
}
function update_title(){
	if(document.getElementById('main_title')!=null){
		tobeprepend='';
		if (get_album()!=''){
			tobeprepend=get_album();
		}
		if (get_artist()!=''){
			if (get_album()!=''){	
				tobeprepend=tobeprepend+' by ';
			}
			tobeprepend=tobeprepend+' '+get_artist();
		}
		if (get_track()!=''){
			tobeprepend=tobeprepend+': '+get_track();
		}
		if (tobeprepend!=''){
			tobeprepend=tobeprepend+' - ';
		}
	 document.getElementById('main_title').replaceChildren(tobeprepend+get_site_title());
	}
	if(document.getElementById('main_description')!=null){
		tobeprepend='';
		if (get_album()!=''){
			tobeprepend=get_album();
		}
		if (get_artist()!=''){
			tobeprepend=tobeprepend+' by '+get_artist();
		}
		if (get_track()!=''){
			tobeprepend=tobeprepend+': '+get_track();
		}
		if (tobeprepend!=''){
			tobeprepend=tobeprepend+' - ';
		}
		document.getElementById('main_description').value=tobeprepend+get_site_description();
	}
}
function cr_window_overloadtime(){
	window.clearInterval(get_overloadtimer());
}

function get_site_name(){
	if (document.getElementById('site_name')!=null){
		return document.getElementById('site_name').value;
	}
}
function get_site_title(){
	if (document.getElementById('site_title')!=null){
		return document.getElementById('site_title').value;
	}
}
function get_site_description(){
	if (document.getElementById('site_description')!=null){
		return document.getElementById('site_description').value;
	}
}

function create_arr(param_ca){
	return [encodeURIComponent(param_ca)];
}

function  cr_document_autoplay(){
	return document.getElementById('autoplay');
}
function cr_c_document_getElementById(ajax_splash){

	return document.getElementById(ajax_splash);

}
function cr_document_getElementById_overload_splash__style_display(display){
	document.getElementById('overload_splash').style.display=display;
}
function cr_document_getElementById_player__volume(){
		return document.getElementById(player).volume;
}
function cr_document_menu_getElementById(menu_item){
	return document.getElementById(menu_item);
	
}
function get_player(){
	return document.getElementById('player');
	
}
function is_playing(){
	return (is_playing);
}
function  digolder(customoffset){
	customoffset=parseInt(customoffset);
	arrartist=[];
	arralbum=[];
	arrtrack=[];
	embembed=[];

	artfrag='';
	albfrag='';
	trackfrag='';
	embfrag='';
	
	if (get_artist()!=null&&get_artist()!=''){
		arrartist=[encodeURIComponent(get_artist())];
		artfrag='&artist='+encodeURI(JSON.stringify(arrartist));
	}
	/*if (get_album()!=null&&get_album()!=''){
		arralbum=[encodeURIComponent(get_album())];
		albfrag='&album='+encodeURI(JSON.stringify(arralbum));
	}*/
	if (get_embed_value()!=null&&get_embed_value()!=''){
		embembed=[encodeURIComponent(get_embed_value())];
		embfrag='&embed='+encodeURI(JSON.stringify(embembed));
	}
	//get_player().pause();
	//set_isplaying(0);
	//set_isplaying(NaN);
	set_page_init(false);update_ajax_body('./?'+artfrag+embfrag+'&offset='+encodeURI(customoffset)+'&autoplay=true');
	//setplayerstall(false);
	//update_autoplay();
}
function updateSocialData(obj){
	  
	  dataurl='./network/index.php';
	  if (document.getElementById('bodyajax')!=null&&document.getElementById('bodyajax').value!=''){
			dataurl=dataurl+'?fromajax=1';
	  }
		obj.data=dataurl;

}

function cr_info_document_getElementById(target_info){
	return document.getElementById(target_info);
}
var isplaying;
var currenttarget;
var currentclewn;
var currentid;
var currentautoplay;
var player;
		
function set_isplaying(myid){
	isplaying=parseInt(myid);
}
function get_isplaying(){
	return parseInt(isplaying);
}
function autoplay(target, id, isclewn, isautoplay){
	set_page_load(true);
	set_isplaying(play(target, id, isclewn, isautoplay));
	return get_isplaying();
}
function play(target, id, isclewn, isautoplay){
	player=get_player();
	player.pause();
	
	if (isautoplay&&document.getElementById(id)!=null){
		document.getElementById(id).innerHTML='■';
	}
	
	
	
	if (isclewn) {
			target=dl_audiourl+encodeURI(target);
	}
	else  {
			target=str_audiourl+encodeURI(target);
	}
	currenttarget=target;
	currentclewn=isclewn;
	
	if (get_isplaying()!=id){
		if (!isautoplay) 
		{	
			if (document.getElementById(id)!=null){
				document.getElementById(id).innerHTML='■';	
			}
		}
	if (document.getElementById(id)!=null){
		set_isplaying(parseInt(id));
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
		if (document.getElementById('track_artist'+id)!=null&&document.getElementById('track_name'+id)!=null){
			update_controler(document.getElementById('track_artist'+id).value, document.getElementById('track_name'+id).value, false);
		}
		else {
			update_controler('no info', 'no info', true)
		} 
		player.load();
		player.play();
		setplayerstall(false);
	}
	else
		{
			set_isplaying(parseInt('-1'));
		}
	return parseInt(get_isplaying());

	}
	
	else {
		if (document.getElementById(id)!=null){
			document.getElementById(id).innerHTML='▶';	
		}
		player.pause();
		player.currentTime=0;
		player.innerHTML=null;
		setplayerstall(false);
		set_isplaying(parseInt(-1));	
			
	}
	return parseInt(get_isplaying());
}
function playNext() {
	if (isNaN(parseInt(get_isplaying()))){
		set_isplaying(parseInt('0'));
	}
	
	player=get_player();
	
	while(!player.paused){player.pause();}
	if (document.getElementById(get_isplaying())!=null){
		document.getElementById(get_isplaying()).innerHTML='▶';
	}
	if (document.getElementById(parseInt(parseInt(get_isplaying())+1))!=null){
		document.getElementById(parseInt(parseInt(get_isplaying())+1)).click();
		setplayerstall(false);
		//return parseInt(get_isplaying());
	}
	else {
		player.pause();
		/*if (document.getElementById('digolder')!=null&&get_embed()!=null&&get_embed()==true){//AJAX is not needed on embeded content in separate muscian specific website, since an iframe will honour autoplay without a reclick
			set_isplaying(-1);
			setplayerstall(false);
			window.location.href=document.getElementById('digolder').name+'&autoplay=true';//'./?offset='+(offset+1)+'&autoplay=true';
		}
		else*/ if(document.getElementById('digolder')!=null){
			//document.getElementById('digolder').href=document.getElementById('digolder').href+'&autoplay=true';
			set_isplaying(-1);
			document.getElementById('digolder').click();
			//document.location.href='./?album='+target_album+'&autoplay=true';
		}

		else
		
		{
			//	document.location.href='./?offset='+(offset+1)+'&autoplay=true';
		}

	}
	
}
function playPrevious() {
	if (isNaN(parseInt(get_isplaying()))){
		set_isplaying(parseInt('0'));
	}
	player=get_player();
	while(!player.paused){player.pause();}
	if (document.getElementById(get_isplaying())!=null){
		document.getElementById(get_isplaying()).innerHTML='▶';
	}
	if (document.getElementById(parseInt(parseInt(get_isplaying())-1))!=null){
		document.getElementById(parseInt(parseInt(get_isplaying())-1)).click();
		setplayerstall(false);
		//return parseInt(get_isplaying());
	}
	else {
		player.pause();
		/*if (document.getElementById('dignewer')!=null&&get_embed()!=null&&get_embed()==true){//AJAX is not needed on embeded content in separate muscian specific website, since an iframe will honour autoplay without a reclick
			set_isplaying(-1);
			setplayerstall(false);
			window.location.href=document.getElementById('dignewer').name+'&autoplay=true';//'./?offset='+(offset+1)+'&autoplay=true';
		}
		else*/ if(document.getElementById('dignewer')!=null){
			//document.getElementById('digolder').href=document.getElementById('digolder').href+'&autoplay=true';
			set_isplaying(-1);
			
			document.getElementById('dignewer').click();
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

function getCover (img, target, size, ratio){
	img.src="./thumbnailer.php?target="+target+"&viewportwidth="+parseInt(size)+"&ratio="+parseFloat(ratio);
	
}
function computeSize(width, height){
	size=640;
	if (width>=height){
				size=height;
			}
			else
			{
				size=width;
			}
	return size;
}
function update_ajax_body(http_url_target){
	set_page_load(false);
	var autoplay='false';
	var arttruc='';
	var size=0;
	var nosocialupdate=false;
	var finalurl;
	var parameters='';
	var overload_track_counter=0;
	var arg='';
				
	tar = http_url_target;
	if (tar.startsWith('http://') || tar.startsWith('https://') || tar.startsWith('//') || tar.startsWith('/'))
		{
			//this is an absolute, or external url. We don't wan't to do anything with it. 
			//caller shouldn't have it passed to our function
			//just in case, and it won't respect an possible "target" directive wished by the caller
			//we are going to move the user brower straight to this address
			window.location.href=tar;
			//note that window.location is preferable to document.location. Both are the same. But some awkward browsers have document.location read-only
		}
		else {
			//Here comes our main job. First off, split the target url into 
			//slash-separated tokens
			finalurl='./?body=ajax';
			slashes=tar.split('/');
			for (let i=0 ; i<slashes.length ; i++){
				while (i<slashes.length&&!(slashes[i].startsWith('?'))){
					if (slashes[i]=='.'){
						//our good ol' coder warns you that it is an url relative to the current folder
						//which is nice
						//so we don't have to do anything with it
					}
					else {
						finalurl=finalurl+'&target[]='+slashes[i];
						
					}
					i++;
				}
				//oh, we are out of the path, and entering the parameters
				//first, reconcantenate all of them, because a parameter can use a slash ! 
				//we already started to forge an http get chain
				//then the intial ? has now to become a &
				if (i<slashes.length){
					parameters=parameters+slashes[i].replace('?', '&');
					i++;
					for (let j=i ; j<slashes.length ; j++){
						parameters=parameters+'/'+slashes[j];
						}
					//and we got nothinig more to do since the parameter part of target url was already usable as is
					//In yo dream mama
					//Still work to do
					
					prefinalurl='';
					
					splitedparam=parameters.split('&');
					//console.log(splitedparam);
						
					for (k=0 ; k<splitedparam.length; k++){
						ourpar=splitedparam[k];
						
						if (ourpar!=''){//just in case
							//console.log(ourpar);
							
							splitedourpar=ourpar.split('=');
							
							arg='';
							jsonsuccess=null;
							jarg='';
							try {
									jarg=JSON.parse(decodeURI(splitedourpar[1]))[0];
									if (jarg!=null){
										jsonsuccess=true;
									}
							}
							catch (e) {
									jsonsuccess=false;
									targ=splitedourpar[1];
								}
							
							if (splitedourpar[0]=='autoplay'){
									autoplay=splitedourpar[1];
							}
							if (splitedourpar[0]=='artist'){
									arttruc=decodeURI(jarg);
									set_isplaying(-1);
							}
							
							
							prefinalurl=prefinalurl+'&'+splitedourpar[0];
							
							
							//console.log(splitedourpar[0]);
							//console.log(splitedourpar[1]);
							if (jsonsuccess==true) {
								prefinalurl=prefinalurl+'='+jarg;
							}
							else {
								prefinalurl=prefinalurl+'='+splitedourpar[1];
							}
						}	
					}
				}
				
				
				
				finalurl=finalurl+prefinalurl;	
				
				i=slashes.lenght;
				}
			}
			//Now the AJAX part of the thing
			
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 document.getElementById('crerobody').innerHTML = this.responseText;
			 set_page_load(true);
			 document.getElementById('bodyajax').value = finalurl ;
			 document.getElementById('bodyajax_autoplay').value = autoplay;
			 document.getElementById('bodyajax_arttruc').value = arttruc;
			}
			};
			//console.log(finalurl);
			xhttp.open("GET", finalurl, true);
			xhttp.send();
			
			
			
			
			
	
			
			
			
		}
		
function update_controler(artist, track, noinfo){
	c_target=document.getElementById('controler_nowplaying');
	if (c_target!=null){
		
		c_target.innerHTML=artist+' - '+track;
	}
	
}	

function hideRecentlyPlayed(){
	document.getElementById('recently_played').innerHTML='<a href="javascript:void(0);" onClick="displayRecentlyPlayed();">Recently played?</a><br/><hr style="float:none;clear:both;"/>';
}
function clock_tick(){
	if (document.getElementById('playerclock')!=null){
		myclock=document.getElementById('playerclock');
	}
	if (get_isplaying()=='-1'){
		myclock.innerHTML='0:00'+'/'+'0:00';
		
	}
	mytime=get_player().currentTime;
	myduration=get_player().duration;
	
	if (isNaN(mytime)){
		mytime=parseInt(Math.floor((0)));
	}
	
	if (isNaN(myduration)){
		myduration=parseInt(Math.floor((0)));
	}
	
	if (mytime>60){
		seconds=Math.floor(mytime%60);
		if (seconds<10){
			seconds='0'+seconds;
		}
		mytime=Math.floor(mytime/60)+':'+seconds;
	}
	else {
		seconds=Math.floor(mytime%60);
		if (seconds<10){
			seconds='0'+seconds;
		}
		mytime='0:'+seconds;
	}
	
	if (myduration>60){
		seconds=Math.floor(myduration%60)
		if (seconds<10&&seconds!=0){
			seconds='0'+seconds;
		}
		else if (seconds==0||isNaN(seconds))
		{
			seconds='00';
		}
		myduration=Math.floor(myduration/60)+':'+seconds;
	}
	else {
		seconds=parseInt(Math.floor(myduration));
		if (seconds<10&&seconds!=0){
			seconds='0'+seconds;
		}
		else if (parseInt(seconds)==parseInt('0'))
		{
			seconds='00';
		}		
		if (isNaN(seconds)){
			seconds='00';
		}
		myduration='0:'+seconds;
	}	
	
	myclock.innerHTML=mytime+'/'+myduration;
}
function controler_next(){
	if(!get_playerstall()){setplayerstall(true);player=get_player();
		if (get_isindex()==true)
			{while(!player.paused){player.pause();}
			page_init=false;digolder(0);} 
			else 
			{while(!player.paused){player.pause();}
				playNext();}
				player.addEventListener('canplaythrough', function(){setplayerstall(false);});player.addEventListener('error', function(){setplayerstall(false);});player.addEventListener('abort', function(){setplayerstall(false);});}
}
function controler_prev(){
	if(!get_playerstall()){
		setplayerstall(true);
		if (get_isindex()||(get_embed()&&get_offset()=='-1'))
			{void(0);}
		else {
			player=get_player();
			while(!player.paused){player.pause();}
			playPrevious();}
			player.addEventListener('canplaythrough', function(){setplayerstall(false);});player.addEventListener('error', function(){setplayerstall(false);});player.addEventListener('abort', function(){setplayerstall(false);});}
}	
var playerstall;
function get_playerstall(){
	return(playerstall);
}
function setplayerstall(arg){
	playerstall=arg;
}
var twindex=0;
function twirling(){
	baton=document.getElementById('twirling');
	anim= ['(\\)', '(|)', '(/)', '(-)', '(\\)', '(|)', '(/)', '(-)']
	baton.innerHTML=anim[twindex];
	twindex++;
	if (twindex==anim.length){
		twindex=0;
	}
	if (get_page_init()){
		baton.style.display='none';
	}
	if (!get_page_init()){
		baton.style.display='block';
	}
	if (!get_page_load()){
		baton.style.display='block';
	}
	if (get_page_load()){
		baton.style.display='none';
	}

}
window.setInterval(clock_tick, 1200);	
window.setInterval(twirling, 650);	

var loaded;		
function get_page_load(){
	return loaded;
}	
function set_page_load(argk){
	loaded=argk;
}	
	

// @license-end
