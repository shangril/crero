<?php
error_reporting(E_ERROR | E_PARSE);

/*if (file_exists('./php-getid3/getid3.php')){
	include('./php-getid3/getid3.php');
}
else {
	include('./php-getid3/getid3/getid3.php');

}*/

require_once('./Redist-LGPL/cretID3/getid3/getid3.php');

if (!file_exists('./z')){
	mkdir ('./z');
}
else if (!is_dir('./z')){
	if (array_key_exists('l', $_GET)||array_key_exists('listallalbums', $_GET)){
			header('Content-Type: application/x-httpd-php; charset=utf-8');
			$resp=Array();
			$resp[0]=Array();
			$resp[0]['artist']='Fatal error on download media tier. ./z exists, but is not a directory. It is a regular file. Exiting';
			$resp[0]['album']='Fatal error on download media tier. ./z exists, but is not a directory. It is a regular file. Exiting';
			echo serialize($resp);
	}
	else {
		echo 'Fatal error on streaming media tier. ./z exists, but is not a directory. It is a regular file. Exiting';
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

function listformats(){
	//returns the list of available audio formats for the current catalog
	//it is expected that the whole catalog is coherent : if one format is available
	//each file of the catalog has to have it provided
	if (!is_dir('./z')){
		die();
	}
	$files=array_diff(scandir('./z'), array ('..', '.', '.htaccess', 'index.html', 'index.php', 'index.htm'));
	shuffle($files);
	$sample=$files[0];
	$toks=explode ('.', $sample);
	$extension=array_reverse($toks)[0];
	
	
	$supported=array('.flac', '.ogg', '.mp3');
	
	$result=array();
	
	
	
		foreach ($supported as $tested){
			if (file_exists('./z/'.str_replace('.'.$extension, $tested, $sample))){
					$result[str_replace('.', '', $tested)]=str_replace('.', '', $tested);
					
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

		//empty streaming only tier
	}
else if (count($_GET)==0){
	header('Content-Type: text/plain; charset=utf-8');
	echo 'No API command detected.';
	
}

else if (isset($_GET['listformats'])){
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
else if (isset($_GET['freshness'])){
	header('Content-Type: text/plain; charset=utf-8');
	//DONE : case of emptied ./audio : once a freshness is obtained, store in ./audio-freshness.dat ; after foreach, if $albums is empty, echo content of this .dat file instead of usual return. If no freshness was obtained, echo, and store in .dat, current time() 
	$files=scandir('./z');
	$albums=Array();
	foreach ($files as $file){
		if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))||$file=='.timestamp'){
			$albums[filectime('./z/'.$file)]=filectime('./z/'.$file);
	
		}
	}
	
	if (count($albums)>0){
		
		krsort($albums);
		$oldfreshness=file_get_contents('freshness.dat');
		if ($oldfreshness===false){
			$oldfreshness=time();
		}
			
		if ($oldfreshness>array_keys($albums)[0]){
					$freshness=time();
					}
			else {
				$freshness = array_keys($albums)[0];
		    }
		file_put_contents('freshness.dat', $freshness);
		
		if (!unlink('./audio-freshness.dat')){
			rename('./audio-freshness.dat', './audio-freshness-TRASH.dat');
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
				if($info['comments']['artist'][0]===$artist){
					
					$albums[strval($info['comments_html']['album'][0])]=array_reverse(explode('-', $info['comments_html']['date'][0]))[0].'.'.filemtime('z/'.$file).$info['comments_html']['album'][0];
					
				
				}
			
		}
		
		
	}
	arsort($albums);
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
////********SECURITY WARNING*********************
//CALLS TO THIS API METHOD IS UNSAFE FOR THE CALLER
//A ROGUE MEDIA TIER COULD USE IT TO EXECUTE ARBITRARY CODE
//ON THE CALLER SERVER
//USE listallalbums2 or l2 instead	
//***********************************************
	
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
					if(in_array($info['comments_html']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){ //'comments_html']['album'][0])){
							
							
							
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
else if (isset($_GET['listallalbums2'])||isset($_GET['l2'])) {
////********SECURITY WARNING*********************
//CALLS TO THIS API METHOD IS SAFE FOR THE CALLER
//A ROGUE MEDIA TIER CANNOT USE IT TO EXECUTE ARBITRARY CODE
//ON THE CALLER SERVER
//PREFER IT TO listallalbums or l
//***********************************************
	
header('Content-Type: application/json; charset=utf-8');
	//returns a serialized array of all albums for a specified array of artists
	//keys are mtime of the albums
	//values are ['album'] : album title
	if (isset($_GET['l2'])){
		$_GET['listallalbums2']=$_GET['l2'];
	}
	if (isset($_POST['l2'])){
		$_GET['listallalbums2']=$_POST['l2'];
	}

//basic cachign mechanism, reading. Will simply compare cache content with the mtime of the newest file in ./z
	$id='';
	$artists=$_GET['listallalbums2'];
	sort($artists);
	foreach ($artists as $artist) {
		$id.=$artist."\n";
	}
	
	
	$cachedfreshness=0;
				
	if (file_exists('./apicache.dat')){
				$cachedoutput=unserialize(file_get_contents('./apicache.dat'));
				if ($cachedoutput!==false){
					if (array_key_exists($id, $cachedoutput)&&array_key_exists('freshness', $cachedoutput[$id])&&is_numeric($cachedoutput[$id]['freshness'])){
						
						$cachedfreshness=floatval($cachedoutput[$id]['freshness']);
					}	
					
				}
			}
	
	if(file_exists('./apicache-timestamp.dat')&&file_get_contents('./apicache-timestamp.dat')!==false&&$cachedfreshness>=floatval(file_get_contents('./apicache-timestamp.dat'))){
	
		echo json_encode($cachedoutput[$id]['data']);
	
	}
	
	
	
	else
	{
		$numberoffiles=0;
		$currentfreshness=0;
		$cachedfresshness=0;
		$moved_freshness=false;
			
		
		if (file_exists('./l2-numberoffiles.dat')){$numberoffiles=intval(file_get_contents('./l2-numberoffiles.dat'));}
		if (file_exists('./storedfreshness.dat')){$currentfreshness=floatval(file_get_contents('./storedfreshness.dat'));}

		
		$cachedoutput=Array();

		if (file_exists('./apicache.php')){
			rename('./apicache.php', 'apicache.dat');
		}
		
		if (true)//$numberoffiles!==count(scandir('./z')))
		{
			$files=scandir('./z');
			$albums=Array();
			foreach ($files as $file){
				if ((! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format)))||$file=='.timestamp'){
					$albums[filectime('./z/'.$file)]=filectime('./z/'.$file);
			
				}
			}
			krsort($albums);
			
			if (count($albums)==intval($numberoffiles)){
				if ($currentfreshness==intval(array_keys($albums)[0])){
					$moved_freshness=true;
				
				}
				
				file_put_contents('./l2-numberoffiles.dat', count($albums));
				if (file_exists('audio-freshness.dat')){
					if (!unlink('audio-freshness.dat')){
						rename ('audio-freshness.dat', 'audio-freshness-TRASH.dat');
						
						}
						
					}
				}
				else //count($albums) has moved
				{	
					file_put_contents('./l2-numberoffiles.dat', count($albums));
				
					unlink('apicache.dat');
					
					
					if (file_get_contents('./audio-freshness.dat')){
						
						$currentfreshness = file_get_contents('./audio-freshness.dat');
						
					}
					else {
						
						$currentfreshness=time();
						if (!file_put_contents('./audio-freshness.dat', $freshness)){
							if (!unlink('./audio-freshness.dat')){
								rename('./audio-freshness.dat', './audio-freshness-TRASH.dat');
							}
						}
					}
					
					
				}
		}
			if (file_exists('./apicache.dat')){
				$cachedfreshness=0;
				$cachedoutput=unserialize(file_get_contents('./apicache.dat'));
				if ($cachedoutput!==false){
					foreach ($cachedoutput as $put){
						if (floatval($put['freshness'])>floatval($cachedfreshness)){
								$cachedfreshness=floatval($put['freshness']);
							
						}
					}	
					
				}
				else {
					unlink('./apicache.dat');
					$cachedfreshness=0;
				}
			}
			else
			{
				$cachedfreshness=0;
				
			}




		if (file_exists('./apicache.dat')){
				$cachedoutput=unserialize(file_get_contents('./apicache.dat'));
			}
		else{
				$cachedoutput=array();
		}


		if (((intval($cachedfreshness)==intval($currentfreshness)))&&!$moved_freshness){
			echo json_encode($cachedoutput[$id]['data']);
			
		}
		else {
			
				//outdated cache
			
				//outdated cache




			$artists=$_GET['listallalbums2'];
			$files=scandir('./z');
			$albumlist=Array();
			
			foreach ($files as $file){
			

				if (! is_dir('./z/'.$file)&&strpos($file, $format)===(strlen($file)-strlen($format))){
					
						$getID3 = new getID3;
						$info = $getID3->analyze('z/'.$file);
						getid3_lib::CopyTagsToComments($info); 
						if(in_array($info['comments']['artist'][0],$artists)&&!in_array($info['comments_html']['album'][0],$albumlist)){ //'comments_html']['album'][0])){
								
								
								
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
			$cachedoutput[$id]['freshness']=$currentfreshness;
			$cachedoutput[$id]['data']=$content;
			if($cachedoutput[$id]['data']!==false){	
				file_put_contents('./apicache.dat', serialize($cachedoutput));
			}
			file_put_contents('./apicache.dat', serialize($cachedoutput));
			file_put_contents('./apicache-timestamp.dat', $currentfreshness);
				
			echo json_encode($content);
			//storing the cache

		}
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
