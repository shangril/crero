<?php
chdir('../');
require_once('config.php');
chdir('./donate');

?>
<!DOCTYPE html>
<html>
	<head>
	<meta charset="UTF-8"/>
		<title>Help funding <?php echo htmlspecialchars($sitename);?></title>
		<meta name="keywords" content="Donate to <?php echo htmlspecialchars($sitename);?>"/>
		<meta name="description" content="Donate to <?php echo htmlspecialchars($sitename);?>. <?php echo htmlspecialchars($description)?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
	
	
	</head>
	<body>
	<a href="../">Home</a> &gt; Donate
	<h1>Donate to <?php echo htmlspecialchars($sitename);?></h1>
	<span style="font-size:119%">
	<?php 
	if ($acceptdonations){
		chdir('../');
		include ('donate.php');
		chdir('./donate');
	}
	if ($LightningBTCDonationAddress!==false){
		echo '<hr/>Lightning Bitcoin accepted here: '.$LightningBTCDonationAddress[0];
	}
	?>
	</span>
	</body>
</html>
