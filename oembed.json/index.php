<?php
chdir('..');
require_once('config.php');
chdir('./oembed.json');

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['album'])){

	$album = $_GET['album'] ?? '';



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
}
else if (isset($_GET['v'])){
	$arts = explode ("\n", file_get_contents('../d/artists.txt'));

	sort($arts);

	$list= (file_get_contents($videoapiurl."?listallvids=1"));
	$tokens = explode ("\n", $list);

	$res=array();
	$al=array();
	$title=array();
	$desc=array();
	$art = array();
	$allink = array();

	for ($i=0;$i<count($tokens);$i++){
	if (in_array($tokens[$i+1], $arts)){
		array_push($res, $tokens[$i]);
		$i++;
		array_push($art, $tokens[$i]);
		$i++;
		array_push($title, $tokens[$i]);
		$i++;
		array_push($al, $tokens[$i]);
		array_push($allink, 'https://'.$server.'/?album='.urlencode($tokens[$i]));
		$i++;
		array_push($desc, $tokens[$i]);
	}
	}

	$rescopy = $res ;
	shuffle($res);

	$z = array_search ($_GET['v'], $rescopy);

	$in_title = $title[$z];
	$in_art = $art[$z];
	$in_al = $al[$z];
	$in_allink = $allink[$z];
	$in_desc = $desc[$z];
	$vidtarget = $videourl.rawurlencode($res[0]);
	$vidshare = $res[0];
	$initial_index = $z;

	if (isset($_GET['v'])){

	$zd = array_search($_GET['v'], $rescopy);

	if ($zd !== false){
			$in_title = $title[$zd];
			$in_art = $art[$zd];
			$in_al = $al[$zd];
			$in_allink = $allink[$zd];
			$in_desc = $desc[$zd];

			$vidtarget = $videourl.rawurlencode($rescopy[$zd]);
			$vidshare = $rescopy[$zd];
			$initial_index = $zd;
	

		// Variables supposées existantes (issues de votre base de données ou routing)
		// $in_title, $in_art, $in_al, $in_allink, $in_desc, $in_iframe_url
		$in_iframe_url='https://'.$server.'/oembed.json/vplayer.php?v='.urlencode($_GET['v']);
		// 1. Construction de l'iframe HTML avec échappement de sécurité
		$html = sprintf(
		'<iframe src="%s" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen title="%s"></iframe>',
		htmlspecialchars($in_iframe_url, ENT_QUOTES, 'UTF-8'),
		htmlspecialchars($in_title, ENT_QUOTES, 'UTF-8')
		);

		// 2. Création du tableau de données oEmbed
		$oembed_data = [
		"version"       => "1.0",
		"type"          => "video",
		"title"         => $in_title,
		"author_name"   => $in_art,
		"author_url"    => $in_allink,
		"provider_name" => $sitename,
		"html"          => $html,
		"width"         => 640,
		"height"        => 360,

		// Champs non standardisés par oEmbed mais couramment acceptés par les clients enrichis
		"description"   => $in_desc,
		"album"         => $in_al
		];

		// 3. Génération du JSON
		// JSON_UNESCAPED_SLASHES évite d'échapper les "/" dans l'URL et le HTML
		// JSON_UNESCAPED_UNICODE préserve les accents (ex: "é" au lieu de "\u00e9")
		echo json_encode($oembed_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		exit;
	}
}
	
}
