<?php
error_reporting(0);
chdir('../..');
require_once('./config.php');
chdir('./radio/stream.mp3');
srand();
if (!$hasradio){
	die();
}

header('content-type: audio/mpeg');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: no-cache');
    
if (!file_exists('../d/listeners')){
	mkdir('../d/listeners');
	
}

function dothelistenerscount(){
	$inittime=microtime(true);
	$listeners=array_diff(scandir('../d/listeners'), Array('..', '.'));
	foreach ($listeners as $listener){
		if (floatval($listener)+6<microtime(true)){
				unlink('../d/listeners/'.$listener);
		}
	}
	file_put_contents('../d/listeners/'.microtime(true), '1');
	return microtime(true)-$inittime;
}


function play(){

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
		$thisfeatured=$featured[0];
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
		$thisfeatured=$featured[0];
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

$timetosleep=1000000+(1000000*(floatval($nowplayingduration)-(floatval(microtime(true))-floatval($hasstarted))))-(1000000*$beta);
$sleeped=0;

while ($timetosleep-$sleeped>5000000){

$offset=dothelistenerscount();

usleep (5000000-$offset*1000000);

$sleeped=$sleeped+5000000;

}
usleep ($timetosleep-$sleeped);



}

//while (floatval(microtime(true))<floatval($expire)){
//	sleep(5);
//}

play();


}
play();

?>
