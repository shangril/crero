<?php
error_reporting(0);
chdir('../..');
require_once('./config.php');
chdir('./radio/stream.mp3');
srand();
$statid=mt_rand(0, 1000000);

if (!$hasradio){
	die();
}
if ($autobuildradiobase){

	$linez=explode("\n",  file_get_contents($clewnapiurl.'?listfiles=true'));
	
	$a2='';
	
	foreach ($linez as $line){
		$a2.=$clewnaudiourl.$line."\n";
	}
	file_put_contents('../../d/radioBase.txt',$a2);
}


if (!file_exists('../d/featuredapitime.txt')){
	file_put_contents('../d/featuredapitime.txt', 0);
}
if (!file_exists('../d/mediafetch-0.txt')){
	file_put_contents('../d/mediafetch-0.txt', 0);
}
if (!file_exists('../d/mediafetch-1.txt')){
	file_put_contents('../d/mediafetch-1.txt', 0);
}



if (!file_exists('../d/baseapitime.txt')){
	file_put_contents('../d/baseapitime.txt', 0);
}


if ($radiohasyp&&!file_exists('../d/ypexpires.txt')){
	file_put_contents('../d/ypexpires.txt', '0');
}

header('Content-Type: audio/mpeg');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: no-cache');
    header('icy-name: '.str_replace("\r\n", ' ', $radioname));
    header('icy-description: '.str_replace("\r\n", ' ', $radiodescription));
	header('icy-url: http://'.$server.'/radio');
    
    
    
if (!file_exists('../d/listeners')){
	mkdir('../d/listeners');
	
}

function dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $duration){
	$inittime=microtime(true);
	$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
	foreach ($listeners as $listener){
		if (intval(file_get_contents('../d/listeners/'.$listener))===$statid){
				unlink('../d/listeners/'.$listener);
			
		}
	}
	file_put_contents('../d/listeners/'.microtime(true), $statid);
	if (!file_exists('../d/maxlisteners.txt')){
		file_put_contents('../d/maxlisteners.txt', '0');
	}
	
	$listeners=count(array_diff(scandir('../d/listeners'), Array ('.', '..')));
	
	if ($listeners>intval(file_get_contents('../d/maxlisteners.txt'))){
		file_put_contents('../d/maxlisteners.txt', $listeners);
	}
	if ((floatval(filectime('../d/maxlisteners24hours.txt'))+24*60*60)<=microtime(true)){
	
		unlink('../d/maxlisteners24hours.txt');
	
	}
	if ($listeners>intval(file_get_contents('../d/maxlisteners24hours.txt'))){
		file_put_contents('../d/maxlisteners24hours.txt', $listeners);
	}


	if ($radiohasyp&&floatval(trim(file_get_contents('../d/ypexpires.txt')))<microtime(true)){
		$genres='';
		foreach ($labelgenres as $labelgenre){
			$genres.=$labelgenre.' ';
		}
		$genres=trim($genres);
		
		$nowplaying=html_entity_decode(file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt'));
		
		$listenerscount=count(array_diff(scandir('./d/listeners'), Array ('.', '..')));
		
		$postdata = http_build_query(
			array(
				'action' => 'add',
				'sn' => $radioname, 
				'type' => 'audio/mpeg', 
				'genre' => $genres, 
				'b' => file_get_contents('../d/nowplayingbitrate.txt'), 
				'listenurl' => 'http://'.$server.'/radio/stream.mp3', 
				'desc' => $radiodescription, 
				'url' => 'http://'.$server.'/radio'
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
		$handler=fopen('http://dir.xiph.org/cgi-bin/yp-cgi', 'r', false, $context);
		
		$meta_data = stream_get_meta_data($handler);
		$ttl=0;
		$save=false;
		$sid='';
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
		fclose ($handler);
		
		if($save) {
			
			file_put_contents('../d/ypexpires.txt', microtime(true)+$ttl);
			file_put_contents('../d/ypttl.txt', $ttl);
			file_put_contents('../d/ypsid.txt', $sid);
		}
		if (file_exists('../d/ypsid.txt')&&floatval(trim(file_get_contents('../d/ypexpires.txt')))<microtime(true)){
			$sid=file_get_contents('../d/ypsid.txt');
			$nowplaying=html_entity_decode(file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt'));
			
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
			$handler = fopen('http://dir.xiph.org/cgi-bin/yp-cgi', 'r', false, $context);
			
			$meta_data = stream_get_meta_data($handler);
			$ttl=0;
			$save=false;
			foreach ($meta_data['wrapper_data'] as $response) {

				if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
					$save = boolval(substr($response, 12));
				}

			}
			fclose($handler);
			if ($save){
				file_put_contents('../d/ypexpires.txt', microtime(true)+floatval(file_get_contents('../d/ypttl.txt')));
			}
		}
		
	
	
	
	}
	return microtime(true)-$inittime;
}


function play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial){

if (file_exists('../d/lock.txt')&&(microtime(true)-floatval(file_get_contents('../d/lock.txt'))>120)){
	unlink('../d/lock.txt');
}

$radiofeatured=file_get_contents('../../d/radioFeatured.txt');
$radiobase=file_get_contents('../../d/radioBase.txt');


$nowplayingduration=intval(file_get_contents('../d/nowplayingduration.txt'));
$nowplayingurl=file_get_contents('../d/nowplayingurl.txt');
$nowplayingalbum=file_get_contents('../d/nowplayingalbum.txt');
$nowplayingtitle=file_get_contents('../d/nowplayingtitle.txt');
$nowplayingartist=file_get_contents('../d/nowplayingartist.txt');
$expire=floatval(file_get_contents('../d/expire.txt'));
$nowplayingbitrate=intval(file_get_contents('../d/nowplayingbitrate.txt'));
$starttime=intval(file_get_contents('../d/starttime.txt'));


$nexturl=$nowplayingurl;
$nextduration=$nowplayingduration;
$nextalbum=$nowplayingalbum;
$nextartist=$nowplayingartist;
$nextbitrate=$nowplayingbitrate;
$nexttitle=$nowplayingtitle;

while (file_exists('../d/lock.txt')){
		
	$silenttimer=microtime(true);

	fpassthru('../silence.mp3');
	ob_flush();
	flush();
	usleep (round(1000000*(0.052-(microtime(true)-$silenttimer))));

}

if (microtime(true)>=$expire&&(!file_exists('../d/lock.txt'))){
	
	$nextartist='';
	
		if (strlen($nextartist)===0){
		
		
		file_put_contents('../d/lock.txt', microtime(true));
		$apitimestart=microtime(true);
		$featuredapi=false;
		
		
		
		
		$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
		foreach ($listeners as $listener){
			if (intval(file_get_contents('../d/listeners/'.$listener))!==$statid){
					unlink('../d/listeners/'.$listener);
				
			}
		}
		
		$isthislistenercounted=false;
		foreach ($listeners as $listener){
			if (intval(file_get_contents('../d/listeners/'.$listener))===$statid){
					$isthislistenercounted=true;
				
			}
		}
		if (!$isthislistenercounted){
			file_put_contents('../d/listeners/'.microtime(true), $statid);
		}
		$dice=rand(1,10);
		if ($dice==1){
			$featuredapi=true;
			if (!$isinitial){
				$loop=ceil(floatval(file_get_contents('../d/featuredapitime.txt'))/0.052);
				$silent='';
				$silentfile=file_get_contents('../silence.mp3');
				for ($i=0;$i<$loop;$i++){
					$silent.=$silentfile;
					
				}
				echo $silent;
				ob_flush();
				flush();
			}
			
			$featured=explode("\n", $radiofeatured);
			shuffle($featured);
			$thisfeatured = $featured[mt_rand(0, count($featured) - 1)];
			$featuredbasenamed=explode('/', $thisfeatured);
			$featuredbasename=array_pop($featuredbasenamed);
			$apihook=str_replace($featuredbasename, '', $thisfeatured);
			$apihook=str_replace('/z/', '/api.php', $apihook);
			$apihook=str_replace('/audio/', '/api.php', $apihook);
			
			$apihook.='?radio='.urlencode($featuredbasename);
			$apirequest=file_get_contents($apihook);
			if (count($apirequest)>0){
				$result=explode("\n", $apirequest);
				
				$nexturl=$thisfeatured;
				$nextartist=$result[0];
				$nextalbum=$result[1];
				$nexttitle=$result[2];
				$nextduration=$result[3];
				$nextbitrate=$result[4];
				file_put_contents('../d/nowplayingisfeatured.txt', '0');
				file_put_contents('../d/starttime.txt', microtime(true));
				file_put_contents('../d/license.txt', '');
			}
		}
		else {
			
			if (!$isinitial){
				$loop=ceil(floatval(file_get_contents('../d/baseapitime.txt'))/0.052);
				$silent='';
				$silentfile=file_get_contents('../silence.mp3');
				for ($i=0;$i<$loop;$i++){
					$silent.=$silentfile;
					
				}
				echo $silent;
				ob_flush();
				flush();
			}
			
			$featured=explode("\n", $radiobase);
			shuffle($featured);
			$thisfeatured = $featured[mt_rand(0, count($featured) - 1)];
			
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
			
			file_put_contents('../d/license.txt', file_get_contents($apibase.'?getinfo='.urlencode(str_replace('.mp3', '', $featuredbasename))));
			
			
			file_put_contents('../d/nowplayingisfeatured.txt', '1');
			file_put_contents('../d/starttime.txt', microtime(true));
			}
		}
$nowplayingduration=$nextduration;

file_put_contents('../d/nowplayingduration.txt', $nowplayingduration);
$nowplayingurl=$nexturl;
file_put_contents('../d/nowplayingurl.txt',$nowplayingurl);
$nowplayingalbum=$nextalbum;
file_put_contents('../d/nowplayingalbum.txt', $nowplayingalbum);
$nowplayingtitle=$nexttitle;
file_put_contents('../d/nowplayingtitle.txt',$nowplayingtitle);
$nowplayingartist=$nextartist;
file_put_contents('../d/nowplayingartist.txt', $nowplayingartist);
$expire=floatval(microtime(true))+floatval($nextduration);
file_put_contents('../d/expire.txt', $expire);
$nowplayingbitrate=$nextbitrate;
file_put_contents('../d/nowplayingbitrate.txt',$nowplayingbitrate);

unlink('../d/lock.txt');
/*
if(false&&(!isset($nowplayingartist) || trim($nowplayingartist)==='')&&$autodeleteuntagguedtracks){
			$basename=array_reverse(explode('/', $nowplayingurl))[0];
			file_put_contents($autodeleteprefixpath.$basename, file_get_contents('../silence.mp3'));
			file_put_contents('../d/expire.txt', '0');
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
	if (file_exists('../d/ypsid.txt')&&floatval(trim(file_get_contents('../d/ypexpires.txt')))<microtime(true)){
			$sid=file_get_contents('../d/ypsid.txt');
			$nowplaying=html_entity_decode(file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt'));
			
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
			$handler = fopen('http://dir.xiph.org/cgi-bin/yp-cgi', 'r', false, $context);
			
			$meta_data = stream_get_meta_data($handler);
			$ttl=0;
			$save=false;
			foreach ($meta_data['wrapper_data'] as $response) {

				if (strtolower(substr($response, 0, 12)) == 'ypresponse: ') {
					$save = boolval(substr($response, 12));
				}

			}
			fclose($handler);
			if ($save){
				file_put_contents('../d/ypexpires.txt', microtime(true)+floatval(file_get_contents('../d/ypttl.txt')));
			}
		}

if ($featuredapi){
	file_put_contents('../d/featuredapitime.txt', microtime(true)-$apitimestart);
}
else {
		file_put_contents('../d/baseapitime.txt', microtime(true)-$apitimestart);
	}
		
}
 
	$nowplayingduration=intval(file_get_contents('../d/nowplayingduration.txt'));
	$nowplayingurl=file_get_contents('../d/nowplayingurl.txt');
	$nowplayingalbum=file_get_contents('../d/nowplayingalbum.txt');
	$nowplayingtitle=file_get_contents('../d/nowplayingtitle.txt');
	$nowplayingartist=file_get_contents('../d/nowplayingartist.txt');
	$expire=floatval(file_get_contents('../d/expire.txt'));
	$nowplayingbitrate=intval(file_get_contents('../d/nowplayingbitrate.txt'));
	$starttime=intval(file_get_contents('../d/starttime.txt'));

 

$filepath='';
if (strstr( $nowplayingurl, 'clewn.org')){
	$filepath=str_replace('http://audio.clewn.org/audio', '../../../audio/clewn/opt/hop/audio', $nowplayingurl);
}
if (strstr( $nowplayingurl, 'cremroad.com'	)){
	$filepath=str_replace('http://cremroad.com/z', '../../z', $nowplayingurl);
}

$alpha=microtime(true);



$hasstarted=file_get_contents('../d/starttime.txt');


$bytestosend=intval($nowplayingbitrate/8);

if (floatval(microtime(true))<floatval($expire)&&$bytestosend>=1&&$nowplayingurl===file_get_contents('../d/nowplayingurl.txt')){
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
	
	
	
	
	$handle=fopen($nowplayingurl, 'rb', false, $context);
	if ($handle!==false&&$nowplayingurl===file_get_contents('../d/nowplayingurl.txt')){


	$bursttimer=microtime(true);
	$bursthassleeped=false;

		while (!feof($handle)&&$nowplayingurl===file_get_contents('../d/nowplayingurl.txt')){
			
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

				usleep (intval(($bytesread/$bytestosend)*1000000-$offset*1000000-$secondoffset*1000000));
			
			}
			else {
				$initialburstcounter++;
				$burstbytesent=$burstbytesent+$bytesread;
			}
			$thirdtimer=microtime(true);
		}
		fclose ($handle);
		//and now let's resync just in case eof was reached befor the station clock says next song
		while (microtime(true)<floatval($expire)&&$nowplayingurl===file_get_contents('../d/nowplayingurl.txt')){
		
			$silenttimer=microtime(true);
		
			fpassthru('../silence.mp3');//all I found. Quite hugly
			ob_flush();
			flush();
		}

	
	}
	
	}
	$isinitial=false;
	if (!isset($_GET['web'])){
		play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial);
	}
	else {
		ob_flush();
		exit();
	}
}
$bytessent=0;
$isinitial=true;
play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp, $statid, $bytessent, $isinitial);

?>
