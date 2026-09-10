<?php
chdir('..');
require_once('config.php');
chdir('./oembed.json');
$album = $_GET['album'] ?? '';
//error_reporting(E_ALL);

$covers = [];
if (file_exists('../d/covers.txt')&&!is_dir('../d/covers.txt')){
	$dcovers=trim(file_get_contents('../d/covers.txt'));
	$coverlines=explode("\n", $dcovers);
	for ($i=0;$i<count($coverlines);$i++){
		$covers[$coverlines[$i]]=$coverlines[$i+1];
		$i++;
	}
}
if (!file_exists('./PCaceh')){
	mkdir('./PCaceh');
}
if (isset($covers[$album])&&(file_exists('./PCaceh/'.str_replace('/', '_',base64_encode($album).'.dat'))

		//cache revalidation if outdated cover
		&&filemtime('../covers/'.rawurlencode($covers[$album])<filemtime('./PCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat'))
		)
		){
	echo file_get_contents('./PCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat');
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
$adataz = explode ("\n", $dataz);
for ($i=0;$i<count($adataz);$i++){
	array_push($filez, $adataz[$i]);
	$i++;
	$titlez[$adataz[$i-1]]= $adataz[$i];
	$i++;
	$artistz[$adataz[$i-2]]= $adataz[$i];

	
}
$albumartists=[];

//get unique artists
foreach ($artitz as $ar){
	$albumartists[$ar]=$ar;
}

// 1. Récupération de l'album demandé
$album_request = $_GET['album'] ?? '';

if (empty($album_request)) {
    http_response_code(400);
    die;
    exit;
}



$server_clean = rtrim($server, '/');
$cover_filename = $covers[$album] ?? '';
$cover_http_url = 'https://' . $server_clean . '/covers/' . ltrim($cover_filename, '/');
$clewnaudiourl_clean = rtrim($clewnaudiourl, '/');

// 3. Construction du lecteur HTML embarqué (Player)
// Ce HTML sera interprété sur le site distant


// 1. On prépare les données de la playlist sous forme de tableau PHP pour le transmettre à JavaScript
$playlistData = [];
foreach ($filez as $file) {
    $playlistData[] = [
        'title'  => $titlez[$file] ?? 'Titre inconnu',
        'artist' => $artistz[$file] ?? '',
        'url'    => $clewnaudiourl_clean . '/' . ltrim($file, '/')
    ];
}

// Encodage sécurisé en JSON pour JavaScript
$playlist_json = json_encode($playlistData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$album_artists_str = htmlspecialchars(implode(', ', $albumartists), ENT_QUOTES, 'UTF-8');
$album_title_str = htmlspecialchars($album, ENT_QUOTES, 'UTF-8');
$cover_url_str = htmlspecialchars($cover_http_url, ENT_QUOTES, 'UTF-8');

// 2. On génère le code HTML/CSS/JS de l'iframe
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* CSS adapté aux petits écrans et iFrames */
        body { margin: 0; padding: 0; background: transparent; }
        
        .oembed-music-player { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            border: 1px solid #e1e1e1; 
            padding: 15px; 
            border-radius: 8px; 
            background: #fff;
            box-sizing: border-box;
            max-width: 100%;
        }

        .album-header { 
            display: flex; 
            align-items: center; 
            margin-bottom: 15px; 
            flex-wrap: wrap; /* Permet le retour à la ligne si l'iframe est trop étroite */
            gap: 15px;
        }

        .cover-img { 
            width: 100px; 
            height: 100px; 
            object-fit: cover; 
            border-radius: 6px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            flex-shrink: 0;
        }

        .album-info { 
            flex: 1; 
            min-width: 120px; 
        }

        .album-title { margin: 0 0 5px 0; font-size: 18px; line-height: 1.2; }
        .album-artist { margin: 0; color: #666; font-size: 14px; }

        .player-container { 
            background: #f9f9f9; 
            padding: 15px; 
            border-radius: 6px; 
        }

        .current-track-info { margin-bottom: 12px; text-align: center; }
        .track-title { display: block; font-size: 15px; font-weight: 600; color: #222; }
        .track-artist { font-size: 13px; color: #666; }

        audio { width: 100%; height: 40px; margin-bottom: 10px; outline: none; }

        .controls { 
            display: flex; 
            justify-content: center; 
            gap: 10px; 
        }

        .btn-ctrl { 
            background: #e4e4e7; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 20px; 
            font-size: 13px;
            font-weight: 600; 
            cursor: pointer; 
            color: #333; 
            transition: background 0.2s; 
        }

        .btn-ctrl:hover:not(:disabled) { background: #d4d4d8; }
        .btn-ctrl:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Media Queries pour le format très étroit de Mastodon sur mobile */
        @media (max-width: 350px) {
            .cover-img { width: 80px; height: 80px; }
            .album-title { font-size: 16px; }
            .btn-ctrl { padding: 8px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="oembed-music-player">
    <div class="album-header">
        <img src="<?php echo $cover_url_str; ?>" alt="<?php echo $album_title_str; ?>" class="cover-img" />
        <div class="album-info">
            <h2 class="album-title"><?php echo $album_title_str; ?></h2>
            <p class="album-artist"><?php echo $album_artists_str; ?></p>
        </div>
    </div>

    <div class="player-container">
        <div class="current-track-info">
            <span id="track-title" class="track-title">loading...</span>
            <span id="track-artist" class="track-artist"></span>
        </div>
        
        <audio id="main-audio" controls preload="metadata"></audio>
        
        <div class="controls">
            <button id="btn-prev" class="btn-ctrl"> &laquo; </button>
            <button id="btn-next" class="btn-ctrl"> &raquo; </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupération sécurisée de la playlist depuis PHP
    const tracks = <?php echo $playlist_json; ?>;
    let currentIndex = 0;

    const audioEle = document.getElementById('main-audio');
    const titleEle = document.getElementById('track-title');
    const artistEle = document.getElementById('track-artist');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    if (tracks.length === 0) {
        titleEle.textContent = "Aucun titre disponible";
        return;
    }

    // Fonction pour charger une piste
    function loadTrack(index, play = false) {
        const track = tracks[index];
        titleEle.textContent = track.title;
        artistEle.textContent = track.artist;
        audioEle.src = track.url;
        
        // Gérer l'état des boutons (désactivé si début/fin de liste)
        btnPrev.disabled = (index === 0);
        btnNext.disabled = (index === tracks.length - 1);

        if (play) {
            // Le .catch permet d'éviter une erreur console si le navigateur bloque l'autoplay non sollicité
            audioEle.play().catch(err => console.log('Autoplay bloqué', err));
        }
    }

    // Écouteurs sur les boutons
    btnPrev.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            loadTrack(currentIndex, true);
        }
    });

    btnNext.addEventListener('click', () => {
        if (currentIndex < tracks.length - 1) {
            currentIndex++;
            loadTrack(currentIndex, true);
        }
    });

    // Passer automatiquement au suivant quand le titre est terminé
    audioEle.addEventListener('ended', () => {
        if (currentIndex < tracks.length - 1) {
            currentIndex++;
            loadTrack(currentIndex, true);
        }
    });

    // Initialisation : charge la première piste sans la jouer automatiquement (règles des navigateurs)
    loadTrack(0, false);
});
</script>

</body>
</html>
<?php
$html_player = ob_get_clean();

// 5. Envoi du HTML final
echo $html_player;
	if ($html_player!=''){
		file_put_contents('./PCaceh/'.str_replace('/', '_',base64_encode($album)).'.dat', $html_player);
	}
	
exit;
