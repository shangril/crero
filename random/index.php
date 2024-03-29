<?php
if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
//We've nothing to say to most impolite bots

chdir('..');
require_once('./config.php');
chdir('./radio');

session_start();

function session_put_contents($key, $value){
	$_SESSION[$key]=$value;
}

function session_get_contents($key){
	return $_SESSION[$key];
}
function session_exists($key){
	if (array_key_exists($key, $_SESSION)){
			return true;
	}
	else {
		return false;
	}
}


if (false||!$hasradio){
	die();
}

if (false||isset($_GET['m3u'])){
	
	header('Content-type: application/x-mpegurl');
	
	if ($IsRadioStreamHTTPS){
		echo 'https://'.$server.'/radio/stream.mp3';
		exit();
	}
	else
	{
		echo 'http://'.$server.'/radio/stream.mp3';
		exit();
	
	}
}

if (isset($_GET['ajax'])){
	if ($_GET['ajax']==='block'){
		
		$material_artists_file=htmlentities(trim(session_get_contents('../d/material_artists.txt')));
	
		$material_artists=explode("\n", $material_artists_file);
			
		$material_blacklist_file=htmlentities(trim(session_get_contents('../d/material_blacklist.txt')));
			
		$material_blacklist=explode("\n", $material_blacklist_file);

		$duration=intval(session_get_contents('../d/nowplayingduration.txt'));
		
		$nowplayingdurationminutes=floor($duration/60);
		
		$nowplayingdurationseconds=$duration-$nowplayingdurationminutes*60;
		
		$hasplayed=intval(microtime(true)-floatval(session_get_contents('../d/starttime.txt')));

		$hasplayedminutes=floor($hasplayed/60);
		
		$hasplayedseconds=$hasplayed-$hasplayedminutes*60;
		
		if ($hasplayedseconds<10){
			$hasplayedseconds='0'.$hasplayedseconds;
		}
		if ($nowplayingdurationseconds<10){
			$nowplayingdurationseconds='0'.$nowplayingdurationseconds;
		}
		
		echo '<strong style="font-size:125%;">';
		echo '<a target="new" id="artist" href="../?artist='.urlencode(session_get_contents('../d/nowplayingartist.txt')).'">';
		echo session_get_contents('../d/nowplayingartist.txt');
		echo '</a>';
		echo '</strong>';
		echo ' - ';
		echo '<a target="new" href="../?album='.urlencode(session_get_contents('../d/nowplayingalbum.txt')).'&track='.urlencode(session_get_contents('../d/nowplayingtitle.txt')).'">';
		echo '<em id="title" style="font-size:125%;">'.session_get_contents('../d/nowplayingtitle.txt').'</em>';
		echo '</a>';
		echo ' - [';
		//.htmlspecialchars($hasplayedminutes).':'.htmlspecialchars($hasplayedseconds).'/'.
		echo htmlspecialchars($nowplayingdurationminutes).':'.htmlspecialchars($nowplayingdurationseconds).']';
		echo '<br/><span  style="font-size:125%;">(';
		echo '<a target="new"  id="album" href="../?album='.urlencode(session_get_contents('../d/nowplayingalbum.txt')).'">';
		echo session_get_contents('../d/nowplayingalbum.txt');
		echo '</a>';
		
		echo ')</span><br/>';
		if (boolval(trim(session_get_contents('../d/nowplayingisfeatured.txt')))){
			$target=session_get_contents('../d/nowplayingurl.txt');
			$targetflac=str_replace('.mp3', '.flac', $target);
			$targetogg=str_replace('.mp3', '.ogg', $target);
			
			echo 'Download <a download target="new" href="'.$targetflac.'">flac</a> <a download target="new" href="'.$targetogg.'">ogg</a> <a download target="new" href="'.$target.'">mp3</a>';
			echo '<br/>'.session_get_contents('../d/license.txt');
		}
		else {
			echo 'Exclusive premiere track. Out for download soon';
		}
		if (!in_array(trim(html_entity_decode(session_get_contents('../d/nowplayingalbum.txt'))),$material_blacklist)
			&&
			in_array(trim(html_entity_decode(session_get_contents('../d/nowplayingartist.txt'))),$material_artists)
			&& boolval(trim(session_get_contents('../d/nowplayingisfeatured.txt')))
		
		
		) {
			echo '<br/>Available as <a target="new" href="../?listall=mixed&album='.urlencode(session_get_contents('../d/nowplayingalbum.txt')).'">material release</a> at our online shop';
			
		}
		if (!session_exists('../d/maxlisteners.txt')){
			session_put_contents('../d/maxlisteners.txt', '0');
		}
		
		$listeners=count(array_diff(scandir('../d/listeners'), Array ('.', '..')));
		
		if ($listeners>intval(session_get_contents('../d/maxlisteners.txt'))){
			session_put_contents('../d/maxlisteners.txt', $listeners);
		}
		if ((floatval(filectime('../d/maxlisteners24hours.txt'))+24*60*60)<=microtime(true)){
		
			unlink('../d/maxlisteners24hours.txt');
		
		}
		if ($listeners>intval(session_get_contents('../d/maxlisteners24hours.txt'))){
			session_put_contents('../d/maxlisteners24hours.txt', $listeners);
		}
		$peaktime=microtime(true)-filectime('../d/maxlisteners24hours.txt');
		$peakhours=ceil($peaktime/(60*60));

		echo '<br/>Listeners<br/>Current : '.$listeners.' / Peak : '.htmlspecialchars(session_get_contents('../d/maxlisteners.txt')).' (all-time) '.htmlspecialchars(session_get_contents('../d/maxlisteners24hours.txt')).' ('.htmlspecialchars($peakhours).'-hours)';
	}
	else if ($_GET['ajax']==='cover'){
		$covers=trim(file_get_contents('../d/covers.txt'));
		$coverlines=explode("\n", $covers);
		$artworks=Array();
		for ($i=0;$i<count($coverlines);$i++){
			$artworks[$coverlines[$i]]=$coverlines[$i+1];
			$i++;
		}
		if (isset( $artworks[html_entity_decode(trim(session_get_contents('../d/nowplayingalbum.txt')))])){
			echo $artworks[html_entity_decode(trim(session_get_contents('../d/nowplayingalbum.txt')))];
		
		}
		else {
		
			echo '../favicon.png';
		}
	}
	exit();
}



$sessionstarted=session_start();

srand();

if (!isset($_SESSION['origin'])){
	$_SESSION['origin']=$_SERVER['HTTP_REFERER'];
	
}



if ($activatestats&&isset($_GET['pingstat'])){
	//if audience figures are activated, let's store the hit details in the stats directory
	
	if ($sessionstarted){
		
		if (!isset($_SESSION['statid'])){
			$_SESSION['statid']=microtime(true);
			$_SESSION['css_color']='rgb('.rand(140, 255).','.rand(140, 255).','.rand(140, 255).')';
			
		}
		
		$page['data']['agent']=$_SERVER['HTTP_USER_AGENT'];
		$variable='agent';
		
		if (!(strstr($page['data'][$variable],'bot')||
		strstr($page['data'][$variable],'Yahoo! Slurp')||
		strstr($page['data'][$variable],'+http://')||
		strstr($page['data'][$variable],'+https://')||
		strstr($page['data'][$variable],'()'))) {
		//may be an human, we store it
			$figure['userid']=$_SESSION['statid'];
			$figure['css_color']=$_SESSION['css_color'];
			$figure['page']='/?random=random';
			$figure['referer']=$_SERVER['HTTP_REFERER'].'/?random=random';
			$figure['random']=$_SESSION['random'];
			$figure['origin']=$_SESSION['origin'];
			session_put_contents('../admin/d/stats/'.microtime(true).'.dat', serialize($figure));
		}
		
		
		
	}
		
		
	exit();
}



function loginpanel($activateaccountcreation){
	if (!$activateaccountcreation) {

		return;
	}
/*	
	
	if (isset($_SESSION['logged'])&&$_SESSION['logged']) {
		
		
	}	
	
	
	else if (!isset($_GET['login'])&&!isset($_GET['createaccount'])&&!isset($_POST['validateemail'])){
//		echo '<a href="./?login=login">Login</a> or <a href="./?createaccount=createaccount">Create account</a>';
*/ else if (!isset ($_POST['validateemail'])){


		echo '<form id="orderform" style="display:inline;" method="POST" action="./"><a href="#" onclick="document.getElementById(\'friends\').style.display=\'inline\';">Let\'s make friends ! </a><span id="friends" style="display:none;"><input type="text" name="validateemail" value="your email address" onfocus="if (this.value==\'your email address\'){this.value=\'\';}"/><input type="submit"/></span></form>';
		 	
	}
	else if (isset($_GET['createaccount'])) {
		echo 'Please enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
		echo '<form id="orderform" style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
	}
	else if (isset ($_POST['validateemail'])&&session_exists('../d/mailing-list-owner.txt')) {
		
		$_POST['validateemail']=explode("\n",$_POST['validateemail'])[0];
		$_POST['validateemail']=trim($_POST['validateemail']);
		$message ='<html><body>Hello<br/>';
		
		$message.="\r\n".'Someone requested mailing list ';
		$message.="\r\n".'subscription using the email address <br/>'.htmlentities($_POST['validateemail']);
		$message.="\r\n".'</body></html>';
		$message=chunk_split($message);
	
		if (
	
		mail(trim(session_get_contents('../d/mailing-list-owner.txt')), 'Mailing list subscription request', $message, 'Content-Type: text/html;charset=UTF-8')
		
		){
		
			echo 'A subscription request has been sent for the address '.htmlspecialchars($_POST['validateemail']).'. We will get in touch shortly to confirm. <a href="./">Close</a>';
			
		}
		else {
			echo 'The system has not been able to subscribe '.htmlspecialchars($_POST['validateemail']).'. Please <a href="">try again</a> later';
			
			
		}
	}
	
}


?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="../<?php echo $favicon;?>" />
<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($sitename); ?> - Random player</title>
<meta name="description" content="<?php echo htmlspecialchars($description); ?>" />

<style>
	
	@media screen and (max-width:800px) {

	
		.damnMobilesWhoUsedToSupportControlsFromTheBeginningOfAudioThenSomeStoppedSupportingForEIGHTYearsBeforeReintroducingThem {
			
				display:block;
				font-size:72%;
		}
	
	}
	@media screen and (min-width:801px) {

	
		.damnMobilesWhoUsedToSupportControlsFromTheBeginningOfAudioThenSomeStoppedSupportingForEIGHTYearsBeforeReintroducingThem {
			
				display:none;
		}
	
	}
	


</style>
</head>
<body>
	<?php
	if ($acceptdonations){
		echo '<span style="float:right;text-align:right">';
		
		include ('../donate.php');
		
		echo '</span><br style="clear:both;float:none;"/>';
		
	}
	?>
<a name="menu"></a><div id="mainmenu" style="display:block;">	
	<span style=""><img style="float:left;width:3%;" src="../favicon.png"/></span>
		
	<h1 id="title" style="display:inline;"><?php echo $sitename; ?></h1>
	<h2 style="clear:both;"><em><?php echo htmlspecialchars($description);?></em> <br/><a href="../">Home</a> &gt; Great Random Player</h2>
</div>
<!--<div><a href="#menu" onclick="mainmenu=document.getElementById('mainmenu');if(mainmenu.style.display=='none'){mainmenu.style.display='inline';this.innerHTML='&lt;';}else{mainmenu.style.display='none';this.innerHTML='☰<?php echo str_replace("'", "\\'", htmlspecialchars($title));?>';}">☰<?php echo strip_tags($sitename);?></a></div>-->

<span id="loginpanel" style="float:right;text-align:right;margin-bottom:2%;">
	<?php
		loginpanel($activateaccountcreation);
	?>

</span><br style="clear:both;"/>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

	
var cover='';
var start;
var blockInterval;
var coverInterval;
var blockLock=false;
var coverLock=false;
var syncLock=false;
var allowGentleResync=false;
var xbhttp = [];
var xchttp = [];
var xci=0;
var xbi=0;

var oldsrc='';
var oldartist='';
var oldtitle='';
var oldalbum='';

var arr_timeouts = [] ; 

var justzapped = false;
var justended = true;
function clearTimeouts(){
	for (i=0;i<arr_timeouts.lenght;i++){
		window.clearTimeout(arr_timeouts[i]);
	}
	
} 
function nanopause(){
	playa=document.getElementById('player');
	if (!playa.paused){
		playa.pause();
		window.setTimeout(playa.play(), 25);
	}
}
function resync() {
	
			if (!syncLock<?php
			
			if ($RadioHasGentleResync&&!$IsRadioResyncing){
			
				echo ('&&allowGentleResync');
			}
			?>){
			  syncLock=true;
			  
			  var d = new Date();
			  start = d.getTime()/1000;
				
			  var xhttp = new XMLHttpRequest();
			  xhttp.onreadystatechange = function(){
				  if (xhttp.readyState==4) {
						syncLock=false;
						
						if (xhttp.status==200) {
							var d = new Date ();
							var stop = d.getTime()/1000;
							
							if (true){//&&abs(document.getElementById('player').currentTime-(parseFloat(xhttp.responseText) + (stop-start)))<10) {
								//this if statement was to avoid the restart of a track from the beginning when sync replied just after a track change
							
										document.getElementById('player').currentTime = parseFloat (xhttp.responseText) + (stop-start) ;
										document.getElementById('resync').innerHTML="R: "+(document.getElementById('player').currentTime-(parseFloat (xhttp.responseText) + (stop-start))) ;
								}
						
							}
					
						<?php
							if ($RadioHasGentleResync&&!$IsRadioResyncing){
									echo 'else {syncLock=false;resync();}';
									
							}
							
							
							
							
							
							?>
					
					
					
					}
				  
				  };
			  xhttp.open("GET", "./currentplaytime.php?current="+encodeURI(document.getElementById('player').currentTime)+"&start="+encodeURI(start), true);
			  xhttp.send();
			}
		}

function refreshBlock() {
		  clearTimeouts();	
		  for (i=0;i<xbhttp.length;i++){
				if (xbhttp[i]!=null){xbhttp[i].abort();}
	      }
	      xbi=xbhttp.length;
		  xbhttp[xbi] = new XMLHttpRequest();
		  
		  xbhttp[xbi].onreadystatechange = function(){
			  
			  if (xbhttp[xbi].readyState==4 && xbhttp[xbi].status==200) {
					blockLock=false;
					document.getElementById('block').innerHTML = xbhttp[xbi].responseText;
					
					artist=document.getElementById('artist');
					title=document.getElementById('title');
					album=document.getElementById('album');
					src=document.getElementById('player').src;
					
					if (album!=null&&title!=null&&artist!=null&&!justended){
						if (!justended&&((album.innerHTML!=oldalbum||title.innerHTML!=oldtitle||artist.innerHTML!=oldartist)&&(oldsrc!='')&&(src!=oldsrc&&!(oldartist==''&&oldalbum==''&&oldtitle=='')))){//||(src==oldsrc&&(album.innerHTML!=oldalbum||title.innerHTML!=oldtitle||artist.innerHTML!=oldartist))){ //&&src==oldsrc
							oldalbum=album.innerHTML;
							oldtitle=title.innerHTML;
							oldartist=artist.innerHTML;
							if (!justzapped){
							document.getElementById('player').pause();
							document.getElementById('player').src='./stream.mp3/index.php?web=web&'+Math.random();
							document.getElementById('player').load();
							oldsrc=document.getElementById('player').src;
							document.getElementById('player').play();
							}
							else{
								oldsrc=document.getElementById('player').src;
							
								justzapped=false;
							}
						}
						else {
							justended=false;
							justzapped=false;
						}
						
						
					}
					else{
						justended=false;
						justzapped=false;
					}
				}
			  /*else if (xbhttp.readyState == 4){
					refreshBlock();
			  }*/
			  };
		  //xbhttp.timeout=25000;
		  //xbhttp.ontimeout=function(){refreshBlock();};
		  xbhttp[xbi].open("GET", "./?ajax=block", true);
		  
		  blockLock=true;
		  xbhttp[xbi].send();
		  arr_timeouts[arr_timeouts.length] = window.setTimeout(function(){		  for (i=0;i<xbhttp.length;i++){
											if (xbhttp[i]!=null){xbhttp[i].abort();}
									  }
							refreshBlock();}, 30000); 
}
function refreshCover(){
		  for (i=0;i<xchttp.length;i++){
				if (xchttp[i]!=null){xchttp[i].abort();}
	      }
	      xci=xchttp.length;
		  xchttp[xci] = new XMLHttpRequest();
		  
		
		
		  xchttp[xci] = new XMLHttpRequest();
		  xchttp[xci].onreadystatechange = function(){
			  if (xchttp[xci].readyState==4 && xchttp[xci].status==200) {
					coverLock=false;
					document.getElementById('cover').src = "../covers/"+xchttp[xci].responseText;
				}
				/*else if (xchttp.readyState == 4){
					refreshCover();
				}*/
			  };
		  //xchttp.timeout=55000;
		  //xchttp.ontimeout=function(){refreshCover();};
		  xchttp[xci].open("GET", "./?ajax=cover", true);
		  xchttp[xci].send();
		  arr_timeouts[arr_timeouts.length] = window.setTimeout(function(){for (i=0;i<xchttp.length;i++){
											if (xchttp[i]!=null){xchttp[i].abort();}
									  }
									  refreshCover();}, 60000);

	
}
function onPlayLaunch(){
if (xchttp[xci]!=null){xchttp[xci].abort();}
if (xbhttp[xbi]!=null){xbhttp[xbi].abort();}

arr_timeouts[arr_timeouts.length] = window.setTimeout(refreshCover, 6500);
arr_timeouts[arr_timeouts.length] = window.setTimeout(refreshBlock, 5000);
//window.setInterval(nanopause, 30000);
}
<?php if ($IsRadioResyncing) { ?>
window.setInterval(resync, <?php echo $RadioResyncInterval; ?>);
<?php   } ?>

// @license-end
</script>
<!--Stream : <a href="?m3u=m3u">m3u</a> <a href="./stream.mp3">mp3</a>--><br/>

<img style="float:left;width:25%;" id="cover"/>
<span style="float:left;">
<div>Now Playing</div>
<div id="block" style="padding-left:4%;"></div>
<br style="clear:both;float:none;"/>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

<?php
if (true){
?>

function skipsong(auto) {
	
	if (document.getElementById('skip')!=null){
		document.getElementById('skip').innerHTML= 'Skipping song, please be patient...';
	}
				<?php
				if (!isset($_SESSION['nick'])){
					?>
		
				if (document.getElementById('social')!=null){
		
					document.getElementById('social').data="../network/?login=login";
				}	
					<?php
					
				}
				?>
	
	var xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function(){
		  if (xhttp.readyState==4) {
				if (xhttp.status==200){
				document.getElementById('player').src='./stream.mp3/index.php?web=web&'+Math.random();oldsrc=document.getElementById('player').src;document.getElementById('player').play();
				
				if (document.getElementById('skip')!=null){
					document.getElementById('skip').innerHTML= 'Skip this song';
				}
					document.getElementById('player').pause();
					document.getElementById('player').src='./stream.mp3/index.php?web=web&'+Math.random();
					document.getElementById('player').load();
					oldsrc=document.getElementById('player').src;
					justzapped=true;
					document.getElementById('player').play();
							
						
					}
				//	location.reload();
				
				else {
					skipsong();
				}
			}

		  
		  };
	  arg='';
	  if (auto){arg='?auto=auto';}
	  xhttp.open("GET", "skipsong.php"+arg, true);
	  xhttp.send();
	  

}
<?php
}
?>
function cr_rad(){
	window.setTimeout(function(){document.getElementById('player').src='./stream.mp3/index.php?web=web&'+Math.random();oldsrc=document.getElementById('player').src;document.getElementById('player').play();}, 7500);
	
}
function launchPlay(playa){
	window.clearInterval(blockInterval);window.clearInterval(coverInterval);playa.play();allowGentleResync=true;
}

// @license-end
</script>
<div style="text-align:left;"><audio id="player" src="" preload="none" controls="controls" 
 onEnded="this.src='./stream.mp3/index.php?web=web&'+Math.random();clearTimeouts();oldsrc=this.src;justended=true;justzapped=true;this.play();" 
 onError="cr_rad();" 
 onCanPlay="onPlayLaunch();"
 <?php
							if ($RadioHasGentleResync&&!$IsRadioResyncing){
									echo ' onPlay="resync();allowGentleResync=false;" ';
									
							}
 ?>
 ></audio>


 
 <!--Here comes The SPECIALS-->
 <div class="damnMobilesWhoUsedToSupportControlsFromTheBeginningOfAudioThenSomeStoppedSupportingForEIGHTYearsBeforeReintroducingThem">
							<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later



							
							function togglePlay(){
								
								var audio=document.getElementById('player');
								if (!audio.paused){
									
									audio.pause();
									return;
								}
								if(audio.networkState==0){audio.load();}
								audio.play();
								audio.autoplay=true;	 
								
								
							}
							
// @license-end
</script>
						If you don't see Controls, update your mobile browser app if you can. In the meanwhile you can <a href="javacript:void(0);" onClick="togglePlay();">tap here</a> to start or pause sound. 
</div>
<!--SPECIAL 2014->2021-->
 


<span id="resync" style="float:right;"></span></div>	
<?php
if (true){
?>
		<a href="javascript:void(0);" id="skip" onclick="skipsong(false);">Zap this song!</a>
<?php
}
?>
</span>

<hr style="float:none;clear:both;">
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

document.getElementById('player').src='./stream.mp3/index.php?web=web&'+Math.random();
oldsrc=document.getElementById('player').src;

var radiomsg='';

var radioajax;

radioajax = new XMLHttpRequest;
radioajax.onreadystatechange = radionymous;
//radioajax.open("GET", "../radio_data.php", true);
//radioajax.send();
		
	

function radionymous() {
				if (xbhttp[xbi]!=null){return;}
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
		document.getElementById('block').innerHTML=radiomsg;

		}
//window.setInterval(function(){blockLock=false;}, 30000);
//window.setInterval(function(){coverLock=false;}, 60000);
//document.getElementById('player').load();
//document.getElementById('player').autoplay='autoplay';
//document.getElementById('player').play();


// @license-end
</script>
<?php
if (!$activatechat===false){
?>
		<a name="social"/><object id="social" data="../network/index.php" style="	width:100%;height:495px;" width="100%" height="495"></object>
<?php
}


if ($activatestats){

?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later


    var xhttp = new XMLHttpRequest();
	  xhttp.open("GET", "./?pingstat=true", true);
	  xhttp.send();


// @license-end
</script>

<?php
}
?>

<div style="float:rigth;font-size:76%;">Powered by <a href="http://crero.clewn.org" title="CreRo, the open-source CMS for record labels and webradios">CreRo, the CMS for record labels and webradios</a> - AGPL licensed - <a href="http://github.com/shangril/crero">code repo</a></div>
</body>
</html>
