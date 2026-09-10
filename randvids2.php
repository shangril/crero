<?php
require_once('./config.php');
$whitelist = false;
if (isset($_GET['a'])){
	$whitelist = $_GET['a'];
}

/* ------------------------------------------------------------------ *
 *  Chargement des vidéos / métadonnées.
 *  NB: ce bloc était auparavant exécuté après le <head>. Il est
 *  déplacé ici (avant toute sortie HTML) pour pouvoir alimenter les
 *  balises OpenGraph et le lien de découverte oEmbed dans le <head>.
 * ------------------------------------------------------------------ */
$arts = explode ("\n", file_get_contents('./d/artists.txt'));
if ($whitelist!==false){
	$arts = array_intersect($whitelist, $arts);
}
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

/* ------------------------------------------------------------------ *
 *  URL courante, type MIME de la vidéo, URL OpenGraph / oEmbed.
 * ------------------------------------------------------------------ */
$ext = strtolower(pathinfo($vidshare, PATHINFO_EXTENSION));
$mimemap = array('mp4'=>'video/mp4','webm'=>'video/webm','ogv'=>'video/ogg','mov'=>'video/quicktime');
$vidmime = isset($mimemap[$ext]) ? $mimemap[$ext] : 'video/mp4';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$selfpath = strtok($_SERVER['REQUEST_URI'], '?'); // chemin sans query string

$og_desc = ($in_desc !== '') ? $in_desc : $description;
?><!DOCTYPE html>
<html>
<head>
<title>Random videos from <?php echo htmlspecialchars($sitename);?></title>
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />

<meta name="description" value="<?php echo htmlspecialchars($description);?>"/>
<meta charset="UTF-8"/>
<link href="./style.css?v=2" rel="stylesheet" type="text/css"/>
<meta name="viewport" content="width=device-width" />

<!-- OpenGraph -->
<meta property="og:type" content="video.other" />
<meta property="og:title" content="<?php echo htmlspecialchars($in_title);?>" />
<meta property="og:description" content="<?php echo htmlspecialchars($og_desc);?>" />
<meta property="og:site_name" content="<?php echo htmlspecialchars($sitename);?>" />
<meta property="og:url" content="<?php echo htmlspecialchars($pageurl);?>" /><!-- -->
<meta property="og:video" content="<?php echo htmlspecialchars($vidtarget);?>" />
<meta property="og:video:secure_url" content="<?php echo htmlspecialchars($vidtarget);?>" />
<meta property="og:video:type" content="<?php echo htmlspecialchars($vidmime);?>" />-->

<!-- oEmbed discovery  -->
<link rel="alternate" type="application/json+oembed" href="./oembed.json/?v=<?php echo urlencode($_GET['v']);?>" title="<?php echo htmlspecialchars($in_title);?>" />

</head>
<body onload="init();">
	<script>
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0

	<?php
	echo "var res=[";
	for ($i=0;$i<count($res);$i++){
		echo "'".$videourl.rawurlencode($rescopy[$i])."'";
		if ($i<count($rescopy)-1){
			echo ', ';
		}
	}
	echo "];\n";
	echo "var art=[";
	for ($i=0;$i<count($art);$i++){
		echo "'".htmlspecialchars($art[$i])."'";
		if ($i<count($art)-1){
			echo ', ';
		}
	}
	echo "];\n";
	echo "var title=[";
	for ($i=0;$i<count($title);$i++){
		echo "'".htmlspecialchars($title[$i])."'";
		if ($i<count($title)-1){
			echo ', ';
		}
	}
	echo "];\n";
	echo "var al=[";
	for ($i=0;$i<count($al);$i++){
		echo "'".htmlspecialchars($al[$i])."'";
		if ($i<count($al)-1){
			echo ', ';
		}
	}
	echo "];\n";
	echo "var desc=[";
	for ($i=0;$i<count($desc);$i++){
		echo "'".htmlspecialchars($desc[$i])."'";
		if ($i<count($desc)-1){
			echo ', ';
		}
	}
	echo "];\n";
	echo "var allink=[";
	for ($i=0;$i<count($al);$i++){
		echo "'./?album=".urlencode($al[$i])."'";
		if ($i<count($al)-1){
			echo ', ';
		}
	}
	echo "];\n";
	?>

	// index (dans res/art/title/al/desc/allink) de la vidéo affichée par le serveur au chargement
	var initial_index = <?php echo (int)$initial_index; ?>;

	// "order" contient une permutation des indices : c'est la playlist en cours,
	// "pos" est la position courante dans cette permutation. Contrairement à
	// l'ancienne version, res/art/title/al/desc/allink ne sont JAMAIS modifiés :
	// on peut donc naviguer en avant (next) ET en arrière (prev) sans perdre de données.
	var order = [];
	var pos = 0;

	function shuffleOrder(keepFirst){
		order = [];
		for (var i=0;i<res.length;i++){ order.push(i); }
		for (var i=order.length-1;i>0;i--){
			var j = Math.floor(Math.random()*(i+1));
			var tmp = order[i]; order[i]=order[j]; order[j]=tmp;
		}
		if (typeof keepFirst !== 'undefined' && keepFirst !== -1){
			var idx = order.indexOf(keepFirst);
			if (idx !== -1){
				order.splice(idx,1);
				order.unshift(keepFirst);
			}
		}
	}

	// --- Plein écran automatique : entre en plein écran à la lecture, en sort à la pause ---
	function isFullscreen(){
		return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
	}

	function enterFullscreen(el){
		if (isFullscreen()){ return; } // déjà en plein écran (ex: enchaînement auto next()) : rien à faire
		var req = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen || el.msRequestFullscreen;
		if (req){
			try {
				var p = req.call(el);
				if (p && p.catch){ p.catch(function(){ /* navigateur ayant refusé (pas de geste utilisateur) : on ignore */ }); }
			} catch(e){}
		}
	}

	function exitFullscreen(){
		if (!isFullscreen()){ return; }
		var exit = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
		if (exit){
			try { exit.call(document); } catch(e){}
		}
	}

	function display(v, i){
		v.src = res[i];
		document.getElementById('vtitle').innerHTML = title[i];
		document.getElementById('vartist').innerHTML = art[i];
		document.getElementById('valbum').innerHTML = '<a href="'+allink[i]+'">'+al[i]+'</a>';
		document.getElementById('vdesc').innerHTML = desc[i];

		var shareUrl = "?v="+res[i].replace("<?php echo $videourl;?>","");
		document.getElementById('share').href = shareUrl;
		document.title = title[i]+" - "+art[i];

		if (window.history && window.history.replaceState){
			window.history.replaceState(null, '', shareUrl);
		}
	}

	function goTo(v, p){
		if (order.length===0){ return; }
		pos = ((p % order.length) + order.length) % order.length;
		display(v, order[pos]);
		document.getElementById('splash').innerHTML = "Vidéo "+(pos+1)+" / "+order.length;
	}

	function playAt(v, p){
		goTo(v, p);
		v.play();
	}

	// Appelée automatiquement à la fin d'une vidéo (onEnded) ou par le bouton "Suivant".
	function next(v){
		var nextPos = pos + 1;
		if (nextPos >= order.length){
			// La playlist a été entièrement parcourue : on la remélange.
			shuffleOrder();
			nextPos = 0;
		}
		playAt(v, nextPos);
	}

	// Appelée par le bouton "Précédent".
	function prev(v){
		playAt(v, pos - 1);
	}

	// Saute directement à la vidéo d'index absolu i (dans res/art/title...), via la liste déroulante.
	function jumpTo(v, i){
		var idx = order.indexOf(i);
		if (idx === -1){ idx = i; } // ne devrait pas arriver, order est une permutation complète
		playAt(v, idx);
	}

	function jumpToSelected(sel){
		var i = parseInt(sel.value, 10);
		sel.selectedIndex = 0; // remet le placeholder pour pouvoir resélectionner la même entrée plus tard
		if (!isNaN(i)){
			jumpTo(document.getElementById('player'), i);
		}
	}

	function buildJumpList(){
		var sel = document.getElementById('jumpto');
		if (!sel){ return; }
		var placeholder = document.createElement('option');
		placeholder.value = '';
		placeholder.textContent = '\u2014 jump to video \u2014';
		sel.appendChild(placeholder);

		// on trie l'affichage par "artiste - titre" sans toucher aux tableaux res/art/title/...
		var indices = [];
		for (var i=0;i<res.length;i++){ indices.push(i); }
		indices.sort(function(a,b){
			var la = (art[a]+' - '+title[a]).toLowerCase();
			var lb = (art[b]+' - '+title[b]).toLowerCase();
			return la<lb ? -1 : (la>lb ? 1 : 0);
		});

		for (var k=0;k<indices.length;k++){
			var idx = indices[k];
			var opt = document.createElement('option');
			opt.value = idx;
			opt.textContent = art[idx]+' - '+title[idx];
			sel.appendChild(opt);
		}
	}

	function init(){
		shuffleOrder(initial_index);
		pos = 0; // order[0] === initial_index, déjà affiché côté serveur : pas de display() ici.
		document.getElementById('splash').innerHTML = "Vidéo "+(pos+1)+" / "+order.length;

		buildJumpList();

		var player = document.getElementById('player');
		player.addEventListener('play', function(){ enterFullscreen(player); });
		player.addEventListener('pause', function(){
			if (!player.ended){ // évite un aller-retour plein écran quand next() enchaîne juste après la fin naturelle
				exitFullscreen();
			}
		});
	}
// @license-end
</script>


<?php if ($whitelist===false){ ?>
	<a href="./"><?php echo htmlspecialchars($sitename);?> Home</a> &gt; Random vids<hr/>
	<br/>
<?php } ?>
<div id="splash">000</div>
<video id="player" style="width:80%;" controls src="<?php echo $vidtarget;?>" onEnded="next(this);">Browser has no video support</video>
<div id="vtitle"><?php echo $in_title;?></div>
<h2 id="vartist"><?php echo $in_art;?></h2>
<h3 id="valbum"><a href="<?php echo $in_allink; ?>"><?php echo $in_al; ?></a></h3>
<h4 id="vdesc"><?php echo $in_desc; ?></h4>
<div class="playlist-nav">
	<button type="button" onclick="prev(document.getElementById('player'));">&laquo; Previous</button>
	<button type="button" onclick="next(document.getElementById('player'));">Next &raquo;</button>
	<select id="jumpto" onchange="jumpToSelected(this);"></select>
</div>
<a id="share" target="_blank" href="?v=<?php echo urlencode($vidshare);?>">Share?</a>
</body>
</html>
