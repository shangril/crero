<?php
//error_reporting(E_ERROR | E_PARSE);
session_start();
require_once('config.php');

function processdata ($data){
	echo '<ul>';
	if (is_array($data)){
		$keys=array_keys($data);
		foreach ($keys as $key){
			echo '<li>'.htmlspecialchars($key).'</li>';
			processdata($data[$key]);
		}
	}
	else {
		echo '<li>'.htmlspecialchars($data).'</li>';
		
	}

	echo '</ul>';
}

?>

<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($title); ?></title>
<meta name="description" value="<?php echo htmlspecialchars($description); ?>" />
<script src="http://<?php echo $server; ?>/script.js">
</script>
</head>
<body>
	<h1><?php echo $title; ?> - Material releases orders history</h1>
	<h2>Curious about how the label is going ? Here's our history of material release orders, from newest to oldest</h2>
<?php
if (file_exists('./d/orders')&&is_dir('./d/orders')){
	$orders=array_diff(scandir('./d/orders'), array('..', '.'));
	$bills=Array();
	foreach ($orders as $bill){
		$number=explode ('-', $bill)[0];
		$date=date(DATE_RSS, explode('.',explode('-', $bill)[1])[0]);
		$bills[$number]['data']=unserialize(file_get_contents('./d/orders/'.$bill));
		$bills[$number]['date']=$date;
	}
	krsort($bills);
	foreach (array_keys($bills) as $billindex)	
		{
		echo '<h2>Order number '.htmlspecialchars($billindex).'</h2>';
		echo '<h4>Processed on '.htmlspecialchars($bills[$billindex]['date']).'</h4>';
		processdata($bills[$billindex]['data']);
		
		
	}
	if (!isset($bills)||count($bills)==0){
		echo '<div>No one ordered anything up-to-date. <a href="./?listall=material">Be the first ! </a></div>';
	}
	
}
else {
	echo '<div>No one ordered anything up-to-date. <a href="./?listall=material">Be the first ! </a></div>';
}


?>
</body>
</html>
