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
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />
	<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="charset" value="utf-8" />
	<meta charset="UTF-8"/>
	<title><?php echo htmlspecialchars($sitename).' - '.htmlspecialchars($description); ?></title>
	<meta name="description" content="<?php echo htmlspecialchars($title.' - '.$description); ?>" />
</head>
<body>
	<h1><?php echo htmlspecialchars($sitename);?></h1>
	<h2><?php echo htmlspecialchars($description);?></h2>
	<h4><em>Quick links: </em>[Chatroom] [<a href="../?nochat=1"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;catalog</a>] [<a href="../radio"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;radio</a>] [<a href="../random"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;random&nbsp;music&nbsp;player</a>]</h4>
		<a name="social"><button onClick="this.style.display='none';this.nextElementSibling.style.display='block';">Display the chatroom</button><object style="display:none;" onload="if (!nosocialupdate){updateSocialData(this);nosocialupdate=true;}" data="../network/?void=void" style="width:100%;height:495px;" width="100%" height="495"></object></a>
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
echo "<h2>All albums</h2>";

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
echo "<h2>Exclusive, listen-only unfinished, growing, work in progress albums</h2>";

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
