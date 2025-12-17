<?php
chdir ('..');
require_once ('./config.php');
chdir ('./webchat');

$artlist = explode("\n", trim(file_get_contents('../d/artists.txt')));

if (isset($_GET['a'])&&in_array($_GET['a'], $artlist)){
	$albs = file_get_contents($clewnapiurl."?listalbums=".urlencode($_GET['a']));
	if ($albs !== false){
		$albz = explode("\n", trim($albs));
		foreach ($albz as $al){
			echo "<a href=\"../?album=".urlencode(html_entity_decode($al))."\">[".$al."]</a> - ";
		}
	  }
	die();
}
if (isset($_GET['b'])&&in_array($_GET['b'], $artlist)){
	$albs = file_get_contents($serverapi."?listalbums=".urlencode($_GET['b']));
	if ($albs !== false){
		$albz = explode("\n", trim($albs));
		foreach ($albz as $al){
			echo "<a href=\"../?album=".urlencode(html_entity_decode($al))."\">[".$al."]</a> - ";
		}
	  }
	die();
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="../<?php echo $favicon;?>" />
	<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="charset" value="utf-8" />
	<meta charset="UTF-8"/>
	<title><?php echo htmlspecialchars($sitename).' - '.htmlspecialchars($description); ?></title>
	<meta name="description" content="<?php echo htmlspecialchars($title.' - '.$description); ?>" />
	<script>
	
	
	
	
	
	</script>
</head>
<body>
	<h1><?php echo htmlspecialchars($sitename);?></h1>
	<h2><?php echo htmlspecialchars($description);?></h2>


<?php
function displaycover($album, $ratio, $param='cover', $AlbumsToBeHighlighted = -1, $highlightcounter = 0){
/******
This is a frozen -and slightly modified- version from displaycover 20220729 from index.html
* Please keep track of issue in the original function if any
* and correct this one

*/

	if (file_exists('../d/covers.txt')){
		
		if ($highlightcounter<$AlbumsToBeHighlighted){
			
			$ratio=$ratio*2;
			
			
			
		}
		
		
		
		
		$coversfile=trim(file_get_contents('../d/covers.txt'));
		$coverslines=explode("\n", $coversfile); 
		
		$i=0;
		$url=null;
		while ($i<count($coverslines)){
			if ($coverslines[$i]===html_entity_decode($album)){
				if (isset($coverslines[$i+1])){
					$url=$coverslines[$i+1];
				}
			}
			$i++;
			$i++;
		}
		if (isset($url)){
			$output='';
			$output.='<img style="width:100%;"  src="../covers/'.rawurlencode($url).'" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';
		
			
			return $output;
		}
		else {
			return '<img style="width:100%;"  src="../favicon.png" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';
		}
	
	
	
	}
	else{
		return '<img style="width:100%;"  src="../favicon.png" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';

	}	
}










$recents=Array();
if (file_exists('../d/recent.dat')){
	$recents=unserialize(file_get_contents('../d/recent.dat'));
	
}
$allowedAlbums=Array();

if (file_exists('../d/recently_generated_albums.dat')){
	$allowedAlbums=unserialize(file_get_contents('../d/recently_generated_albums.dat'));
	
}
$counter=0;
$recentsr=array_reverse($recents);
echo '<h4 style="display:inline;">Recently played:</h4><br/>';
foreach ($recentsr as $recent){
	if (!isset($recent['jailed'])){
		$recent['jailed']=false;
	}
	
	
	if(in_array($recent['album'], $allowedAlbums)&&!$recent['jailed']&&$counter<10){
		$counter++;
		echo '<span style="width:10%;float:left;margin-left:auto;margin-right:auto;"><a href="../?album='.urlencode($recent['album']).'">'.displaycover($recent['album'], 10, 'mini'.rand(0,1000)).'</a><br/>';
		echo htmlspecialchars(round((time()-intval($recent['date']))/60));
		echo ' mn<br/>';
		//echo ' <a href="#social" style="'.$recent['who']['color'].'">'.htmlspecialchars($recent['who']['nick']).'</a>';
		echo '</span>';
	}//if allowedAlbum
}
?>

<hr style="clear:both;"/>

	<h4><em>Quick links: </em>
	
	<?php if ($activatechat){ ?>
	[Chatroom]
	<?php } ?>
	
	
	 [<a href="../?nochat=1"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;catalog</a>] 
	
	<?php if (!$isRadioDisabled){ ?>
	
	
	[<a href="../radio"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;radio</a>] 
	
	<?php } ?>
	
	
	<?php if ($RandomPlayer){ ?>
	
	
	[<a href="../random"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;random&nbsp;music&nbsp;player</a>]

	<?php } ?>

	<?php if (!$videoapiurl===false){ ?>
	
	
	[<a href="../random_vids.php"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;music&nbsp;videos</a>]

	<?php } ?>
	<?php if (!$acceptdonations===false){ ?>
	
	
	[<a href="../donate">Help&nbsp;funding <?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?></a>]

	<?php } ?>

	</h4>
	<?php if ($activatechat){ ?>

		<a name="social"><button onClick="this.style.display='none';this.nextElementSibling.style.display='block';">Display the chatroom</button><object style="display:none;" onload="if (!nosocialupdate){updateSocialData(this);nosocialupdate=true;}" data="../network/?void=void" style="width:100%;height:495px;" width="100%" height="495"></object></a>

	<?php } ?>


<hr/>
<?php echo htmlspecialchars($sitename);?> Artists: 
<?php

foreach ($artlist as $ar){
	echo '
	 [<a href="../?artist='.urlencode($ar).'">
	
	'.str_replace(' ', '&nbsp;', htmlspecialchars($ar)).'
	
	</a>] 
	
	';
	
}
echo "<h2>Free download albums</h2>";

foreach ($artlist as $ar){
	echo "<span>";
	echo "<strong> * ".htmlspecialchars($ar).": </strong>";
		 ?> <span>Loading…
			 <script>
			       (async function(span){
        try {
          const response = await fetch("?a=<?php echo urlencode ($ar);?>");
          const text = await response.text();
          span.innerHTML = text;
        } catch (e) {
          span.innerHTML = "Loading error";
        }
      })(document.currentScript.parentElement);
			 
			 
			 
			 </script>
			 </span>
			 
			 <?php
	
	echo "</span>";
}
echo "<h2>Exclusive, listen-only (unfinished, growing, work in progress…) albums</h2>";

foreach ($artlist as $ar){
	echo "<span>";
	echo "<strong> * ".htmlspecialchars($ar).": </strong>";
		 ?> <span>Loading…
			 <script>
			       (async function(span){
        try {
          const response = await fetch("?b=<?php echo urlencode ($ar);?>");
          const text = await response.text();
          span.innerHTML = text;
        } catch (e) {
          span.innerHTML = "Loading error";
        }
      })(document.currentScript.parentElement);
			 
			 
			 
			 </script>
			 </span>
			 
			 <?php
	
	echo "</span>";
}
?>

		
</body>
</html>
