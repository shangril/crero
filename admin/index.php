<?php
session_start();
error_reporting(0);
include('./config.php');
chdir('./..');
require_once('./config.php');
chdir('./admin/');



if ($password==='hackme'){
	echo '<html><body><h1>It\'s working</h1>Please change the $user and $password php variables in <em>admin/config.php</em> to complete the setup</body></html>';
	die();
}
if (isset($_POST['pwd'])){
	if ($_POST['pwd']===$password&&$_POST['user']===$user){
			$_SESSION['loggedadmin']=true;
	}
	else {
		echo '<html><body>Access denied. <a href="./">Try again</a></body></html>';
		die();
	}
}
if (!$_SESSION['loggedadmin']) {
		echo '<html><body><form method="post" action="./">Welcome to the admin panel. Connection accepted. Username : <input type="text" name="user"/> Password : <input type="password" name="pwd"/><input type="submit"/></form></body></html>';
		die();
	
}
$fields=explode("\n", trim(file_get_contents('./d/fields.txt')));
$options=Array();
for ($i=0;$i<count($fields);$i++){
	$key=$fields[$i];
	$i++;
	$value=$fields[$i];
	$options[$key]=$value;
}

if (!isset($_GET['ajaxstatupdate'])){
?>
<!DOCTYPE html>
<html>
<head>
<title>CreRo admin panel for domain <?php echo htmlspecialchars($_SERVER['SERVER_NAME']); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
</head>
<body>

CreRo admin panel for domain <?php echo htmlspecialchars($_SERVER['SERVER_NAME']); ?><br/>
<a href="./?stats=realtime">Visitor figures (realtime)</a>
<a href="./?admin=config">Edit configuration options</a>
<a href="./?covers=manage">Manage cover arts</a>
<?php 
}
if (isset($_GET['stats'])&&$_GET['stats']=='realtime'){
	echo '<div id="statpanel"><script>var lastupdate=0;</script></div>';
	?>
	<noscript>This feature requires Javascript</noscript>
	<script>
	function updateStatPanel(){
		  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function() {
			if (xhttp.readyState==4 && xhttp.status== 200) {
			 document.getElementById("statpanel").innerHTML = xhttp.responseText;
			}
		  };
		  xhttp.open("GET", "./?ajaxstatupdate="+encodeURI(lastupdate), true);
		  xhttp.send();
		  window.setTimeout (updateStatPanel, 25000);
	}
	updateStatPanel();
	
	
	</script>
	<?php
}
else if (isset($_GET['ajaxstatupdate'])){

	$data=array_diff(scandir('./d/stats'), Array('..', '.', '.htaccess'));
	$pageviews=Array();
	
	foreach ($data as $dat){
		$pageviews[floatval(str_replace('.dat', '', $dat))]=unserialize(file_get_contents('./d/stats/'.$dat));
		$pageviews[floatval(str_replace('.dat', '', $dat))]['time']=floatval(str_replace('.dat', '', $dat));
	}
	krsort($pageviews);
	if (floatval($_GET['ajaxstatupdate'])<$pageviews[array_keys($pageviews)[0]]['time']){
		echo '<script>var lastupdate='.htmlspecialchars($pageviews[array_keys($pageviews)[0]]['time']).';</script>';
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
			
			echo 'coming from '.htmlspecialchars($pageview['referer']);
			echo '</div>';
			
		}//foreach pageview
	}//if newer
}// stats=realtime
else if (isset($_GET['admin'])){
	
	//**** main admin logic
	
	//**** if admin=config display config option list
	
	if ($_GET['admin']=='config'){
		echo '<ul>'; 
		foreach (array_keys($options) as $opt){
			echo '<li><a href="./?admin=listconfig&field='.urlencode($opt).'">'.htmlspecialchars($opt).'</a></li>';
			
		}
		echo'</ul>';
	}
	
	
	//**** if admin=listconfig & field=whatever display edit panel for that file
	else if ($_GET['admin']==='listconfig') {
		$target=$_GET['field'];
		if (in_array($target, array_keys($options))){
			echo '<h2>'.htmlspecialchars($target).'</h2>';
			echo '<div><em>'.htmlspecialchars($options[$target]).'</em></div>';
			echo '<form method="POST" action="./?admin=postfield&field='.urlencode($target).'">';
			echo '<textarea style="width:90%;" rows="35" name="data">';
			
			echo htmlspecialchars(file_get_contents('../d/'.$target));
			
			
			echo '</textarea>';
			echo '<br/><input type="submit"/>';
			
			
			echo '</form>';
		}
		
	}
	//**** if admin=postfield && $_POST['data'] is set update this particular file
	else if ($_GET['admin']==='postfield') {
		
		
		//first we check the field is valid and referenced by $options
		$target=$_GET['field'];
		if (in_array($target, array_keys($options))){
			$data=$_POST['data'];
			$data=str_replace("\r\n", "\n", $data);
			$data=str_replace("\r", "\n", $data);
			
			if (file_put_contents('../d/'.$target, $data)){
				echo '<hr/>Changes were saved<hr/>';
				
			}
			else {
				echo '<hr/>Something wrong happened and your changes have not been saved ! <hr/>';
			}
		}
	
	
	}
}

if (!isset($_GET['ajaxstatupdate'])){

?>
</body>
</html>
<?php } ?>
