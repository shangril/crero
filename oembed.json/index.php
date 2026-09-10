<?php
chdir('..');
require_once('config.php');
chdir('./oembed.json');
$album = $_GET['album'] ?? '';


$covers = [];
if (file_exists('./d/covers.txt')&&!is_dir('./d/covers.txt')){
	$covers=trim(file_get_contents('./d/covers.txt'));
	$coverlines=explode("\n", $covers);
	for ($i=0;$i<count($coverlines);$i++){
		$ArtistSites[$coverlines[$i]]=$coverlines[$i+1];
		$i++;
	}
}
if (!file_exists('./sharerCaceh')){
	mkdir('./sharerCaceh');
}
if (file_exists('./sharerCaceh/'.base64_encode($album).str_replace('/', '_').'.dat')

		//cache revalidation if outdated cover
		&&filemtime('./covers/'.rawurlencode($covers[$album])<filemtime('./sharerCaceh/'.base64_encode($album).str_replace('/', '_').'.dat'))

		){
	return file_get_contents('./sharerCaceh/'.base64_encode($album).str_replace('/', '_').'.dat');
	}
//api interrogation
$filez = false;

$filez = [];
$titlez = [];
$artistz = [];
$dataz = file_get_contents('https://'.$clewnapiurl.'?albumtracklist='.urlencode($album));

if ($dataz===false){
	return '';
}
$adataz = explode ("\n", $dataz);
for ($i=0;$i<count($adataz);$i++){
	array_push($filez, $dataz[$i]);
	$i++;
	$titlez[$dataz[$i-1]]= $dataz[$i];
	$i++;
	$artistz[$dataz[$i-2]]= $dataz[$i];

	
}
$albumartists=[];

//get unique artists
foreach ($artitz as $ar){
	$albumartists[$ar]=$ar;
}



header('Content-Type: application/json; charset=utf-8');

// 1. Récupération de l'album demandé
$album_request = $_GET['album'] ?? '';

if (empty($album_request)) {
    http_response_code(400);
    echo json_encode(["error" => "Album manquant"]);
    exit;
}



$server_clean = rtrim($server, '/');
$cover_filename = $covers[$album] ?? '';
$cover_http_url = 'https://' . $server_clean . '/covers/' . ltrim($cover_filename, '/');
$clewnaudiourl_clean = rtrim($clewnaudiourl, '/');

// 3. Construction du lecteur HTML embarqué (Player)
// Ce HTML sera interprété sur le site distant (Discord, WordPress, etc.)
$html_player  = '<div class="oembed-music-player" style="font-family: sans-serif; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; max-width: 600px; background: #fff;">';
$html_player .= '  <div style="display: flex; align-items: center; margin-bottom: 20px;">';
$html_player .= '    <img src="' . htmlspecialchars($cover_http_url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($album, ENT_QUOTES, 'UTF-8') . '" style="width: 120px; height: 120px; object-fit: cover; border-radius: 6px; margin-right: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" />';
$html_player .= '    <div>';
$html_player .= '      <h2 style="margin: 0 0 5px 0; font-size: 20px;">' . htmlspecialchars($album, ENT_QUOTES, 'UTF-8') . '</h2>';
$html_player .= '      <p style="margin: 0; color: #666; font-size: 16px;">' . htmlspecialchars(implode(', ', $albumartists), ENT_QUOTES, 'UTF-8') . '</p>';
$html_player .= '    </div>';
$html_player .= '  </div>';

$html_player .= '  <div style="display: flex; flex-direction: column; gap: 15px;">';

foreach ($filez as $file) {
    $titre = $titlez[$file] ?? 'Titre inconnu';
    $artiste_morceau = $artists[$file] ?? '';
    $mp3_url = $clewnaudiourl_clean . '/' . ltrim($file, '/');
    
    $html_player .= '    <div style="background: #f9f9f9; padding: 10px; border-radius: 6px;">';
    $html_player .= '      <div style="margin-bottom: 8px;">';
    $html_player .= '        <strong style="display: block; font-size: 15px; color: #333;">' . htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') . '</strong>';
    if (!empty($artiste_morceau)) {
        $html_player .= '        <span style="font-size: 13px; color: #777;">' . htmlspecialchars($artiste_morceau, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    $html_player .= '      </div>';
    $html_player .= '      <audio controls preload="none" style="width: 100%; height: 35px;" src="' . htmlspecialchars($mp3_url, ENT_QUOTES, 'UTF-8') . '"></audio>';
    $html_player .= '    </div>';
}

$html_player .= '  </div>';
$html_player .= '</div>';

// 4. Assemblage de la réponse OEmbed conforme
$oembed_response = [
    "version"       => "1.0",
    "type"          => "rich", // "rich" est le standard pour les lecteurs complexes
    "title"         => $album,
    "author_name"   => implode(', ', $albumartists),
    "provider_name" => $server_clean,
    "provider_url"  => "https://" . $server_clean,
    "html"          => $html_player,
    "width"         => 600,
    "height"        => 160 + (count($filez) * 85), // Calcul dynamique approximatif de la hauteur du widget
    "thumbnail_url" => $cover_http_url
];

// 5. Envoi du flux JSON final
echo json_encode($oembed_response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
