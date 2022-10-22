<?php
error_reporting(0);
require_once('./config.php');

if (file_exists('./d/artists.txt')){

	$dl_cnt_artists_file=file_get_contents('./d/artists.txt'); 

}
else {
	$dl_cnt_artists_file=file_get_contents($clewnapiurl.'?listartists=true');
	
	
}

if ($dl_cnt_artists_file!==false){

	$dl_cnt_artists=explode("\n", trim($dl_cnt_artists_file));




	$dl_cnt_querystring='';
	foreach ($dl_cnt_artists as $dl_cnt_artist) 
	{
		if (file_exists('./d/artists.txt')){

			$dl_cnt_querystring.='l2[]='.urlencode(htmlentities($dl_cnt_artist, ENT_COMPAT)).'&';
		}
		else {
			$dl_cnt_querystring.='l2[]='.urlencode($dl_cnt_artist).'&';
		}
		
	}



	$dl_cnt_ch = curl_init();

	curl_setopt($dl_cnt_ch, CURLOPT_URL,$clewnapiurl.'?l2=true');
	curl_setopt($dl_cnt_ch, CURLOPT_POST, 1);
	curl_setopt($dl_cnt_ch, CURLOPT_POSTFIELDS, $dl_cnt_querystring);
	curl_setopt($dl_cnt_ch, CURLOPT_CONNECTTIMEOUT ,300); 
	curl_setopt($dl_cnt_ch, CURLOPT_TIMEOUT, 300);

	curl_setopt($dl_cnt_ch, CURLOPT_RETURNTRANSFER, true);

	$dl_cnt_albums_file_json= curl_exec ($dl_cnt_ch);

	curl_close ($dl_cnt_ch);
	$dl_cnt_albums_file=$dl_cnt_albums_file_json;

	$dl_cnt_albums=json_decode($dl_cnt_albums_file_json,true);

	if ($dl_cnt_albums===null){
		
		echo '0';
		
	}
	else {
		echo count($dl_cnt_albums);
	}
}
else{
	echo '-1';
}


?>
