<?php
error_reporting(E_ERROR | E_PARSE);
if (!file_exists('./audio')){
	mkdir ('./audio');
}


$formats=array('mp4');
	
if (isset($_GET['hasalbum'])){
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
else if (isset($_GET['hasdowngrade'])&&isset($_GET['format'])) {
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
?>
