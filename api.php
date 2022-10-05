<?php
error_reporting(E_ERROR | E_PARSE);

/*if (file_exists('./php-getid3/getid3.php')){
	include('./php-getid3/getid3.php');
}
else {
	include('./php-getid3/getid3/getid3.php');

}*/

require_once('./Redist-LGPL/cretID3/getid3/getid3.php');


function findAFormat(){


	$formats=explode("\n", listformats());
	$format='.'.$formats[0];


	if (in_array('flac', $formats)){
		$format='.flac';
		
	}
	return $format;
}

function listformats(){
	//returns the list of available audio formats for the current catalog
	//it is expected that the whole catalog is coherent : if one format is available
	//each file of the catalog has to have it provided
	if (!is_dir('./z')){
		die();
	}
	$files=array_diff(scandir('./z'), array ('..', '.', '.htaccess'));
	shuffle($files);
	$sample=$files[0];
	$toks=explode ('.', $sample);
	$extension=array_reverse($toks)[0];
	
	$formats=array();
	
	$formats[$extension]=$extension;
	
	$supported=array('.flac', '.ogg', '.mp3');
	
	$result=$formats;
	
	
	
	foreach ($formats as $format){
			foreach ($supported as $tested){
				if (file_exists('./z/'.str_replace('.'.array_reverse($toks)[0], $tested, $sample))){
						$result[str_replace('.', '', $tested)]=str_replace('.', '', $tested);
						
				}
			}
	}	
	ksort($result);
	$return='';
	foreach ($result as $line) {
						$return.=$line."\n";
						
				}

	return $return;

}
$format=findAFormat();

if (!is_dir('./z')&&!(isset($_GET['listallalbums'])||isset($_GET['l']))){
		header('Content-Type: text/plain; charset=utf-8');
		exit (0);
		//empty streaming only tier
	}

if (isset($_GET['listformats'])){
	//returns the list of available audio formats for the current catalog
	//it is expected that the whole catalog is coherent : if one format is available
	//each file of the catalog has to have it provided

	header('Content-Type: text/plain; charset=utf-8');

	echo listformats();

}
else if (isset($_GET['getinfo'])){

	header('Content-Type: text/plain; charset=utf-8');
		$file=str_replace ('./', '', $_GET['getinfo']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('z/'.$file.$format);
	getid3_lib::CopyTagsToComments($info); 
	$artist=$info['comments_html']['description'][0];
	echo $artist;

}
else if (isset($_GET['listalbums'])) {
header('Content-Type: text/plain; charset=utf-8');


	//returns the list of albums for a specified artist
	
	
	$artist=$_GET['listalbums'];
	$files=scandir('./z');
	$albums=array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['artist'][0]===$artist){
					if(strlen(trim($info['comments_html']['album'][0]))>0&&$info['comments_html']['album'][0]){
					
						$albums[$info['comments_html']['album'][0]]=array_reverse(explode('-', $info['comments_html']['year'][0]))[0].'.'.filemtime('z/'.$file).$info['comments_html']['album'][0];
					
				}
				}
			
		}
		
		
	}
	array_multisort($albums);
	$albums = array_reverse($albums, true);
	foreach ($albums as $album){
		echo array_keys($albums, $album)[0]."\n";
		
	}
}
else if (isset($_GET['getmtime'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the mtime of a specified album
	$album=$_GET['getmtime'];
	$files=scandir('./z');
	$mtime=Array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						array_push($mtime,filemtime('z/'.$file));
						
						
				}
			
		}
		
		
	}
	sort($mtime);
	array_reverse($mtime);
	echo $mtime[0]."\n";
		








}

else if (isset($_GET['gettracks'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the file basenames of the tracks for a specified album 
	$album=$_GET['gettracks'];
	$files=scandir('./z');
	$tracks=Array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						$tracks[str_replace($format, '', $file)]=str_replace($format, '', $file);
					
				}
			
		}
		
		
	}
	foreach ($tracks as $track){
		echo $track."\n";
		
	}





}
else if (isset($_GET['gettitle'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the track title for a specified basename
	$file=str_replace ('./', '', $_GET['gettitle']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('z/'.$file.$format);
	getid3_lib::CopyTagsToComments($info); 
	$title=$info['comments_html']['title'][0];
			
			


	echo $title."\n";







}
else if (isset($_GET['getartist'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the track title for a specified basename
	$file=str_replace ('./', '', $_GET['getartist']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('z/'.$file.$format);
	getid3_lib::CopyTagsToComments($info); 
	$artist=$info['comments_html']['artist'][0];
			
			


	echo $artist."\n";







}

else if (isset($_GET['listallalbums'])||isset($_GET['l'])) {
header('Content-Type: application/x-httpd-php; charset=utf-8');
	//returns a serialized array of all albums for a specified array of artists
	//keys are mtime of the albums
	//values are ['album'] : album title
	if (isset($_GET['l'])){
		$_GET['listallalbums']=$_GET['l'];
	}
	if (isset($_POST['l'])){
		$_GET['listallalbums']=$_POST['l'];
	}

//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./z
	$id='';
	$artists=$_GET['listallalbums'];
	sort($artists);
	foreach ($artists as $artist) {
		$id.=$artist."\n";
	}
	
	$numberoffiles=0;
	$currentfreshness=0;
	
	
	if (file_exists('./numberoffiles.dat')){$numberoffiles=file_get_contents('./numberoffiles.dat');}
	if (file_exists('./storedfreshness.dat')){$currentfreshness=file_get_contents('./storedfreshness.dat');}

	
	$cachedoutput=Array();

	if (file_exists('./apicache.php')){
		rename('./apicache.php', 'apicache.dat');
	}


	if (file_exists('./apicache.dat')){
		$cachedoutput=unserialize(file_get_contents('./apicache.dat'));
		$cachedfreshness=intval($cachedoutput[$id]['freshness']);
		
	}
	else
	{
		$cachedfreshness=0;
		
	}
	if (true!==scandir('./z')&&$numberoffiles!==count(scandir('./z')))
	{
		$files=scandir('./z');
		$albums=Array();
		foreach ($files as $file){
			if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				$albums[filemtime('./z/'.$file)]=filemtime('./z/'.$file);
		
			}
		}
		krsort($albums);

		$currentfreshness=intval(array_keys($albums)[0]);
		file_put_contents('./numberoffiles.dat', count(scandir('./z')));
		file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	else if (true!==scandir('./z'));
	{
			$currentfreshness=time();
			
			file_put_contents('./numberoffiles.dat', '0');
				
			
			file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	if ($cachedfreshness>=$currentfreshness){
		echo $cachedoutput[$id]['data'];
		
	}
	else {
		
			//outdated cache




		$artists=$_GET['listallalbums'];
		$files=scandir('./z');
		$albumlist=Array();
		
		foreach ($files as $file){
		

			if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				
					$getID3 = new getID3;
					$info = $getID3->analyze('z/'.$file);
					getid3_lib::CopyTagsToComments($info); 
					if(in_array($info['comments_html']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){
							
							
							
							$albumlist[filemtime('z/'.$file)]=$info['comments_html']['album'][0];
							
							$year=$info['comments_html']['date'][0];
							
							$year=array_reverse(explode('-', $year))[0];
							
							$content[$year.'.'.filemtime('z/'.$file)]['album']=$info['comments_html']['album'][0];
							$content[$year.'.'.filemtime('z/'.$file)]['artist']=$info['comments_html']['artist'][0];
							
							
							//bogus; do not use !
							if (!isset($content[$year.'.'.filemtime('z/'.$file)]['artists'])){
								$content[$year.'.'.filemtime('z/'.$file)]['artists']=Array();
							}
							//end of bogus
							
							array_push($content[$year.'.'.filemtime('z/'.$file)]['artists'],$info['comments_html']['artist'][0]);
							
					
					
					}
				
			}
			
		}
		echo serialize($content);
		//storing the cache
		$cachedoutput[$id]['freshness']=time();
		$cachedoutput[$id]['data']=serialize($content);
		file_put_contents('./apicache.dat', serialize($cachedoutput));

	}
}
else if (isset($_GET['getcover'])) {
	//BUG NOT WORKING still safe but not working
	
header('Content-Type: text/plain; charset=utf-8');

	die('Feature not implemented');
	//get the cover for a specified album 
	$album=$_GET['gettracks'];
	$files=scandir('./covers/'.str_replace('./', '', $album));
	$target=null;
	foreach ($files as $file){
		$file='./covers/'.$file;
		if (!is_dir('./covers/'.$file)){
			$target=$file;
		}
		else {
			$subdir=scandir('./covers/'.$file);
			foreach ($subdir as $subfile)
			
			{
				if (!is_dir('./covers/'.$file.'/'.$subfile)){
					
					$target=$file.'/'.$subfile;
					
				}	
			
			
			}
		}
		
		
	}
	
	
	if (isset($target)){
		
		echo $target;
	}





}
else if (isset($_GET['radio'])) {
	header('Content-Type: text/plain; charset=utf-8');

	//get the radio infos for a specified file
	$file=str_replace ('./', '', $_GET['radio']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('z/'.$file);
	getid3_lib::CopyTagsToComments($info); 
	$artist=$info['comments_html']['artist'][0];
	echo $artist."\n";
	echo $info['comments_html']['album'][0]."\n";
	echo $info['comments_html']['title'][0]."\n";
	echo $info['playtime_seconds']."\n";
	echo $info['audio']['bitrate']."\n";






//			$nextartist=$result[0];
//			$nextalbum=$result[1];
//			$nexttitle=$result[2];
//			$nextduration=$result[3];
//			$nextbitrate=$result[4];

}
else {
	//echo "\n"; THE MOST STUPID THING I EVER DONE IN MY LIFE 
	//IS THIS LINE AND THE ONE I JUST CORRECTED JUSTE BELLOW
}
ob_flush();
exit(0);
?>
