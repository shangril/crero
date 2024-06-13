<?php
require_once('./config.php');

?><!DOCTYPE html>
<html>
<head>
<title>Random videos from <?php echo htmlspecialchars($sitename);?></title>
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />

<meta name="description" value="<?php echo htmlspecialchars($description);?>"/>
<meta charset="UTF-8"/>
<link href="./style.css" rel="stylesheet" type="text/css"/>
<meta name="viewport" content="width=device-width" />




</head>
<body onload="init();">
	<script>
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0

	<?php
	$arts = explode ("\n", file_get_contents('./d/artists.txt'));
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
	echo "var res=[";
	for ($i=0;$i<count($res);$i++){
		echo "'".$videourl.rawurlencode($res[$i])."'";
		if ($i<count($res)-1){	
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


	$rescopy = $res ;
	shuffle($res);
	
	$z = array_search ($res[0], $rescopy);
	
	$in_title = $title[$z];
	$in_art = $art[$z];
	$in_al = $al[$z];
	$in_allink = $allink[$z];
	$in_desc = $desc[$z];
	
	?>
	
	function next(v){
		i=Math.floor(Math.random()*res.length);
		
		v.src = res[i];

		v.nextElementSibling.innerHTML = title[i];
		v.nextElementSibling.nextElementSibling.innerHTML = art[i];
		v.nextElementSibling.nextElementSibling.nextElementSibling.innerHTML = '<a href="'+allink[i]+'">'+al[i]+'</a>';
		v.nextElementSibling.nextElementSibling.nextElementSibling.nextElementSibling.innerHTML = desc[i];
		
		v.play();
		
		res.splice(i, 1);
		title.splice(i, 1);
		art.splice(i, 1);
		al.splice(i, 1);
		allink.splice(i, 1);
		desc.splice(i, 1);
		
		document.getElementById('splash').innerHTML=res.length+" videos still not played";
	
		
		if (res.length==0){
			res=saved_res.slice();
			title=saved_title.slice();
			art=saved_art.slice();
			al=saved_al.slice();
			allink=saved_allink.slice();
			desc=saved_desc.slice();
		}
	}
	function init(){
		var saved_res = res.slice();
		var saved_title = title.slice();
		var saved_art = art.slice();
		var saved_al = al.slice();
		var saved_allink = allink.slice();
		var saved_desc = desc.slice();
		
		document.getElementById('splash').innerHTML=res.length+" videos still not played";
	}
// @license-end
</script>



	<a href="./"><?php echo htmlspecialchars($sitename);?> Home</a> &gt; Random vids<hr/>
	<br/>
<div id="splash">000</div>
<video controls src="<?php echo $videourl.rawurlencode($res[0]);?>" onEnded="next(this);">Browser has no video support</video><div><?php echo $in_title;?></div><h2><?php echo $in_art;?></h2><h3><a href="<?php echo $in_allink; ?>"><?php echo $in_al; ?></a></h3><h4><?php echo $in_desc; ?></h4>
</body>
</html>
