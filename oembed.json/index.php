<?php
chdir('..');
require_once('config.php');
chdir('./oembed.json');
//error_reporting(E_ALL);
$album = $_GET['album'] ?? '';



header('Content-Type: application/json; charset=utf-8');
$covers = [];
if (file_exists('../d/covers.txt')&&!is_dir('../d/covers.txt')){
	$coversd=trim(file_get_contents('../d/covers.txt'));
	$coverlines=explode("\n", $coversd);
	for ($i=0;$i<count($coverlines);$i++){
		$covers[$coverlines[$i]]=$coverlines[$i+1];
		$i++;
	}
}
if (!file_exists('./sharerCaceh')){
	mkdir('./sharerCaceh');
}
if (isset($covers[$album]) && (file_exists('./sharerCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat')

		//cache revalidation if outdated cover
		&&(filemtime('../covers/'.$covers[$album])<filemtime('./sharerCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat'))
		)
		)
		{
	echo file_get_contents('./sharerCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat');
	exit;
	}
//api interrogation
$filez = false;

$filez = [];
$titlez = [];
$artistz = [];
$dataz = file_get_contents($clewnapiurl.'?albumtracklist='.urlencode($album));

if ($dataz===false){
	return '';
}
$adataz = explode ("\n", trim($dataz));
for ($i=0;$i<count($adataz);$i++){
	array_push($filez, $adataz[$i]);
	$i++;
	$titlez[$dataz[$i-1]]= $adataz[$i];
	$i++;
	$artistz[$dataz[$i-2]]= $adataz[$i];

	
}
$albumartists=[];

//get unique artists
foreach ($artistz as $ar){
	$albumartists[$ar]=$ar;
}



// 1. Récupération de l'album demandé
$album_request = $_GET['album'] ?? '';

if (empty($album_request)) {
    http_response_code(400);
    echo json_encode(["error" => "Album not found"]);
    exit;
}



$server_clean = rtrim($server, '/');
$cover_filename = $covers[$album] ?? '';
$cover_http_url = 'https://' . $server_clean . '/covers/' . ltrim($cover_filename, '/');
$clewnaudiourl_clean = rtrim($clewnaudiourl, '/');


$html_player .= '<iframe src="https://'.$server_clean.'/oembed.json/player.php?album='.urlencode($album).'"></iframe>';
$zalbumartists='';
if(count($albumartists)>1){
$zalbumartists = implode(', ', $albumartists);
}
else{
		$zalbumartists=array_keys($albumartists)[0];
	}
// 4. Assemblage de la réponse OEmbed conforme
$oembed_response = [
    "version"       => "1.0",
    "type"          => "rich", // "rich" est le standard pour les lecteurs complexes
    "title"         => $album,
    "author_name"   => $zalbumartists,
    "provider_name" => $server_clean,
    "provider_url"  => "https://" . $server_clean,
    "html"          => $html_player,
    "thumbnail_url" => $cover_http_url
];
//"width"         => 600,
 //   "height"        => 160 + (count($filez) * 85), // Calcul dynamique approximatif de la hauteur du widget
    
// 5. Envoi du flux JSON final
echo json_encode($oembed_response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if ($oembed_response!=''){
		file_put_contents('./sharerCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat', json_encode($oembed_response,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
	
exit;
