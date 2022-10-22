<?php
error_reporting(0);
require_once('./config.php');


if (file_exists('./d/artists.txt')){

	$str_cnt_artists_file=file_get_contents('./d/artists.txt'); 

}
else {
	$str_cnt_artists_file=false;
	
	
}

if ($str_cnt_artists_file!==false){

	$str_cnt_artists=explode("\n", trim($str_cnt_artists_file));




	$str_cnt_querystring='';
	foreach ($str_cnt_artists as $str_cnt_artist) 
	{
		$str_cnt_querystring.='l2[]='.urlencode($str_cnt_artist).'&';
		
		
	}



	$str_cnt_ch = curl_init();

	curl_setopt($str_cnt_ch, CURLOPT_URL, $serverapi.'?l2=true');
	curl_setopt($str_cnt_ch, CURLOPT_POST, 1);
	curl_setopt($str_cnt_ch, CURLOPT_POSTFIELDS, $str_cnt_querystring);
	curl_setopt($str_cnt_ch, CURLOPT_CONNECTTIMEOUT ,3000); 
	curl_setopt($str_cnt_ch, CURLOPT_TIMEOUT, 3000);

	curl_setopt($str_cnt_ch, CURLOPT_RETURNTRANSFER, true);

	$str_cnt_albums_file_json= curl_exec ($str_cnt_ch);

	curl_close ($str_cnt_ch);
	$str_cnt_albums_file=$str_cnt_albums_file_json;

	$str_cnt_albums=json_decode($str_cnt_albums_file_json, true);

	if ($str_cnt_albums===null){
		
		echo '0';
		
	}
	else {
		echo count($str_cnt_albums);
	}
}
else{
	echo '-1';
}


?>
