<?php
error_reporting(0);

if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}



header( 'Content-Type: text/plain; charset=utf-8');

//returns a series of useful data about radio current playing track
//first line is for the expire timestamp
//second line is for the startime of the currenly playing track
//third line if the duration of it
//fourth line is the entitified arist name
//fifth line is the entitified track tile

//let's setup some default value
$expire=microtime(true);
$starttime=0;
$nowplayingduration=0;
//the following will be used only if the preceding show an expired track
$nowplayingartist='';
$nowplayingtitle='';

function fillme ($target_file){
	if (file_exists($target_file)&&file_get_contents($target_file)!==false){
		return file_get_contents($target_file);
	}
	else {
		return false;
	}
}

if (fillme ('./radio/d/expire.txt')!==false){
	$expire=fillme('./radio/d/expire.txt');
}
if (fillme ('./radio/d/starttime.txt')!==false){
	$starttime=fillme('./radio/d/starttime.txt');
}
if (fillme ('./radio/d/nowplayingduration.txt')!==false){
	$nowplayingduration=fillme('./radio/d/nowplayingduration.txt');
}
if (fillme ('./radio/d/nowplayingartist.txt')!==false){
	$nowplayingartist=fillme('./radio/d/nowplayingartist.txt');
}
if (fillme ('./radio/d/nowplayingtitle.txt')!==false){
	$nowplayingtitle=fillme('./radio/d/nowplayingtitle.txt');
}
echo $expire;
echo "\n";
echo $starttime;
echo "\n";
echo $nowplayingduration;
echo "\n";
echo $nowplayingartist;
echo "\n";
echo $nowplayingtitle;
echo "\n";
echo microtime(true);//server time
?>
