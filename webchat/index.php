<?php
chdir ('..');
require_once ('./config.php');
chdir ('./webchat');


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
	<title>Chatroom - <?php echo htmlspecialchars($sitename).' - '.htmlspecialchars($footerReadableName); ?></title>
	<meta name="description" content="Chatroom - <?php echo htmlspecialchars($title.' - '.$description); ?>" />
</head>
<body>
	<h1><?php echo htmlspecialchars($sitename);?></h1>
	<h2><?php echo htmlspecialchars($description);?></h2>
	<h4><em>Quick links: </em>[Chatroom] [<a href="../?nochat=1"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;catalog</a>] [<a href="../radio"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;radio</a>] [<a href="../random"><?php echo str_replace(' ', '&nbsp;', htmlspecialchars($sitename));?>&nbsp;random&nbsp;music&nbsp;player</a>]</h4>
		<a name="social"><button onClick="this.style.display='none';this.nextElementSibling.style.display='block';">Display the chatroom</button><object style="display:none;" onload="if (!nosocialupdate){updateSocialData(this);nosocialupdate=true;}" data="../network/?void=void" style="width:100%;height:495px;" width="100%" height="495"></object></a>
<hr/>
<?php echo htmlspecialchars($sitename);?> Artists: 
<?php
$artlist = explode("\n", trim(file_get_contents('../d/artists.txt')));
foreach ($artlist as $ar){
	echo '
	 [<a href="../?artist='.urlencode($ar).'">
	
	'.str_replace(' ', '&nbsp;', htmlspecialchars($ar)).'
	
	</a>] 
	
	';
	
}

?>

		
</body>
</html>
