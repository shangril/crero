<?php
error_reporting(E_ERROR | E_PARSE);
require_once('./php-getid3/getid3.php');
if (isset($_GET['listalbums'])) {
header('Content-Type: text/plain; charset=utf-8');


	//returns the list of albums for a specified artist
	$artist=$_GET['listalbums'];
	$files=scandir('./z');
	$albums=Array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, '.flac')===(strlen($file)-5)){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['artist'][0]===$artist){
						$albums[$info['comments_html']['album'][0]]=$info['comments_html']['album'][0];
					
				}
			
		}
		
		
	}
	foreach ($albums as $album){
		echo $album."\n";
		
	}
}
else if (isset($_GET['getmtime'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the mtime of a specified album
	$album=$_GET['getmtime'];
	$files=scandir('./z');
	$mtime=Array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, '.flac')===(strlen($file)-5)){
			
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
		if (! is_dir('./z/'.$file)&&strpos($file, '.flac')===(strlen($file)-5)){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						$tracks[str_replace('.flac', '', $file)]=str_replace('.flac', '', $file);
					
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
	$info = $getID3->analyze('z/'.$file.'.flac');
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
	$info = $getID3->analyze('z/'.$file.'.flac');
	getid3_lib::CopyTagsToComments($info); 
	$artist=$info['comments_html']['artist'][0];
			
			


	echo $artist."\n";







}

else if (isset($_GET['listallalbums'])) {
header('Content-Type: application/x-httpd-php; charset=utf-8');


	//returns a serialized array of all albums for a specified array of artists
	//keys are mtime of the albums
	//values are ['album'] : album title
	$artists=$_GET['listallalbums'];
	$files=scandir('./z');
	$albumlist=Array();
	
	foreach ($files as $file){
	

		if (! is_dir('./z/'.$file)&&strpos($file, '.flac')===(strlen($file)-5)){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('z/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if(in_array($info['comments_html']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){
						
						
						
						$albumlist[filemtime('z/'.$file)]=$info['comments_html']['album'][0];
						
						$year=$info['comments_html']['date'][0];
						
						$year=array_reverse(explode('-', $year))[0];
						
						$content[$year.'.'.filemtime('z/'.$file)]['album']=$info['comments_html']['album'][0];
						
						
						
				
				
				}
			
		}
		
	}
	echo serialize($content);
	
}
else if (isset($_GET['getcover'])) {
	//BUG NOT WORKING still safe but not working
	
header('Content-Type: text/plain; charset=utf-8');


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

die();
?>
