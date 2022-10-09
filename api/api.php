<?php
error_reporting(E_ERROR | E_PARSE);
//require_once('./config.php');
/*if (file_exists('../php-getid3/getid3.php')){
	require_once('../php-getid3/getid3.php');
}
else {
	require_once('../php-getid3/getid3/getid3.php');

}*/

require_once('./../Redist-LGPL/cretID3/getid3/getid3.php');

if (!file_exists('./audio')){
	mkdir ('./audio');
}
else if (!is_dir('./audio')){
	if (array_key_exists('l', $_GET)||array_key_exists('listallalbums', $_GET)){
			header('Content-Type: application/x-httpd-php; charset=utf-8');
			$resp=Array();
			$resp[0]=Array();
			$resp[0]['artist']='Fatal error on download media tier. ./api/audio exists, but is not a directory. It is a regular file. Exiting';
			$resp[0]['album']='Fatal error on download media tier. ./api/audio exists, but is not a directory. It is a regular file. Exiting';
			echo serialize($resp);
	}
	else {
		echo 'Fatal error on download media tier. ./api/audio exists, but is not a directory. It is a regular file. Exiting';
	}
	exit(1);
}






function findAFormat(){


	$formats=explode("\n", listformats());
	$format='.'.$formats[0];


	if (in_array('flac', $formats)){
		$format='.flac';
		
	}
	return $format;
}
function listFormats() {
	
	if (!is_dir('./audio')){
		
		die('No ./audio/ subdirectory found');
	}
	
	$files=array_diff(scandir('./audio'), array ('..', '.', '.htaccess', 'index.html', 'index.php', 'index.htm'));
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
				if (file_exists('./audio/'.str_replace('.'.array_reverse($toks)[0], $tested, $sample))){
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
if (count($_GET)==0){
	header('Content-Type: text/plain; charset=utf-8');
	echo 'No API command detected.';
	
}

else if (isset($_GET['listfiles'])){

	header('Content-Type: text/plain; charset=utf-8');
	$filez=array_diff(scandir('audio'), array ('.', '..'));
	foreach ($filez as $fil)
		{
			
			if (strpos($fil, '.mp3')==strlen($fil)-4){
				echo $fil."\n";

			}

		}
}
else if (isset($_GET['getinfo'])){

	header('Content-Type: text/plain; charset=utf-8');
		$file=str_replace ('./', '', $_GET['getinfo']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('audio/'.$file.$format);
	
	//echo $file.$format;
	
	//echo file_exists('audio/'.$file.$format);
	getid3_lib::CopyTagsToComments($info); 
	
	if (!isset($info['comments_html']['description'][0])) {
		
		$artist=$info['comments_html']['comment'][0];
	
		}
	else {
		$artist=$info['comments_html']['description'][0];
	}
	echo $artist;

}

else if (isset($_GET['freshness'])){
	header('Content-Type: text/plain; charset=utf-8');
	//DONE : case of emptied ./audio : once a freshness is obtained, store in ./audio-freshness.dat ; after foreach, if $albums is empty, echo content of this .dat file instead of usual return. If no freshness was obtained, echo, and store in .dat, current time() 
	$files=scandir('./audio');
	$albums=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
	
		}
	}
	
	if (count($albums)>0){
		
		krsort($albums);

		$freshness = array_keys($albums)[0];
		
		if (!file_put_contents('./audio-freshness.dat', $freshness)){
			if (!unlink('./audio-freshness.dat')){
				rename('./audio-freshness.dat', './audio-freshness-TRASH.dat');
			}
		}
	}
	else {
		if (file_get_contents('./audio-freshness.dat')){
			
			$freshness = file_get_contents('./audio-freshness.dat');
			
		}
		else {
			
			$freshness=time();
			if (!file_put_contents('./audio-freshness.dat', $freshness)){
				if (!unlink('./audio-freshness.dat')){
					rename('./audio-freshness.dat', './audio-freshness-TRASH.dat');
				}
			}
		}
	}
	
	
	
	
	echo $freshness;
}
else if (isset($_GET['listformats'])){
	//returns the list of available audio formats for the current catalog
	//it is expected that the whole catalog is coherent : if one format is available
	//each file of the catalog has to have it provided

	header('Content-Type: text/plain; charset=utf-8');
	echo listformats();
}

else if (isset($_GET['freshness'])){
	header('Content-Type: text/plain; charset=utf-8');

	$files=scandir('./audio');
	$albums=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
	
		}
	}
	krsort($albums);

	echo array_keys($albums)[0];
}

else if (isset($_GET['listalbums'])) {
header('Content-Type: text/plain; charset=utf-8');


	//returns the list of albums for a specified artist
	$artist=$_GET['listalbums'];
	$files=scandir('./audio');
	$albums=array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('audio/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['artist'][0]===$artist){
					if(strlen(trim($info['comments_html']['album'][0]))>0&&$info['comments_html']['album'][0]){
						$albums[$info['comments_html']['album'][0]]=array_reverse(explode('-', $info['comments_html']['year'][0]))[0].'.'.filemtime('audio/'.$file).$info['comments_html']['album'][0];
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
	$files=scandir('./audio');
	$mtime=Array();
	foreach ($files as $file){
		if (! is_dir('./audio'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('audio/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						array_push($mtime,filemtime('audio/'.$file));
						
						
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
	$files=scandir('./audio');
	$tracks=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('audio/'.$file);
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
	$info = $getID3->analyze('audio/'.$file.$format);
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
	$info = $getID3->analyze('audio/'.$file.$format);
	getid3_lib::CopyTagsToComments($info); 
	$artist=$info['comments_html']['artist'][0];
			
			


	echo $artist."\n";







}
//***************SECURITY WARNING***********************
//THIS API CALL IS DANGEROUS FOR THE CALLER
//A ROGUE MEDIA TIER COULD EXECUTE ARBITRARY CODE
//ON THE CALLER SERVER
//PLEASE USE listallalbums2 and l2 instead
else if (isset($_GET['listallalbums'])||isset($_GET['l'])) {
header('Content-Type: application/x-httpd-php; charset=utf-8');
	//TODO : introduce listallalbums2 and l2 which would do exactly the same thing but with JSON output instead of serialized PHP data
	//to prevent auto-execution of malicious code at client-side deserialization if the audio tier is rogue (maybe public, offering free storage...)
	//until now no public audio tier never born execpting the one operated by the author of this line. 

	//returns a serialized array of all albums for a specified array of artists
	//keys are mtime of the albums
	//values are ['album'] : album title
	if (isset($_GET['l'])){
		$_GET['listallalbums']=$_GET['l'];
	}
	if (isset($_POST['l'])){
		$_GET['listallalbums']=$_POST['l'];
	}

//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./audio
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
	if ($numberoffiles!==count(scandir('audio')))
	{
		$files=scandir('./audio');
		$albums=Array();
		foreach ($files as $file){
			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
		
			}
		}
		krsort($albums);

		$currentfreshness=intval(array_keys($albums)[0]);
		file_put_contents('./numberoffiles.dat', count(scandir('./audio')));
		file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	if ($cachedfreshness>=$currentfreshness){
		echo $cachedoutput[$id]['data'];
		
	}
	else {
		
			//outdated cache




		$artists=$_GET['listallalbums'];
		$files=scandir('./audio');
		$albumlist=Array();
		
		foreach ($files as $file){
		

			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				
					$getID3 = new getID3;
					$info = $getID3->analyze('audio/'.$file);
					getid3_lib::CopyTagsToComments($info); 
					if(in_array($info['comments_html']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){
							
							
							
							$albumlist[filemtime('audio/'.$file)]=$info['comments_html']['album'][0];
							
							$year=$info['comments_html']['date'][0];
							
							$year=array_reverse(explode('-', $year))[0];
							
							$content[$year.'.'.filemtime('audio/'.$file)]['album']=$info['comments_html']['album'][0];
							$content[$year.'.'.filemtime('audio/'.$file)]['artist']=$info['comments_html']['artist'][0];
							
							
							//bogus; do not use !
							if (!isset($content[$year.'.'.filemtime('audio/'.$file)]['artists'])){
								$content[$year.'.'.filemtime('audio/'.$file)]['artists']=Array();
							}
							//end of bogus
							
							array_push($content[$year.'.'.filemtime('audio/'.$file)]['artists'],$info['comments_html']['artist'][0]);
							
					
					
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
//***************SECURITY WARNING***********************
//THIS API CALL IS SAFE FOR THE CALLER
//A ROGUE MEDIA TIER CANNOT EXECUTE ARBITRARY CODE
//ON THE CALLER SERVER
//PREFER IT OVER listallalbums and l instead
else if (isset($_GET['listallalbums2'])||isset($_GET['l2'])) {
header('Content-Type: application/json; charset=utf-8');
	//DONE : introduce listallalbums2 and l2 which would do exactly the same thing but with JSON output instead of serialized PHP data
	//to prevent auto-execution of malicious code at client-side deserialization if the audio tier is rogue (maybe public, offering free storage...)
	//until now no public audio tier never born execpting the one operated by the author of this line. 

	//returns a serialized array of all albums for a specified array of artists
	//keys are mtime of the albums
	//values are ['album'] : album title
	if (isset($_GET['l2'])){
		$_GET['listallalbums2']=$_GET['l2'];
	}
	if (isset($_POST['l2'])){
		$_GET['listallalbums2']=$_POST['l2'];
	}

//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./audio
	$id='';
	$artists=$_GET['listallalbums2'];
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
		if ($cachedoutput!==false){
			$cachedfreshness=intval($cachedoutput[$id]['freshness']);
		}
		else {
			$cachedfreshness=0;
		}
	}
	else
	{
		$cachedfreshness=0;
		
	}
	if ($numberoffiles!==count(scandir('audio')))
	{
		$files=scandir('./audio');
		$albums=Array();
		foreach ($files as $file){
			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
		
			}
		}
		krsort($albums);

		$currentfreshness=intval(array_keys($albums)[0]);
		file_put_contents('./numberoffiles.dat', count(scandir('./audio')));
		file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	if ($cachedfreshness>=$currentfreshness){
		echo json_encode(unserialize($cachedoutput[$id]['data']));
		
	}
	else {
		
			//outdated cache




		$artists=$_GET['listallalbums2'];
		$files=scandir('./audio');
		$albumlist=Array();
		
		foreach ($files as $file){
		

			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				
					$getID3 = new getID3;
					$info = $getID3->analyze('audio/'.$file);
					getid3_lib::CopyTagsToComments($info); 
					if(in_array($info['comments_html']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){
							
							
							
							$albumlist[filemtime('audio/'.$file)]=$info['comments_html']['album'][0];
							
							$year=$info['comments_html']['date'][0];
							
							$year=array_reverse(explode('-', $year))[0];
							
							$content[$year.'.'.filemtime('audio/'.$file)]['album']=$info['comments_html']['album'][0];
							$content[$year.'.'.filemtime('audio/'.$file)]['artist']=$info['comments_html']['artist'][0];
							
							
							//bogus; do not use !
							if (!isset($content[$year.'.'.filemtime('audio/'.$file)]['artists'])){
								$content[$year.'.'.filemtime('audio/'.$file)]['artists']=Array();
							}
							//end of bogus
							
							array_push($content[$year.'.'.filemtime('audio/'.$file)]['artists'],$info['comments_html']['artist'][0]);
							
					
					
					}
				
			}
			
		}
		echo json_encode($content);
		//storing the cache
		$cachedoutput[$id]['freshness']=time();
		$cachedoutput[$id]['data']=serialize($content);
		file_put_contents('./apicache.dat', serialize($cachedoutput));

	}
}



else if (isset($_GET['getcover'])) {
	//BUG NOT WORKING still safe but not working
	//UPDATE not safe ! Do not call. BTW it will die()
header('Content-Type: text/plain; charset=utf-8');

	die ('feature not implemented');
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
else if (isset($_GET['getalbumartist'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the artist of a specified album
	$album=$_GET['getalbumartist'];
	$files=scandir('./audio');
	$mtime=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('audio/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						array_push($mtime,$info['comments_html']['artist'][0]);
						
						
				}
			
		}
		
		
	}
	sort($mtime);
	array_reverse($mtime);
	echo $mtime[0]."\n";
		








}
else if (isset($_GET['getalbumartists'])) {
header('Content-Type: text/plain; charset=utf-8');


	//get the artists of a specified album featuring several artists
	$album=$_GET['getalbumartists'];
	$files=scandir('./audio');
	$mtime=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			
				$getID3 = new getID3;
				$info = $getID3->analyze('audio/'.$file);
				getid3_lib::CopyTagsToComments($info); 
				if($info['comments_html']['album'][0]===$album){
						array_push($mtime,$info['comments_html']['artist'][0]);
						
						
				}
			
		}
		
		
	}
	sort($mtime);
	foreach ($mtime as $artist){
		echo $artist."\n";
		
	}	








}
else if (isset($_GET['radio'])) {
	header('Content-Type: text/plain; charset=utf-8');

	//get the radio infos for a specified file
	$file=str_replace ('./', '', $_GET['radio']);
	$title='';

	$getID3 = new getID3;
	$info = $getID3->analyze('audio/'.$file);
	getid3_lib::CopyTagsToComments($info); 
	
	$artist=html_entity_decode($info['comments_html']['artist'][0]);
	
	$title=html_entity_decode($info['comments_html']['title'][0]);
	
	$comment=html_entity_decode($info['comments_html']['comment'][0]);
	
	$album=html_entity_decode($info['comments_html']['album'][0]);
	
	
	$run=false;
	/* this is an unused feature that was once useful
	 * 1) files that were batch imported from Dogmazic.net
	 * 2) files that used the old, pre-2013 filename formating specific to dogmazic.net
	 * 3) files that got empty tags (ie, not tagged by the artist and uploaded in the years either when the tagging robot of Dogmazic hasn't been invented, or the late, troubled years nearby 2011-2012 when it was failing)
	
	In such case, this feature used to write semi correct or correct tags on the fly. 
	
	Since GetID3 has been frozen/forked ; is now bundled with CreCro ; this fork is a subset and does not support writing tags
	
	And since it was a niche feature, we decided to disable it completely
	* 
	*/	
	
	
	/* DISABLED CODE BEGINS HERE
	if(
	
	(
	(!isset($artist)||strlen(trim($artist))<1)
	||
			(!isset($title)||strlen(trim($title))<1)

	||
			(!isset($comment)||strlen(trim($comment))<1)

	|| (!strstr($comment, 'Creative')&&!strstr($comment,('Licence')))
	)
	&&(
	
	strstr($file, 'www.dogmazic.net')
	
	|| (strstr($file, 'dogmazic_net')&&strstr($file, '_ogg'))
	)
	) {
			
		$suite=str_replace('[','*', $file);
		$suite2=str_replace(']','*', $suite);
		$suite3=str_replace('.mp3','*', $suite2);
		
		$tok=explode('*', $suite3);

		$artist=trim(str_replace('_', ' ', $tok[1]));

		
		$title=trim(str_replace('_', ' ', $tok[2]));
		

	

		$comment=trim(str_replace('_', ' ', $tok[3]));
		if (!strstr($comment, 'Creative')&&!strstr($comment,('Licence'))) {
				$comment=trim(str_replace('_', ' ', $tok[4]));
		
			}
		$run=true;
		
	}
	if (!isset($info['comments_html']['album'][0])||strlen(trim($info['comments_html']['album'][0]))<1){
			
			
		

	
		$album=$artist;
		// populate data array
		$run=true;
		

	}
	if($run&&false){//DEAD BRANCH ! REMOVE THE FALSE AND THE WHOLE RADIO API WILL FAIL BECAUSE OF require OF php-getid3/write.php not satistied ! ! ! ! 
		
		$getID3 = new getID3;
		
		require_once('../php-getid3/write.php');
			
		
		$tagwriter = new getid3_writetags;
		$tagwriter->filename = 'audio/'.$file;
		$tagwriter->tagformats = array('id3v1', 'id3v2.3');


		$TagData=array();
		$TagData['title'][] = $title;
		$TagData['artist'][] = $artist;
		$TagData['comment'][] = $comment;
		$TagData['album'][] = $album;
		
		

		$tagwriter->tag_data = $TagData;
		

		$tagwriter->overwrite_tags = true;
		$tagwriter->tag_encoding = 'UTF-8';
		$tagwriter->remove_other_tags = true;

		
		

		if ($tagwriter->WriteTags()) {
			$info = $getID3->analyze('audio/'.$file);
			getid3_lib::CopyTagsToComments($info); 
			$artist=$info['comments_html']['artist'][0];
	
			
			}
		}
		*/
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

else if (isset($_GET['listalbums-noartist'])) {
header('Content-Type: application/json; charset=utf-8');
	//returns a no longer serialized array, now a json-encoded array, of all albums for all artists
	//keys are mtime of the albums
	//values are ['album'] : album title


//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./audio
	$id='noartist';
	
	
	$cachedoutput=Array();
	if (file_exists('./apicache-noartist.dat')){
		$cachedoutput=unserialize(file_get_contents('./apicache-noartist.dat'));
		if ($cachedoutput){	
			$cachedfreshness=intval($cachedoutput[$id]['freshness']);
		}
		else{
			$cachedfreshness=0;
		}
	}
	else
	{
		$cachedfreshness=0;
		
	}
	$files=scandir('./audio');
	$albums=Array();
	foreach ($files as $file){
		if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
			$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
	
		}
	}
	krsort($albums);

	$currentfreshness=intval(array_keys($albums)[0]);
	if ($cachedfreshness>=$currentfreshness){
		echo json_encode(unserialize($cachedoutput[$id]['data']));
		
	}
	else {
		
			//outdated cache




		$artists=$_GET['listallalbums'];
		$files=scandir('./audio');
		$albumlist=Array();
		
		foreach ($files as $file){
		

			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				
					$getID3 = new getID3;
					$info = $getID3->analyze('audio/'.$file);
					getid3_lib::CopyTagsToComments($info); 
					if(!in_array($info['comments_html']['album'][0],$albumlist)){
							
							
							
							$albumlist[filemtime('audio/'.$file)]=$info['comments_html']['album'][0];
							
							$year=$info['comments_html']['date'][0];
							
							$year=array_reverse(explode('-', $year))[0];
							
							$content[$year.'.'.filemtime('audio/'.$file)]['album']=$info['comments_html']['album'][0];
							$content[$year.'.'.filemtime('audio/'.$file)]['artist']=$info['comments_html']['artist'][0];
							
							
							//bogus; do not use !
							if (!isset($content[$year.'.'.filemtime('audio/'.$file)]['artists'])){
								$content[$year.'.'.filemtime('audio/'.$file)]['artists']=Array();
							}
							//end of bogus
							
							array_push($content[$year.'.'.filemtime('audio/'.$file)]['artists'],$info['comments_html']['artist'][0]);
							
					
					
					}
				
			}
			
		}
		echo json_encode($content);
		//storing the cache
		$cachedoutput[$id]['freshness']=time();
		$cachedoutput[$id]['data']=serialize($content);
		file_put_contents('./apicache-noartist.dat', serialize($cachedoutput));

	}
}
else if (isset($_GET['listartists'])) {
header('Content-Type: text/plain; charset=utf-8');
//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./audio
	$id='noartist';
	$numberoffiles=file_get_contents('./numberoffile.dat');
	$currentfreshness=file_get_contents('./storedfreshness.dat');
	
	$cachedoutput=Array();
	if (file_exists('./apicache-artist-list.dat')){
		$cachedoutput=unserialize(file_get_contents('./apicache-artist-list.dat'));
		$cachedfreshness=intval($cachedoutput[$id]['freshness']);
		
	}
	else
	{
		$cachedfreshness=0;
		
	}
	
	if ($numberoffile!==count(scandir('audio')))
	{
		$files=scandir('./audio');
		$albums=Array();
		foreach ($files as $file){
			if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
				$albums[filemtime('./audio/'.$file)]=filemtime('./audio/'.$file);
		
			}
		}
		krsort($albums);

		$currentfreshness=intval(array_keys($albums)[0]);
		file_put_contents('./numberoffiles.dat', count(scandir('./audio')));
		file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	
	if ($cachedfreshness>=$currentfreshness){
		echo $cachedoutput[$id]['data'];
		
	}
	else {
		
			//outdated cache


			$files=scandir('./audio');
			$albums=Array();
			foreach ($files as $file){
				if (! is_dir('./audio/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
					
						$getID3 = new getID3;
						$info = $getID3->analyze('audio/'.$file);
						getid3_lib::CopyTagsToComments($info);
						if (!isset($info['comments_html']['artist'][0])&&strlen($info['comments_html']['artist'])>=1){
									$art=$info['comments_html']['artist'];
									$info['comments_html']['artist']=array($art);
								}
							
						 
								$albums[$info['comments_html']['artist'][0]]=$info['comments_html']['artist'][0];
							
					
				}
				
				
			}
			foreach ($albums as $album){
				echo $album."\n";
				
			}
			//storing the cache
			$cachedoutput[$id]['freshness']=time();
			$cachedoutput[$id]['data']=implode("\n", $albums);
			file_put_contents('./apicache-artist-list.dat', serialize($cachedoutput));

		}
}
ob_flush();
exit(0);
?>
