<?php
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);

if(!file_exists('./metadatacache')){
	mkdir('./metadatacache');
}

if(!file_exists('./metadatacache/albums')){
	mkdir('./metadatacache/albums');
}

if(!file_exists('./metadatacache/tracklists')){
	mkdir('./metadatacache/tracklists');
}
if(!file_exists('./metadatacache/json')){
	mkdir('./metadatacache/json');
}

if (!file_exists('../d/wizard_completed.txt')){
	die("Initial wizard setup not completed");
	//the initial CMS setup hasn't been completed
}
$art64='';
$artists=Array();


$artlist=file_get_contents('../d/artists.txt');
if ($artlist!==false){
	$artists=explode("\n", trim($artlist));
}
if 
 (($clewnapiurl=file_get_contents('../d/clewnapiurl.txt')) === false){
	  die("API URL not set");
  }

$clewnapiurl=trim($clewnapiurl);

$formats='';
if (!file_exists('./formats.txt')){
	if(($formats = file_get_contents($clewnapiurl.'?listformats=true'))!==false){
		file_put_contents('./formats.txt', $formats);
		}
	}

else {
	if(($formats=file_get_contents('./formats.txt'))===false){
		$formats='';
		}

	}

if ($formats!==false&&!strstr($formats, 'mp3')){
	die();
}
else{
	
	$format='mp3';
	
}	
$freshness = file_get_contents($clewnapiurl.'?freshness=1');

if ($freshness===false){
	http_response_code(500);
	die("API didn't reply for freshness");
}
else{
	$freshness = intval($freshness);
}
if ($artlist!==false){
	$tracklist=array();
	$albumlist=array();
	echo "\nentering artist loop";
	foreach ($artists as $artist){
		echo "\nartist: $artist";
		$albums=false;
		$artist64=str_replace('/', '_',base64_encode($artist));
		
		if (file_exists('./metadatacache/albums/'.$artist64.'.txt')&&
			filemtime('./metadatacache/albums/'.$artist64.'.txt')>$freshness
			
			&& filesize('./metadatacache/albums/'.$artist64.'.txt')>0){
				$albums=file_get_contents('./metadatacache/albums/'.$artist64.'.txt');
				echo "\nalbums already cached";
			}
		else {
			$running=true;
			while($running){
				echo "\ntrying to cache";
				$albums=file_get_contents($clewnapiurl.'?listalbums='.urlencode($artist));
				if ($albums!==false){
					file_put_contents('./metadatacache/albums/'.$artist64.'.txt', $albums);
					$running=false;
					echo "\ncached OK";
				}
			}
		}
		if ($albums!==false){
			$albdat = explode("\n", trim($albums));
			echo "\nentering album loop";
			foreach ($albdat as $album){
				echo "\nalbum: $album";
				$tracks=false;
				$album64=str_replace('/', '_',base64_encode($album)).$artist64;
				if (file_exists('./metadatacache/tracklists/'.$album64.'.txt')&&
					filemtime('./metadatacache/tracklists/'.$album64.'.txt')>$freshness
					&&filesize('./metadatacache/tracklists/'.$album64.'.txt')>0
					){
						$tracks=file_get_contents('./metadatacache/tracklists/'.$album64.'.txt');
						echo "\ntracks already cached";
					}
				else {
					$running=true;
					while($running){
						echo "\ntrying to cache";
	
						$tracks=file_get_contents($clewnapiurl.'?gettracks_unentitified='.urlencode(html_entity_decode($album)));
						
						if ($tracks!==false){
							$tracks=trim($tracks);
							file_put_contents('./metadatacache/tracklists/'.$album64.'.txt', $tracks);
							$running=false;
							echo "\ncached OK";
						}
					}
					
				}
								
				if($tracks!==false){
					foreach (explode("\n", trim($tracks)) as $track){
						$tracklist[$track]=$track;
						$albumlist[$track]=$album;
					}
				}
			}
		}
	}
	echo "\ncaching JSON";
	//last caching thing: the json
	$query = '?';
	
	foreach($artists as $art){
		$query.='l2[]='.urlencode(($art)).'&';
	}//TODO move this to POST method
	
	$jsonlastlist=false;
	if (file_exists('./metadatacache/json/'.$art64.'l2.json')&&file_get_contents('./metadatacache/json/'.$art64.'l2.json')==='null'){
		unlink('./metadatacache/json/'.$art64.'l2.json');
	}
	
	
	if (file_exists('./metadatacache/json/'.$art64.'l2.json')){
		if($freshness>filemtime('./metadatacache/json/'.$art64.'l2.json')){
			$jsonlastlist=file_get_contents($clewnapiurl.$query);
		}
		else{
			$jsonlastlist=file_get_contents('./metadatacache/json/'.$art64.'l2.json');
			echo "\nalready cached";
		}
	}
	else {
		$jsonlastlist=file_get_contents($clewnapiurl.$query);
	
	}
	
	if (false!==$jsonlastlist&&false!==($lastlist=json_decode($jsonlastlist, true))&&is_array($lastlist)){
		
		//savethecache
		
		file_put_contents('./metadatacache/json/'.$art64.'l2.json', $jsonlastlist);
		echo "\ncached OK";
	
	}



echo "\nentering track loop";
	foreach ($tracklist as $trackitem){
		if (trim($trackitem)==''){
			break;
		}
		echo "\ntrack $trackitem";
		$m='';
		$title=false;
		/*
		echo 'entering $title';
		echo '$freshness is'.$freshness;
		echo 'file_exists returns '.file_exists('./metadatacache/'.$trackitem.'.title.txt');
		echo 'filemtime returns '.filemtime('./metadatacache/'.$trackitem.'.title.txt');
		echo 'filesize returns '.filesize('./metadatacache/'.$trackitem.'.title.txt');
		*/
		$t=$freshness-1;
		if (false===($t=filemtime('./metadatacache/'.$trackitem.'.title.txt'))){
			$t=$freshness-1;
		}
		
		
		if (file_exists('./metadatacache/'.$trackitem.'.title.txt')&&
			$t>$freshness
			&&(false!==($m=file_get_contents('./metadatacache/'.$trackitem.'.title.txt')))&&strlen($m)>0){
				$title=$m;
				//echo "\ntitle already cached";
			}
		else{
			//echo 'entering running !!!!!';
			$running=true;
			$sleeper=1;
			while($running){
				$title=file_get_contents($clewnapiurl.'?gettitle='.urlencode($trackitem));
				echo "\ntrying to cache title";
				if ($title!==false){
					$title=trim($title);
					file_put_contents('./metadatacache/'.$trackitem.'.title.txt', $title);
					$running=false;
					echo "\ncached OK";
					}
				else{
					//sleep(intval(mt_rand(1,16)));
echo "failure on $trackitem";$running=false;
					}
				
				
				}
				
				//$sleeper=1.25*$sleeper;
			}
			
			
		
		$duration = false;
		
		$t=$freshness-1;
		if (false===($t=filemtime('./metadatacache/'.$trackitem.'.duration.txt'))){
			$t=$freshness-1;
		}
		
		
		if (file_exists('./metadatacache/'.$trackitem.'.duration.txt')&&
			$t>$freshness
			&&(false!==($m=file_get_contents('./metadatacache/'.$trackitem.'.duration.txt')))&&strlen($m)>0){
				$duration=$m;
				//echo "\nduration already cached";
			}
		
		else{
			$running=true;
			$sleeper=1;
			while ($running){
				$duration=file_get_contents($clewnapiurl.'?duration='.urlencode($trackitem.'.'.$format));
				echo "\ntrying to cache duration";
				if ($duration!==false&&strlen(trim($duration))>0){
					$duration=trim($duration);
					file_put_contents('./metadatacache/'.$trackitem.'.duration.txt', $duration);
					$running=false;
					echo "\ncached OK";
				}
				else{
					//sleep(intval(mt_rand(1,16)));
					echo "failure on $trackitem";$running=false;
				}
				
			}
				//$sleeper=1.25*$sleeper;
				
		}
		
		
		
		$artist=false;
		$t=$freshness-1;
		if (false===($t=filemtime('./metadatacache/'.$trackitem.'.artist.txt'))){
			$t=$freshness-1;
		}
		if (file_exists('./metadatacache/'.$trackitem.'.artist.txt')&&
			$t>$freshness
			&&(false!==($m=file_get_contents('./metadatacache/'.$trackitem.'.artist.txt')))&&strlen($m)>0){
				$artist=$m;
				//echo "\nartist already cached";
			}
		
		else{
			$running=true;
			$sleeper=1;
			while ($running){
				echo "\ntrying to cache artist";
				$artist=file_get_contents($clewnapiurl.'?getartist='.urlencode($trackitem));
				
				if ($artist!==false){
					$artist=trim($artist);
					file_put_contents('./metadatacache/'.$trackitem.'.artist.txt', $artist);
					$running=false;
					echo "\ncached OK";
				}
				else{
					//sleep(intval(mt_rand(1,16)));
echo "failure on $trackitem";$running=false;
				}
			}
		}
		$pubdate=false;
		
		$t=$freshness-1;
		if (false===($t=filemtime('./metadatacache/'.$trackitem.'.pubdate.txt'))){
			$t=$freshness-1;
		}
		
		if (file_exists('./metadatacache/'.$trackitem.'.pubdate.txt')&&
			$t>$freshness
			&&(false!==($m=file_get_contents('./metadatacache/'.$trackitem.'.pubdate.txt')))&&strlen($m)>0){
				$pubdate=$m;
				//echo "\npubdate already cached";
			}
		
		else{
			$running=true;
			$sleeper=1;
			while ($running){
				$pubdate=file_get_contents($clewnapiurl.'?pubdate='.urlencode($trackitem).'.mp3');
				echo "\ntrying to cache pubdate";
				if ($pubdate!==false){
					$pubdate=trim($pubdate);
					file_put_contents('./metadatacache/'.$trackitem.'.pubdate.txt', $pubdate);
					$running=false;
					echo "\ncached OK";
				}
				else{
					//sleep(intval(mt_rand(1,16)));
echo "failure on $trackitem";$running=false;
				
				}
				//$sleeper=1.25*$sleeper;
				
			}
		}
		
		$length=false;
		$t=$freshness-1;
		if (false===($t=filemtime('./metadatacache/'.$trackitem.'.length.txt'))){
			$t=$freshness-1;
			
		}
		if (file_exists('./metadatacache/'.$trackitem.'.length.txt')&&
			$t>$freshness
			&&(false!==($m=file_get_contents('./metadatacache/'.$trackitem.'.length.txt')))&&strlen($m)>0){
				$length=$m;
				//echo "\nlength already cached";
			}
		
		else{
			$running=true;
			$sleeper=1;
			while ($running){
				echo "\ntrying to cache length";
				$length=file_get_contents($clewnapiurl.'?length='.urlencode($trackitem).'.mp3');
				
				if ($length!==false){
					$length=trim($length);
					file_put_contents('./metadatacache/'.$trackitem.'.length.txt', $length);
					$running=false;
					echo "\ncached OK";
				}
				else{
					//sleep(intval(mt_rand(1,16)));
echo "failure on $trackitem";$running=false;
				
				}
				//$sleeper=1.25*$sleeper;
				
			}
		}
	}
}
echo "\nResponse code 0xDEADBEEF: Cache Build"
?>
