<?php
error_reporting(0);

class SystemExit extends Exception {}
try {

require_once('./config.php');
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);
//error_reporting(E_WARNING|E_NOTICE);

require_once('./crero-lib.php');
//error_reporting(E_WARNING|E_NOTICE|E_ERROR|E_PARSE);
error_reporting(0);

$myhtmlcache=null;

if (isset($_GET['nochat'])){
	$_SESSION['forceWebchat'] = true ;
}

if (
	$ForceWebchatAsHomepage &&

	!isset($_SESSION['forceWebchat']) &&
	
	!isset($_GET['artist']) &&
	!isset($_GET['album']) &&
	!isset($_GET['track']) &&
	!isset($_GET['offset']) &&
	!isset($_GET['autoplay']) &&
	!isset($_GET['vid']) &&
	!isset($_GET['twist']) &&
	!isset($_GET['embed']) &&
	!isset($_GET['body']) &&
	!isset($_GET['target[]']) &&
	!isset($_POST['overload_form']) &&
	!isset($_POST['page_purge'])
	
	
	
	)
{
	
	
	header('Location: '.$proto.'://'.$server.'/webchat', true, 301);
	
	exit(0);
}


//Whoever you are, you deserve a cleaning
//LOCKING 			 
//By the way, let's remove old locks on callbacks ping recently played
if (array_key_exists('recently_callback_id' , $_SESSION)){
	
	foreach (array_keys($_SESSION['recently_callback_id']) as $stamped){
			
			
			
			if (floatval($_SESSION['recently_callback_id'][$stamped]['stamp'])+300<microtime(true)){
				unset($_SESSION['recently_callback_id'][$stamped]);
			}
	}
	
}






			if (file_exists('./recently_ping.lock')&&(floatval(filectime('./recently_ping.lock'))+5.0)<microtime(true)){
				unlink('./recently_ping.lock');
			}
			

			while (file_exists('./recently_ping.lock')){
				if (file_exists('./recently_ping.lock')&&(floatval(filectime('./recently_ping.lock'))+5.0)>microtime(true)){
					unlink('./recently_ping.lock');
					session_reset();
				}
				sleep(1);
			}
			touch ('./recently_ping.lock');
			 
//LOCKED			 
$recentsjailed=Array();
if (file_exists('./d/recent.dat')){
	$recentsjailed=unserialize(file_get_contents('./d/recent.dat'));
	
}
$recentsfinal=Array();
if ($recentjailed){
	foreach ($recentsjailed as $recent){
		
		
		
		
		if($recent['jailed']==false||(($recent['jailed']===true)&&(floatval($recent['date'])+90.0)>floatval(time()))){
			array_push($recentsfinal, $recent);
			
		}//if jailed && jailtime < 90 secondes OR not jailed
		}
	file_put_contents('./d/recent.dat', serialize($recentsfinal));
}
unlink('./recently_ping.lock');
//UNLOCKED


if (array_key_exists('void', $_GET)){
	echo '<!DOCTYPE html><html><body>Initializing...</body></html>';
	throw new SystemExit();
}


function checkOverload ($apiResponse) {
	if ($apiResponse===false){
	echo '<br/>Oooops... We are currently over capacity. Please try again later<br/></body></html>';
	throw new SystemExit();
	}
	
}
if ((filectime('./ypservices.lock')+intval(60))>time()){
	unlink ('./ypservices.lock');
}
if (array_key_exists('ypservices', $_GET)){
    touch ('./ypservices.lock');

	$ypindex=$_GET['ypservices'];
	
	
	if (!is_numeric($ypindex)){
		throw new SystemExit();
	}
	$ypindex=intval($ypindex);
	$arryp=array();
	if (file_exists('./ypservices.dat')){
		$ypfile=file_get_contents('./ypservices.dat');
		$ypdata=unserialize($ypfile);
		$arryp=$ypdata;
		//Note that in the case of the file was not readable, 
		//we will continue anyway, 
		//and ypdata will be an FALSE boolean no longer an//empty array
		
	}
	if (count($creroypservices)>0){
		//let's do some cleanup in the .dat record
		$barryp=array();
		foreach ($creroypservices as $srv){
			if (array_key_exists($srv, $arryp))
			{
				$barryp[$srv]=$arryp[$srv];
				
			}
		
		}
		$arryp=$barryp;
		
		$data=serialize($arryp);
		
		file_put_contents('./ypservices.dat', $data);//note that it can return false on failure and we won't handle such a thing. 

		
		//Here comes the main stuff
		
		if (isset($creroypservices[$ypindex])){
			$ypservice=$creroypservices[$ypindex];
			$ypre=null;
			
			if (!is_array($arryp[$ypservice]))
				{
					$arryp[$ypservice]=array();
				}
			
			if (array_key_exists('ping', $_GET)){//We only ping YP when homepage is displayed, not upon every call to the AJAX
				$ypre=file_get_contents(trim($ypservice).'?url='.urlencode('http://'.$server).'&name='.urlencode($sitename).'&description='.urlencode($description).'&forceHTTPS='.urlencode($YPForceHTTPS));
				
				if (($ypre===false)){
					
							$arryp[$ypservice]['hasFailed']=true;
							$arryp[$ypservice]['repliedFailureTime']=time();
						}
						
					else {//we pinged since ypre is no longer null, and YP replied, since it is not false, so store the pong reply
						$yppong=explode (' ', $ypre);
							$arryp[$ypservice]['hasFailed']=false;
							$arryp[$ypservice]['repliedSuccessTime']=time();
							$arryp[$ypservice]['pong']=array();
							$arryp[$ypservice]['pong']=$yppong;
						
					
					}
				//we didn't ping'ed, but can continue
			
				//at this point is soon enough to save the .dat on disk, since we will only need read access from now
				$data=serialize($arryp);
				
				file_put_contents('./ypservices.dat', $data);//note that it can return false on failure and we won't handle such a thing. 
			}	
			//and now let's thing about our web browser waiting for data
			$yp_server_addr_no_proto = $ypservice;
			$yp_server_addr_no_proto = str_replace('https://', '', $yp_server_addr_no_proto);
			$yp_server_addr_no_proto = str_replace('http://', '', $yp_server_addr_no_proto);
			
			if (isset($arryp[$ypservice]['pong'])){//3 is enough for now and stricly necessary according to protocol RTFS specifications
				$pong_saved_status=boolval ($arryp[$ypservice]['pong'][0]=='0');
				$pong_force_https=boolval (strtoupper($arryp[$ypservice]['pong'][1])=='HTTPS');
				$pong_public=boolval (strtolower($arryp[$ypservice]['pong'][2])=='public');
			}
			else {//lets use default value suitable in most cases
				$pong_saved_status=true;//never got a failure, never, before pong replies introduction
				
				$pong_force_https=false;
				if (!(strpos($ypservice, 'https://')===false)&&strpos($ypservice, 'https://')==0){
					$pong_force_https=true;//if nothing more indicated, let's trust what have been entered by CreRo admins
				}
				$pong_public=true;
			}
			$ourproto='http://';
			if ($pong_force_https){
				$ourproto='https://';
			}
			if($pong_public){
				
				
				
				echo '[<a href="'.$ourproto.str_replace('"','', $yp_server_addr_no_proto).'">'.$ourproto.str_replace('"','', $yp_server_addr_no_proto).'</a>] (';
				
				if (array_key_exists('hasFailed', $arryp[$ypservice])){
					$yp_hasfailed=$arryp[$ypservice]['hasFailed'];
				}
				else $yp_hasfailed=null;
				if (array_key_exists('repliedFailureTime', $arryp[$ypservice])){
					$yp_failuretime=$arryp[$ypservice]['repliedFailureTime'];
				}
				else $yp_failuretime=null;
				if (array_key_exists('repliedSuccessTime', $arryp[$ypservice])){
					$yp_successtime=$arryp[$ypservice]['repliedSuccessTime'];
				}
				else $yp_successtime=null;
				
				
				if (!isset($yp_hasfailed))
					echo 'Last ping success is unknown';
				else if ($yp_failuretime>$yp_successtime){
					echo 'Last ping has failled ';
					if (isset($yp_failuretime)){
							echo ''.date(DATE_RSS, $yp_failuretime).'  ';
						}
						else
							echo 'an unkown time ago.';
				}
				if (isset($yp_successtime)){
					echo 'Last ping success '.date(DATE_RSS, $yp_successtime).' ';
					if (!$pong_saved_status){
						echo 'but the YP replied that its record was not updated';
						}
					}
				else
					echo 'Last ping success is unknown ';
			
			   echo ') ';
			
			}
		}
		else {//we finished, going bybye
			unlink ('./ypservices.lock');
			throw new SystemExit();
			
		}
	}

	//and now we're done, now we can go home
	unlink ('./ypservices.lock');
	throw new SystemExit();


}//Ajax reply to ypservices get parameter

if (isset($_GET['embed'])){
	$embed=$_GET['embed'];
			//as a temporary measure, to comply with per-site cookies isolation introduced in modern mozilla
		//we disable download_cart
		//to allow download on embeding sites
		//awaiting for further dev. enabling it
		$enableDownloadCart=false;
}else{$embed=false;}
/**********************************************************************
 * IMPORTANT NOTICE ABOUT $embed
 * Embed can be of two types ! Either a String, or a Boolean
 * So never write <?php if ($embed) ?> of <?php if (!$embed) ?> !!!!!!
 * Instead, write <?php if (((true==$embed)||(false!==$embed)))
 * or <?php if (!((true==$embed)||(false!==$embed))) ?>
 * 
 * Otherwise, if the embed string contains the name of an artist who
 * has for name "0", it will evaluate as false
 * and will not embed properly
 * so a strict double check is necessary
 * firstly without strict type checking for normal name artists
 * secondary with an OR (||) with strict type checking
 * to be sure it's not strictly a boolean that has its value "false"
 * ********************************************************************/

//$sessionstarted=session_start();
header("Content-Type: text/html; charset=utf-8");
if (!array_key_exists('crero_uid', $_SESSION)){
	$_SESSION['crero_uid']=microtime(true).'-'.random_int(1, 1000000);
}
ob_start();


if (!isset($_SESSION['origin'])){
	$_SESSION['origin']=$_SERVER['HTTP_REFERER'];
	
}

srand();

if (isset($_SESSION['random'])&&$_SESSION['random']){
	$_GET['twist']='random';//necessary if cache enabled
	
}
if (array_key_exists('noscript', $_GET)&&$_GET['noscript']=='footer'){
?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />
<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" href="//<?php echo $server; ?>/rss" />
<?php
	$artists_file=trim(file_get_contents('./d/artists.txt')); 

	$artists=explode("\n", $artists_file);
	foreach ($artists as $a){
		echo '<link rel="alternate" type="application/rss+xml" href="//'.$server.'./rss/?artist='.urlencode($a).'"/>'."\n";

	}
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<meta charset="UTF-8"/>
<title><?php echo htmlspecialchars($sitename).' - '.htmlspecialchars($footerReadableName); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($title.' - '.$description); ?>" />
</head>
<body>
<a href="./"><?php echo htmlspecialchars($sitename); ?></a> &gt; <?php echo htmlspecialchars($footerReadableName); ?><br/>
<?php echo $footerhtmlcode; ?> 
</body>
</html>
<?php
	throw new SystemExit();
}

$willhavetocache=false;
if ($activatehtmlcache){
$myhtmlcache=new creroHtmlCache($htmlcacheexpires);

}
$cachingkey='key:';

$get_keys=array_keys($_GET);

$whitelist= array ('artist', 'album', 'track', 'offset', 'autoplay', 'vid', 'twist', 'embed', 'body', 'target[]');

foreach ($get_keys as $get_key){
	//EXPECT crazy behaviors if target[] count more than one single elements. For now this is not a problem, cause the the sole target[] is array ('radio') and has only one element
	if (in_array($get_key, $whitelist)){
		$cachingkey.=$get_key."\n".$_GET[$get_key];
		}
}
if ($activatehtmlcache){
if (isset($_GET['purge'])){
		$myhtmlcache->purgeCache();
		echo '<html><body>Cache purged. <a href="./">Proceed</a></body></html>';
		throw new SystemExit();

	}
}
if (isset($_POST['page_purge'])&&$activatehtmlcache){
	$pseudoget=json_decode(base64_decode($_POST['page_purge']),true);
	
	$cachingkey='key:';
	$get_keys=array_keys($pseudoget);

	foreach ($get_keys as $get_key){
		if (in_array($get_key, $whitelist)){

			$cachingkey.=$get_key."\n".$pseudoget[$get_key];
		}
	}

	
	$myhtmlcache->purgePage($cachingkey);
	
	$redirect_proto='http';
	
	if (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!==''){
		$redirect_proto='https';
	}
	$querystring='?';
	$get_keys=array_keys($pseudoget);
	foreach ($get_keys as $get_key){
		if (in_array($get_key, $whitelist)){

			if ($get_key!='body'&&$get_key!='no-infinite-loop-please'){
				$querystring=$querystring.$get_key.'='.urlencode($pseudoget[$get_key]).'&';
			}

		}
	}
	if ($querystring=='?'){
		$querystring='';
	}
	header_remove(null);
	header("Location: ".$redirect_proto.'://'.$_SERVER['SERVER_NAME'].str_replace ('index.php', '', $_SERVER['PHP_SELF']).$querystring, true, 302);
	
	
	echo '<html><head><title>Cache page purging</title></head><body style="font-size:700%;">The page was purged from the cache.<br/>
	
	
	<a onload="this.click();" href="./'.$querystring;

	
	echo '">Continue...</a></body></html>';
	throw new SystemExit();

}





if (isset($_GET['getinfo'])){
	echo file_get_contents($clewnapiurl.'?getinfo='.urlencode($_GET['getinfo']));
	throw new SystemExit();
}
//some last pre-caching thing
//***************PRE CACHING THINGS***************************
//We want to store in the recent.dat
//The album currenlty played (if any)
//If the RecentPlay option has been desired
//And the session started, just to keep many bots
//Out of the log
//And polite bots are not wished as well
//thanks to them for being polite
if (((array_key_exists('recently_callback', $_GET)&&
array_key_exists('recently_callback_id', $_GET))
&&isset($_GET['album'])
&&(((!array_key_exists('recently_callback_id', $_SESSION))||!array_key_exists($_GET['album'], $_SESSION['recently_callback_id']))&&$_SESSION['recently_callback_id'][$_GET['album']]['id']!=$_GET['recently_callback_id']))
		&&!isset($_GET['listall'])&&$recentplay&&$sessionstarted
		)
		
		 {
//LOCKING 			 
			if (file_exists('./recently_ping.lock')&&(floatval(filectime('./recently_ping.lock'))+5.0)<microtime(true)){
				unlink('./recently_ping.lock');
			}
			

			while (file_exists('./recently_ping.lock')){
				if (file_exists('./recently_ping.lock')&&(floatval(filectime('./recently_ping.lock'))+5.0)<microtime(true)){
					unlink('./recently_ping.lock');
					session_reset();
					
				}
				sleep(1);
			}
			touch ('./recently_ping.lock');
			 
//LOCKED			 
			 
			 
			 
			 
	$recent=Array();
	$recent['album']=$_GET['album'];
	$recent['date']=microtime(true); 
	$recent['who']['color']=$_SESSION['color'];
	$recent['who']['nick']=$_SESSION['nick'];
	$recent['uid']=$_SESSION['crero_uid'];
	$recent['jailed']=true;
	if (!file_exists('./d/recent.dat')){
		$recents= Array();
	}
	else {
		$recents=unserialize(file_get_contents('./d/recent.dat'));
		
	}
	
	$_SESSION['jailtime']=$recent['date'];
	if (!array_key_exists('recently_callback_id', $_SESSION)){
		$_SESSION['recently_callback_id']=array();
	}
	$_SESSION['recently_callback_id'][$recent['album']]=array( 'id' => $_GET['recently_callback_id'], 'stamp' => microtime(true));
			
	if (true){	
		if (count($recents)>=10000){//hey guys, let's store 1000 times more than we need, just to keep the jailed ones, unjail the legitimates upon validation, and with a certain incertainyty get 0.1% of our list that is valid visitors. 
			
			$recents=array_slice($recents, 1, 9999);
			
		}

		array_push($recents, $recent);
		$dat=serialize($recents);
		if ($dat!==false){
			file_put_contents('./d/recent.dat', $dat);
		}
		if ($activatechat&&array_key_exists('nick', $_SESSION)){
			$data['long']=$_SESSION['long'];
			$data['lat']=$_SESSION['lat'];
			$data['nick']=$_SESSION['nick'];
			$data['range']=$_SESSION['range'];
			$data['message']=' is playing '.html_entity_decode($recent['album']).' *';
			$data['color']=$_SESSION['color'];
			$dat=serialize($data);
			file_put_contents('./network/d/'.microtime(true).'.dat', $dat);
		}
	}
	
	
	
	
}

//by the way, it's time to delete jailed album in recently played
//that haven't been validated within a minute and a half
//just by keeping those jailed
//since less than this period
$recentsjailed=Array();
if (file_exists('./d/recent.dat')){
	$recentsjailed=unserialize(file_get_contents('./d/recent.dat'));
	
}
$recentsfinal=Array();
foreach ($recentsjailed as $recent){
	if($recent['jailed']==false||(($recent['jailed']===true)&&(floatval($recent['date'])+90.0)>floatval(time()))){
		array_push($recentsfinal, $recent);
		
	}//if jailed && jailtime < 90 secondes OR not jailed
}
file_put_contents('./d/recent.dat', serialize($recentsfinal));

unlink ('./recently_ping.lock');
//UNLOCKED 









if (array_key_exists('recently_callback', $_GET)){
	throw new SystemExit();
}

//*************PRE CACHING ENDS**************
//* caching of htmlpage ; here we are


if ($activatehtmlcache&&!isset($_POST['validateemail'])&&!isset($_GET['pingstat'])){
	if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
	
	if ($myhtmlcache->hasPageExpired($cachingkey)){
		$willhavetocache=true;
		
	}
	else {
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) <= date(DATE_RFC822, $myhtmlcache->getCachedPageDate($cachingkey))){
				header(null);
				session_abort();
				header('HTTP/1.0 304 Not Modified');
				throw new SystemExit();
			}
			
		}
		
		
		header('Last-Modified: '.date(DATE_RFC822, $myhtmlcache->getCachedPageDate($cachingkey)));
		echo $myhtmlcache->getCachedPage($cachingkey);
		throw new SystemExit();
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
		
		
	throw new SystemExit();//die();
}
		


$mosaic=false;
if (count(array_intersect(array_keys($_GET),array ('offset', 'listall', 'autoplay', 'vid', 'twist', 'embed')))==0){
	$mosaic=true;
	if ($_GET['listall']!='material'){
		$_GET['listall']='albums';
	}
	
	
}
if (count(array_intersect(array_keys($_GET), array ('artist', 'album', 'track', 'offset')))>0){
	if($_GET['listall']=='albums')
		unset($_GET['listall']);
	$mosaic=false;
}
if (array_key_exists('listall', $_GET)&&$_GET['listall']!='albums'&&$_GET['listall']!='material'&&$_GET['listall']!='bogus'){
	$mosaic=false;
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


if (isset($_GET['artist'])) {
	//$artist=$_GET['artist'];

	//$favicon='//'.$server.'/favicon.png';
	$arturl='&artist='.urlencode($_GET['artist']);
	//$title=htmlspecialchars($_GET['artist']).' - A '.htmlspecialchars($sitename).' artist';
	$artists_file=trim(file_get_contents('./d/artists.txt')); 

	$artists=explode("\n", $artists_file);
	if (!in_array($_GET['artist'], $artists)&&(file_exists('./d/artists.txt')&&count($artists)>0)) {
		echo 'ooops... Invalid artist ! <a href="./">Browse the site, maybe?</a>';
		throw new SystemExit();
	}

}
else {
	//$favicon='/favicon.png';
	$arturl='';
	$artist='';

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

$material_artists_file=htmlentities(trim(file_get_contents('./d/material_artists.txt')), ENT_COMPAT);
	
$material_artists=explode("\n", $material_artists_file);
	
$material_blacklist_file=htmlentities(trim(file_get_contents('./d/material_blacklist.txt')), ENT_COMPAT);
	
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
function cacheTracklist($album_entitified, $myserverapi) {
	if (!file_exists('./d/albums_track_counter.dat')){
		file_put_contents('./d/albums_track_counter.dat', serialize(array()));
	}
	$data=unserialize(file_get_contents('./d/albums_track_counter.dat'));
	if ($data!==false){
		$localfreshness=filectime('./d/albums_track_counter.dat');
		$remotefreshness=file_get_contents($myserverapi.'?freshness=1');
		if ($remotefreshness!==false){
			if (floatval($localfreshness)>=floatval(trim($remotefreshness))){
				if (array_key_exists($album_entitified, $data)){
					return $data[$album_entitified];
					}
				}
			
			}
			
		}
		
	
	/////NOT IN CACHE, QUERYING
	$remotedat=file_get_contents($myserverapi.'?gettracks='.urlencode($album_entitified));
	if ($remotedat!==false){
		$data=unserialize(file_get_contents('./d/albums_track_counter.dat'));
		if ($data!==false){
			if (array_key_exists($album_entitified, $data)&&(strlen($data[$album_entitified])<=strlen($remotedat))){
				$data[$album_entitified]=$remotedat;
				file_put_contents('./d/albums_track_counter.dat', serialize($data));
				return $remotedat;	
			}
			else if (array_key_exists($album_entitified, $data)){
				return $data[$album_entitified];	
			}
			else if (!array_key_exists($album_entitified, $data)){
				$data[$album_entitified]=$remotedat;
				file_put_contents('./d/albums_track_counter.dat', serialize($data));
				return $remotedat;	
			
			}
			else {
				return false;
			}
		}
	}
	return false;
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
		echo 'ease enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
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

function outputArtistSiteLink($artistHTML, $albumHTML, $ArtistSites){
		$returnLink='';
	
		if (is_array($ArtistSites)&&count($ArtistSites)>0&&array_key_exists(html_entity_decode($artistHTML), $ArtistSites)){
			$returnlink='Also available on <a target="_blank" href="'.$ArtistSites[html_entity_decode($artistHTML)].'?album='.rawurlencode(html_entity_decode($albumHTML)).'">'.$artistHTML.'</a> site';
		
			
		}
	
		return $returnlink;
}



function generatevideo($track_name, $album, $track_artist, $videoapiurl, $videourl, $albumhasvid) {
	//let's see if there is a video available
					if ($videoapiurl===false||intval($albumhasvid)<=0){
						return;
					}	
					
	
	
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
							echo '<source src="'.htmlspecialchars($videourl.rawurlencode($videotarget).'.'.rawurlencode($videoformat)).'" mime-type="'.htmlspecialchars(mime_content_type($videotarget.'.'.$videoformat)).'"></source>';
							
							
						}
						echo 'Your browser is very old and does not support HTML5 video, sorry</video>';
						echo '<br/>';
						echo 'Download : ';
						foreach ($videoformats as $videoformat) {
							echo '<a download href="'.$videourl.rawurlencode($videotarget).'.'.rawurlencode($videoformat).'">'.htmlspecialchars($videoformat).'</a> ';
							
						}
						echo '<br/>';
						echo htmlspecialchars(trim(file_get_contents($videourl.$videotarget.'.description.txt')));
						echo '</div>';
						
					}
					
}
function showsongsheet($track) {
	if (file_exists('./songbook/'.$track.'.txt')){
		echo '<div style="background-color:#A8A8A8;"><a href="#'.htmlspecialchars($track.'.txt').'" onclick="cr_document_menu_getElementById(\''.htmlspecialchars($track.'.txt').'\').style=\'display:block;\'">Lyrics/chords</a></div>';
		echo '<a name="'.htmlspecialchars($track.'.txt').'"><div style="font-family:monospace;display:none;background-color:#DFDFDF;" id="'.htmlspecialchars($track.'.txt').'">'.str_replace("\n", '<br/>', htmlspecialchars(file_get_contents('./songbook/'.$track.'.txt'))).'</div></a>';	
			
	}
	
}
function displaycover($album, $ratio, $param='cover', $AlbumsToBeHighlighted = 0, $highlightcounter = 0){
	if (file_exists('./d/covers.txt')){
		
		if ($highlightcounter<$AlbumsToBeHighlighted){
			
			$ratio=$ratio*2;
			
			
			
		}
		
		
		
		
		$coversfile=trim(file_get_contents('./d/covers.txt'));
		$coverslines=explode("\n", $coversfile); 
		
		$i=0;
		$url=null;
		while ($i<count($coverslines)){
			if ($coverslines[$i]==html_entity_decode($album)){
				if (array_key_exists($i+1, $coverslines)){
					$url=$coverslines[$i+1];
				}
			}
			$i++;
			$i++;
		}
		if (isset($url)){
			$output='';
			$output.='<table style="border:solid red 4px;border-radius:3px;"><tr><td><div style="display:inline;align:center;text-align:center;border:solid black 3px;border-radius:2px;"><img style="margin:auto;" class="lineTranslate" alt=" ['.$album.'] " id="'.$param.'_'.htmlspecialchars($album).'" onload="if (!get_page_init()){init_page()};gogetit();if (this.src.endsWith(\'favicon.png\')){increment_thumbnail_max();};if (this.src.endsWith(\'favicon.png\')){chckImg(this, \''.str_replace("'", "\\'", urlencode($url)).'\', '.floatval($ratio).');album_displayed++;};increment_overload_track_counter();" src="favicon.png" />';
		
			/*$output.='<script>
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
			//var onload="if(!get_page_init()){;
			/*if (document.documentElement.clientWidth>=document.documentElement.clientHeight){
				onload="if(!get_page_init()){=document.documentElement.clientHeight;
			}
			else
			{
				onload="if(!get_page_init()){=document.documentElement.clientWidth;
			}//				 ';
			$output.='document.getElementById('."'".$param.'_'.str_replace("'","\\'",$album)."'".').src='."'".'./thumbnailer.php?target='."'".'+encodeURI('."'".str_replace("'","\\'",'./covers/'.$url)."'".')+'."'".'&viewportwidth='."'".'+encodeURI(onload="if(!get_page_init()){)+'."'".'&ratio='."'".'+encodeURI('."'".str_replace("'","\\'",$ratio)."'".');';
							 
			$output.='
 // @license-end 
 </script>';
			*/
			$output.='<br/><div style="padding-bottom:3%;text-align:center;border:solid 1px;">'.str_replace(" ", ' ', $album).'</div></div></td></tr/></table>';
			return $output;
		}
		else {//if (!get_page_init()){init_page()};gogetit();if (this.src.endsWith(\'favicon.png\')){increment_thumbnail_max();};if (this.src.endsWith(\'favicon.png\')){
			return ' <img src="favicon.png" class="lineTranslate" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'" onload="if (this.src.endsWith(\'favicon.png\')){getCover(this, \'./favicon.png\', get_size(), '.floatval($ratio).');album_displayed++;};increment_overload_track_counter();if (!get_page_init()){init_page()};"/> <br/><div style="padding-bottom:3%;text-align:center;border:solid 1px;">'.str_replace(" ", '<br/>', $album).'</div>';
		}
	
	
	
	}
	else{
			return ' <img src="favicon.png" class="lineTranslate" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'" onload="increment_overload_track_counter();if (!get_page_init()){init_page()};if (this.src.endsWith(\'favicon.png\')){getCover(this, \'./favicon.png\', get_size(), '.floatval($ratio).');album_displayed++;}"/> <br/><div style="padding-bottom:3%;text-align:center;border:solid 1px;">'.str_replace(" ", '<br/>', $album).'</div>';
	}	
}

$albumhasvid=null;


function checkalbhasvid($alb, $videoapiurl){


if (filter_var($videoapiurl, FILTER_VALIDATE_URL)){
	    
		$cont  = file_get_contents($videoapiurl.'?hasalbum='.urlencode($alb));
	    
		return (intval(trim($cont)));
	
}
else {
	return 0;
}
}
if (!(array_key_exists('body', $_GET)&&$_GET['body']=='ajax')) {
?><!DOCTYPE html>
<html>
<head>


	
	
	
	
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />
<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="alternate" type="application/rss+xml" href="//<?php echo $server; ?>/rss" />
<?php
	$artists_file=trim(file_get_contents('./d/artists.txt')); 

	$artists=explode("\n", $artists_file);
	foreach ($artists as $a){
		echo '<link rel="alternate" type="application/rss+xml" href="//'.$server.'/rss/?artist='.urlencode($a).'"/>'."\n";

	}
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<meta charset="UTF-8"/>

<title id="main_title"><?php echo htmlspecialchars($sitename.' - '.strip_tags($title)); 
?></title>
<meta id="main_description" name="description" content="<?php echo htmlspecialchars($description); ?>" />
 
<script src="./crero-script.js">
</script>

<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
var server='<?php echo str_replace("'", "\\'", $server).'/';?>';

var proto='<?php
if (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!==''){
		echo 'https';
	}
	else {
		echo 'http';
	}
?>';
//WARNING: This value is set for indicative purpose only. Since it is gonna be cached, it can be one, or other, and
//doesn't really reflect the protocol really active for the current page
//it will simply be the one of the request when the page was cached
//so please don't use this value for absolutely anything
//we are going to use it for parsing request URL upon ajax calls
//so it is not really important
//because we just need GET parameters
//and nothing more
//so then whatever is indicated, don't trust
//TODO: include a client side AJAX Request
//that will query the server for its supported protocol
//upon landing on any page
//and pass full series of GET parameter and possible # anchor fragment
//(I.E. will pass as a GET parameter the full content of window.locaction.href)
//and introduce an $server_forces_HTTPS option in admin panel
//that if set will indicate https regardless of the currently in use
//protocol (and if unset https), and the response will also be besides
//protocol, the full, actual path passed as the query)
//and then on client side
//if the reply is not exactly the same than window.location.href
//which would mean the server indicates _forces_https for proto
//move window.location.href to absolute https 
//url provided as the reply
//IMPORTANT this WILL have to be done inside init_page() JS function
//and ONLY after the sanitization of displayed url in address bar is done
//(the window.history.replaceState() thing)

var bodyvoid='';



	
;(function(){
	
        
    window.addEventListener('popstate', function() {
        if (window.history.state!=null){
			window.history.go();
		}
		else {
			set_page_init(false);
			//window.history.go();
		}
        window.dispatchEvent(new Event('locationchange'));
       
    });





})();
	
		
	
	
function displayRecentlyPlayed(){
	var wwwxhttprecalb = new XMLHttpRequest();
		  wwwxhttprecalb.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 document.getElementById("recently_played").innerHTML = this.responseText;
			}
		  };
		  wwwxhttprecalb.open("GET", "recently_played.php", true);
		  wwwxhttprecalb.send();
	
}




 //@license-end 
 </script>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
<?php if (((true==$embed)||(false!==$embed))){
		echo 'var embed=\''.str_replace('\'', "\\'",$embed).'\';';
}
?>

function delegate()  {
	
	if (yprun&&!stall&&document.getElementById('yp-services-content')!=null){
		
		var xhttpyp = new XMLHttpRequest();
		  if (ypcurrentindexretries!=ypindex){
		  
			ypcurrentindexretries=ypindex;
			ypretries=0;
			}
			
		  xhttpyp.onreadystatechange = function() {
			
			if (this.readyState == 4 && this.status == 200) {
			 if (document.getElementById('yp-services-content').innerHTML.startsWith('Loading ')) {
				 document.getElementById('yp-services-content').innerHTML='';
			 }
			 
			 if (yparrvalidated[ypindex]!=true){
			 //juste to make sure we don't add duplicate entries
			 //over slow internet connexions
				document.getElementById('yp-services-content').innerHTML = document.getElementById('yp-services-content').innerHTML+this.responseText;
				yparrvalidated[ypindex]=true;
			 }
			 ypretries=0;
			 ypindex++;
			 stall=false;
			 if (this.responseText.trim() == ''){
				 yprun=false;
				 clearInterval(myfunc);
			 }
			}
			else if (this.status != 200){
				ypretries++;
				stall=false;
			}
		  };
		  
		  
		  
		  if (ypping){
			  appendypreq='&ping=ping';
		  }
		  xhttpyp.open("GET", './?ypservices='+encodeURI(ypindex)+appendypreq, true);
		  stall=true;
		  xhttpyp.send();
		 
		  if (ypretries>9){
			  document.getElementById('yp-services-content').innerHTML=document.getElementById('yp-services-content').innerHTML+' (no reply for YP index: '+parseInt(ypindex)+', skipping) '; 
			  yparrvalidated[ypindex]=true;
			  ypindex++;
		  }
	
	   }//if yprun
	
	}//function delegate()



<?php	if ($enableDownloadCart) {?>
function addFullAlbumToCart(album_cart,target)
		{
			
		var ourtarget_fullalbum=target;
			
		 var qxhttpcartalb = new XMLHttpRequest();
		  qxhttpcartalb.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 /*document.getElementById("ajax_splash").style.display="block";
			 document.getElementById("ajax_splash").style.position="absolute";
			 document.getElementById("ajax_splash").style.height="100%";
			 document.getElementById("ajax_splash_message").innerHTML = this.responseText;*/
			 if (this.responseText.startsWith('FAILURE')){
				ourtarget_fullalbum.innerHTML="Failure, sorry. Please click to retry";
			 }
			 else {
				ourtarget_fullalbum.href="javascript:void(0);";
				ourtarget_fullalbum.onclick="void(0);";
				ourtarget_fullalbum.innerHTML=this.responseText;
				 
			 }
			 update_cart();
			 
			}
		  };
		  qxhttpcartalb.open("GET", "add_album_to_cart.php?album="+album_cart, true);
		  qxhttpcartalb.send();

		}
function addTrackToCart(track_title, track_album, track_basefile, track_artist, target)
		{
			
		 var ourtarget_track=target;	
			
		 var xhttpcarttrk = new XMLHttpRequest();
		  xhttpcarttrk.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 /*document.getElementById("ajax_splash").style.display="block";
			 document.getElementById("ajax_splash").style.position="absolute";
			 document.getElementById("ajax_splash").style.height="100%";
			 
			 document.getElementById("ajax_splash_message").innerHTML = this.responseText;*/
			 if (this.responseText.startsWith('FAILURE')){
				ourtarget_fullalbum.innerHTML="Failure, sorry. Please click to retry";
			 }
			 else {
				ourtarget_track.href="javascript:void(0);";
				ourtarget_track.onclick="void(0);";
				ourtarget_track.innerHTML=this.responseText;
				 
			 }
			 
			 update_cart();
			}
		  };
		  xhttpcarttrk.open("GET", "add_track_to_cart.php?track_title="+track_title+"&track_basefile="+track_basefile+"&track_album="+track_album+"&track_artist="+track_artist, true);
		  xhttpcarttrk.send();

		}

<?php }?>





 //@license-end 
 </script>
<?php if ($activatestats) {?>


<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
	  
function stats(){
	  
	  var xstathttp = new XMLHttpRequest();
	  xstathttp.open("GET", "./?pingstat=true", true);
	  xstathttp.send();

}

 //@license-end 
 </script>
<?php } else { ?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
	function stats(){
		return;
	}

 //@license-end 
 </script>
<?php }



 ?>
<style>
</style>
<?php if ($enableDownloadCart){?>
	<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
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
	
 //@license-end 
 </script>
 <?php } ?>
<!-- Here comes some things that will never change and stay, hardcoded, forever --> 
<script>
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
<?php if ($activatehtmlcache){
	echo 'var activatehtmlcache=true;';
}
else {
	
	echo 'var activatehtmlcache=false;';
	
}
?>

var dl_audiourl='';
var str_audiourl='';
var dlformats=['mp3'];
var strformats=['mp3'];
var dl_apiurl='';
var str_apiurl='';


<?php
echo 'dl_audiourl='."'".$clewnaudiourl."';\n";
echo 'str_audiourl='."'//".$server."/z/';\n";
echo 'dl_apiurl='."'".$clewnapiurl."';\n";
echo 'str_apiurl='."'//".$serverapi."';\n";
$api_dl_formats=file_get_contents($clewnapiurl.'?listformats=1');
$api_str_formats=file_get_contents($serverapi.'?listformats=1');

if ($api_dl_formats!==false&&strlen($api_dl_formats)>2){
	echo 'dlformats='."'".str_replace("\n", ' ', $api_dl_formats)."'".'.split(" ");';
}
else {//we revert to mp3 as a default. How ugly is this, indeed ? 
	
	echo 'dlformats=["mp3"];';
	
}
if ($api_str_formats!==false&&strlen($api_str_formats)>2){

	echo 'strformats='."'".str_replace("\n", ' ',  $api_str_formats)."'".'.split(" ");';
}
else {//we revert to mp3 as a default. How ugly is this, indeed ? 
	
	echo 'strformats=["mp3"];';
	
}	

?>


//@license-end
</script>
 
</head>
<?php





?>
<body id="crerobody" onload="set_page_load(true);"
>
<?php } 
}//if body=ajax
catch (SystemExit $e) { ob_flush(); exit(0); }

try {
//if !$_GET['body']=='ajax';?>
<span id="noscripters" style="position:fixed;top:0px;left:0px;z-index:201;" onLoad="if (!get_page_init()){init_page();}">
<noscript >Dear noscripter, <br/> as of most of music-featured website, this website relies heavily on Javascript, especially to allow continuous playing album after album, because nowadays' browsers won't allow an autoplay upon page load. AJAX is required. <br/>
We suggest you to look at the LibreJS javascript extension, which blocks javascripts, and allows, and unblock, Javascript which is free software and human readable and therefore checked for safety and privacy compliance. It is edited by the Free Software Foundation (fsf.org). </noscript>
</span><span style="float:right;text-align:right;"><?php if ($videoapiurl!==false){echo '<a href="./random_vids.php">Random videos</a><br/><a href="./all_vids.php">Browse videos</a>';} ?></span><br style="clear:both;"/>
<div id="twirling" style="position:fixed;opacity:0.7;top:0px;right:0px;background-color:white;display:block;z-index:200;color:black;font-size:100%;text-align:center;">-
</div>
<span id="twirling_message" style="position:fixed;opacity:0.8;bottom:0px;left:0px;background-color:white;display:block;z-index:202;color:black;font-size:100%;text-align:center;width:100%;">Initializing...</span>

<input type="hidden" id="bodyajax" value="" onload="if (bodyvoid!=''){this.value=bodyvoid;};"/>
<input type="hidden" id="bodyajax_autoplay" value="false"/>
<input type="hidden" id="bodyajax_arttruc" value=""/>





<?php if ($activatehtmlcache) { ?>
<!--overcapacity splash-->
<div id="overload_splash" style="z-index:128;position:absolute; top:0; left:0;width:100%;height:100%;margin-bottom:100%;display:none;background-color:white;">
	<span id="overload_splash_message"></span>
	<h1 style="text-align:center;">Sorry, something went wrong</h1>
	<hr/>
	<?php if (array_key_exists('body', $_GET)&&$_GET['body']=='ajax'){
		echo '<h1 style="text-align:center;">Overload autorepair. Step 1 / 2</h1>';
	}
	else {
		echo '<h1 style="text-align:center;">Overload autorepair Step 1 effective. Now step 2 / 2</h1>';
	
	}
	?>
	<hr/>
	<span style="text-align:center;">
	It seems that this version of this page as stored in the site's cache has been generated while the media storage tier was suffering an overload, or that the content of the tier as changed since the caching.<br/> 
	<br/>
	We will try to clear the cache for this particular page and see if it can solve this error. 
	<form id="overload_form" method="POST">
	<input type="hidden" name="page_purge" value="<?php echo base64_encode(json_encode(array_diff_assoc($_GET, array('listall' => $_GET['listall'], 'no_infinite_loop_please' => $_GET['no_infinite_loop_please']))));?>"/>
	
	<span id="overload_countdown">This page will try to autorepair in <span id="overload_countdown_seconds">6</span> seconds...<a href="javascript:void(0);" onclick="cr_window_overloadtime();">Cancel</a></span>
	<span id="overload_button">
	</span>
	</form>
	</span>
	<hr/>Note for site administrator: your CMS is not able to handle the deletion of a particular track. If you want to remove a track from an album, you <strong>must</strong> manually delete the file named <em>./d/albums_track_counter.dat</em> at the root of your CMS install to get rid of this error.<br/>
	<br/><a href="javascript:void(0);" style="margin-bottom:100%;text-align:right;width:100%;" onclick="cr_document_get_overload_splash__style_display('none');">X Close</a>
</div>
<?php } ?>


<!-- volume controler code -->
<span onload="if(!get_page_init()){init_page();}" style="position: fixed; left:0px; top:0px; font-size:140%; border:solid 1px; border-radius:5px;z-index:127;background-color:grey;" >
<a href="javascript:void(0);" style="border:solid 1px; border-radius:3px;background-color:#18FF18;" onClick="cr_document-getElementById_player_-volume()=cr_document-getElementById_player_-volume()-0.1;">-</a>
<span style="font-size:108%;background-color:black;">🔈</span>
<a href="javascript:void(0);" style="border:solid 1px; border-radius:3px;;background-color:#18FF18;" onClick="cr_document-getElementById_player_-volume()=cr_document-getElementById_player_-volume()+0.1;">+</a>
<a style="" href="javascript:void(0);" onclick="setplayerstall(false);player=get_player();if (get_isindex()){set_page_init(false);update_ajax_body('./?offset=0&autoplay=true');} else if (player.paused){if (get_isplaying()!=-1){set_isplaying(player.play());}else{cr_document_autoplay().click();};this.style.backgroundColor='white';this.style.color='green';}else{player.pause();this.style.backgroundColor='black';this.style.color='red';}">⏯</a>
<a href="javascript:void(0);" onclick="controler_prev();">⏴|</a>
<span id="playerclock"></span>
<a href="javascript:void(0);" onclick="controler_next();">|⏵</a><br/>
<span style="font-size:72%;" id="controler_nowplaying">Nothing playing</span>
<!--end volume controler code -->

</span>	
<a name="top"><div name="spacer" style="margin-top:85px;"/></a>

	<?php
	if ($enableDownloadCart)
	{
	?>

	<div style="width:100%;text-align:right">In your download cart: <a target="blank" onclick="get_player().pause();" href="./view_cart.php"><span id="dlcartcounter"></span> items</a></div>
	<?php
	}
	?>
	<?php
	if ($activatestats){

?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

	ajaxstats();

 //@license-end 
 </script>

<?php
}


 ?>
	
<a><audio id="player" onEnded="playNext();" onError="tryToRecoverPlayerError();">
	Your browser is very old ; sorry but streaming will not be enabled<br/>
</audio></a>

	
	
<?php if (!((true==$embed)||(false!==$embed))) { //IF NOT EMBED ********************* STARTS?>	
	
<?php
echo '<span id="splash-wrapper">';
echo '<span id="splash">';
if (file_exists('./splash.php')){
	include ('./splash.php');
}
echo '</span>';
echo '</span>';
if (isset ($_GET['message'])&&isset($message[$_GET['message']])){
	
	echo '<div style="color:red;background-color:black;width:100%;text-align:center;"><span><strong>'.$message[$_GET['message']].'</strong><a href="./" style="color:black;background-color:red;text-decoration:underline;float:right;text-align:right;">X</a></span></div>';
	
}
echo '<span id="links-wrapper">';
if (count($socialmediaicons)>0){
	//let's display the social media icons
	echo '<hr/><span style="float:left;">Various stuff: ';
	
	foreach ($socialmediaicons as $socialicon){
		echo '<strong><a target="new" href="'.$socialicon['link'].'" style="color:'.$socialicon['color'].';background-color:'.$socialicon['background-color'].';border-radius:3px;">';
		
		echo str_replace(' ', '&nbsp;', htmlspecialchars($socialicon['letter']));
		
		echo '</a></strong> ';
		
		
	}
	echo '</span><hr/>';
}
echo '</span>';
echo '<span id="donate-wrapper">';
if ($acceptdonations){
	echo '<span style="float:right;text-align:right">';
	
	include ('./donate.php');
	
	echo '</span><br style="clear:both;float:none;"/>';
	
}
echo '</span>';	

echo '<span id="radio-wrapper">';
if ($hasradio){
	echo '<div style="width:98%;text-align:right;float:right;"><strong>Now on the radio:</strong> <br/><a id="radio_nowplaying" href="./radio/index.php">';
	
	echo 'Initializing...';
	echo '</a>';
	
	
	echo '</div>';
	
}
echo '</span>';
?>
<div></div>
<span id="recent-wrapper">
<span id="recentplay" style="display:<?php
if ($recentplay){
	echo 'block';
	
}
else {
	echo 'none';
	
}
?>;"><hr/>



<span id="recently_played"><a href="javascript:void(0);" onClick="this.innerHTML='Loading...';displayRecentlyPlayed();">Recently played?</a><br/>

<hr style="float:none;clear:both;"/>
</span>
</span>
</span>
<span id="title-dest"></span>
<br style="float:none;clear:both;"/>


<?php
if (isset ($_GET['track'])) {
	echo '<a href="./">Home</a> &gt; '.htmlspecialchars($_GET['track']).' <em>on</em> '.htmlspecialchars($_GET['album']).'<br/>';
}

else if (isset ($_GET['artist'])) {
	echo '<a href="./">Home</a> &gt; '.htmlspecialchars($_GET['artist']).'<br/>';
}

?>

<span id="menu-wrapper">
<a name="menu"></a><div id="mainmenu" style="display:none;">	
	<span style=""><img style="float:left;width:3%;" src="./<?php echo $favicon ;?>" onload="increment_overload_track_counter();if(!get_page_init()){init_page();};"/></span>
		
	<h1 id="title" style="display:inline;"><?php echo $title; ?></h1>
	<?php if (!isset($_GET['listall'])){
		echo '<a id="listalbums" style="float:right;" href="./?listall=albums'.$arturl.'">List all albums</a><br/>';
		
	}
	?>
	<h2 style="clear:both;"><em><?php echo htmlspecialchars($description);?></em> <br/><a href="./">Home</a></h2>

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
		echo '<span style="margin-top:4px;marging-bottom:4px;"><a style="float:left;padding:2px;" >Artists : </a> ';

		foreach ($artists as $artist) {
			echo '<a style="float:left;border:solid 1px;background-color:#A0A0A0;padding:2px;" onclick="set_page_init(false);arr2=[encodeURIComponent(\''.str_replace("'", "\\'", html_entity_decode($artist)).'\')];update_ajax_body(\'./?artist=\'+encodeURI(JSON.stringify(arr2)));" href="javascript:void(0);">';
			echo htmlspecialchars($artist);
			echo '</a>';
		}
		echo '</span><br style="clear:both;"/>';
	}
?>
</div>

<div><a href="javascript:void(0);" onclick="mainmenu=cr_document_menu_getElementById('mainmenu');if(mainmenu.style.display=='none'){mainmenu.style.display='inline';this.innerHTML='&lt;';}else{mainmenu.style.display='none';this.innerHTML='☰<?php echo str_replace("'", "\\'", htmlspecialchars($title));?>';}">☰<?php echo htmlspecialchars($title);?></a></div>
</span>
<span id="loginpanel" style="float:right;text-align:right;margin-bottom:2%;">
	<?php
		loginpanel($activateaccountcreation);
	?>

	</span><br style="clear:both;"/>
	
<?php

if ($mosaic) {
	//include ('featuredvids.php');
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

}//************************************************IF NOT EMBED ENDS


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
			$artists=explode("\n", trim(html_entity_decode(file_get_contents($clewnapiurl.'?listartists=true'), ENT_COMPAT | ENT_HTML401 )));
			
			
		}
		if (isset($_GET['artist']))
		{
			$artists=array(($_GET['artist']));
			
		}
		$querystring='';
		foreach ($artists as $artist) 
		{
			$querystring.='l2[]='.urlencode($artist).'&';
			
			
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





		//$albums_file=file_get_contents($clewnapiurl.'?'.$querystring, false, stream_context_create($opts));
			
			$fresh=file_get_contents($clewnapiurl.'?freshness=true');
								
			if ($fresh!==false){
				$fresh=floatval($fresh);
				if (floatval($fresh)>=floatval(filemtime('./remoteapicache-v2.dat'))){
							
							
							unlink('./remoteapicache-v2.dat');
					
						}
			    
			
			}
			$cache_dl_count=file_get_contents($server.'/gimme_dl_album_count.php');
			if ($cache_dl_count===false){
				unlink('./remoteapicache-v2.dat');
			}
			else {
				$remoteapicache=unserialize(file_get_contents('./remoteapicache-v2.dat'));
				if (isset($remoteapicache[$querystring])){
					$albums_file=$remoteapicache[$querystring];
					$albums=json_decode($albums_file, true);

					if (!is_array($albums)){$albums=Array();}
					if (count($albums)!=intval($cache_dl_count)){
							unlink('./remoteapicache-v2.dat');
					}
				}
				
				
				
			}
			if (!file_exists('./remoteapicache-v2.dat')){
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL,$clewnapiurl.'?l2=true');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,$timeout); 
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$albums_file_json= curl_exec ($ch);

				curl_close ($ch);
				$albums_file=$albums_file_json;

				
				if ($albums_file_json===false){
				echo '<h2><strong>Sorry ! </strong>It seems that the free albums host, is currently over capacity.</h2>That is why this page took so long to load, and free albums will not display. ';
				
				//20221130 : Autorepair of media tier overcapacity
				?>
					<img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/> An attempt to repair the broken page may be attempted soon. If not, your can try to refresh this page. 
				
				<?php
				
				//Ends 20221130 autorepair of media tier overcapacity
				
				
				
				}
				$cache=Array();
			
			
				$cache[$querystring]=$albums_file_json;
				file_put_contents('./remoteapicache-v2.dat', serialize($cache));

			}
			else {
				$remoteapicache=unserialize(file_get_contents('./remoteapicache-v2.dat'));
				if (isset($remoteapicache[$querystring])){
					$albums_file=$remoteapicache[$querystring];
					
				}
				else {
					
					echo '<h2><strong>Deeply sorry ! </strong>It seems that the host of the free albums, is currently over capacity.</h2>That is why this page took so long to load, and free albums will not display.';
			
				}
			
			}
			$overloadmove=$timeout-2;
		
			
		
		
		
		if ($overloadmove<=0){
			$overloadmove=18;
		}
		
		file_put_contents('./overload.dat', $overloadmove);
			
		$albums=json_decode($albums_file, true);

		if (!is_array($albums)){$albums=Array();}

		ksort($albums);
		$albums=array_reverse($albums);
		
		$content=$albums;
//let's query the local storage
$querystring = '';
		foreach ($artists as $artist) 
		{
			$querystring.='l2[]='.urlencode($artist).'&';
			
			
		}



		$albums_file=file_get_contents($serverapi.'?'.$querystring);

//****************************NEEDS WORK
/*				$timeout=300;
		if (file_exists('./loverload.dat')){
			//$timeout=intval(file_get_contents('./loverload.dat'));
		}
		else {
			file_put_contents('./loverload.dat', '180');
			
		}
		//$opts = array(
		//	'http'=>array(
		//	'timeout'=>$timeout
		//)
		//);

		$overloadmove=$timeout+2;





		//$albums_file=file_get_contents($clewnapiurl.'?'.$querystring, false, stream_context_create($opts));
			
			$fresh=file_get_contents($serverapi.'?freshness=true');
								
			if ($fresh!==false){
				$fresh=floatval($fresh);
				if (floatval($fresh)>=floatval(filemtime('./lremoteapicache-v2.dat'))){
							
							
							unlink('./lremoteapicache-v2.dat');
					
						}
			    
			
			}
			/*$cache_dl_count=file_get_contents($server.'/gimme_str_album_count.php');
			if ($cache_dl_count===false){
				unlink('./lremoteapicache-v2.dat');
			}
			else {*/
			
			/*
				$remoteapicache=unserialize(file_get_contents('./lremoteapicache-v2.dat'));
				if (isset($remoteapicache[$querystring])){
					$albums_file=$remoteapicache[$querystring];
					$albums=json_decode($albums_file, true);

					if (!is_array($albums)){$albums=Array();}
					//if ($albums===false||count($albums)!=intval($cache_dl_count)){
					//		unlink('./lremoteapicache-v2.dat');
					//}
				//}
				
				
				
			}
			if (!file_exists('./lremoteapicache-v2.dat')){
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL,$serverapi.'?l2=true');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,$timeout); 
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$albums_file_json= curl_exec ($ch);

				curl_close ($ch);
				$albums_file=$albums_file_json;

				
				if ($albums_file_json===false){
				echo '<h2><strong>Sorry ! </strong>It seems that the streaming albums host, is currently over capacity.</h2>That is why this page took so long to load, and streaming albums will not display. ';
				
				//20221130 : Autorepair of media tier overcapacity
				?>
					<img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/> An attempt to repair the broken page may be attempted soon. If not, your can try to refresh this page. 
				
				<?php
				
				//Ends 20221130 autorepair of media tier overcapacity
				
				
				
				}
				$cache=Array();
			
			
				$cache[$querystring]=$albums_file_json;
				file_put_contents('./lremoteapicache-v2.dat', serialize($cache));

			}
			else {
				$remoteapicache=unserialize(file_get_contents('./lremoteapicache-v2.dat'));
				if (is_array($remoteapicache)&&isset($remoteapicache[$querystring])){
					$albums_file=$remoteapicache[$querystring];
					
				}
				else {
					
					echo '<h2><strong>Deeply sorry ! </strong>It seems that the host of the streaming albums, is currently over capacity.</h2>That is why this page took so long to load, and streaming albums will not display.';
					?>
					<img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/> An attempt to repair the broken page may be attempted soon. If not, your can try to refresh this page. 
				
					<?php
				}
			
			}
			$overloadmove=$timeout-2;
		
			
		
		
		
		if ($overloadmove<=0){
			$overloadmove=180;
		}
		
		file_put_contents('./loverload.dat', $overloadmove);
			
		//$albums=json_decode($albums_file, true);

		//if (!is_array($albums)){$albums=Array();}


*/
//************************************
		$albums_file=file_get_contents($serverapi.'?'.$querystring);


		$albums=json_decode($albums_file, true);
		if (!is_array($albums)){$albums=Array();
		/*?>	
			<div style="background-color:red;width:100%;"><img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/>
					An error was encountered, due to overcapacity. 
					<?php if ($activatehtmlcache){echo 'Please wait will the website tries to auto-recover...' ; }?>
					</div>
			
		<?php*/	
		}

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
	$embedurl='';
	if (((true==$embed)||(false!==$embed))) {
		$embedurl='&embed='.urlencode($embed);
	}
	
	if ($offset!==0&&!$_SESSION['random']&&!isset($_GET['listall'])){
	
		echo '<a id="dignewer" style="float:right;" href="javascript:void(0);" onclick="try{set_page_init(false);digolder(get_offset()-1);}catch(e){player=get_player();if(player.paused){set_page_init(false);digolder(get_offset()-1);};player.addEventListener(\'pause\', function(){set_page_init(false);digolder(get_offset()-1);});player.addEventListener(\'canplay\', function(){set_page_init(false);digolder(get_offset()-1);});player.addEventListener(\'loadedmetadata\', function(){set_page_init(false);digolder(get_offset()-1);});while(!player.paused){player.pause();};player.addEventListener(\'canplaythrough\', function(){set_page_init(false);digolder(get_offset()-1);});player.addEventListener(\'error\', function(){set_page_init(false);digolder(get_offset()-1);});player.addEventListener(\'abort\', function(){set_page_init(false);digolder(get_offset()-1);})};" name="./?offset='.intval($offset-1).$arturl.$autourl.$embedurl.'">Dig newer...</a><br/>';;
	
	}

}
else {
	$offset=0;
	if (array_key_exists('artist', $_GET)){
		$_GET['offset']=0;
	}
	
}

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

echo '<span id="artists-wrapper">';
if ($mosaic&&$artisthighlighthomepage){
	$flipcoin=true;
	echo '<span style="float:left;position: static; top:Opx;text-align:center;display:inline;">';
	$nobr=true;
	echo '<table><tr>';
	foreach ($hlartists as $hlart){
		echo '<td><span class="colTranslate" style="float:left;width:100%;background-color:white;padding:2%;text-align;center;border:solid 3px; border-radius:8px;"><span style="font-size:120%;"><strong>';
		//echo '<a href="./?artist='.urlencode(htmlentities($hlart['name'])).'">'..'</a><br/>';
		echo '<a onclick="set_page_init(false);arr2=[encodeURIComponent(\''.str_replace("'", "\\'", $hlart['name']).'\')];update_ajax_body(\'./?artist=\'+encodeURI(JSON.stringify(arr2)));" href="javascript:void(0);">';
		echo htmlspecialchars($hlart['name']);
		echo '</a><br/>';
				echo '</strong></span>';
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
echo '</span>';
$highlightcounter=0;


//local *****

foreach ($contentlocal as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0 || isset($_GET['listall'])) {
	$running=true;
	
	if ((isset($_GET['album'])&&$_GET['album']!=html_entity_decode($item['album']))||(isset($_GET['listall'])&&$_GET['listall']==='material')) {
		$running=false;
		
	}
	
	
	
	
	if (isset ($item['album'])&&$running){
		
		if((isset($_GET['album'])||isset($_GET['offset']))&&$albumhasvid==null){
		
			$albumhasvid = checkalbhasvid(html_entity_decode($item['album']), $videoapiurl);
		
		}
		
		
		
		
		
		$weactuallydisplayedsomething=true;
		$ran=true;

		if (!isset($_GET['listall'])&&!$mosaic&&isset($_SESSION['nick'])){
			echo '<h1>Album : ';
			
		}
		else if (!$mosaic){
			echo '<h1>';
		}
		
		
		
		if (!$mosaic) {
			
			if (!((true==$embed)||(false!==$embed))){
				echo '<a onclick="set_page_init(false);arr=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr)));" href="javascript:void(0);">';
			}
			
			echo $item['album'];
			
			
			if (!((true==$embed)||(false!==$embed))){
			
				echo '</a>';
			}
			
			echo '</h1>';
			if (array_key_exists('track', $_GET)){
				echo '<h2>Track: '.htmlspecialchars($_GET['track']).'</h2>';
			}

			
			echo '<div style="margin-left:auto;margin-right:auto;">'.displaycover($item['album'], 0.65).'
			
			<noscript><a href="./?album='.urlencode($item[$album]).'">'.htmlspecialchars($item[$album]).'</a></noscript>
			
			
			</div>';

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
					if (!in_array(html_entity_decode($item['album'], ENT_COMPAT  | ENT_HTML401 ), $coverslines)){
							$displaythatcover=false;
					}
				}
				echo '<span class="lineTranslate" style="border:solid 0px;';
				if ($artisthighlighthomepage){
					if (false)
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
				
					echo '<a href="javascript:set_page_init(false);arr=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr))+\'&autoplay=true\');" title="'.$item['album'].'">';
				}
			
				echo displaycover($item['album'], $streaming_albums_ratio, 'cover', $AlbumsToBeHighlighted, $highlightcounter);
				$highlightcounter++;

		}

		
		if ($mixed||!isset($_GET['listall'])){
			if (!$mixed){
		
				 if (isset($streamingAlbumsInfoHeader[$item['album']])){
					echo '<div style="color:green;background-color:white;border-radius:5px;">';
					echo $streamingAlbumsInfoHeader[$item['album']];
					echo '</div>';
				 }
				
				echo '<div>Controls / tracklisting<br/><span id="tracklist" ';
				if (array_key_exists('offset', $_GET)){
					$_GET['album']=html_entity_decode($item['album']);
				}
				if ((isset($_SESSION['random'])&&$_SESSION['random'])||isset($_GET['autoplay'])&&!isset($_GET['track'])){
					echo 'style="display:inline;"';
				}
				echo '>';
			}
			else {
				echo '<span id="tracklist" style=float:right;">';
			}
			
			
			//here we go, query local API for track list
			$tracks_file=cacheTracklist($item['album'],$serverapi);//.'?gettracks='.urlencode($item['album']));
			if ($tracks_file===false){
				$tracks_file=array();
			}
			$tracks=explode("\n", $tracks_file);
			$trackcounter=0;
			$hasntautoplayed=true;
			$track_artist='';
			$weactuallydisplayedatrack=false;
			$eachtrackwasdisplayedok=true;
			foreach ($tracks as $track) {
			if ($track!==''){
				$weactuallydisplayedatrack=true;
				//we want its name and the artist name as well
				$track_name=trim(file_get_contents($serverapi.'?gettitle='.urlencode($track)));
		
				$track_artist=trim(file_get_contents($serverapi.'?getartist='.urlencode($track)));

			
				if ($track_name==''||(!((true==$embed)||(false!==$embed)))&&$track_artist==''){
					$eachtrackwasdisplayedok=false;
				}

				
				if (!isset($_GET['track'])||$_GET['track']==html_entity_decode($track_name))
				{
					if (in_array(html_entity_decode($track_artist), $artists)){
											?>
					<span onload="if(!get_page_init()){init_page();};increment_overload_track_counter();">* </span> 
					<a href="javascript:void(0);" onClick="set_isplaying(play('<?php echo str_replace ("'", "\\'", htmlspecialchars($track, ENT_COMPAT  | ENT_HTML401 )); ?>', <?php echo $trackcounter; ?>, false));" id="<?php echo $trackcounter; ?>"><span style="font-size:125%;">▶</span></a>
					 
					 <?php if (!((true==$embed)||(false!==$embed))){ ?>
					 
					 
					 <!--<a href="./?artist=<?php echo urlencode (html_entity_decode($track_artist)); ?>">-->
					 <?php
					 echo '<a onclick="set_page_init(false);arr2=[encodeURIComponent(\''.str_replace("'", "\\'", html_entity_decode($track_artist)).'\')];update_ajax_body(\'./?artist=\'+encodeURI(JSON.stringify(arr2)));" href="javascript:void(0);">';
					 ?>
					 
					 <?php } ?>
					 <?php echo  $track_artist; ?>
					 <input type="hidden" id="track_artist<?php echo $trackcounter;?>" value="<?php echo $track_artist;?>"/>
					 <input type="hidden" id="track_name<?php echo $trackcounter;?>" value="<?php echo $track_name;?>"/>
					 <?php if (!((true==$embed)||(false!==$embed))){?></a> - <?php } ?>
					 <?php 
					 if (!((true==$embed)||(false!==$embed))){
					 echo '<a onclick="set_page_init(false);arr3=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($track_name)).'\')];arr=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr))+\'&track=\'+encodeURI(JSON.stringify(arr3)));" href="javascript:void(0);">'; 
					 //echo  '<a href="./?track='.urlencode(html_entity_decode($track_name)).'&album='.urlencode(html_entity_decode($item['album'])).'">';
					 }
					 echo $track_name;
					 if (!((true==$embed)||(false!==$embed))) {
					 echo '</a>'; 
					 
					}
					 ?>
					 <div style="background-color:#F0F0F0;text-align:right;">
						 <?php
						 if (isset($streamingAlbumsInfoNotice[html_entity_decode($item['album'])])){
						 
							echo $streamingAlbumsInfoNotice[html_entity_decode($item['album'])];
						 }
						 else {
						?> 
						 
						 Early access download not available for now
					
					
					
					<?php
							}
					?>
					
					
					<!--<a href="javascript:void(0);" style="text-align:right;float:right;" onclick="infoselected=cr_info_document_getElementById('info<?php echo $trackcounter;?>');loadInfo('<?php echo htmlspecialchars($track);?>');">+</a>-->
							</div>
					<div style="display:none;" id="info<?php echo $trackcounter;?>"></div>
					<?php
					
					
					
					
					
					generatevideo(html_entity_decode($track_name), html_entity_decode($item['album']), html_entity_decode($track_artist), $videoapiurl, $videourl, $albumhasvid);
					showsongsheet($track);
					?>
					
					<?php
					if ($hasntautoplayed){
						?>
						<span id="autoplay" onclick="set_isplaying(autoplay('<?php echo str_replace ("'", "\\'", $track); ?>', <?php echo $trackcounter; ?>, false, true));" ></span>

						<?php
						$hasntautoplayed=false;
					}
				}
			}
			$trackcounter++;
			}
		}
			echo '</span>';

			?>
			<!--Artist site external url should go here **************************************************************-->
			<?php
			if (!$eachtrackwasdisplayedok||!$weactuallydisplayedatrack){
					?>
					<div style="background-color:red;width:100%;"><img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/>
					An error was encountered, due to overcapacity. 
					<?php if ($activatehtmlcache){echo 'Please wait will the website tries to auto-recover...' ; }?>
					</div>
					<?php
				}




			if (!$mixed&&!((true==$embed)||(false!==$embed))&&!$material&&!$mosaic){
				
				echo outputArtistSiteLink($track_artist, $item['album'], $ArtistSites);
				
				
			}
			
			
			
			
			?>			
			<!--End of artist external url-->
			<?php
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
	
	if (isset($_GET['album'])&&$_GET['album']!=html_entity_decode($item['album'])) {
		$running=false;
		
	}
	if (!(in_array($item['artist'], $material_artists)&&!in_array($item['album'], $material_blacklist))&&(isset($_GET['listall'])&&$_GET['listall']==='material'))
	{
		$running=false;
	}
	
	
	
	if (isset ($item['album'])&&$running){
		if((isset($_GET['album'])||isset($_GET['offset']))&&$albumhasvid==null){
		
			$albumhasvid = checkalbhasvid(html_entity_decode($item['album']), $videoapiurl);
		
		}

		
		
		
		
		$weactuallydisplayedsomething=true;
		$ran=true;
		$float=true;


		
		
		
		
		if (!$mosaic) {
			if (!((true==$embed)||(false!==$embed))){
				echo '<a onclick="set_page_init(false);arr=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr)));" href="javascript:void(0);">';
			}
			
			echo '<h1>'.$item['album'].'</h1>';
			if (array_key_exists('track', $_GET)){
				echo '<h2>Track: '.htmlspecialchars($_GET['track']).'</h2>';
			}
			
			if (!((true==$embed)||(false!==$embed))){
			
				echo '</a>';
			}
			echo '<div style="margin-left:auto;margin-right:auto;';
			if ($mixed){
				echo 'float:left;';
			}
			echo '">'.displaycover($item['album'], 0.65).'
					<br/>
					<noscript><a href="./?album='.urlencode($item[$album]).'">'.htmlspecialchars($item[$album]).'</a></noscript>
	
			
			
			</div>';
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
					if (!in_array(html_entity_decode($item['album'], ENT_COMPAT  | ENT_HTML401 ), $coverslines)){
							$displaythatcover=false;
					}
				}
				echo '<span  class="lineTranslate" id="anim'.$animindex.'" style="';
				
				echo 'border:solid 0px;';
				
				if ($artisthighlighthomepage){
					if (false)
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
				echo '<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 var anim=\'anim\'
 //@license-end 
 </script>';
				
				echo '<a href="javascript:set_page_init(false);arr=[encodeURIComponent(\''.str_replace("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr))+\'&autoplay=true\');" title="'.$item['album'].'">';
				echo displaycover($item['album'], 0.1, 'cover', $AlbumsToBeHighlighted, $highlightcounter);
				$highlightcounter++;

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

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'item['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				
 //@license-end 
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

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'option['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				
 //@license-end 
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
			echo 'available on material support at our <a target="_blank" href="//'.$server.'/?listall=material">physical releases shop</a><br/>';
			
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
					echo '<div id="ajax_splash" style="z-index:126;width:100%;height:100%;display:none;background-color:white;"><span id="ajax_splash_message"></span><br/><a href="javascript:void(0);" style="text-align:right;width:100%;" onclick="cr_c_document_getElementById(\'ajax_splash\').style.display=\'none\';">X Close</a></div>';
					echo '<a href="javascript:void(0);" onclick="addFullAlbumToCart(\''.str_replace("'", "\\'", urlencode($item['album'])).'\',this);">Download full album</a><br/>';	
					
				}
					
					
				
				
				
				
				
				
				
				echo '<div>Controls / tracklisting</div><span id="tracklist" ';
				if(array_key_exists('offset', $_GET)){
					$_GET['album']=html_entity_decode($item['album']);
				}
			
				if ((isset($_SESSION['random'])&&$_SESSION['random'])||isset($_GET['autoplay'])&&isset($_GET['track'])){
					echo 'style="display:inline;"';
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
			//$tracks_file=file_get_contents($clewnapiurl.'?gettracks='.urlencode($item['album']));
			$tracks_file=cacheTracklist($item['album'],$clewnapiurl);//.'?gettracks='.urlencode($item['album']));
			if ($tracks_file===false){
				$tracks_file="\n";
			}
			$tracks=explode("\n", $tracks_file);
			$trackcounter=0;
			$hasntautoplayed=true;
			$track_artist='';
			$weactuallydisplayedatrack=false;
			$eachtrackwasdisplayedok=true;
			foreach ($tracks as $track) {
				if ($track!==''){
					$weactuallydisplayedatrack=true;
					//we want its name and the artist name as well
					$track_name=trim(file_get_contents($clewnapiurl.'?gettitle='.urlencode($track)));
			
					$track_artist=trim(file_get_contents($clewnapiurl.'?getartist='.urlencode($track)));
					
					if ($track_name==''||((!((true==$embed)||(false!==$embed)))&&$track_artist=='')){
						$eachtrackwasdisplayedok=false;
					}
					
					
					
					if ($enableDownloadCart&&in_array(html_entity_decode($track_artist), $artists))
					{
						$mypair=array();
						
						$mypair['title']=$track_name;
						$mypair['file_basename']=$track;
						
						
						
						array_push($_SESSION['album_tracklisting'][$item['album']],$mypair );
						
						
						
					}
					
					
					
					if (!isset($_GET['track'])||$_GET['track']==html_entity_decode($track_name))
					{
						if (in_array(html_entity_decode($track_artist), $artists)){
						?>
						<span onload="if(!get_page_init()){init_page();};increment_overload_track_counter();">* </span> 
						<a href="javascript:void(0);" onClick="set_isplaying(play('<?php echo str_replace ("'", "\\'", htmlspecialchars($track, ENT_COMPAT  | ENT_HTML401 )); ?>', <?php echo $trackcounter; ?>, true));" id="<?php echo $trackcounter; ?>">▶</a>
						 <?php if (!((true==$embed)||(false!==$embed))) {?>
							 
							<!-- <a href="./?artist=<?php echo urlencode (html_entity_decode($track_artist)); ?>">-->
							<?php
					 echo '<a onclick="set_page_init(false);arr2=[encodeURIComponent(\''.str_replace("'", "\\'", html_entity_decode($track_artist)).'\')];update_ajax_body(\'./?artist=\'+encodeURI(JSON.stringify(arr2)));" href="javascript:void(0);">';
					 ?>
					 
						 <?php echo  $track_artist; ?></a> - <?php } ?>
						 <?php 
						 if (!((true==$embed)||(false!==$embed))){
						 echo '<a onclick="set_page_init(false);arr3=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($track_name)).'\')];arr=[encodeURIComponent(\''.str_replace ("'", "\\'", html_entity_decode($item['album'])).'\')];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr))+\'&track=\'+encodeURI(JSON.stringify(arr3)));" href="javascript:void(0);">'; 

						 //echo  '<a href="./?track='.urlencode(html_entity_decode($track_name)).'&album='.urlencode(html_entity_decode($item['album'])).'">';
						}
						 
						 echo $track_name;
						 ?>
						 <input type="hidden" id="track_artist<?php echo $trackcounter;?>" value="<?php echo $track_artist;?>"/>
						 <input type="hidden" id="track_name<?php echo $trackcounter;?>" value="<?php echo $track_name;?>"/>
						 <?php
						 if (!((true==$embed)||(false!==$embed))){
						 
						 echo '</a>';
						
						}
						
						
						?>
						<div style="display:none;" id="info<?php echo $trackcounter;?>"></div>
						<?php
						 if (!$mixed&&!$enableDownloadCart){
								
							 ?>
							 <div style="background-color:#F0F0F0;text-align:right;">Download 
							 
							 <?php
							 foreach ($supported_formats_remote as $mat){
								if (trim($mat)!==''){
							 
								 ?>
								  [<a download="<?php echo htmlspecialchars($track.'.'.$mat);?>" target="_blank" href="<?php 
								 echo $clewnaudiourl.urlencode ($track).'.'.$mat.'">'.htmlspecialchars($mat); 
								 ?></a>]  
								 <?php
									}
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
							 echo str_replace("'", "\\'", urlencode($track));?>', '<?php 
							 echo str_replace("'", "\\'", urlencode ($track_artist));?>' ,this);">Download track</a> 
							 
							 <?php
								}
						if (!$mixed){
							?>
							 -<a href="javascript:void(0);" 
							 style="text-align:right;float:right;" 
							 onclick="infoselected=cr_info_document_getElementById('info<?php echo $trackcounter;?>');loadInfo('<?php echo str_replace ("'", "\\'", htmlspecialchars($track, ENT_COMPAT | ENT_HTML401 ))?>');">|info+|</a>
							 
							 </div>
							<?php
							generatevideo(html_entity_decode($track_name), html_entity_decode($item['album']), html_entity_decode($track_artist), $videoapiurl, $videourl, $albumhasvid);
							showsongsheet($track);
							?>
							<?php
							
						}
						else {
							
							echo '<br/>';
							
						}
						if ($hasntautoplayed){
							?>
							
							<span id="autoplay" onclick="set_isplaying(autoplay('<?php echo str_replace ("'", "\\'", $track); ?>', <?php echo $trackcounter; ?>, true, true));" ></span>

							<?php
							$hasntautoplayed=false;
						}
					}
				}
				if (!$eachtrackwasdisplayedok||!$weactuallydisplayedatrack){
					?>
									<?php
				}


				$trackcounter++;
				}
			
			}
			echo '</span>';
			
			?>
			<!--Artist site external url should go here **************************************************************-->
			<?php
			if (!$eachtrackwasdisplayedok||!$weactuallydisplayedatrack){
					?>
					<div style="background-color:red;width:100%;"><img src="favicon.png" onload="if(!get_page_init()){init_page();};<?php if ($activatehtmlcache){echo 'set_album_error(true);checkOverload(false);';} ?>"/>
					An error was encountered, due to overcapacity. 
					<?php if ($activatehtmlcache){echo 'Please wait will the website tries to auto-recover...';} ?>
					</div>
					<?php
				}

			
			
			
			
			
			
			
			
			if (!$mixed&&!((true==$embed)||(false!==$embed))&&!$material&&!$mosaic){
				
				echo outputArtistSiteLink($track_artist, $item['album'], $ArtistSites);
				
				
			}
			
			
			
			
			?>			
			<!--End of artist external url-->
			<?php
			
			if ($mixed) {
				?><script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 document.getElementById('mixedtracks').innerHTML=document.getElementById('tracklist').innerHTML;
						  document.getElementById('tracklist').style.display='none';
				
 //@license-end 
 </script>
				<?php
				echo '';
				echo '</form>';
				echo '<hr/';
				echo '<div style="float:none;clear:both;"></div>';
				?>
								<h3></h3>Tip download :</h3> 
				<em>Name your price and get instant download access to <strong><?php echo $item['album'];?></strong> in available formats</em><span style="font-size:84%;">
				
				<form  name="_xclick" action="https://www.paypal.com/fr/cgi-bin/webscr" method="post" >
				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="business" value="<?php echo htmlspecialchars($material_paypal_address);?>" />
				<input type="hidden" name="item_name" value="Tip download for album <?php echo str_replace('"', '', $item['album'])?>" />
				<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($material_currency);?>" />
				<br/>I want to pay <?php echo htmlspecialchars($material_currency);?> <input type="text" size="4" name="amount" value="2.50" />
				<input type="hidden" name="shipping" value="0" />
				<input type="hidden" name="cancel_return" value="http://<?php echo htmlspecialchars($server);?>" />
				<input type="hidden" name="return" value="http://<?php echo htmlspecialchars($server);?>/?album=<?php echo urlencode(($_GET['album']));?>" />
				<input type="submit" name="submit" value="Buy now !" /> or <a href="//<?php echo $server?>/?album=<?php echo urlencode(($_GET['album']));?>">get it for free</a></span>
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

if (!isset($_GET['listall'])&&isset($_GET['album'])&&file_exists('./album_sleeve.php')){
	require_once ('./album_sleeve.php');

}


if (!$_SESSION['random']&&$weactuallydisplayedsomething&&!isset($_GET['listall'])){
	$autourl='';
	if (false&&isset($_GET['autoplay'])&&boolval($_GET['autoplay'])){//this case is managed by some other code place
		$autourl='&autoplay=true';
	}
	if (!isset($_GET['target_album'])){
	
	$embedurl='';
	
	if (((true==$embed)||(false!==$embed))) {

		
		
		
		$embedurl='&embed='.urlencode($embed);
	}
	/* <a href="javascript:set_page_init(false);arr=[\''.urlencode(html_entity_decode($item['album'])).'\'];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr)));" */
	?>

	<?php
	echo '<a id="digolder" style="float:right;" href="javascript:void(0);" onclick="try{set_page_init(false);digolder(get_offset()+1);}catch(e){player=get_player();if(player.paused){set_page_init(false);digolder(get_offset()+1);};player.addEventListener(\'ended\', function(){set_page_init(false);digolder(get_offset()+1);});player.addEventListener(\'loadedmetadata\', function(){set_page_init(false);digolder(get_offset()+1);});player.addEventListener(\'pause\', function(){set_page_init(false);digolder(get_offset()+1);});player.addEventListener(\'canplay\', function(){set_page_init(false);digolder(get_offset()+1);});while(!player.paused){player.pause();};player.addEventListener(\'canplaythrough\', function(){set_page_init(false);digolder(get_offset()+1);});player.addEventListener(\'error\', function(){set_page_init(false);digolder(get_offset()+1);});player.addEventListener(\'abort\', function(){set_page_init(false);digolder(get_offset()+1);});}" name="./?offset='.intval($offset+1).$arturl.$autourl.$embedurl.'">Dig older...</a><br/>';
	
	}
	else {
	//Never used
	echo '<a id="digolder" style="float:right;" href="javascript:void(0);" name="./?album='.urlencode(($_GET['target_album'])).$arturl.$autourl.'">Dig more...</a><br/>';
	
	}
}
if (!$weactuallydisplayedsomething){
	/****
	 * 
	 * 	if (document.getElementById('infiniteloop')!=null){
		if (document.getElementById('bodyajax_arttruc').value!=''){
			set_artist(document.getElementById('bodyajax_arttruc').value);
			}
		page_init=true;
		document.getElementById('infiniteloop').click();
	 **********************************************************/
	unset($_GET['album']);
	unset($_GET['artist']);
	unset($_GET['offset']);
	$_GET['listall']='failed';
	
	
	echo '<img src="favicon.png" onload="increment_overload_track_counter();if(!get_page_init()){init_page();};"/> <a  href="javascript:void(0);" onclick="if(get_album_error()!=undefined&&get_album_error()!=false){if (document.getElementById(\'bodyajax_arttruc\').value!=\'\'){set_artist(document.getElementById(\'bodyajax_arttruc\').value);};set_page_init(false);digolder(0, true);};" id="infiniteloop">Yeah! You reached the bottom... There is nothing older...Continuing to newer</a><br/>';
	}
	

echo '</div>';
if($material) {
	
			
			
	echo '</form><br style="clear:both;"/>';
	echo $materialreleasessalesagreement.'<br style="clear:both;"/>';
}
if ($mosaic) {
	echo '<br style="clear:both;"/>';
}
if (!((true==$embed)||(false!==$embed))){// IF NOT EMBED STARTS **************************************************
echo $pageFooterSplash;
?>
<span id="splash-dest" style="margin-left:6%;padding-left:6%;"></span>
<span id="donate-dest"></span>
<span id="radio-dest"></span>
<span id="recent-dest"></span>
<span id="menu-dest"></span>
<span id="links-dest"></span>
<span id="artists-dest" style="margin-right:6%;"></span>
<?php if($RandomPlayer||$Podcast){
	echo 'need more? <ul>';
}?>
<?php if ($RandomPlayer){
	echo '<li><a href="./random/">The Great Random Player</a></li>';
	
}?>

<?php if ($Podcast){
	$protocol='http://';
	if (isset($_SERVER['HTTPS'])){
		$protocol='https://';
	}
	
	echo '<li><a href="javascript:void(0)" onclick="navigator.clipboard.writeText(\''.$protocol.$server.'/rss'.'\');this.nextElementSibling.innerHTML=\' <strong>Link copied to clipboard!</strong>\';">General podcast</a><em> (click to copy to clipboard)</em></li>';
	echo '<li>';
	echo 'Artist podcasts: <em>(click to copy to clipboard)<em><span>';
	foreach ($artists as $a){
		echo '[<a href="javascript:void(0)" onclick="navigator.clipboard.writeText(\''.$protocol.$server.'/rss/?artist='.urlencode($a).'\');alert(\'Link copied to clipboard!\');">'.str_replace(" ", "&nbsp;", htmlspecialchars($a)).'</a>] ';
	}
	echo '</span></li>';
}?>
<?php if($RandomPlayer||$Podcast){
	echo '</ul>';
}?>
<hr/>

<a href="javascript:void(0);" style="border:solid 1px;" onclick="bottommenu=cr_document_menu_getElementById('bottommenu');if(bottommenu.style.display=='none'){bottommenu.style.display='inline';this.innerHTML='&lt;';}else{bottommenu.style.display='none';this.innerHTML='<?php echo str_replace("'", "\\'", htmlspecialchars($footerReadableName));?>';}"><?php echo htmlspecialchars($footerReadableName);?></a>
<a name="bottommenu"></a>


<div style="display:none;" id="bottommenu">

<?php
echo $footerhtmlcode;
echo '</div>';?>
<span id="noscripters_footer" onload="if(!get_page_init()){init_page();}?>">
<noscript> <a href="./?noscript=footer"><?php echo htmlspecialchars($footerReadableName);?> for noscripters</a></noscript>
</span>



<?php

}// IF NOT EMBED ENDS ** ** *  * ** * ** * * *** * *
?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
var album_counter=<?php echo $counter;?>;

// @license-end
</script>
<?php 

if ($activatehtmlcache){
?>

<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
//var album_counter=<?php echo $secondcounter;?>;


function checkOverload(allowRecursive){
   if (page_init){
	if (allowRecursive){
		var dl_queried=-1;
		var str_queried=-1;
		
		  dlwwwxhttprecalb = new XMLHttpRequest();
		  dlwwwxhttprecalb.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 dl_queried = parseInt(this.responseText);
			
			 if (Number.isInteger(parseInt(overload_track_counter))&&(dl_queried!=parseInt(-1))&&(parseInt(get_dl_album_count())!=dl_queried)){
					set_album_error(true);
					
					set_dl_album_count(dl_queried);
					
					checkOverload(false);
				}
			
			
			}
		  };
		  dlwwwxhttprecalb.open("GET", "gimme_dl_album_count.php", true);
		  dlwwwxhttprecalb.send();
		  
		  
		  
		  
		  strwwwxhttprecalb = new XMLHttpRequest();
		  strwwwxhttprecalb.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 str_queried = parseInt(this.responseText);
		
			 if (Number.isInteger(overload_track_counter)&&(str_queried!=parseInt(-1))&&(parseInt(get_str_album_count())!=str_queried)){
					
					set_album_error(true);
					
					set_str_album_count(str_queried);
					
					checkOverload(false);
				}
		
		
		
			}
		  };
		  strwwwxhttprecalb.open("GET", "gimme_str_album_count.php", true);
		  strwwwxhttprecalb.send();
		
		  //set_overloadindexchecked(true);
		
	}
	
	if (get_album_error()==true){
		set_overload_track_counter(parseInt(0));
	}
	
	
	if (get_overload_track_counter()==0){
		if (get_player()!=null){get_player().pause();}
		document.getElementById('overload_splash').style.display='block';
		document.getElementById('overload_form').action='./';
		document.getElementById('overload_button').innerHTML='<input type="submit" value="Try now"></input>';
		var overloadtimer;
		
		set_overloadtimer(window.setInterval(function(){
			seconds=parseInt(document.getElementById('overload_countdown_seconds').innerHTML);
			seconds--;
			document.getElementById('overload_countdown_seconds').innerHTML=seconds;
			if (seconds<=0){
					cr_window_overloadtime();
					document.getElementById('overload_form').submit();
				
			}
		},1000));
		
	}
  }
}


 //@license-end 
 </script>

<?php

}

if (!((true==$embed)||(false!==$embed))){ //IF NOT EMBED STARTS ******************************************
?>

<hr style="clear:both;">
<?php
if (!$activatechat===false){
?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later


 //@license-end 
 </script>


		<a name="social"><object onload="if (!nosocialupdate){updateSocialData(this);nosocialupdate=true;}" data="./?void=void" style="width:100%;height:495px;" width="100%" height="495"></object></a>
		
<?php
}


if (count($creroypservices)>0) { ?>
<div>YellowPages services in use: 
<span id="yp-services-content">Loading <noscript>... If you enable Javascript</noscript></span>
</div>
<?php } //end of YP Services infos
} //IF NOT EMBED ENDS * * ** * * * * ** * * * * ** * * * * ** * * ?>
<input type="hidden" id="isindex" value="<?php
	if (($mosaic&&(!(array_key_exists('artist',$_GET)||array_key_exists('track',$_GET)||array_key_exists('album',$_GET))))
		){
		echo 'true';
	}
	else {
		echo 'false';
	}
	?>"/>
<input type="hidden" id="offset" value="<?php
	if (array_key_exists('offset', $_GET)){
		echo intval($_GET['offset']);
	}
	else {
		echo intval(-1);
	}
	?>"/>
<input type="hidden" id="album" value="<?php
	if (array_key_exists('album', $_GET)){
		echo htmlspecialchars(($_GET['album']));
	}
	
	?>"/>
<input type="hidden" id="dl_album_count" value="<?php require('./gimme_dl_album_count.php'); ?>"/>
<input  type="hidden" id="str_album_count" value="<?php require('./gimme_str_album_count.php'); ?>"/>

<input  type="hidden" id="site_name" value="<?php echo htmlspecialchars($sitename); ?>"/>
<input  type="hidden" id="site_title" value="<?php echo htmlspecialchars($title); ?>"/>
<input  type="hidden" id="site_description" value="<?php echo htmlspecialchars($description); ?>"/>





<input type="hidden" id="artist" value="<?php
	if (array_key_exists('artist', $_GET)){
		echo htmlspecialchars(($_GET['artist']));
	}
	
	?>"/>
<input type="hidden" id="track" value="<?php
	if (array_key_exists('track', $_GET)){
		echo htmlspecialchars(($_GET['track']));
	}
	
	?>"/>
<input type="hidden" id="embed" value="<?php
	if ((true==$embed)||(false!==$embed)){
		echo 'true';
	}
	else {
		echo 'false';
	}
	?>"/>
<input type="hidden" id="embed_value" value="<?php
	if ((true==$embed)||(false!==$embed)){
		echo htmlspecialchars($embed);
	}
	else {
		echo '';
	}
	?>"/>	
<input type="hidden" id="recentplay_pass" value="<?php
	if ($recentplay==true){
		echo 'true';
	}
	else if ($recentplay==false) {
		echo 'false';
	}
	?>"/>
<input type="hidden" id="artisthighlighthomepage" value="<?php
	if ($artisthighlighthomepage){
		echo 'true';
	}
	else {
		echo 'false';
	}
	?>"/>	
<input type="hidden" id="creroypservices" value="<?php
		echo count($creroypservices);
	
	?>"/>		
	
<div style="float:left;font-size:76%;">Powered by <a href="https://crero.clewn.org" title="CreRo, the open-source CMS for record labels and webradios">CreRo, the CMS for record labels and webradios</a> - AGPL licensed - <a href="http://github.com/shangril/crero">code repo</a></div>


<?php  ?>
<br/><a style="float:right;font-size:76%;" href="./about-js.html" data-jslicense="1" target="_blank">JavaScript license information</a>



<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

if(!get_page_init()){init_page();}

// @license-end 

</script>


<?php if (!(array_key_exists('body', $_GET)&&$_GET['body']=='ajax')){
echo '</body>
</html>';
}


//oh, did we generate an album page (caching enabled or not) ?
//and was it a valid album which displayed a tracklist ?
//if so, let's store it in the list of currently valid albums
//providing we want a RecentPlay feature
	
if (isset($_GET['album'])&&$weactuallydisplayedsomething&&$recentplay){	
	$recentalbums= Array();  

	if (file_exists('./d/recently_generated_albums.dat')){
		$recentalbums=unserialize(file_get_contents('./d/recently_generated_albums.dat'));
		
	}
	$recentalbumsbkp=$recentalbums;
	$recentalbums[$_GET['album']]=$_GET['album'];
	if(!in_array($_GET['album'], $recentalbumsbkp)){//only useful to write to disk if the array has changed
		file_put_contents('./d/recently_generated_albums.dat', serialize($recentalbums));
	}
	

}


if ($activatehtmlcache){


	if ($willhavetocache){
			$htmlcode=ob_get_contents();
			if ($htmlcode!==false){
				$myhtmlcache->cachePage($cachingkey, $htmlcode);
		}
	}

}
}//TRY initial
catch (SystemExit $e) { /* do nothing */ }
?>
