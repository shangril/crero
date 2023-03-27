<?php
error_reporting(0);

if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
//We've nothing to say to most impolite bots

	header('Content-Type: audio/mpeg');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: no-cache');
    header('icy-name: '.str_replace("\r\n", ' ', $radioname));
    header('icy-description: '.str_replace("\r\n", ' ', $radiodescription));
	header('icy-url: http://'.$server.'/radio');
    


header('Accept-Ranges: none');
header('Content-type: audio/mpeg');


session_start();

if ($_SESSION['../d/expires.txt']>microtime(true)){
	$_SESSION['../d/expires.txt']=0;
}


function session_put_contents($key, $value){
	$_SESSION[$key]=$value;
}

function session_get_contents($key){
	if (array_key_exists($key, $_SESSION)) {return $_SESSION[$key];}
		else
	{return false;}
}
function session_exists($key){
	if (array_key_exists($key, $_SESSION)){
			return true;
	}
	else {
		return false;
	}
}
function session_unlink($key){
	unset($_SESSION[$key]);
}

chdir('../..');
require_once('./config.php');
chdir('./radio/stream.mp3');
srand();
$statid=mt_rand(0, 1000000).microtime(false);
$dontdoit=false;

if ($autobuildradiobase){

	$linez=explode("\n",  session_get_contents($clewnapiurl.'?listfiles=true'));
	
	$a2='';
	
	foreach ($linez as $line){
		$a2.=$clewnaudiourl.rawurlencode($line)."\n";
	}
	session_put_contents('../../d/radioBase.txt',$a2);
}


if (!session_exists('../d/featuredapitime.txt')){
	session_put_contents('../d/featuredapitime.txt', 0);
}
if (!session_exists('../d/mediafetch-0.txt')){
	session_put_contents('../d/mediafetch-0.txt', 0);
}
if (!session_exists('../d/mediafetch-1.txt')){
	session_put_contents('../d/mediafetch-1.txt', 0);
}



if (!session_exists('../d/baseapitime.txt')){
	session_put_contents('../d/baseapitime.txt', 0);
}


if ($radiohasyp&&!session_exists('../d/ypexpires.txt')){
	session_put_contents('../d/ypexpires.txt', '0');
}

    
    

function dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $duration){
	$inittime=microtime(true);
	$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
	foreach ($listeners as $listener){
		if ((session_get_contents('../d/listeners/'.$listener))==$statid){
				session_unlink('../d/listeners/'.$listener);
			
		}
		if(floatval($listener)+30>(microtime(true))){
				//ghost listener since more than 30 seconds, maybe ?
				//let's delete it
				session_unlink('../d/listeners/'.$listener);
		}
	}
	session_put_contents('../d/listeners/'.microtime(true), $statid);
	if (!session_exists('../d/maxlisteners.txt')){
		session_put_contents('../d/maxlisteners.txt', '0');
	}
	
	$listeners=count(array_diff(scandir('../d/listeners'), Array ('.', '..')));
	
	if ($listeners>intval(session_get_contents('../d/maxlisteners.txt'))){
		session_put_contents('../d/maxlisteners.txt', $listeners);
	}
	if ((floatval(filectime('../d/maxlisteners24hours.txt'))+24*60*60)<=microtime(true)){
	
		session_unlink('../d/maxlisteners24hours.txt');
	
	}
	if ($listeners>intval(session_get_contents('../d/maxlisteners24hours.txt'))){
		session_put_contents('../d/maxlisteners24hours.txt', $listeners);
	}


	
	return microtime(true)-$inittime;
}


function play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial, $dontdoit, $IsRadioStreamHTTPS, $isfirstpass){

if (session_exists('../d/lock.txt')&&(microtime(true)-floatval(session_get_contents('../d/lock.txt'))>120)){
	session_unlink('../d/lock.txt');
}

$radiofeatured=file_get_contents('../../d/radioFeatured.txt');
$radiobase=file_get_contents('../../d/radioBase.txt');


$nowplayingduration=intval(session_get_contents('../d/nowplayingduration.txt'));
$nowplayingurl=session_get_contents('../d/nowplayingurl.txt');
$nowplayingalbum=session_get_contents('../d/nowplayingalbum.txt');
$nowplayingtitle=session_get_contents('../d/nowplayingtitle.txt');
$nowplayingartist=session_get_contents('../d/nowplayingartist.txt');
$expire=null?0:floatval(session_get_contents('../d/expire.txt'));
$nowplayingbitrate=intval(session_get_contents('../d/nowplayingbitrate.txt'));
$starttime=intval(session_get_contents('../d/starttime.txt'));


$nexturl=$nowplayingurl;
$nextduration=$nowplayingduration;
$nextalbum=$nowplayingalbum;
$nextartist=$nowplayingartist;
$nextbitrate=$nowplayingbitrate;
$nexttitle=$nowplayingtitle;

while (session_exists('../d/lock.txt')){
		
	$silenttimer=microtime(true);

	fpassthru('../silence.mp3');
	ob_flush();
	flush();
	usleep (round(1000000*(0.052-(microtime(true)-$silenttimer))));

}

if (microtime(true)>=$expire&&(!session_exists('../d/lock.txt'))){
	
	$nextartist='';
	
		if (strlen($nextartist)===0){
		
		
		session_put_contents('../d/lock.txt', microtime(true));
		$apitimestart=microtime(true);
		$featuredapi=false;
		
		
		
		
		$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
		foreach ($listeners as $listener){
			if (intval(session_get_contents('../d/listeners/'.$listener))!==$statid){
					session_unlink('../d/listeners/'.$listener);
				
			}
		}
		
		$isthislistenercounted=false;
		foreach ($listeners as $listener){
			if (intval(session_get_contents('../d/listeners/'.$listener))===$statid){
					$isthislistenercounted=true;
				
			}
		}
		if (!$isthislistenercounted){
			session_put_contents('../d/listeners/'.microtime(true), $statid);
		}
		
		unset($_SESSION ['streamhit']);
		
		
		
		$dice=random_int(1,10);
		if ($dice==1){
			session_put_contents('../d/is_featured.txt', '1');
			$featuredapi=true;
			if (!$isinitial){
				$loop=ceil(floatval(session_get_contents('../d/featuredapitime.txt'))/0.052);
				$silent='';
				$silentfile=file_get_contents('../silence.mp3');
				for ($i=0;$i<$loop;$i++){
					$silent.=$silentfile;
					
				}
				echo $silent;
				ob_flush();
				flush();
			}
			
			$featured=explode("\n", trim($radiofeatured));
			shuffle($featured);
			$thisfeatured = $featured[random_int(0, count($featured) - 1)];
			$featuredbasenamed=explode('/', $thisfeatured);
			$featuredbasename=array_pop($featuredbasenamed);
			$apihook=str_replace($featuredbasename, '', $thisfeatured);
			$apihook=str_replace('/z/', '/api.php', $apihook);
			$apihook=str_replace('/audio/', '/api.php', $apihook);
			
			$apihook.='?radio='.urlencode($featuredbasename);
			$apirequest=file_get_contents($apihook);
			$result=explode("\n", $apirequest);

			if (count($result)>0){
				$result=explode("\n", $apirequest);
				
				$nexturl=$thisfeatured;
				$nextartist=$result[0];
				$nextalbum=$result[1];
				$nexttitle=$result[2];
				$nextduration=$result[3];
				$nextbitrate=$result[4];
				session_put_contents('../d/nowplayingisfeatured.txt', '0');
				session_put_contents('../d/starttime.txt', microtime(true));
				session_put_contents('../d/license.txt', '');
			}
		}
		else {
			session_put_contents('../d/is_featured.txt', '0');
			if (!$isinitial){
				$loop=ceil(floatval(session_get_contents('../d/baseapitime.txt'))/0.052);
				$silent='';
				$silentfile=file_get_contents('../silence.mp3');
				for ($i=0;$i<$loop;$i++){
					$silent.=$silentfile;
					
				}
				echo $silent;
				ob_flush();
				flush();
			}
			$alreadyPlayed=Array();
			if (session_exists('../d/already_played.dat')){
				$aldat=session_get_contents('../d/already_played.dat');
				if ($aldat!==false){
					$alreadyPlayed=unserialize($aldat);
				}
				else {
					session_unlink('../d/already_played.dat');
					session_put_contents('../d/already_played.dat', serialize($alreadyPlayed));
				}
			}
			else {
				session_put_contents('../d/already_played.dat', serialize($alreadyPlayed));
			}
			if (!is_array($alreadyPlayed)){
				$alreadyPlayed=Array();
				session_unlink('../d/already_played.dat');
			}
			
			
			$featured=explode("\n", trim($radiobase));
			
			$tempPlay=array_diff($featured, $alreadyPlayed);
			
			if (count($tempPlay)==0){
				$tempPlay=$featured;
				session_unlink('../d/already_played.dat');
			}
			$featured=$tempPlay;
			
			shuffle($featured);
			$thisfeatured = $featured[random_int(0, count($featured) - 1)];
			
			array_push($alreadyPlayed, $thisfeatured);
			session_put_contents('../d/already_played.dat', serialize($alreadyPlayed));
			
			
			$featuredbasenamed=explode('/', $thisfeatured);
			$featuredbasename=array_pop($featuredbasenamed);
			
			$apihook=str_replace($featuredbasename, '', $thisfeatured);
			$apihook=str_replace('/audio/', '/api.php', $apihook);
			$apihook=str_replace('/z/', '/api.php', $apihook);
			
			$apibase=$apihook;
			
			$apihook.='?radio='.urlencode($featuredbasename);
			$apirequest=file_get_contents($apihook);
			$result=explode("\n", $apirequest);
			
			$nexturl=$thisfeatured;
			$nextartist=$result[0];
			$nextalbum=$result[1];
			$nexttitle=$result[2];
			$nextduration=$result[3];
			$nextbitrate=$result[4];
			
			session_put_contents('../d/license.txt', file_get_contents($apibase.'?getinfo='.urlencode(str_replace('.mp3', '', $featuredbasename))));
			
			
			session_put_contents('../d/nowplayingisfeatured.txt', '1');
			session_put_contents('../d/starttime.txt', microtime(true));
			}
		}
$nowplayingduration=$nextduration;

session_put_contents('../d/nowplayingduration.txt', $nowplayingduration);
$nowplayingurl=$nexturl;
session_put_contents('../d/nowplayingurl.txt',$nowplayingurl);
$nowplayingalbum=$nextalbum;
session_put_contents('../d/nowplayingalbum.txt', $nowplayingalbum);
$nowplayingtitle=$nexttitle;
session_put_contents('../d/nowplayingtitle.txt',$nowplayingtitle);
$nowplayingartist=$nextartist;
session_put_contents('../d/nowplayingartist.txt', $nowplayingartist);
$expire=floatval(microtime(true))+floatval($nextduration);
session_put_contents('../d/expire.txt', $expire);
$nowplayingbitrate=$nextbitrate;
session_put_contents('../d/nowplayingbitrate.txt',$nowplayingbitrate);

session_unlink('../d/lock.txt');
/*
if(false&&(!isset($nowplayingartist) || trim($nowplayingartist)==='')&&$autodeleteuntagguedtracks){
			$basename=array_reverse(explode('/', $nowplayingurl))[0];
			file_put_contents($autodeleteprefixpath.$basename, session_get_contents('../silence.mp3'));
			session_put_contents('../d/expire.txt', '0');
			fpassthru('../silence.mp3');
			if (!isset($_GET['web'])){
					$isinitial=false;
					play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial);
				}
				else {
					ob_flush();
					exit();
				}
			
			}*/
//	else{


///***yp stuff
	if(false&&radiohasyp&&floatval(trim(session_get_contents('../d/ypexpires.txt')))<microtime(true)){
		session_unlink('../d/ypsid.txt');
	}

	if (false&&$radiohasyp){//&&floatval(trim(session_get_contents('../d/ypexpires.txt')))<microtime(true)){
		$genres='';
		foreach ($labelgenres as $labelgenre){
			$genres.=$labelgenre.' ';
		}
		$genres=trim($genres);
		
		
		$streamProtocol='http';
		
		if ($IsRadioStreamHTTPS){
			$streamProtocol='https';
		}
		
		$nowplaying=html_entity_decode(session_get_contents('../d/nowplayingartist.txt').' - '.session_get_contents('../d/nowplayingtitle.txt'));
		
		$listenerscount=count(array_diff(scandir('./d/listeners'), Array ('.', '..')));
		
		$postdata = http_build_query(
			array(
				'action' => 'add',
				'sn' => $radioname, 
				'type' => 'audio/mpeg', 
				'genre' => $genres, 
				'b' => session_get_contents('../d/nowplayingbitrate.txt'), 
				'listenurl' => $streamProtocol.'://'.$server.'/radio/stream.mp3', 
				'desc' => $radiodescription, 
				'url' => $streamProtocol.'://'.$server.'/radio'
			)
		);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => Array('Content-type: application/x-www-form-urlencoded', 'User-Agent: CreRo'),
				'content' => $postdata
			)
		);

		$context = stream_context_create($opts);
		$save=false;

		$ttl=0;
		$sid="0";

			
		if($handler=fopen('https://dir.xiph.org/cgi-bin/yp-cgi', 'rb', false, $context)){
			
			$meta_data = stream_get_meta_data($handler);
			foreach ($meta_data['wrapper_data'] as $response) {


				if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
					$save = boolval(substr($response, 12));
					session_put_contents('../d/ypresp.txt', substr($response, 12));
				}
				if (strtolower(substr($response, 0, 11)) == 'touchfreq: ') {
					$ttl = floatval(substr($response, 11));
				}
				if (strtolower(substr($response, 0, 5)) == 'sid: ') {
					$sid=substr($response, 5);
				}

			}
		}
		fclose ($handler);
		
		if($save) {
			
			session_put_contents('../d/ypexpires.txt', (string) (microtime(true)+$ttl));
			session_put_contents('../d/ypttl.txt', (string) $ttl);
			session_put_contents('../d/ypsid.txt', (string) $sid);
		}
		
	}	
		
	
	
	
	if ($radiohasyp&&session_exists('../d/ypsid.txt')){
		$sid=trim(session_get_contents('../d/ypsid.txt'));
		$ttl=floatval(session_get_contents('../d/ypttl.txt'));
		$nowplaying=html_entity_decode(session_get_contents('../d/nowplayingartist.txt').' - '.session_get_contents('../d/nowplayingtitle.txt'));
		
		$listenerscount=count(array_diff(scandir('../d/listeners'), Array ('.', '..')));
		
		$postdata = http_build_query(
			array(
				'action' => 'touch',
				'sid' => $sid, 
				'st' => $nowplaying, 
				'listeners' => $listenerscount
			)
		);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);

		$context = stream_context_create($opts);
		if($handler = fopen('https://dir.xiph.org/cgi-bin/yp-cgi', 'rb', false, $context)){
			foreach ($meta_data['wrapper_data'] as $response) {


			if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
				$save = boolval(substr($response, 12));
			}
			if (strtolower(substr($response, 0, 11)) == 'touchfreq: ') {
				$ttl = floatval(substr($response, 11));
			}
			if (strtolower(substr($response, 0, 5)) == 'sid: ') {
				$sid=substr($response, 5);
			
				}

			}
		}
		$meta_data = stream_get_meta_data($handler);
		$ttl=0;
		$save=false;
		foreach ($meta_data['wrapper_data'] as $response) {

			if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
				$save = boolval(substr($response, 12));
			}
			if (strtolower(substr($response, 0, 11)) == 'touchfreq: ') {
				$ttl = floatval(substr($response, 11));
			}
			if (strtolower(substr($response, 0, 5)) == 'sid: ') {
				$sid=substr($response, 5);
			}
		}
		fclose($handler);
		if ($save){
			session_put_contents('../d/ypexpires.txt', microtime(true)+$ttl);
			session_put_contents('../d/ypsid.txt', $sid);
			session_put_contents('../d/ypttl.txt', $ttl);
		}
	}
	






///***en yp stuff





	/*if ($radiohasyp&&session_exists('../d/ypsid.txt')&&floatval(trim(session_get_contents('../d/ypexpires.txt')))<microtime(true)){
			$sid=session_get_contents('../d/ypsid.txt');
			$ttl=0;
			$nowplaying=html_entity_decode(session_get_contents('../d/nowplayingartist.txt').' - '.session_get_contents('../d/nowplayingtitle.txt'));
			
			$listenerscount=count(array_diff(scandir('../d/listeners'), Array ('.', '..')));
			
			$postdata = http_build_query(
				array(
					'action' => 'touch',
					'sid' => $sid, 
					'st' => $nowplaying, 
					'listeners' => $listenerscount
				)
			);

			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);

			$context = stream_context_create($opts);
			$save=false;
				
				if($handler = fopen('https://dir.xiph.org/cgi-bin/yp-cgi', 'r', false, $context)){
				
				$meta_data = stream_get_meta_data($handler);
				$ttl=0;
				foreach ($meta_data['wrapper_data'] as $response) {

					if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
						$save = boolval(substr($response, 12));
					}
					if (strtolower(substr($response, 0, 11)) == 'touchfreq: ') {
						$ttl = floatval(substr($response, 11));
					}
					if (strtolower(substr($response, 0, 5)) == 'sid: ') {
					$sid=substr($response, 5);
				
					}


				}
				fclose($handler);
			}
			if ($save){

				session_put_contents('../d/ypexpires.txt', microtime(true)+$ttl);
				session_put_contents('../d/ypsid.txt', $sid);
			
				}
		}*/

if ($featuredapi){
	session_put_contents('../d/featuredapitime.txt', microtime(true)-$apitimestart);
}
else {
		session_put_contents('../d/baseapitime.txt', microtime(true)-$apitimestart);
	}
		
}
 
	$nowplayingduration=intval(session_get_contents('../d/nowplayingduration.txt'));
	$nowplayingurl=session_get_contents('../d/nowplayingurl.txt');
	$nowplayingalbum=session_get_contents('../d/nowplayingalbum.txt');
	$nowplayingtitle=session_get_contents('../d/nowplayingtitle.txt');
	$nowplayingartist=session_get_contents('../d/nowplayingartist.txt');
	$expire=floatval(session_get_contents('../d/expire.txt'));
	$nowplayingbitrate=intval(session_get_contents('../d/nowplayingbitrate.txt'));
	$starttime=intval(session_get_contents('../d/starttime.txt'));

 
 
$alpha=microtime(true);



$hasstarted=session_get_contents('../d/starttime.txt');


$bytestosend=intval($nowplayingbitrate/8);

if (floatval(microtime(true))<floatval($expire)&&$bytestosend>=1&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){
	$content='';
	$nowplayingfile='';
	
	
	
	$initialburstcounter=0; 
	$thirdtimer=microtime(true);
	$beta=microtime(true)-$alpha;

	
	$burstbytesent=0;

	$hassavedfetch=false;


	$opts=Array( 'http'=>
			Array ( 'header' => 'Range: bytes='.intval((intval($nowplayingbitrate)/8)*(microtime(true)-floatval($hasstarted)+$beta)).'-'
			)
		);
	$context=stream_context_create($opts);
	$bravo=microtime(true);
	
	
if (true||!(boolval(trim(session_get_contents('../d/is_featured.txt')))&&$radioFeaturedPlaylistRelativeFilesystemLocation!==''))	
{
	$handle=fopen($nowplayingurl, 'r', false, $context);
	if ($handle!==false&&$nowplayingurl==trim(session_get_contents('../d/nowplayingurl.txt'))){


	$bursttimer=microtime(true);
	$bursthassleeped=false;
			$offset=dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $expire-microtime(true));

		while (!feof($handle)&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){
			
			$offset=dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $expire-microtime(true));

			$secondtimer=microtime(true);
			$bytestoread=$bytestosend+intval($bytetosend*(microtime(true)-$thirdtimer));
			
			$bytesread=0;
			$content='';
			$position=ftell($handle);
			while ($bytesread<=$bytestoread&&!feof($handle)){
				$content.=fread($handle,8192);
				
				if (!feof($handle)){
				$bytesread=$bytesread+8192;
				}
				else{
				$bytesread=ftell($handle)-$position;
				if(isset($_GET['web'])&&!$isfirstpass)
					{
						echo $content;
						
						ob_flush();
						flush();
						
						fclose($handle);
						exit();
						
						
					}
				}
			}



			
			echo  $content;
			ob_flush();
			flush();

			
			if ($initialburstcounter>=10)
				 {//we send an initial burst of data upon client connection to fill in the cache and prevent cutoff in the first seconds
				if (!$bursthassleeped){
					usleep(1000000);
					$bursthassleeped=true;
					
				}
				
				
				
				$secondoffset=microtime(true)-$secondtimer;

				usleep (floor((intval(($bytesread/$bytestosend)*1000000-$offset*1000000-$secondoffset*1000000))/20));
			
			}
			else {
				$initialburstcounter++;
				$burstbytesent=$burstbytesent+$bytesread;
			}
			$thirdtimer=microtime(true);
		}
		ob_flush();
		flush();
		fclose ($handle);
		//and now let's resync just in case eof was reached befor the station clock says next song
		while (microtime(true)<floatval($expire)&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){
		
			$silenttimer=microtime(true);
		
			fpassthru('../silence.mp3');//all I found. Quite hugly
			ob_flush();
			flush();
		}

	
	}
	
	}
	
	
}
else {
	$nowplayingfilearr=explode('/', $nowplayingurl);
	$nowplayingfile=end($nowplayingfilearr);
	$nowplayingfile='../../'.$radioFeaturedPlaylistRelativeFilesystemLocation.$nowplayingfile;
	
	
	
	$handle=fopen($nowplayingfile, 'r', false);//, $context);
	
	session_put_contents('./log.txt', var_dump($handle));
	
	fseek($handle, intval((intval($nowplayingbitrate)/8)*(microtime(true)-floatval($hasstarted)+$beta)));
	if ($handle!==false&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){


		$bursttimer=microtime(true);
		$bursthassleeped=false;
				$offset=dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $expire-microtime(true));

			while (!feof($handle)&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){
				
				$offset=dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $expire-microtime(true));

				$secondtimer=microtime(true);
				$bytestoread=$bytestosend+intval($bytetosend*(microtime(true)-$thirdtimer));
				
				$bytesread=0;
				$content='';
				$position=ftell($handle);
				while ($bytesread<=$bytestoread&&!feof($handle)){
					$content.=fread($handle,8192);
					
					if (!feof($handle)){
					$bytesread=$bytesread+8192;
					}
					else{
					$bytesread=ftell($handle)-$position;
					if(isset($_GET['web'])&&!$isfirstpass)
						{
							echo $content;
							
							ob_flush();
							flush();
							
							fclose($handle);
							exit();
							
							
						}
					}
				}



				
				echo  $content;
				ob_flush();
				flush();

				
				if ($initialburstcounter>=10)
					 {//we send an initial burst of data upon client connection to fill in the cache and prevent cutoff in the first seconds
					if (!$bursthassleeped){
						usleep(1000000);
						$bursthassleeped=true;
						
					}
					
					
					
					$secondoffset=microtime(true)-$secondtimer;

					usleep (floor((intval(($bytesread/$bytestosend)*1000000-$offset*1000000-$secondoffset*1000000))/20));
				
				}
				else {
					$initialburstcounter++;
					$burstbytesent=$burstbytesent+$bytesread;
				}
				$thirdtimer=microtime(true);
			}
			ob_flush();
			flush();
			fclose ($handle);
			//and now let's resync just in case eof was reached befor the station clock says next song
			while (microtime(true)<floatval($expire)&&$nowplayingurl===session_get_contents('../d/nowplayingurl.txt')){
			
				$silenttimer=microtime(true);
			
				fpassthru('../silence.mp3');//all I found. Quite hugly
				ob_flush();
				flush();
			}

		
		}
	}

	$isinitial=false;
	if (!isset($_GET['web'])){
		$isfirstpass=false;
		play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial, $dontdoit, $IsRadioStreamHTTPS, $isfirstpass);
	}
	else {
		$isfirstpass=false;
		//play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial, $dontdoit, $IsRadioStreamHTTPS, $isfirstpass);
		ob_flush();
		flush();
		exit();
	}
}

$isfirstpass=true;
$bytessent=0;
$isinitial=true;
play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial, $dontdoit, $IsRadioStreamHTTPS, $isfirstpass);
?>
