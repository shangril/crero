<?php
error_reporting(E_ERROR | E_PARSE);
if (!file_exists('./audio')){
	mkdir ('./audio');
}


$formats=array('mp4');

if (isset($_GET['listallvids'])){
	//works only with mp4 vids
	if (in_array('mp4', $formats)){

		header('Content-Type: text/plain; charset=utf-8');

		$res=array();
		$al=array();
		$title=array();
		$desc=array();
		$art = array();
		
		
		$files=scandir('./audio');
		foreach ($files as $file) {
			if (!is_dir($file)){
				
					if (strpos ($file, '.mp4')==(strlen($file)-4)){
						array_push($res, $file);
					
						array_push($art, trim(file_get_contents(str_replace('.mp4', '.artist.txt', 'audio/'.$file))));
						
						array_push($al, trim(file_get_contents(str_replace('.mp4', '.album.txt', 'audio/'.$file))));
						array_push($title, trim(file_get_contents(str_replace('.mp4', '.title.txt', 'audio/'.$file))));
						array_push($desc, trim(file_get_contents(str_replace('.mp4', '.description.txt', 'audio/'.$file))));
						
					}
			}
		
		}
		for ($i=0;$i<count($res);$i++){
			echo $res[$i]."\n";
			echo $art[$i]."\n";
			echo $title[$i]."\n";
			echo $al[$i]."\n";
			echo $desc[$i]."\n";
		}
	}
}
	
else if (isset($_GET['hasalbum'])){
	header('Content-Type: text/plain; charset=utf-8');


	$al=$_GET['hasalbum'];
	$res=array();
	
	$files=scandir('./audio');
	foreach ($files as $file) {
		if (!is_dir($file)){
			
			if (mime_content_type('./audio/'.$file)=='text/plain'){
				if (strpos ($file, '.album.txt')==(strlen($file)-10)){
					if(trim(file_get_contents('audio/'.$file))==$al){
					
						array_push($res, $file);
					
					}
			}
		}
	}
}
	echo count($res);
	die();
}
	
else if (isset($_GET['artist'])&&isset($_GET['album'])&&isset($_GET['title'])&&isset($_GET['gettarget'])) {
		header('Content-Type: text/plain; charset=utf-8');
		$files=scandir('./audio');
		foreach ($files as $file) {
			if (!is_dir($file)){
				
				if (mime_content_type('./audio/'.$file)=='text/plain'){
					if (strpos ($file, '.artist.txt')==(strlen($file)-11)){
						if ((trim(file_get_contents('audio/'.$file)))==$_GET['artist']
						&& (trim(file_get_contents('audio/'.str_replace('.artist.txt', '.album.txt',$file))))==$_GET['album']
						&& (trim(file_get_contents('audio/'.str_replace('.artist.txt', '.title.txt',$file))))==$_GET['title']
						) {
							echo str_replace ('.artist.txt', '', $file);
							exit();
							
							
						}/*
						if ((trim(file_get_contents('./audio/'.$file)))===html_entity_decode($_GET['artist'])){
						echo 'artist matched';
						}
						if ((trim(file_get_contents('./audio/'.str_replace('.artist.txt', '.album.txt',$file))))===html_entity_decode($_GET['album'])){
						echo 'album matched';
						}
						if ((trim(file_get_contents('./audio/'.str_replace('.artist.txt', '.title.txt',$file))))===html_entity_decode($_GET['title'])){
						echo 'title matched';
						}	
						*/
					}
					
					
					
					
					
					
				}
				
			}
			
			
		}
	
	
	
}
else if (isset($_GET['listformats'])){
	header('Content-Type: text/plain; charset=utf-8');
	
	$target=str_replace('/', '', $_GET['listformats']);
	
	foreach ($formats as $format)
	{
		if (file_exists('./audio/'.$target.'.'.$format)){
			echo $format."\n";
			
		}
	}
	
}
else if (isset($_GET['baselistformats'])){
	header('Content-Type: text/plain; charset=utf-8');
	foreach ($formats as $format){
		echo $format."\n";
	}


}
else if (isset($_GET['hasdowngrade'])&&isset($_GET['format'])) {
	die();
	header('Content-Type: text/plain; charset=utf-8');
	$files=scandir('./audio');
	$target=str_replace('./','',$_GET['hasdowngrade']);
	$format=str_replace('./','',$_GET['format']);
	$result=Array();
	
	for ($i=0;$i<=25000; $i++){
		if (in_array($i.'.'.$target.'.'.$format, $files)) {
			array_push($result, $i);
			
			
		}
		
		
		
	}
	foreach ($result as $line) {
		echo $line."\n";
	}
	
}
else if (isset($_GET['listalbvids'])){
	//PARAM album title
	//outputs one line with vid base name, one line with vid title, one line with vid basename, and so on
	$currentfreshness=0;
	$cachedfreshness=0;
	if (file_exists('./storedfreshness.dat')){$currentfreshness=file_get_contents('./storedfreshness.dat');}

	$format=$formats[0];
	
	$id=$_GET['listalbvids'];
	
	header('Content-Type: text/plain; charset=utf-8');
	$cached=true;
	if (file_exists('./apicache.dat')){
		$cachedoutput=unserialize(file_get_contents('./apicache.dat'));
		$cachedfreshness=intval($cachedoutput[$id]['freshness']);
		
	}
	else
	{
		$cachedfreshness=0;
		$cached=false;
	}
	if (true!==scandir('./audio')&&$numberoffiles!==count(scandir('./audio')))
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
	else if (true!==scandir('./audio'));
	{
			$currentfreshness=time();
			
			file_put_contents('./numberoffiles.dat', '0');
				
			
			file_put_contents('./storedfreshness.dat', $currentfreshness);
		
	}
	if ($cachedfreshness>=$currentfreshness&&$cached){
		echo $cachedoutput[$id]['data'];
		
	}
	else {
		
			//outdated cache


	
	
	
		$files=scandir('./audio');
		sort($files);
		$data='';
			foreach ($files as $file) {
				if (!is_dir($file)){
				if (strpos($file,'.album.txt')==strlen($file)-10&&trim(file_get_contents('./audio/'.$file))==$_GET['listalbvids']){
					$data.=str_replace('.album.txt', '', $file)."\n";
					$data.=trim(file_get_contents('./audio/'.str_replace('.album.txt', '.title.txt', $file)))."\n";
					
					
				
				
				}
			}
		}
		$cachedoutput[$id]['freshness']=time();
		$cachedoutput[$id]['data']=serialize($data);
		file_put_contents('./apicache.dat', serialize($cachedoutput));

		
		echo $data;
	}
}

?>
