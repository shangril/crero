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
	<h4><?php echo htmlspecialchars($sitename);?> Chatroom</h4>
	<h4><em>Quick links</em> [<a href="..">Label&nbsp;catalog</a>] [<a href="../radio">Radio</a>] [<a href="../random">Random music</a>]</h4>
		<a name="social"><object onload="if (!nosocialupdate){updateSocialData(this);nosocialupdate=true;}" data="../network/?void=void" style="width:100%;height:495px;" width="100%" height="495"></object></a>
		
</body>
</html>
