<?php
error_reporting(0);
chdir('../..');
require_once('./config.php');
chdir('./radio/stream.mp3');
srand();
if (!$hasradio){
	die();
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

function dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp){
	$inittime=microtime(true);
	$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
	foreach ($listeners as $listener){
		if (floatval($listener)+4.9<=microtime(true)){
				unlink('../d/listeners/'.$listener);
		}
	}
	file_put_contents('../d/listeners/'.microtime(true), '1');
	if ($radiohasyp&&floatval(trim(file_get_contents('../d/ypexpires.txt')))<microtime(true)){
		$genres='';
		foreach ($labelgenres as $labelgenre){
			$genres.=$labelgenre.' ';
		}
		$genres=trim($genres);
		
		$nowplaying=file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt');
		
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
//		file_put_contents('../d/debug.txt', $meta_data);
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
			$nowplaying=file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt');
			
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
	//		file_put_contents('../d/debug.txt', $meta_data);
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


function play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp){

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

if (microtime(true)>$expire){
		
	
	$dice=rand(1,10);
	if ($dice==1){
		$featured=explode("\n", $radiofeatured);
		shuffle($featured);
		//$thisfeatured=$featured[0];
		$thisfeatured = $featured[mt_rand(0, count($featured) - 1)];
		$featuredbasenamed=explode('/', $thisfeatured);
		$featuredbasename=array_pop($featuredbasenamed);
		$apihook=str_replace($featuredbasename, '', $thisfeatured);
		$apihook=str_replace('z/', 'api.php', $apihook);
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
		}
	}
	else {
		$featured=explode("\n", $radiobase);
		shuffle($featured);
		//$thisfeatured=$featured[0];
		$thisfeatured = $featured[mt_rand(0, count($featured) - 1)];
		
		$featuredbasenamed=explode('/', $thisfeatured);
		$featuredbasename=array_pop($featuredbasenamed);
		
		$apihook=str_replace($featuredbasename, '', $thisfeatured);
		$apihook=str_replace('audio/', 'api.php', $apihook);
		$apihook.='?radio='.urlencode($featuredbasename);
		$apirequest=file_get_contents($apihook);
		$result=explode("\n", $apirequest);
		
		$nexturl=$thisfeatured;
		$nextartist=$result[0];
		$nextalbum=$result[1];
		$nexttitle=$result[2];
		$nextduration=$result[3];
		$nextbitrate=$result[4];
		file_put_contents('../d/nowplayingisfeatured.txt', '1');
		file_put_contents('../d/starttime.txt', microtime(true));
		
	}
$nowplayingduration=$nextduration;

file_put_contents('../d/nowplayingduration.txt', $nowplayingduration);
$nowplayingurl=$nexturl;
file_put_contents('../d/nowplayingurl.txt', $nowplayingurl);
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
	if (file_exists('../d/ypsid.txt')&&floatval(trim(file_get_contents('../d/ypexpires.txt')))<microtime(true)){
			$sid=file_get_contents('../d/ypsid.txt');
			$nowplaying=file_get_contents('../d/nowplayingartist.txt').' - '.file_get_contents('../d/nowplayingtitle.txt');
			
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
	//		file_put_contents('../d/debug.txt', $meta_data);
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
$filepath='';
if (strstr( $nowplayingurl, 'clewn.org')){
	$filepath=str_replace('http://audio.clewn.org/audio', '../../../audio/clewn/opt/hop/audio', $nowplayingurl);
}
if (strstr( $nowplayingurl, 'cremroad.com'	)){
	$filepath=str_replace('http://cremroad.com/z', '../../z', $nowplayingurl);
}
//echo $nowplayingurl;
//echo $filepath;

$alpha=microtime(true);

$opts=Array( 'http'=>
		Array ( 'header' => 'Range: bytes='.intval((intval($nowplayingbitrate)/8)*(microtime(true)-floatval(file_get_contents('../d/starttime.txt')))).'-'
		)
	);
$context=stream_context_create($opts);

$handle=fopen($nowplayingurl, 'rb', false, $context);


//fseek($handle, intval((intval($nowplayingbitrate)/8)*(microtime(true)-floatval(file_get_contents('../d/starttime.txt')))))

fpassthru($handle);


fclose ($handle);
flush();

$hasstarted=file_get_contents('../d/starttime.txt');

$beta=microtime(true)-$alpha;

if (floatval(microtime(true))<floatval($expire)){

$timetosleep=(1000000*(floatval($nowplayingduration)-(floatval(microtime(true))-floatval($hasstarted))))-(1000000*$beta);
$sleeped=0;



while ($timetosleep-$sleeped>5000000){

$offset=dothelistenerscount($radioname, $server, $radiodescription, $labelgenres, $radiohasyp);

usleep (5000000-$offset*1000000);

$sleeped=$sleeped+5000000;

}
usleep ($timetosleep-$sleeped);



}

	//while (floatval(microtime(true))<floatval($expire)){
	//	sleep(5);
	//}
if (!isset($_GET['web'])){

	play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp);
}

}
play($radioname, $server, $radiodescription, $labelgenres, $radiohasyp);

?>
