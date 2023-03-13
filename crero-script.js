// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0
var page_init;
var album_displayed=0;
var overload_track_counter=NaN;
var infoselected=null;
var infoselected=null;
var size;

var thumbnail_counter=0;
var thumbnail_max=0;

var target_album='';
var embed=null;
var overloadindexchecked=false;

//yp stuff
var ypping=true;

var myfunc=null;	
var yprun=true;
var ypindex=0;
var appendypreq='';
var ypretries=0;
var ypcurrentindexretries=0;
var stall=false;
var yparrvalidated=[];
var nosocialupdate=false;	  
//ping recently played
var xhttpingprecalb=null;
var titleSite='';
var artist;
var album;
var track;
var offset;
var isindex=undefined;
var overloadtimer=null;
var embed_value='';
var isplaying;
var isautoplay;
var str_album_count;
var dl_album_count;
var recentplay;

var playerErrorTimer=0;
var playerErrorTimestamp=0;


var myoverloadtimer=null;

var radioIntervalFunc=null;
var radiomsg='';

var radioajax;

function clearRadioInterval(){
	
	if (radioIntervalFunc!=null){	
	
		window.clearInterval(radioIntervalFunc);
		radioIntervalFunc=null;	
		
	}
}


function update_radio_nowplaying(){
	if (document.getElementById('radio_nowplaying')==null){
		window.clearInterval(radioIntervalFunc);
		radioIntervalFunc=null;	
	}
	else {
		document.getElementById('radio_nowplaying').innerHTML=radiomsg;
	}
	radioajax = new XMLHttpRequest;
	radioajax.onreadystatechange = radionymous;
	radioajax.open("GET", "./radio_data.php", true);
	radioajax.send();
		
	
}
function radionymous() {
				if (this.readyState == 4 && this.status == 200) {
					myresponse=new String(this.responseText).split("\n");
					if (parseFloat(myresponse[0])<parseFloat(myresponse[5])){
						radiomsg="Nothing currently - Waiting for a click";
						
					}
					else {
					radiomsg=myresponse[3]+' - '+myresponse[4];
					}
				
			
				}
				else {
				radiomsg="Ongoing fetching...";
				}
		
		}
function tryToRecoverPlayerError() {
	//this funtion is called each time the <audio> only player on the main section (aka anything but Radio: index page, album pages, artist-album pages, track pages) will throw an onError JS event
	//since the error can be caused for many, various reason, we are just going to try to relaunch play(); on the player ; this is typically for media data not coming from the HTTP server serving... Media data
	//by far the most common case
	//So let it a chance to send again the (partial-content) data and then to allow the listener to get its playback back after a quick cut is our aim with this call to play() after an error was triggered
	//but since we don't want to overload the HTTP server providing us with audio, we are going
	//to wait a bit, before trying... And if errors continue to arrive in a short time
	//to wait each time a bit more, increasing the waiting time
	//which is necessary to avoid general server overload and therefore never recovering
	//This waiting behavior is achevied with the use of two global scope var defined outside this function:
	// * playerErrorTimer : it is initially set to 0. It defines how much we will wait before a retry. Once the first call, we'll increment it of 500ms, wait for 500ms, and try to play()
	// but on any possible subsequent call, we'll increment it again of 500ms each time. It means that if errors remains, we'll wait for 1000, 1500, 2000, 2500, 3000 and so on each time before attempting play()
	// (and test if player is not already playing, before launching play(), because an previous thread can possibly already have solved the error, since we'll use timeOuts, which are asynchronous)
	// * playerErrorTimestamp : it is initially set to 0. If this function is called for the first time, we'll store a current Date().getTime() and then we'll know we're in trouble and do the playerErrorIncrement
	// has explained above
	// BUT if this function is called, but playerErrorTimestamp is older than 5 minutes, which means the player hasn't triggered any error for the past 5 minutes
	// we will reset playerErrorTimer to its initial zero value
	// because if errors cames like the Niagara Falls, if the listener had to wait, if playerErrorTimer raised up to several, long seconds between each play() attempt, and finally, the playback process resumed quietly
	// and then that after a long while without error (5 minutes as indicated), if on another track, or on another album, an new error is triggered
	// we want to try to cure it after only a short (500ms) cutoff for the listener, and won't wait for the previously set to a possibly very long time playerErrorTimer before attempting our retry. 
	
	
	//First off we look at playerErrorTimestamp, and, if it is older than 5 minutes, 
	// * we reset playerErrorTimer
	// * we set playerErrorTimestamp to the current time, and, for the next 5 minutes, it will be useful
	d = new Date();
	currentTime = d.getTime();
	if (parseInt(playerErrorTimestamp)+300000<currentTime){
		playerErrorTimestamp=currentTime;
		playerErrorTimer=0;
	}
	
	//Then we are going to try to recover, but for the first retry, after 500ms only, and on each subsequent retry if any, each time after 500ms added to the previous playerTimerError value

	playerErrorTimer=parseInt(playerErrorTimer)+500;
	window.setTimeout(function(){
		if (get_player()!=null){
				get_player().play();
			}
		
		}, playerErrorTimer);

	//end of TryToRecoverPlayerError()
}


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
function cr_document_overload_splash__style_display(display){
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
function  digolder(customoffset, comesfrominfiniteloop){
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
	set_page_init(false);update_ajax_body('./?'+artfrag+embfrag+'&offset='+encodeURI(customoffset)+'&autoplay=true', comesfrominfiniteloop);
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
  }
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
function update_twirling_message(text){
	document.getElementById('twirling_message').innerHTML=text;
}
function increment_thumbnail_counter(){
		thumbnail_counter++;
		update_twirling_message(thumbnail_counter+" thumbnails generated just for you, for you screen resolution, once for all");
}
function increment_thumbnail_max(){
		thumbnail_max++;
		//update_twirling_message(thumbnail_counter+"/"+thumbnail_max+" thumbnails generated juste for you, for you screen resolution, once for all");
}


function update_ajax_body(http_url_target, comesfrominfiniteloop){
	clearRadioInterval();
	
	var infiniteloop=false;
	
	if (comesfrominfiniteloop!=undefined){
		infiniteloop=true;
	}
	
	set_page_load(false);
	set_page_init(false);
	var thumbnail_counter=0;
	var thumbnail_max=0;
	var autoplay='false';
	var arttruc='';
	var size=0;
	var nosocialupdate=false;
	var finalurl;
	var parameters='';
	var arg='';
    set_overload_track_counter(0);
	//set_page_init(false);
	
				
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
			for (i=0 ; i<slashes.length ; i++){
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
			
			
			
			
			var uaxhttpattempt=0;
			var uaxhttp = new XMLHttpRequest();
			uaxhttp.onreadystatechange = function() {
			if (this.readyState == 4){
			  if (this.status == 200) {

			 document.getElementById('crerobody').innerHTML = this.responseText;
			 set_page_load(true);
			 
			 document.getElementById('bodyajax').value = finalurl ;
			 document.getElementById('bodyajax_autoplay').value = autoplay;
			 document.getElementById('bodyajax_arttruc').value = arttruc;
			 if (infiniteloop){init_page();}
				}
				else {
					uaxhttpattempt++;
					update_twirling_message('The server replied with a non OK code: '+this.status+' ; retrying, retry is '+uaxhttpattempt);
					this.abort();
					this.send();
					
				}
				}
			}
			//console.log(finalurl);
			uaxhttp.open("GET", finalurl, true);
			uaxhttp.send();
			update_twirling_message("Loading...");
			
			
			
			
			
	
			
			
			
		}
function chckImg(img, url, ratio){
	getCover(img, encodeURIComponent('./covers/')+url, get_size(), ratio);if (img.src!='favicon.png'){increment_thumbnail_counter();};
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
var twirl=true;
function twirling(){
	baton=document.getElementById('twirling');
	anim= ['(\\)', '(|)', '(/)', '(-)', '(\\)', '(|)', '(/)', '(-)']
	
	if (twirl){
		baton.innerHTML=anim[twindex];
		twindex++;
	}
	if (twindex==anim.length){
		twindex=0;
	}
	if (!get_page_load()){
		twirl=true;
		baton.style.display='block';
		document.getElementById('twirling_message').style.display='block';
	}
	if (get_page_load()){
		twirl=false;
		baton.style.display='none';
		document.getElementById('twirling_message').style.display='none';

	}


	/*if (get_page_init()){
		baton.style.display='none';
	}
	if (!get_page_init()){
		baton.style.display='block';
	}*/

}
window.setInterval(clock_tick, 1200);	
window.setInterval(twirling, 650);	

var loaded=false;		
function get_page_load(){
	return loaded;
}	
function set_page_load(argk){
	loaded=argk;
}	


function autoplay (mycurrenttarget, mycurrentid, mycurrentclewn, mycurrentautoplay){
	play(mycurrenttarget, mycurrentid, mycurrentclewn, mycurrentautoplay);
}
function get_isindex(){
	if (isindex==undefined){
		return false;
	}
	
	return isindex;
}
function set_isindex(par){
	isindex=par;
}
//var overloadindexchecked=false;
function get_overloadindexchecked(){
	return overloadindexchecked;
}
function set_overloadindexchecked(ckpar){
	overloadindexchecked=ckpar;
}





function update_isindex(){
	if (document.getElementById('isindex')!=null){
		if (document.getElementById('isindex').value=='true'){
			set_isindex(true);
		}
		else {
			set_isindex(false);
		}
	}
	else {
		set_isindex(false);
	}
	
	
}
function update_embed(){
	if (document.getElementById('embed')!=null){
		if (document.getElementById('embed').value=='true'){
			set_embed(true);
		}
		else {
			set_embed(false);
		}
	}
	else {
		set_embed(false);
	}
	
	
}
function update_embed_value(){
	if (document.getElementById('embed_value')!=null){
		set_embed_value(document.getElementById('embed_value').value);
		
	}

}
function update_offset(){
	if (document.getElementById('offset')!=null){
		return parseInt(document.getElementById('offset').value);
		}
		else {
			return parseInt(-1);
		}
	
	
	
}
function update_album(){
	if (document.getElementById('album')!=null){
		return decodeURI(document.getElementById('album').value);
		}
		else {
			return '';
		}
}
function update_artist(){
	if (document.getElementById('artist')!=null){
		return decodeURI(document.getElementById('artist').value);
		}
		else {
			return '';
		}
}
function update_track(){
	if (document.getElementById('track')!=null){
		return decodeURI(document.getElementById('track').value);
		}
		else {
			return '';
		}
}
function update_autoplay(){
	if (document.getElementById('bodyajax_autoplay')!=null){
		if(document.getElementById('bodyajax_autoplay').value=='true'){
			set_autoplay(true);

	

			if (document.getElementById('autoplay')!=null){
				document.getElementById('autoplay').click();
			}
		}
		else
		{
			set_autoplay(false);
		}
	}
	else
		{
			set_autoplay(false);
		}
}

function update_recentplay() {
	if (document.getElementById('recentplay_pass')!=null){
		if (document.getElementById('recentplay_pass').value=='true'){
			set_recentplay(true);
	
			}
		else
			{
			set_recentplay(false);	
			}
			
	}
	else {
		set_recentplay(false);
	}
}
function update_artisthighlighthomepage() {
	if (document.getElementById('artisthighlighthomepage')!=null){
		if (document.getElementById('artisthighlighthomepage').value=="true"){
			set_artisthighlighthomepage(true);
	
			}
		else
			{
			set_artisthighlighthomepage(false);	
			}
			
	}
	else {
		set_artisthighlighthomepage(false);
	}
}
function update_creroypservices() {
	if (document.getElementById('creroypservices')!=null){
		set_creroypservices(parseInt(document.getElementById('creroypservices').value));
	
		
			
	}
	else {
		set_creroypservices(0);
	}
}


function update_album_count(){
	if (document.getElementById('dl_album_count')!=null){
		set_dl_album_count(parseInt(document.getElementById('dl_album_count').value));
		}
		else {
			set_dl_album_count(parseInt('-2'));
		}
	if (document.getElementById('str_album_count')!=null){
		set_str_album_count(parseInt(document.getElementById('str_album_count').value));
		}
		else {
			set_str_album_count(parseInt('-2'));
		}
	
	
}


var inc_stall=undefined;

function get_inc_stall(){
	if (inc_stall==undefined){
		return false;
	}
	
	return inc_stall;
}
function set_inc_stall(argst){
	inc_stall=argst;
}
var toincrement=undefined;
function increment_overload_track_counter(){
	if (overload_track_counter=NaN){
		set_overload_track_counter(0);
	}
		
	set_overload_track_counter(parseInt(get_overload_track_counter())+parseInt(1));
}


function get_overload_track_counter(){
	if (isNaN(parseInt(overload_track_counter))){
		return 0;
	}
	return parseInt(overload_track_counter);
	
}
function set_overload_track_counter(otca){
	overload_track_counter=parseInt(otca);
	
}
function get_album(){
	return album;
}

function get_autoplay(){
	return isautoplay;
}
function set_autoplay(auartarg){
	isautoplay=auartarg;
}

function get_init_page(){
	if (init_page==null){
		return false;
	}
	
	return page_init;
}
function set_init_page(onld){
	page_init=onld;
	}

function get_artist(){
	return artist;
}
function set_artist(artarg){
	artist=artarg;
}
function get_track(){
	return track;
}
function set_track(trarg){
	track=trarg;
}


function get_offset(){
	return offset;
}
function get_embed(){
	return embed;
	
}
function set_embed(earg){
	embed=earg;
}
function get_embed_value(){
	return embed_value;
	
}
function set_embed_value(evarg){
	embed_value=evarg;
}
function get_str_album_count(){
	return str_album_count;
	
}
function set_str_album_count(cevarg){
	str_album_count=cevarg;
}
function get_dl_album_count(){
	return dl_album_count;
	
}
function set_dl_album_count(dcevarg){
	dl_album_count=dcevarg;
}
function set_offset(offarg){
	offset=offarg;
}
function set_album(aoffarg){
	album=aoffarg;
}
function set_page_init(piarg){
	page_init=piarg;
}
function get_page_init(){
	return page_init;
}
function set_size(spiarg){
	size=spiarg;
}
function get_size(){
	return size;
}

var albumerror=undefined;

function get_album_error(){
	return albumerror;
}
function set_album_error(myargerr){
	albumerror=myargerr;
}



function get_recentplay(){
	if (recentplay==null){
		return false;
	}
	return recentplay;
	
}	
function set_recentplay(rparg){
	recentplay=rparg;
}
var artisthighlighthomepage=null;

function get_artisthighlighthomepage(){
	if (artisthighlighthomepage==null){
		return false;
	}
	return artisthighlighthomepage;
	
}	
function set_artisthighlighthomepage(artparg){
	artisthighlighthomepage=artparg;
}
var creroypservices=0;

function get_creroypservices(){
	return creroypservices;
}
function set_creroypservices(cyparg){
	creroypservices=parseInt(cyparg);
}
//here the main logic of each page display
function init_page() {
	radiomsg='fetching data...';
	
	if (radioIntervalFunc==null){
		radioIntervalFunc=window.setInterval(update_radio_nowplaying, 4500);
	}
	
	
	set_inc_stall(false);
	//monthly donation
	monthly=false;

	set_overloadindexchecked(false);
	if (overload_track_counter==NaN){
	
		set_overload_track_counter(0);
	}
	set_isplaying('-1');
	
	update_album_count();
	
	update_embed();
	

	update_embed_value();
	
	set_offset(update_offset());
	
	set_album(update_album());
	set_track(update_track());
	set_artist(update_artist());
	
	update_title();

	update_isindex();
	//for everyone's safety, let's make an asynchronous call to crero-yp-api, which amongs lots of things, does routine cleanup on media tiers
	oxhtto = new XMLHttpRequest();
	oxhtto.open ('GET', './crero-yp-api.php', true);
	oxhtto.send();
	//now the audio tiers have been cleaned up
	
	
	
	
	
	
	if (!get_isindex()){
		if(document.getElementById('splash')!=null){
			document.getElementById('splash').style.display='none';
		}
	}

	update_recentplay();
	
	if (get_recentplay()){
	
	tryindex=10;
				
	while(xhttzzpingprecalb!=null&&tryindex>=0){if (xhttzzpingprecalb[tryindex]!=null){xhttzzpingprecalb[tryindex].abort();}tryindex--;}
				
	
	
	
	
	var callback_id=Math.random();
	var alb_willhavetoping=true;
	if (!get_isindex()&&get_album()!=''){
		var recentretries=0;
		var oprpretries=0;
				 
		var current_recent_album=get_album();
		var xhttzzpingprecalb=[];
		var xhttzzpingprecalb_index=0;
		timer=1000;
		while (recentretries < 10) {
			recentretries++;
			setTimeout(function(){
				tryindex=xhttzzpingprecalb_index-1;
				
				while(xhttzzpingprecalb[tryindex]!=null){xhttzzpingprecalb[tryindex].abort();tryindex--;}
				
				
				if (alb_willhavetoping)
					{
					xhttzzpingprecalb[xhttzzpingprecalb_index] = new XMLHttpRequest();
					xhttzzpingprecalb[xhttzzpingprecalb_index].onreadystatechange= function(){


					if (this.readyState == 4 && this.status == 200) {
							for (i=0;i<xhttzzpingprecalb.length;i++){
								xhttzzpingprecalb[i].abort();
							}
							recentretries=10;
							var oxhttozzypingprecalb; 
							oxhttozzypingprecalb = new XMLHttpRequest();
							oxhttozzypingprecalb.onreadystatechange = function(){
								if (this.readyState == 4 && this.status == 200) {
										
										oprpretries=10;
									}
								
								};
							stimer=1000;
								
							while(oprpretries<10){
								oprpretries++;
								setTimeout(function(){
								//oxhttozzypingprecalb.abort();
								
								oxhttozzypingprecalb = new XMLHttpRequest();
								oxhttozzypingprecalb.onreadystatechange = function(){
								if (this.readyState == 4 && this.status == 200) {
										alb_willhavetoping=false;
								
										oprpretries=10;
									}
								
								};
								
								if (alb_willhavetoping){
									oxhttozzypingprecalb.open("GET", "ping_recently_played.php", true);
									oxhttozzypingprecalb.send();
									}
								},stimer);
								stimer=stimer+1000;
							}
							
						}
					
					}
				
					xhttzzpingprecalb[xhttzzpingprecalb_index].open("GET", "./?recently_callback=true&album="+encodeURIComponent(current_recent_album)+"&recently_callback_id="+encodeURIComponent(callback_id), true);
					xhttzzpingprecalb[xhttzzpingprecalb_index].send();
					xhttzzpingprecalb_index++;
				}
			},timer);
			timer=timer+3800;
		}
	
	
	}


	
	
	//ping recently played
	if (!get_isindex()&&alb_willhavetoping){
		var prpretries=0;
		
		var prpxhttozzypingprecalb = new XMLHttpRequest();
		prpxhttozzypingprecalb.onreadystatechange = function(){
			if (this.readyState == 4 && this.status == 200) {
					prpretries=10;
				}
			
			};
		ttimer=1000;
		while(prpretries<10){
			prpretries++;
			setTimeout(function(){
				prpxhttozzypingprecalb.abort();
				prpxhttozzypingprecalb = new XMLHttpRequest();
				prpxhttozzypingprecalb.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200) {
							alb_willhavetoping=false;
							prpretries=10;
						}
					
					};
				if(alb_willhavetoping){
					prpxhttozzypingprecalb.open("GET", "ping_recently_played.php", true);
					prpxhttozzypingprecalb.send();
				}
			},ttimer);
			ttimer=ttimer+1000;
		}
	}
	}
	
	//yp stuff
	ypping=true;
	nosocialupdate=false;	  
	if (myfunc!=null){
		clearInterval(myfunc);	
	}
	myfunc=null;	
	yprun=true;
	ypindex=0;
	appendypreq='';
	ypretries=0;
	ypcurrentindexretries=0;
	stall=false;
	yparrvalidated=[];
	//end yp stuff

	album_displayed=0;
	set_size(computeSize(document.documentElement.clientWidth, document.documentElement.clientHeight));
	
	infoselected=null;
	if (document.getElementById('noscripters')!=null){
		document.getElementById('noscripters').style.display='none';
	}
	if (document.getElementById('noscripters_footer')!=null){
		document.getElementById('noscripters_footer').style.display='none';
	}
	if (document.getElementById('infiniteloop')!=null){
		document.getElementById('bodyajax_autoplay').value='false';
	}
	
//initial stuf
		stats();
update_artisthighlighthomepage();	
if (get_artisthighlighthomepage())
{ 
		
	if (get_isindex()){	
		document.getElementById('crerobody').style.marginLeft="0px";
		document.getElementById('crerobody').style.marginRight="0px";
		document.getElementById('crerobody').style.paddingLeft="0px";
		document.getElementById('crerobody').style.paddingRight="0px";
	} else {
	if (document.documentElement.clientWidth>800){
			document.getElementById('crerobody').style.paddingLeft="8%";
			document.getElementById('crerobody').style.paddingRight="8%";
		}
	}
}//restoration of maring/pading(left/right) is finished
//fortunately enough, these things will never change
//from one page to antoher

//yp stuff
update_creroypservices();

if (!get_embed()&&get_creroypservices()>0)
	{
	yprun=true;myfunc=setInterval (delegate, 1000);
	}

if (typeof update_cart === "function"){
update_cart();
}

//blocks move!  New page design thx to Fauve's advices !
blocksToMove=['splash-','links-','recent-','radio-','menu-','artists-','donate-'];
for (i=0;i<blocksToMove.length;i++){
	if (document.getElementById(blocksToMove[i]+'wrapper')!=null&&document.getElementById(blocksToMove[i]+'dest')!=null){
		code=document.getElementById(blocksToMove[i]+'wrapper').innerHTML;
		document.getElementById(blocksToMove[i]+'dest').innerHTML=code;
		document.getElementById(blocksToMove[i]+'wrapper').innerHTML='';
	}
}
if (get_isindex()){
	
	document.getElementById('title-dest').innerHTML=document.getElementById('title').innerHTML;
	document.getElementById('title-dest').style.textAlign='center';
}

//then, sanitize the display URL with something accurate



if (document.getElementById('bodyajax')!==null){//this should never happen
		
		titleSiteObj={  title:'',  url:''};
		
		titleSiteObj.title=document.getElementById('main_title').innerHTML;
		if ( //this is the most common case ; an GET album, or more GET, are set, and it's an ajax call, and it's not an ajax call to call the homepage
				(get_album()!=''||
				get_artist()!=''||
				(get_track()!=''&&get_album()!='')
				)
			&&	(document.getElementById('bodyajax').value!='./?body=void'&&document.getElementById('bodyajax').value!='')
			){
			ourURL='./?';
			needamp=false;
			if (get_album()!=''){	
				ourURL=ourURL+'album='+encodeURIComponent(get_album());
				needamp=true;
				}
			if (get_artist()!=''){	
				if (needamp) {ourURL=ourURL+'&';}
				ourURL=ourURL+'artist='+encodeURIComponent(get_artist());
				needamp=true;
				}
			if (get_track()!=''){	
				if (needamp)  {ourURL=ourURL+'&';}
				ourURL=ourURL+'track='+encodeURIComponent(get_track());
				needamp=true;
				}	
			
			titleSiteObj.url=ourURL;
			window.history.pushState(titleSiteObj, '', ourURL);
				
			
			
			}
				
		
		else if (document.getElementById('bodyajax').value!='./?body=void'&&document.getElementById('bodyajax').value.startsWith('?body=ajax&')){//this will happen on anything but indexpage, which does not requires sanitization
			ourURL=document.getElementById('bodyajax').value;
			ourURL=ourURL.replace('?body=ajax&', '?');
			
			titleSiteObj.url='./'+ourURL;
			window.history.pushState(titleSiteObj, '', './'+ourURL);
			
			
			
		}
		else if (document.getElementById('bodyajax').value==''){//index page ? 
			//ourURL=document.getElementById('bodyajax').value;
			//ourURL=ourURL.replace('?body=ajax&', '?');
			ourURL='';
			if (window.location.href.endsWith('/radio/')){
				ourURL='radio/';
			}
			if (get_isindex()||ourURL=='radio/'){
				titleSiteObj.url='./'+ourURL;
				window.history.pushState(titleSiteObj, '', './'+ourURL);
			}
			else {
				//direct arrival on a shared link
				titleSiteObj.url=window.location.href;
				window.history.pushState(titleSiteObj, '', window.location.href);

			}
			
			
		}

	}
	
	//playback stuff
	setplayerstall(false);

	if (document.getElementById('digolder')!=null&&get_album()!=''&&(parseInt(get_offset())==parseInt('-1'))||get_offset==''){
		document.getElementById('digolder').innerHTML='Dig what\'s new...';
	}

	
	update_autoplay();

	if (activatehtmlcache){
		if((get_isindex()&&!get_page_init())){increment_overload_track_counter();};checkOverload(true);
	}
	if (document.getElementById('infiniteloop')!=null){
		set_page_init(true);
		
		
		document.getElementById('infiniteloop').click();
	}

	set_page_init(true);
}//function initpage








// @license-end
