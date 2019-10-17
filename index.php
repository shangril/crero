<?php
require_once('./config.php');
//error_reporting(E_WARNING|E_NOTICE);

require_once('./crero-lib.php');
//error_reporting(E_WARNING|E_NOTICE|E_ERROR|E_PARSE);

function checkOverload ($apiResponse) {
	if ($apiResponse===false){
	echo '<br/>Oooops... We are currently over capacity. Please try again later<br/></body></html>';
	die();
	}
	
}

ob_start();

$sessionstarted=session_start();

if (!isset($_SESSION['origin'])){
	$_SESSION['origin']=$_SERVER['HTTP_REFERER'];
	
}

srand();

if (isset($_SESSION['random'])&&$_SESSION['random']){
	$_GET['twist']='random';//necessary if cache enabled
	
}




$willhavetocache=false;

$cachingkey='key:';

$get_keys=array_keys($_GET);

$whitelist= array ('artist', 'album', 'track', 'offset', 'listall', 'autoplay', 'vid', 'twist');

foreach ($get_keys as $get_key){
	if (in_array($get_key, $whitelist)){
		$cachingkey.=$get_key.':'.$_GET[$get_key];
		}
}
$myhtmlcache=new creroHtmlCache($htmlcacheexpires);

if (isset($_POST['page_purge'])&&$activatehtmlcache){
	$pseudoget=unserialize(base64_decode($_POST['page_purge']));
	
	$cachingkey='key:';

	$get_keys=array_keys($pseudoget);

	foreach ($get_keys as $get_key){
		$cachingkey.=$get_key.':'.$pseudoget[$get_key];
	
	}

	
	$myhtmlcache->purgePage($cachingkey);
	echo '<html><head><title>Cache page purging</title></head><body>The page was purged from the cache.<br/>
	
	
	<a href="./?';
	$get_keys=array_keys($pseudoget);
	foreach ($get_keys as $get_key){
		echo urlencode($get_key).'='.urlencode($pseudoget[$get_key]).'&';
	
	}
	
	echo '">Continue</a></body></html>';
	exit();

}



$myhtmlcache=new creroHtmlCache($htmlcacheexpires);
if (isset($_GET['purge'])){
	$myhtmlcache->purgeCache();
	echo '<html><body>Cache purged. <a href="./">Proceed</a></body></html>';
	exit();

}


if (isset($_GET['getinfo'])){
	echo file_get_contents($clewnapiurl.'?getinfo='.urlencode($_GET['getinfo']));
	exit();
}



//* caching of htmlpage ; here we are


if ($activatehtmlcache&&!isset($_POST['validateemail'])&&!isset($_GET['pingstat'])){

	
	if ($myhtmlcache->hasPageExpired($cachingkey)){
		$willhavetocache=true;
		
	}
	else {
		echo $myhtmlcache->getCachedPage($cachingkey);
		exit();
	}


}

//caching of html page ; almost done. Just cache the output buffer once the page is fully generated. See end of this file

$album_scores=Array();
if ($activatestats&&false)
{
//The scoring system for each album is no longer used due to refactoring of the stats module to make it usable with htmlcache

	$raw_scores=Array();
	$scoredats=array_diff(scandir ('./admin/d/stats/'), Array ('.', '..', '.htaccess'));
	foreach ($scoredats as $score_dat){
			$thisscoredat=unserialize(file_get_contents('./admin/d/stats/'.$score_dat));
			if (strstr($thisscoredat['page'], '?')){
				$scoretokens=explode('?', $thisscoredat['page']);
				$scoreuri=$scoretokens[1];
				$scoregets=explode('&', $scoreuri);
				foreach ($scoregets as $scoreget){
					$scorepair=explode('=', $scoreget);
					for ($dex=0;$dex<count($scorepair);$dex++){
						if ($scorepair[$dex]==='album'){
							if (!isset($raw_scores[urldecode($scorepair[$dex+1])])){
								$raw_scores[urldecode($scorepair[$dex+1])]=1;
							}
							else {
								$raw_scores[urldecode($scorepair[$dex+1])]++;
							}
						}
						$dex++;
					}
				}
			}
	}
	
	arsort($raw_scores);
	$albums_scored=array_keys($raw_scores);
	$maxscore=$raw_scores[$albums_scored[0]];
	$multiplicant=floatval($maxscore/155);
	
	foreach ($albums_scored as $album_scored){
		$album_scores[$album_scored]=floatval($raw_scores[$album_scored]*$multiplicant);
		
	}
	//var_dump($raw_scores);
	//var_dump($album_scores);
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
			$figure['page']=$_SERVER['REQUEST_URI'];
			$figure['referer']=$_SERVER['HTTP_REFERER'];
			$figure['random']=$_SESSION['random'];
			$figure['origin']=$_SESSION['origin'];
			file_put_contents('./admin/d/stats/'.microtime(true).'.dat', serialize($figure));
		}
		
		
	}
		
		
	die();
}
		


$mosaic=false;
if (count($_GET)==0||isset($_GET['message'])){
	$mosaic=true;
	$_GET['listall']='albums';
	
	
	//some stuff when displaying the homepage : pinging the yp servers
	foreach ($creroypservices as $ypservice){
		file_get_contents(trim($ypservice).'?url='.urlencode('http://'.$server).'&name='.urlencode($sitename).'&description='.urlencode($description));
	}
}



if (isset($_GET['random'])&&$_GET['random']=='true'&&!isset($_GET['artist'])){
	$_SESSION['random']=true;
	
}
else if (isset($_GET['random'])&&$_GET['random']=='false'){
	$_SESSION['random']=false;
	
}
else if (isset($_GET['artist'])||isset($_GET['listall'])){
	$_SESSION['random']=false;
	
	
}

error_reporting(E_ERROR | E_PARSE);

if (isset($_GET['artist'])) {
	$favicon='http://'.$server.'/favicon.png';
	$arturl='&artist='.urlencode($_GET['artist']);
	$title='<a href="./?artist='.urlencode($_GET['artist']).'">'.htmlspecialchars($_GET['artist']).'</a> - A '.htmlspecialchars($sitename).' artist';
	$artists_file=trim(file_get_contents('./d/artists.txt')); 

	$artists=explode("\n", $artists_file);
	if (!in_array($_GET['artist'], $artists)&&(file_exists('./d/artists.txt')&&count($artists)>0)) {
		echo 'ooops... Invalid artist !';
		exit();
	}

}
else {
	$favicon='/favicon.png';
	$arturl='';
}

if (!isset($_GET['artist'])){

	$artists_file=trim(file_get_contents('./d/artists.txt')); 

	$artists=explode("\n", $artists_file);
	
	
		if (count($artists)==0)
		{
			$artists=explode("\n", trim(file_get_contents($clewnapiurl.'?listartists=true')));
			
			
		}




}
else {
	$artists=Array($_GET['artist']);
}

$material_artists_file=htmlentities(trim(file_get_contents('./d/material_artists.txt')));
	
$material_artists=explode("\n", $material_artists_file);
	
$material_blacklist_file=htmlentities(trim(file_get_contents('./d/material_blacklist.txt')));
	
$material_blacklist=explode("\n", $material_blacklist_file);

if (isset($_GET['listall'])&&($_GET['listall']==='material'||($_GET['listall']==='mixed'&&isset($_GET['album'])))) {
	$artists=$material_artists;
	$material_currency=trim(file_get_contents('./d/material_currency.txt'));
	$material_paypal_address=trim(file_get_contents('./d/material_paypal_address.txt'));
	$material_shipping_file=trim(file_get_contents('./d/material_shipping.txt'));
	$material_shippings=explode("\n", $material_shipping_file);
	
	$material_shipping=Array();
	$count=count($material_shippings);
	$i=0;
	while ($i < $count){
		$material_shipping[$material_shippings[$i]]=$material_shippings[$i+1];
		$i++;
		$i++;
	}
	$material_supports_file=trim(file_get_contents('./d/material_supports_and_prices.txt'));
	$material_supports=explode("\n", $material_supports_file);
	
	$material_support=Array();
	$i=0;
	$count=count($material_supports);
	while ($i<$count){

		$material_support[$material_supports[$i]]['description']=$material_supports[$i+1];
		$material_support[$material_supports[$i]]['price']=$material_supports[$i+2];
		$material_support[$material_supports[$i]]['options']=$material_supports[$i+3];
		$i++;
		$i++;
		$i++;
		$i++;
	}
	
}

function featuredvids(){


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


		echo '<form id="orderform" style="display:inline;" method="POST" action="./"><a href="javascript:void(0);" onclick="document.getElementById(\'friends\').style.display=\'inline\';">Let\'s make friends ! </a><span id="friends" style="display:none;"><input type="text" name="validateemail" value="your email address" onfocus="if (this.value==\'your email address\'){this.value=\'\';}"/><input type="submit"/></span></form>';
		 	
	}
	else if (isset($_GET['createaccount'])) {
		echo 'Please enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
		echo '<form id="orderform" style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
	}
	else if (isset ($_POST['validateemail'])&&file_exists('./d/mailing-list-owner.txt')) {
		
		$_POST['validateemail']=explode("\n",$_POST['validateemail'])[0];
		$_POST['validateemail']=trim($_POST['validateemail']);
		$message ='<html><body>Hello<br/>';
		
		$message.="\r\n".'Someone requested mailing list ';
		$message.="\r\n".'subscription using the email address <br/>'.htmlentities($_POST['validateemail']);
		$message.="\r\n".'</body></html>';
		$message=chunk_split($message);
	
		if (
	
		mail(trim(file_get_contents('./d/mailing-list-owner.txt')), 'Mailing list subscription request', $message, 'Content-Type: text/html;charset=UTF-8')
		
		){
		
			echo 'A subscription request has been sent for the address '.htmlspecialchars($_POST['validateemail']).'. We will get in touch shortly to confirm. <a href="./">Close</a>';
			
		}
		else {
			echo 'The system has not been able to subscribe '.htmlspecialchars($_POST['validateemail']).'. Please <a href="">try again</a> later';
			
			
		}
	}
	
}
function generatevideo($track_name, $album, $track_artist, $videoapiurl, $videourl) {
	//let's see if there is a video available
	
					
	
	
					$videotarget=trim(file_get_contents($videoapiurl.'?artist='.urlencode($track_artist).'&album='.urlencode($album).'&title='.urlencode($track_name).'&gettarget=y'));
					
					
					$hasvideo=false;
					$haslyrics=false;
					if ($videotarget!==''){
						$hasvideo=true;
						
					}
					
					
					//if has video and GET vid not set, dislplay video display link
					//if has lyrics display lyrics link
					if (($hasvideo || $haslyrics)){
						echo '<div style="background-color:#FAFAFA;text-align:center;">';
						if ($hasvideo&&!isset($_GET['vid'])){
							
							echo '<a href="./?vid=play&track='.urlencode($track_name).'&album='.urlencode($album).'&artist='.urlencode($track_artist).'">Video available</a>';
						}
						
						echo '</div>';
						
					}
					if ($hasvideo&&isset($_GET['vid'])&&isset($_GET['artist'])&&isset($_GET['album'])&&isset($_GET['track'])){
						echo '<div style="background-color:#FAFAFA">';
						$videoformatsfile=trim(file_get_contents($videoapiurl.'?listformats='.urlencode($videotarget)));
						$videoformats=explode("\n",$videoformatsfile);
						$videotargetfiles=Array();
						foreach ($videoformats as $videoformat){
							$downgradefile=trim(file_get_contents($videoapiurl.'?hasdowngrade='.$videotarget.'&format='.$videoformat));
							$downgradelist=explode("\n", $downgradefile);
							sort($downgradelist);
							if (count($downgradelist)>0) {
								$videotargetfiles[$videoformat]=$downgradelist[0].'.'.$videotarget.'.'.$videoformat;
							}
							else {
								$videotargetfiles[$videoformat]=$videotarget.'.'.$videoformat;
							}
						
						}
						echo '<video controls="controls" autoplay="autoplay" style="width:100%;">';
						foreach($videoformats as $videoformat) {
							echo '<source src="'.htmlspecialchars($videourl.urlencode($videotarget).'.'.urlencode($videoformat)).'" mime-type="'.htmlspecialchars(mime_content_type($videotarget.'.'.$videoformat)).'"></source>';
							
							
						}
						echo 'Your browser is very old and does not support HTML5 video, sorry</video>';
						echo '<br/>';
						echo 'Download : ';
						foreach ($videoformats as $videoformat) {
							echo '<a href="'.$videourl.urlencode($videotarget).'.'.urlencode($videoformat).'">'.htmlspecialchars($videoformat).'</a> ';
							
						}
						echo '<br/>';
						echo htmlspecialchars(trim(file_get_contents($videourl.$videotarget.'.description.txt')));
						echo '</div>';
						
					}
					
}
function showsongsheet($track) {
	if (file_exists('./songbook/'.$track.'.txt')){
		echo '<div style="background-color:#A8A8A8;"><a href="#'.htmlspecialchars($track.'.txt').'" onclick="document.getElementById(\''.htmlspecialchars($track.'.txt').'\').style=\'display:block;\'">Lyrics/chords</a></div>';
		echo '<a name="'.htmlspecialchars($track.'.txt').'"><div style="font-family:monospace;display:none;background-color:#DFDFDF;" id="'.htmlspecialchars($track.'.txt').'">'.str_replace("\n", '<br/>', htmlspecialchars(file_get_contents('./songbook/'.$track.'.txt'))).'</div></a>';	
			
	}
	
}
function displaycover($album, $ratio, $param='cover'){
	if (file_exists('./d/covers.txt')){
		$coversfile=trim(file_get_contents('./d/covers.txt'));
		$coverslines=explode("\n", $coversfile); 
		
		$i=0;
		$url=null;
		while ($i<count($coverslines)){
			if ($coverslines[$i]===html_entity_decode($album)){
				$url=$coverslines[$i+1];
				
			}
			$i++;
			$i++;
		}
		if (isset($url)){
			$output='';
			$output.='<img class="lineTranslate" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';
		
			$output.='<script>;
			var size;
			if (document.documentElement.clientWidth>=document.documentElement.clientHeight){
				size=document.documentElement.clientHeight;
			}
			else
			{
				size=document.documentElement.clientWidth;
			}				 ';
			$output.='document.getElementById('."'".$param.'_'.str_replace("'","\\'",$album)."'".').src='."'".'./thumbnailer.php?target='."'".'+encodeURI('."'".str_replace("'","\\'",'./covers/'.$url)."'".')+'."'".'&viewportwidth='."'".'+encodeURI(size)+'."'".'&ratio='."'".'+encodeURI('."'".str_replace("'","\\'",$ratio)."'".');';
							 
			$output.='</script>';
			
			return $output;
		}
		else {
			return '';
		}
	
	
	
	}
	else{
		return '';
	}	
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($title); 

if (isset($_GET['artist'])){
	echo ' - '.htmlspecialchars($_GET['artist']);
}

if (isset($_GET['album'])){
	echo ' - '.htmlspecialchars($_GET['album']);
}
if (isset($_GET['track'])){
	echo ' - '.htmlspecialchars($_GET['track']);
}

?></title>
<meta name="description" content="<?php echo htmlspecialchars($description); ?>" />
<?php 
if ($mosaic&&$artisthighlighthomepage)
{
//if the highlight artist is on, we got to change the css
?>
	<style>
	body {
		margin-left:0%;
		margin-right:0%;
		padding-left:0%;
		padding-right:0%;
		}
	
	</style>
	
<?php }?> 
<script src="http://<?php echo $server;?>/script.js">
</script>
<script>
<?php 

echo "var target_album='".urlencode(htmlentities($_GET['target_album']))."';
";




	if ($enableDownloadCart) {?>
function addFullAlbumToCart(album_cart)
		{
		 var xhttpcartalb = new XMLHttpRequest();
		  xhttpcartalb.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 document.getElementById("ajax_splash").style.display="block";
			 document.getElementById("ajax_splash_message").innerHTML = this.responseText;
			 update_cart();
			}
		  };
		  xhttpcartalb.open("GET", "add_album_to_cart.php?album="+album_cart, true);
		  xhttpcartalb.send();

		}
function addTrackToCart(track_title, track_album, track_basefile, track_artist)
		{
		 var xhttpcarttrk = new XMLHttpRequest();
		  xhttpcarttrk.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 document.getElementById("ajax_splash").style.display="block";
			 document.getElementById("ajax_splash_message").innerHTML = this.responseText;
			 update_cart();
			}
		  };
		  xhttpcarttrk.open("GET", "add_track_to_cart.php?track_title="+track_title+"&track_basefile="+track_basefile+"&track_album="+track_album+"&track_artist="+track_artist, true);
		  xhttpcarttrk.send();

		}

<?php }?>
</script>
<?php if ($activatestats) {?>


<script>
	  
function stats(){
	  
	  var xhttp = new XMLHttpRequest();
	  xhttp.open("GET", "./?pingstat=true", true);
	  xhttp.send();

}
</script>
<?php } else { ?>
<script>
	function stats(){
		return;
	}
</script>
<?php }



 ?>
<style>
</style>
</head>
<body 

<?php if ($activatestats){?>
onLoad="stats();"
<?php }?>

>
<!--overcapacity splash-->
<div id="overload_splash" style="z-index:128;position:absolute; top:0; left:0;width:100%;height:100%;margin-bottom:100%;display:none;background-color:white;">
	<span id="overload_splash_message"></span>
	<h1 style="text-align:center;">Sorry, something went wrong</h1>
	<span style="text-align:center;">
	It seems that this version of this page as stored in the site's cache has been generated while the media storage tier was suffering an overload.<br/>As a consequence, the tracklisting for this page is empty. 
	<br/>
	You can try to clear the cache for this particular page and see if it can solve this error. 
	<form id="overload_form" method="POST">
	<input type="hidden" name="page_purge" value="<?php echo base64_encode(serialize($_GET));?>"/>
	<span id="overload_button">
	</span>
	</form>
	</span>
	<br/><a href="javascript:void(0);" style="margin-bottom:100%;text-align:right;width:100%;" onclick="document.getElementById('overload_splash').style.display='none';">X Close</a>
</div>

<script>
var overload_track_counter=0;
</script>



<!-- volume controler code -->
<span style="position: fixed; left:0px; top:0px; font-size:140%; border:solid 1px; border-radius:5px">
<a href="javascript:void(0);" style="border:solid 1px; border-radius:3px;background-color:#18FF18;" onClick="document.getElementById('player').volume=document.getElementById('player').volume-0.1;">-</a>
<span style="font-size:108%;background-color:black;">🔈</span>
<a href="javascript:void(0);" style="border:solid 1px; border-radius:3px;;background-color:#18FF18;" onClick="document.getElementById('player').volume=document.getElementById('player').volume+0.1;">+</a>
<!--end volume controler code -->

</span>	
	<?php
	if ($enableDownloadCart)
	{
	?>
	<script>
	function update_cart(){
		
	 xhttpcart = new XMLHttpRequest();
	  xhttpcart.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
		 document.getElementById("dlcartcounter").innerHTML = this.responseText;
		}
	  };
	  xhttpcart.open("GET", "count_cart_items.php", true);
	  xhttpcart.send();
		
	
	
	}
	update_cart();
	</script>
	<div style="width:100%;text-align:right">In your download cart: <a href="./view_cart.php"><span id="dlcartcounter"></span> items</a></div>
	<?php
	}
	?>
	<?php
	if ($activatestats){

?>
<script>

	ajaxstats();
</script>

<?php
}


 ?>
	
	
	
	
	
<?php

if (file_exists('./splash.php')){
	include ('./splash.php');
}

if (isset ($_GET['message'])&&isset($message[$_GET['message']])){
	
	echo '<div style="color:red;background-color:black;width:100%;text-align:center;"><span><strong>'.$message[$_GET['message']].'</strong><a href="./" style="color:black;background-color:red;text-decoration:underline;float:right;text-align:right;">X</a></span></div>';
	
}

if (count($socialmediaicons)>0){
	//let's display the social media icons
	echo '<span style="float:left;">';
	
	foreach ($socialmediaicons as $socialicon){
		echo '<strong><a target="new" href="'.$socialicon['link'].'" style="color:'.$socialicon['color'].';background-color:'.$socialicon['background-color'].';border-radius:3px;">';
		
		echo htmlspecialchars($socialicon['letter']);
		
		echo '</a></strong> ';
		
		
	}
	echo '</span>';
}

if ($acceptdonations){
	echo '<span style="float:right;text-align:right">';
	
	include ('./donate.php');
	
	echo '</span><br style="clear:both;float:none;"/>';
	
}
	

if ($hasradio){
	echo '<div style="width:100%;text-align:right">Now on the radio : <a href="./radio">';
	
	echo file_get_contents('./radio/d/nowplayingartist.txt');
	
	echo ' - ';
	
	echo file_get_contents('./radio/d/nowplayingalbum.txt');
	
	echo '</a>';
	
	
	echo '</div>';
	
}
?>
<audio id="player" onEnded="playNext();">
	Your browser is very old ; sorry but streaming will not be enabled<br/>
</audio>
<noscript>Your browser does not support Javascript, which is required on this website if you want to stream. Don't panic, we dont include any kind of third party scripts<br/></noscript>
<div style="text-align:center;"><a href="javascript:void(0);" onclick="document.getElementById('recentplay').style.display='block';"></a></div>
<span id="recentplay" style="display:<?php
if ($recentplay){
	echo 'block';
	
}
else {
	echo 'none';
	
}
?>;width:100%;text-align:center;"><hr/>Recently played: <br/>
<?php

$recents=Array();
if (file_exists('./d/recent.dat')){
	$recents=unserialize(file_get_contents('./d/recent.dat'));
	
}
foreach ($recents as $recent){
	echo '<span style="width:10%;float:left;margin-left:auto;margin-right:auto;"><a href="./?album='.urlencode($recent['album']).'&autoplay=true">'.displaycover($recent['album'], 0.08, 'mini'.rand(0,1000)).'</a><br/>';
	echo htmlspecialchars(round((time()-intval($recent['date']))/60));
	echo ' mn<br/>';
	//echo ' <a href="#social" style="'.$recent['who']['color'].'">'.htmlspecialchars($recent['who']['nick']).'</a>';
	echo '</span>';
}
?>
<hr/>
</span>
<br style="clear:both;"/>


<?php
if (isset ($_GET['track'])) {
	echo '<a href="./">../</a><br/>';
}

else if (isset ($_GET['artist'])) {
	echo '<a href="http://'.$server.'/">../</a><br/>';
}

?>


<a name="menu"></a><div id="mainmenu" style="display:none;">	
	<span style=""><img style="float:left;width:3%;" src="<?php echo $favicon ;?>"/></span>
		
	<h1 id="title" style="display:inline;"><?php echo $title; ?></h1>
	<?php if (!isset($_GET['listall'])){
		echo '<a id="listalbums" style="float:right;" href="./?listall=albums'.$arturl.'">List all albums</a><br/>';
		
	}
	?>
	<h2 style="clear:both;"><em><?php echo htmlspecialchars($description);?></em> <br/><a href="http://<?php echo $server;?>">Home</a></h2>

	<?php
	//artist list
	if (!isset($_GET['artist'])&&!isset($_GET['track'])&&!isset($_GET['album'])&&!(isset($_GET['listall'])&&$_GET['listall']==='material')){
		$artists_file=file_get_contents('./d/artists.txt'); 
		
		

		$artists=explode("\n", trim($artists_file));


	
	
		if (!file_exists('./d/artists.txt')||count($artists)==0)
		{
			$artists=explode("\n", trim(html_entity_decode(file_get_contents($clewnapiurl.'?listartists=true'))));
			
			
		}

		sort($artists);
		echo '<span style="margin-top:4px;marging-bottom:4px;"><a style="float:left;padding:2px;" href="./">Artists : </a> ';

		foreach ($artists as $artist) {
			echo '<a style="float:left;border:solid 1px;background-color:#A0A0A0;padding:2px;" href="http://'.$server.'/?artist='.urlencode($artist).'"> '.htmlspecialchars($artist).' </a> ';
			
			
		}
		echo '</span><br style="clear:both;"/>';
	}
?>
</div>
<div><a href="#menu" onclick="mainmenu=document.getElementById('mainmenu');if(mainmenu.style.display=='none'){mainmenu.style.display='inline';this.innerHTML='&lt;';}else{mainmenu.style.display='none';this.innerHTML='☰<?php echo str_replace("'", "\\'", htmlspecialchars($title));?>';}">☰<?php echo strip_tags($title);?></a></div>

<span id="loginpanel" style="float:right;text-align:right;margin-bottom:2%;">
	<?php
		loginpanel($activateaccountcreation);
	?>

	</span><br style="clear:both;"/>
	
<?php

if ($mosaic) {
	include ('featuredvids.php');
}

//material releases : listing products

$material=false;
$mixed=false;
if((isset($_GET['listall'])&&($_GET['listall']==='material'||($_GET['listall']==='mixed'&&isset($_GET['album']))))) {
	$material=true;
	
	if ($_GET['listall']==='mixed'){
		$mixed=true;
	}
	if (!$mixed){
			echo '<h1>Material releases</h1><h2>What we offer : </h2>';
			echo 'All prices are indicated in '.htmlspecialchars($material_currency);
			echo '<div><em>This is the '; 
			
			if ($ismaterialnameyourprice){
				echo 'recommended';
			}
			else{
			
				echo 'minimum';
			
			}
			echo ' price. You name you price, actually, and you can pay more';
			
			if ($ismaterialnameyourprice){
				echo ' or less';
		
			}
			echo ' than this if you wish to.</em></div>';
			$material_items=array_keys($material_support);
			echo '<table><tr>';
			echo '<td style="border: solid 1px;"><strong><em>Product</em></strong></td>';
			$material_item_lines=array_keys($material_support[$material_items[0]]);
			foreach ($material_item_lines as $material_item_line) {
						echo '<td style="border: solid 1px;"><strong><em>'.htmlspecialchars($material_item_line).'</em></strong></td>';
				
			}
				
			echo '</tr>';
			
			foreach ($material_items as $material_item)
			 {
				echo '<tr>';
				echo '<td style="border: solid 1px;"><strong>'.htmlspecialchars($material_item).'</strong></td>';
				$material_item_lines=array_keys($material_support[$material_item]);
				foreach ($material_item_lines as $material_item_line) {
					echo '<td style="border: solid 1px;">';
					if (isset($material_support[$material_item][$material_item_line])&&$material_support[$material_item][$material_item_line]!==''){
						echo htmlspecialchars($material_support[$material_item][$material_item_line]);
						}
					echo '</td>';
				}
				echo '</tr>';
					
			}
			echo '</table>';
		}
	echo '<form method="POST" action="processorder.php">';
}
$weactuallydisplayedsomething=false;

//here we are, let's query  apis to fill the content arrays

$supported_formats_remote=explode("\n", file_get_contents($clewnapiurl.'?listformats=true'));
$supported_formats_local=explode("\n", file_get_contents($serverapi.'?listformats=true'));



$content=Array();

$querystring = '';
$artists_file=file_get_contents('./d/artists.txt'); 
		
		

		$artists=explode("\n", trim($artists_file));


	
	
		if (!file_exists('./d/artists.txt')||count($artists)==0)
		{
			$artists=explode("\n", trim(html_entity_decode(file_get_contents($clewnapiurl.'?listartists=true'))));
			
			
		}
		if (isset($_GET['artist']))
		{
			$artists=array($_GET['artist']);
			
		}
		$querystring='';
		foreach ($artists as $artist) 
		{
			$querystring.='l[]='.urlencode(htmlentities($artist)).'&';
			
			
		}
		
		$timeout=18;
		if (file_exists('./overload.dat')){
			$timeout=intval(file_get_contents('./overload.dat'));
		}
		else {
			file_put_contents('./overload.dat', '18');
			
		}
		//$opts = array(
		//	'http'=>array(
		//	'timeout'=>$timeout
		//)
		//);

		$overloadmove=$timeout+2;


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$clewnapiurl.'?l=true');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,$timeout); 
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$albums_file= curl_exec ($ch);

		curl_close ($ch);




		//$albums_file=file_get_contents($clewnapiurl.'?'.$querystring, false, stream_context_create($opts));

		if ($albums_file===false){
			if (!file_exists('./remoteapicache.dat')){
			
				echo '<h2><strong>Sorry ! </strong>It seems that the free albums host, is currently over capacity.</h2>That is why this page took so long to load, and free albums will not display. ';
			
			}
			else {
				$remoteapicache=unserialize(file_get_contents('./remoteapicache.dat'));
				if (isset($remoteapicache[$querystring])){
					$albums_file=$remoteapicache[$querystring];
					
				}
				else {
					
					echo '<h2><strong>Deeply sorry ! </strong>It seems that the host of the free albums, is currently over capacity.</h2>That is why this page took so long to load, and free albums will not display.';
			
				}
			
			}
			$overloadmove=$timeout-2;
		}
		else {
			$cache=Array();
			
			if (file_exists('./remoteapicache.dat')){
				$cache=unserialize(file_get_contents('./remoteapicache.dat'));
			}
			$cache[$querystring]=$albums_file;
			file_put_contents('./remoteapicache.dat', serialize($cache));

		}
		
		
		if ($overloadmove<=0){
			$overloadmove=18;
		}
		
		file_put_contents('./overload.dat', $overloadmove);
			
		$albums=unserialize($albums_file);
		ksort($albums);
		$albums=array_reverse($albums);
		
		$content=$albums;
//let's query the local storage
$querystring = '';
		foreach ($artists as $artist) 
		{
			$querystring.='&listallalbums[]='.urlencode(htmlentities($artist));
			
			
		}



		$albums_file=file_get_contents($serverapi.'?'.$querystring);
		$albums=unserialize($albums_file);
		ksort($albums);
		$albums=array_reverse($albums);
		
		$contentlocal=$albums;



if ($_SESSION['random']){
	$_GET['offset']=rand(0, count($content)-1);
	$_GET['autoplay']='autoplay';
}



?>

<div>

<?php

//here we go, let's output the content
if (isset($_GET['offset'])&&is_numeric($_GET['offset'])) {
	$offset=intval($_GET['offset']);
	
	if ($offset!==0&&!$_SESSION['random']&&!isset($_GET['listall'])){
	
		echo '<a href="./?offset='.($offset-1).$arturl.'">Dig newer</a><br/>';
	
	}

}
else {
	$offset=0;
	
}
echo '<script>var offset='.$offset.'</script>';
if ($_SESSION['random']){
	echo '<a href="./?random=random">Skip this album</a>';
	
}
$counter=1;
$secondcounter=0;
if ($_SESSION['random']&&!isset($_GET['artist'])){
		echo ' <a style="clear:both;" href="./?random=false">Stop random</a>';

}	
else if (!isset($_GET['artist'])&&!$material)
{
	//echo '<a style="clear:both;" href="./?random=true">random play</a>'; //Regression bug, random not working anymore, has to be disabled
}


if ($mosaic) {
	echo '<br style="clear:both;"/>';
}

//the main block of artists if enabled
$flipcoin=true;
$boolflipcoin=false;

///


if ($mosaic&&$artisthighlighthomepage){
	$flipcoin=true;
	echo '<span style="float:left;position: static; top:Opx;text-align:center;display:inline;">';
	$nobr=true;
	echo '<table><tr>';
	foreach ($hlartists as $hlart){
		echo '<td><span class="colTranslate" style="float:left;width:100%;background-color:white;padding:2%;text-align;center;border:solid 3px; border-radius:8px;"><h2 style="display:inline;">';
		echo '<a href="./?artist='.urlencode($hlart['name']).'">'.htmlspecialchars($hlart['name']).'</a><br/>';
				echo '</h2>';
		echo '<em>'.htmlspecialchars($hlart['styles']).'</em>';
		echo '<br/>';
		echo '<strong>'.htmlspecialchars($hlart['infos']).'</strong>';
		
		echo '</span></td>';
			if (!$nobr)
		{
		echo '</tr><tr>';
		$nobr=true;
		}
		else
		{
		$nobr=false;
		}
		
		
		}
	echo '</tr></table>';
	echo '</span>';
		
	}



///

//local *****

foreach ($contentlocal as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0 || isset($_GET['listall'])) {
	$running=true;
	
	if ((isset($_GET['album'])&&$_GET['album']!==$item['album'])||(isset($_GET['listall'])&&$_GET['listall']==='material')) {
		$running=false;
		
	}
	
	
	
	
	if (isset ($item['album'])&&$running){
		$weactuallydisplayedsomething=true;
		$ran=true;

		if (!isset($_GET['listall'])&&!$mosaic&&isset($_SESSION['nick'])){
			echo '<h1>Album : ';
			if (!$material) {
				$recent=Array();
				$recent['album']=$item['album'];
				$recent['date']=time();
				$recent['who']['color']=$_SESSION['color'];
				$recent['who']['nick']=$_SESSION['nick'];
				if (file_exists('./d/recent.dat')){
					$recents=unserialize(file_get_contents('./d/recent.dat'));
					
				}
				else
				{ $recents= Array(); }  
				
				if (count($recents)>=10){
					
					$recents=array_slice($recents, 1, 9);
					
				}
				array_push($recents, $recent);
				$dat=serialize($recents);
				file_put_contents('./d/recent.dat', $dat);
				
					$data['long']=$_SESSION['long'];
					$data['lat']=$_SESSION['lat'];
					$data['nick']=$_SESSION['nick'];
					$data['range']=$_SESSION['range'];
					$data['message']=' is playing '.html_entity_decode($item['album']).' *';
					$data['color']=$_SESSION['color'];
					$dat=serialize($data);
					file_put_contents('./network/d/'.microtime(true).'.php', $dat);

				
				
				
				
			}
		}
		else if (!$mosaic){
			echo '<h1>';
		}
		
		
		
		if (!$mosaic) {
			
			echo '<a href="./?album='.urlencode($item['album']).'">'.$item['album'].'</a></h1>';
			
			echo '<div style="margin-left:auto;margin-right:auto;">'.displaycover($item['album'], 0.42).'</div>';

			}
		else  {
				if ($activatestats&&false){
					//the album score is no longer used due to refactoring of stats to make them compatible with HTMLcache
					if (isset($album_scores[$item['album']])){
						$thisalbumscore=intval($album_scores[$item['album']])+100;
						
					}
					else {
						$thisalbumscore=0;
					}
				}
				else {
					$thisalbumscore=177;
				}
				$displaythatcover=true;

				if (file_exists('./d/covers.txt')){
					$coversfile=trim(file_get_contents('./d/covers.txt'));
					$coverslines=explode("\n", $coversfile); 
					if (!in_array(html_entity_decode($item['album']), $coverslines)){
							$displaythatcover=false;
					}
				}
				echo '<span class="lineTranslate" style="border:solid 0px;';
				if ($artisthighlighthomepage){
					if ($flipcoin)
					{	echo 'float:right;';
						$flipcoin=false;
					}
					else
					{
						echo 'float:right;';
						
					$flipcoin=true;
					}
				}
				else {
					echo 'float:left;';
				}
				if ($displaythatcover){
					echo 'padding:0px;border-radius:0px;background-color:rgb('.$thisalbumscore.','.$thisalbumscore.','.$thisalbumscore.');';
				}
				echo '">';
				
				if ($mixed){
					
					echo '<a>';
					
				}
				else {
				
					echo '<a href="./?album='.urlencode($item['album']).'" title="'.$item['album'].'">';
				}
				
				echo displaycover($item['album'], $streaming_albums_ratio);

		}

		
		if ($mixed||!isset($_GET['listall'])){
			if (!$mixed){
		
				 if (isset($streamingAlbumsInfoHeader[$item['album']])){
					echo '<div style="color:green;background-color:white;border-radius:5px;">';
					echo $streamingAlbumsInfoHeader[$item['album']];
					echo '</div>';
				 }
				
				echo '<div><a href="javascript:void(0);" onclick="document.getElementById(\'tracklist\').style.display=\'inline\';">Controls / tracklisting</a></div><span id="tracklist" ';
			
				if ((isset($_SESSION['random'])&&$_SESSION['random'])||isset($_GET['autoplay'])&&!isset($_GET['track'])){
					echo 'style="display:inline;"';
				}
				echo '>';
			}
			else {
				echo '<span id="tracklist" style=float:right;">';
			}
			
			
			//here we go, query local API for track list
			$tracks_file=file_get_contents($serverapi.'?gettracks='.urlencode($item['album']));

			$tracks=explode("\n", $tracks_file);
			$trackcounter=0;
			$hasntautoplayed=true;
			foreach ($tracks as $track) {
			if ($track!==''){
			
				//we want its name and the artist name as well
				$track_name=trim(file_get_contents($serverapi.'?gettitle='.urlencode($track)));
		
				$track_artist=trim(file_get_contents($serverapi.'?getartist='.urlencode($track)));
				
				if (!isset($_GET['track'])||$_GET['track']==$track_name)
				{
					if (in_array($track_artist, $artists)){
					?>
					<script>
					overload_track_counter++;
					</script>
					<a href="javascript:void(0);" onClick="play('<?php echo str_replace ("'", "\\'", htmlspecialchars($track)); ?>', <?php echo $trackcounter; ?>, false);" id="<?php echo $trackcounter; ?>">▶</a>
					 <a href="./?artist=<?php echo urlencode ($track_artist); ?>">
					 <?php echo  $track_artist; ?></a> - 
					 <?php echo  '<a href="./?track='.urlencode($track_name).'&album='.urlencode($item['album']).'">'.$track_name.'</a>'; ?>
					 <div style="background-color:#F0F0F0;text-align:right;">
						 <?php
						 if (isset($streamingAlbumsInfoNotice[$item['album']])){
						 
							echo $streamingAlbumsInfoNotice[$item['album']];
						 }
						 else {
						?> 
						 
						 Early access download not available for now
					
					
					
					<?php
							}
					?>
					
					
					<!--<a href="javascript:void(0);" style="text-align:right;float:right;" onclick="infoselected=document.getElementById('info<?php echo $trackcounter;?>');loadInfo('<?php echo htmlspecialchars($track);?>');">+</a>-->
							</div>
					<div style="display:none;" id="info<?php echo $trackcounter;?>"></div>
					<?php
					
					
					
					
					
					generatevideo($track_name, $item['album'], $track_artist, $videoapiurl, $videourl);
					showsongsheet($track);
					?>
					
					<?php
					if (isset($_GET['autoplay'])&&$hasntautoplayed){
						?>
						<script>play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, false, true);</script>
						<?php
						$hasntautoplayed=false;
					}
				}
			}
			$trackcounter++;
			}
		}
			echo '</span>';
			if ($mixed) {
				
				}

		}

		if ($mosaic) {
			echo '</a></span>';
		}





		
	}
	
}
if ($ran) {
	$secondcounter++;

}	
$counter++;

}





//remote ****



//material header
if($material) {

	if (!$mixed) {
		echo '<h2>Order form</h2><div>Please indicate your shipping option, then select your desired items in the list below. Once you are done, please click on the payment button that is repeated below each release</div>';
	}
	echo 'Shipping option : <select name="shipping">';
	
	$shipping_options=array_keys($material_shipping);
	$i=1;
	foreach ($shipping_options as $shipping_option){
		echo '<option value="'.htmlspecialchars($shipping_option).'" ';
		
		if ($i==count($shipping_options)){
			echo 'selected="selected"';
		}
		
		echo '>'.htmlspecialchars($shipping_option).' ('.htmlspecialchars($material_currency.' '.$material_shipping[$shipping_option].'/item)').'</option>';
		$i++;
	}
	
	echo '</select>';
	
	if (!$mixed) { echo '<h2>Albums available as physical releases : </h2>';}
		else
	{echo '<br/>';}
	$itemid=0;
			
}
$animindex=0;
foreach ($content as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0||isset($_GET['listall'])) {
	$running=true;
	
	if (isset($_GET['album'])&&$_GET['album']!==$item['album']) {
		$running=false;
		
	}
	if (!(in_array($item['artist'], $material_artists)&&!in_array($item['album'], $material_blacklist))&&(isset($_GET['listall'])&&$_GET['listall']==='material'))
	{
		$running=false;
	}
	
	
	
	if (isset ($item['album'])&&$running){
		$weactuallydisplayedsomething=true;
		$ran=true;
		$float=true;


		if (!isset($_GET['listall'])&&!$mosaic&&isset($_SESSION['nick'])){
			echo '<h1>Album : ';
			if (!$material) {
				$recent=Array();
				$recent['album']=$item['album'];
				$recent['date']=time();
				$recent['who']['color']=$_SESSION['color'];
				$recent['who']['nick']=$_SESSION['nick'];
				if (file_exists('./d/recent.dat')){
					$recents=unserialize(file_get_contents('./d/recent.dat'));
					
				}
				else
				{ $recents= Array(); }  
				
				if (count($recents)>=10){
					
					$recents=array_slice($recents, 1, 9);
					
				}
				array_push($recents, $recent);
				$dat=serialize($recents);
				file_put_contents('./d/recent.dat', $dat);
				
					$data['long']=$_SESSION['long'];
					$data['lat']=$_SESSION['lat'];
					$data['nick']=$_SESSION['nick'];
					$data['range']=$_SESSION['range'];
					$data['message']=' is playing '.html_entity_decode($item['album']).' *';
					$data['color']=$_SESSION['color'];
					$dat=serialize($data);
					file_put_contents('./network/d/'.microtime(true).'.php', $dat);

				
				
				
				
			}

		}
		else if (!$mosaic){
			echo '<h1>';
		}
		
		
		
		if (!$mosaic) {
			
			echo '<a href="./?album='.urlencode($item['album']).'">'.$item['album'].'</a></h1>';
			echo '<div style="margin-left:auto;margin-right:auto;';
			if ($mixed){
				echo 'float:left;';
			}
			echo '">'.displaycover($item['album'], 0.65).'</div>';
			if ($mixed){
				echo '<div style="float:right;text-align:right;font-size:120%;" id="mixedtracks"></div>';
				echo '<div style="float:none;clear:both;"></div>';
			}
			}
		else  {
				$animindex++;
				if ($activatestats&&false){
					//the album scores are no longer used due to refactoring of stats to make them usable with htmlcache
					if (isset($album_scores[$item['album']])){
						$thisalbumscore=intval($album_scores[$item['album']])+100;
						
					}
					else {
						$thisalbumscore=0;
					}
				}
				else {
					$thisalbumscore=177;
				}

			
				$displaythatcover=true;

				if (file_exists('./d/covers.txt')){
					$coversfile=trim(file_get_contents('./d/covers.txt'));
					$coverslines=explode("\n", $coversfile); 
					if (!in_array(html_entity_decode($item['album']), $coverslines)){
							$displaythatcover=false;
					}
				}
				echo '<span  class="lineTranslate" id="anim'.$animindex.'" style="';
				
				echo 'border:solid 0px;';
				
				if ($artisthighlighthomepage){
					if ($flipcoin)
					{	echo 'float:right;';
						$flipcoin=false;
					}
					else
					{
						echo 'float:right;';
						
					$flipcoin=true;
					}
				}
				else {
					echo 'float:left;';
				}
				
				
				
				if ($displaythatcover){
					echo 'padding:0px;border-radius:0px;background-color:rgb('.$thisalbumscore.','.$thisalbumscore.','.$thisalbumscore.');';
				}
				echo '">';
				echo '<script>var anim=\'anim\'</script>';

				echo '<a href="./?album='.urlencode($item['album']).'" title="'.$item['album'].'">';
				echo displaycover($item['album'], 0.1+$thisalbumscore/$download_albums_magic_number);
				
		}
		
		//material order form
		if ($material) {
			
			$material_items=array_keys($material_support);
			echo '<table><tr>';
		
			foreach ($material_items as $material_item)
			 {
				echo '<td style="border: solid 1px;"><strong>'.htmlspecialchars($material_item).'</strong><br/>quantity : ';
				
				echo '<input type="hidden" id="inputid'.$itemid.'"/>';
				?>
				<script>
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'item['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				</script>
				<?php
				echo '<select onchange="this.name=targetid'.$itemid.';">';
				echo '<option value="0">0</option>';
				$i=1;
				while ($i<=50) {
					echo '<option value="'.htmlspecialchars($i).'">'.htmlspecialchars($i).'</option>';
					$i++;
				}
				
				
				echo '</select>';
				$itemid++;
				if (isset($material_support[$material_item]['options'])&&$material_support[$material_item]['options']!=''){
				?>
				<script>
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'option['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				</script>
				
				<?php
				
					$options=explode (' ', $material_support[$material_item]['options']);
					$optionheader=false;
					$optionselected=false;
					echo '<br/>';
					foreach ($options as $option) {
						if (!$optionheader) {
							echo htmlspecialchars($option);
							echo '<select onchange="this.name=targetid'.$itemid.';">'."\n";
							$optionheader=true;
							echo '<option>Select</option>';
							}
							
						else {
							echo '<option value="'.htmlspecialchars($option).'" ';
							
							echo '>'.htmlspecialchars($option).'</option>';
						}
						
						
						
						}
					echo '</select>';
					$itemid++;
					}	
				
				
				echo '</td>';
				
				
					
			}
			echo '</tr>';
		
			echo '</table>';
			
		echo '<input';
		if (!$mixed){ echo ' style="float:right;text-align:right;"';}
		echo' type="submit" value="Order now ! "/>';		
		
			
			
		}

		




			
		if (in_array($item['artist'], $material_artists)&&!in_array($item['album'], $material_blacklist)&&!$material&&!$mosaic){
			echo 'available on material support at our <a href="http://'.$server.'/?listall=material">physical releases shop</a><br/>';
			
		}
		
		if ($mixed||!isset($_GET['listall'])){
			if (!$mixed){
				
				 if (isset($albumsForDownloadInfoNotice[$item['album']])){
							echo '<div style="color:green;background-color:white;border-radius:5px;">';
							echo $albumsForDownloadInfoNotice[$item['album']];
							echo '</div>';
						 }
				
				
				
				
				if ($enableDownloadCart)
				{
					echo '<div id="ajax_splash" style="position:absolute; top:0; left:0;width:100%;height:100%;display:none;background-color:white;"><span id="ajax_splash_message"></span><br/><a href="javascript:void(0);" style="text-align:right;width:100%;" onclick="document.getElementById(\'ajax_splash\').style.display=\'none\';">X Close</a></div>';
					echo '<a href="javascript:void(0);" onclick="addFullAlbumToCart(\''.str_replace("'", "\\'", urlencode($item['album'])).'\');">Add full album to download cart</a><br/>';	
					
				}
					
					
				
				
				
				
				
				
				
				echo '<div><a href="javascript:void(0);" onclick="document.getElementById(\'tracklist\').style.display=\'inline\';">Controls / tracklisting</a></div><span id="tracklist" ';
			
				if ((isset($_SESSION['random'])&&$_SESSION['random'])||isset($_GET['autoplay'])&&isset($_GET['track'])){
					echo 'style="display:none;"';
				}
				echo '>';
			}
			else {
				echo '<span id="tracklist" style=float:right;">';
			}
			if ($enableDownloadCart)
			{
					if (!isset($_SESSION['album_tracklisting']))
				{
					$_SESSION['album_tracklisting']=array();
					
				}
				$_SESSION['album_tracklisting'][$item['album']]=array();
					
				
			}
			
			//here we go, query Clewn API for track list
			$tracks_file=file_get_contents($clewnapiurl.'?gettracks='.urlencode($item['album']));

			$tracks=explode("\n", $tracks_file);
			$trackcounter=0;
			$hasntautoplayed=true;
			foreach ($tracks as $track) {
				if ($track!==''){
					//we want its name and the artist name as well
					$track_name=trim(file_get_contents($clewnapiurl.'?gettitle='.urlencode($track)));
			
					$track_artist=trim(file_get_contents($clewnapiurl.'?getartist='.urlencode($track)));
					
					if ($enableDownloadCart&&in_array($track_artist, $artists))
					{
						$mypair=array();
						
						$mypair['title']=$track_name;
						$mypair['file_basename']=$track;
						
						
						
						array_push($_SESSION['album_tracklisting'][$item['album']],$mypair );
						
						
						
					}
					
					
					
					if (!isset($_GET['track'])||$_GET['track']==$track_name)
					{
						if (in_array($track_artist, $artists)){
						?>
						<script>
						overload_track_counter++;
						</script>
						<a href="javascript:void(0);" onClick="play('<?php echo str_replace ("'", "\\'", htmlspecialchars($track)); ?>', <?php echo $trackcounter; ?>, true);" id="<?php echo $trackcounter; ?>">▶</a>
						 <a href="./?artist=<?php echo urlencode ($track_artist); ?>">
						 <?php echo  $track_artist; ?></a> - 
						 <?php echo  '<a href="./?track='.urlencode($track_name).'&album='.urlencode($item['album']).'">'.$track_name.'</a>';
						?>
						<div style="display:none;" id="info<?php echo $trackcounter;?>"></div>
						<?php
						 if (!$mixed&&!$enableDownloadCart){
								
							 ?>
							 <div style="background-color:#F0F0F0;text-align:right;">Download 
							 
							 <?php
							 foreach ($supported_formats_remote as $mat){
							 ?>
							 <a href="<?php 
							 echo $clewnaudiourl.urlencode ($track).'.'.$mat.'">'.htmlspecialchars($mat); 
							 ?></a> 
							 <?php
							 }
							 ?>
							 
							 <div style="display:none;" id="info<?php echo $trackcounter;?>"></div>
							<?php
							}
						else if (!$mixed&&$enableDownloadCart){
								?>
							 <div style="background-color:#F0F0F0;text-align:left;"><a href="javascript:void(0);" onclick="addTrackToCart('<?php 
							 echo str_replace("'", "\\'", urlencode($track_name));?>', '<?php 
							 echo str_replace("'", "\\'", urlencode($item['album']));?>', '<?php 
							 echo str_replace("'", "\\'", urlencode(htmlentities($track)));?>', '<?php 
							 echo str_replace("'", "\\'", urlencode ($track_artist));?>' );">Add to download cart</a> 
							 
							 <?php
								}
						if (!$mixed){
							?>
							<a href="javascript:void(0);" style="text-align:right;float:right;" onclick="infoselected=document.getElementById('info<?php echo $trackcounter;?>');loadInfo('<?php echo str_replace ("'", "\\'", htmlspecialchars($track))?>');">+</a>
							 </div>
							<?php
							generatevideo($track_name, $item['album'], $track_artist, $videoapiurl, $videourl);
							showsongsheet($track);
							?>
							<?php
							
						}
						else {
							
							echo '<br/>';
							
						}
						if (isset($_GET['autoplay'])&&$hasntautoplayed){
							?>
							<script>play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, true, true);</script>
							<?php
							$hasntautoplayed=false;
						}
					}
				}
				$trackcounter++;
				}
			
			}
			echo '</span>';
			
			if ($mixed) {
				?><script>document.getElementById('mixedtracks').innerHTML=document.getElementById('tracklist').innerHTML;
						  document.getElementById('tracklist').style.display='none';
				</script>
				<?php
				echo '';
				echo '</form>';
				echo '<hr/';
				echo '<div style="float:none;clear:both;"></div>';
				?>
								<h3></h3>Tip download :</h3> 
				<em>Name your price and get instant download access to <strong><?php echo $item['album'];?></strong> in Flac, Ogg and Mp3 formats</em><span style="font-size:84%;">
				
				<form  name="_xclick" action="https://www.paypal.com/fr/cgi-bin/webscr" method="post" >
				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="business" value="<?php echo htmlspecialchars($material_paypal_address);?>" />
				<input type="hidden" name="item_name" value="Tip download for album <?php echo str_replace('"', '', $item['album'])?>" />
				<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($material_currency);?>" />
				<br/>I want to pay <?php echo htmlspecialchars($material_currency);?> <input type="text" size="4" name="amount" value="2.50" />
				<input type="hidden" name="shipping" value="0" />
				<input type="hidden" name="cancel_return" value="http://<?php echo htmlspecialchars($server);?>" />
				<input type="hidden" name="return" value="http://<?php echo htmlspecialchars($server);?>/?album=<?php echo urlencode(htmlentities($_GET['album']));?>" />
				<input type="submit" name="submit" value="Buy now !" /> or <a href="http://<?php echo $server?>/?album=<?php echo urlencode(htmlentities($_GET['album']));?>">get it for free</a></span>
								<?php
				
				
				?>


				<?php
				
				echo '<hr/><h2>Item details : </h2>';
				echo 'All prices are indicated in '.htmlspecialchars($material_currency);
				echo '<div><em>This is the '; 
			
			if ($ismaterialnameyourprice){
				echo 'recommended';
			}
			else{
			
				echo 'minimum';
			
			}
			echo ' price. You name you price, actually, and you can pay more';
			
			if ($ismaterialnameyourprice){
				echo ' or less';
		
			}
			echo ' than this if you wish to.</em></div>';$material_items=array_keys($material_support);
				echo '<table><tr>';
				echo '<td style="border: solid 1px;"><strong><em>Product</em></strong></td>';
				$material_item_lines=array_keys($material_support[$material_items[0]]);
				foreach ($material_item_lines as $material_item_line) {
							echo '<td style="border: solid 1px;"><strong><em>'.htmlspecialchars($material_item_line).'</em></strong></td>';
					
				}
					
				echo '</tr>';
				
				foreach ($material_items as $material_item)
				 {
					echo '<tr>';
					echo '<td style="border: solid 1px;"><strong>'.htmlspecialchars($material_item).'</strong></td>';
					$material_item_lines=array_keys($material_support[$material_item]);
					foreach ($material_item_lines as $material_item_line) {
						echo '<td style="border: solid 1px;">';
						if (isset($material_support[$material_item][$material_item_line])&&$material_support[$material_item][$material_item_line]!==''){
							echo htmlspecialchars($material_support[$material_item][$material_item_line]);
							}
						echo '</td>';
					}
					echo '</tr>';
						
				}
				echo '</table>';


			}


		}



		if ($mosaic) {
			echo '</a></span>';
		}


		
	}
	
}

if ($ran) {
	$secondcounter++;

}	
$counter++;
}//foreach $content







if (!$_SESSION['random']&&$weactuallydisplayedsomething&&!isset($_GET['listall'])){
	$autourl='';
	if (false&&isset($_GET['autoplay'])&&boolval($_GET['autoplay'])){//this case is managed by some other code place
		$autourl='&autoplay=true';
	}
	if (!isset($_GET['target_album'])){
	
	echo '<a id="digolder" style="float:right;" href="./?offset='.intval($offset+1).$arturl.$autourl.'">Dig older...</a><br/>';
	
	}
	else {
	
	echo '<a id="digolder" style="float:right;" href="./?album='.urlencode(htmlentities($_GET['target_album'])).$arturl.$autourl.'">Dig more...</a><br/>';
	
	}
}
if (!$weactuallydisplayedsomething){
	
	echo 'Yeah ! You reached the bottom... There is nothing older.<br/>';
	
	
}
echo '</div>';
if($material) {
	
			
			
	echo '</form><br style="clear:both;"/>';
	echo $materialreleasessalesagreement.'<br style="clear:both;"/>';
}
if ($mosaic) {
	echo '<br style="clear:both;"/>';
}

echo $pageFooterSplash;
?>

<a href="#bottommenu" style="border:solid 1px;" onclick="bottommenu=document.getElementById('bottommenu');if(bottommenu.style.display=='none'){bottommenu.style.display='inline';this.innerHTML='&lt;';}else{bottommenu.style.display='none';this.innerHTML='+';}">+</a>
<a name="bottommenu"></a><div style="display:none;" id="bottommenu">

<?php
echo $footerhtmlcode;
if (!isset($_GET['album'])&&!isset($_GET['artist'])&&!isset($_GET['track'])&&$activatehtmlcache){
?>
<script> overload_track_counter++;</script>
<?php

}
?>

</div>
<?php 

if ($activatehtmlcache){
?>

<script>
if (overload_track_counter==0){
	document.getElementById('overload_splash').style.display='block';
	document.getElementById('overload_form').action='./';
	document.getElementById('overload_button').innerHTML='<input type="submit" value="Clear the cache for this page"></input>';
}


</script>

<?php

}
?>

<hr style="clear:both;">
<?php
if (!$activatechat===false){
?>
		<a name="social"/><object data="./network" style="width:100%;height:480px;" width="100%" height="480"></object>
<?php
}


if ($mosaic&&false){//use this only for ugly design fans -> remove &&false and enjoy
?>
<script>
var animmax=<?php echo $animindex; ?>;
var donez = new Array();
var senz = new Array();
var coverz = new Array();
var target=0;

if (anim='anim'){
for (var z=1;z<=animmax;z++)	{
	donez[z]=0;
}
for (var z=1;z<=animmax;z++)	{
	senz[z]=0;
}



setInterval(function (){
	target++;
	if (target>animmax){
		target=1;
	}
	if (senz[target]==1){
		donez[target]=donez[target]+1;
	}
	else{
		donez[target]=donez[target]-1;
	}
	if (donez[target]<=0){
		senz[target]=1;
	}
	if (donez[target]>=5){
		senz[target]=0;
	}
		
	document.getElementById('anim'+target).style.margin=donez[target]+'px';
	document.getElementById('anim'+target).style.padding=5-donez[target]+'px';
	
}, 1);

}

</script>
<?php  
}
?>
<div style="float:rigth;font-size:76%;">Powered by <a href="http://crero.clewn.org" title="CreRo, the open-source CMS for record labels and webradios">CreRo, the CMS for record labels and webradios</a> - AGPL licensed - <a href="http://github.com/shangril/crero">code repo</a></div>
</body>
</html><?php
if ($activatehtmlcache){


	if ($willhavetocache){
			$htmlcode=ob_get_contents();
			if ($htmlcode!==false){
				$myhtmlcache->cachePage($cachingkey, $htmlcode);
		}
	}

}
?>
