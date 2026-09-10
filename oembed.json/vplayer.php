<?php
chdir('..');
require_once('config.php');
chdir('./oembed.json');
// Récupération de la variable GET (avec une vérification de base)
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
		array_push($allink, './?album='.urlencode($tokens[$i]));
		$i++;
		array_push($desc, $tokens[$i]);
	}
}

$rescopy = $res ;
shuffle($res);

$z = array_search ($res[0], $rescopy);

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
	}
}

$v = isset($_GET['v']) ? $_GET['v'] : '';

// Concaténation de l'URL
$full_url = $videourl . rawurlencode($v);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $sitename ; ?> video aler</title>
    <style>
        /* Réinitialise les marges pour que la vidéo remplisse parfaitement l'iframe */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000; /* Fond noir typique des players */
            overflow: hidden; /* Empêche les barres de défilement */
        }
        video {
            width: 100%;
            height: 100%;
            outline: none;
        }
    </style>
</head>
<body>

    <?php if (!empty($v)): ?>
        <!-- Lecteur vidéo HTML5 -->
        <video controls autoplay>
            <source src="<?php echo htmlspecialchars($full_url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
            Votre navigateur ne prend pas en charge la lecture de cette vidéo.
        </video>
    <?php else: ?>
        <p style="color: #fff; text-align: center; font-family: sans-serif; margin-top: 20px;">
            Erreur : Paramètre vidéo manquant.
        </p>
    <?php endif; ?>

</body>
</html>
