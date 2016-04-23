<?php
session_start();
error_reporting(0);
include('./config.php');

if ($password==='hackme'){
	echo '<html><body><h1>It\'s working</h1>Please change the $password php variable in <em>admin/config.php</em> to complete the setup</body></html>';
	die();
}
if (isset($_POST['pwd'])){
	if ($_POST['pwd']===$password){
			$_SESSION['loggedadmin']=true;
	}
	else {
		echo '<html><body>Access denied. <a href="./">Try again</a></body></html>';
		die();
	}
}
if (!$_SESSION['loggedadmin']) {
		echo '<html><body><form method="post" action="./">Welcome to the admin panel. Connection accepted. Password : <input type="password" name="pwd"/><input type="submit"/></form></body></html>';
		die();
	
}
$fields=explode('\n', file_get_contents('./d/fields.txt'));
$options=Array();
for ($i=0;$i<count($fields);$i++){
	$key=$fields[$i];
	$i++;
	$value=$field[$i];
	$options[$key]=$value;
}

?>
<!DOCTYPE html>
<html>
<head>
<title>CreRo admin panel for domain <?php echo htmlspecialchars($_SERVER['HTTP_SERVER_NAME']); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
</head>
<body>
CreRo admin panel for domain <?php echo htmlspecialchars($_SERVER['HTTP_SERVER_NAME']); ?><br/>
<a href="./?stats=realtime">Visitor figures (realtime)</a>
<?php 

if (isset($_GET['stats'])&&$_GET['stats']=='realtime'){
	$data=array_diff(scandir('./d/stats'), Array('..', '.', '.htaccess'));
	$pageviews=Array();
	
	foreach ($data as $dat){
		$pageviews[floatval(str_replace('.dat', '', $dat))]=unserialize(file_get_contents('./d/stats/'.$dat));
		$pageviews[floatval(str_replace('.dat', '', $dat))]['time']=floatval(str_replace('.dat', '', $dat));
	}
	krsort($pageviews);
	
	foreach ($pageviews as $pageview){
		echo '<hr/>';
		echo '<div style="background-color:'.$pageview['css_color'].';">';
		echo 'at '.htmlspecialchars(date(DATE_RSS, round($pageview['time']))).' ';
		echo 'user '.htmlspecialchars($pageview['userid']).' is browsing the page <br/>';
		
		$urlpage=Array();
		
		if (strstr ($pageview['page'], '?')){
			$query=explode('?', $pageview['page'])[1];
			$urlpage=explode('&', $query);
		}
		else {
			$urlpage['/']='Label home page';
		}
		
		$keyz=array_keys($urlpage);
		
		foreach ($keyz as $key) {
			echo htmlspecialchars($key).' ';
			echo htmlspecialchars($urlpage[$key]).' - ';
			
		}
		if ($pageview['random']){
			echo ' in random mode';
		}
		echo '<br/>';
		
		echo 'comming from '.htmlspecialchars($pageview['referer']);
		echo '</div>';
		
	}
}// stats=realtime


?>
</body>
</html>
