<?php
session_start();
error_reporting(0);
include('./config.php');
chdir('./..');
require_once('./config.php');
chdir('./admin/');

if (!file_exists('./d/pwd.dat')){

	if ($password==='hackme'){
		echo '<html><body><h1>It\'s working</h1>Please change the $user and $password php variables in <em>admin/config.php</em> to complete the setup</body></html>';
		die();
	}
	else {
		$userhash=password_hash($user, PASSWORD_DEFAULT);
		$pwdhash=password_hash($password, PASSWORD_DEFAULT);
		if ($userhash&&$pwdhash){
			$credentials=Array();
			$credentials ['user']=$userhash;
			$credentials ['pwd']=$pwdhash;
			if (!file_put_contents('./d/pwd.dat', serialize($credentials))){
				die('<html><body>Could not save your admin credentials to disk. Does your http server have right write permission over ./admin/d ? </body></html>');
			}
			else {
				file_put_contents('./config.php', '<?php
				$user=\'changeme\';
				$password=\'hackme\';
				?>');
				
			}
		}
		else {
			die('<html><body>Could not encrypt the admin credentials. Please look at your PHP configuration.</body></html>');
			
		}
	}


} //!file_exist pwd.dat

$credentials=unserialize(file_get_contents('./d/pwd.dat'));


if (isset($_POST['pwd'])){
	if (password_verify($_POST['pwd'], $credentials['pwd'])&&password_verify($_POST['user'], $credentials['user'])){
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
 <a href="../?purge=cache" target="new">Purge htmlcache</a>
  <a href="./?cart=rawstats">Download cart raw stats</a>
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
			
			/*$urlpage=Array();
			
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
			*/
			if ($pageview['random']){
				echo ' Random mode';
			}
			echo '<br/>';
			
			$urlpage=Array();
			
			if (strstr ($pageview['referer'], '?')){
				$query=explode('?', $pageview['referer'])[1];
				$urlpage=explode('&', $query);
			}
			else {
				$urlpage['/']='Label home page';
			}
			
			$keyz=array_keys($urlpage);
			
			foreach ($keyz as $key) {
				//echo htmlspecialchars($key).' ';
				echo htmlspecialchars(urldecode($urlpage[$key])).' - ';
				
			}
			echo '<br/>coming from: '.htmlspecialchars($pageview['origin']);
			
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
			//if (trim($data)!=''&&trim($data)!="\n"){
				if (file_put_contents('../d/'.$target, $data)){
					echo '<hr/>Changes were saved<hr/>';
					
				}
				else {
					echo '<hr/>Something wrong happened and your changes have not been saved ! <hr/>';
				}
			//}
			/*else {
				if (unlink('../d/'.$target)){
					echo '<hr/>Changes were saved<hr/>';
				}
				else {
					echo '<hr/>Something wrong happened and your changes have not been saved ! <hr/>';
				}
			}*/ //not working good and safer to forget about this
		}
	
	
	}
}
else if (isset($_GET['covers'])){
	if ($_GET['covers']==='manage'){
		echo '<h2>Manage cover arts</h2>';
		echo 'Use this form to upload a cover art image to ./covers ; <br/>once it is done you have to declare it in the Covers configuration option to indicate to which album it belongs<br/>';
		echo '<br/>png, gif, jpeg formats are ok<br/>';
		?>
		
		<form action="./?covers=upload" method="POST" enctype="multipart/form-data" style="display:inline;"><input name="torrent" type="file" accept="image/*"/><input type="submit"></form>
		

		
		<?php
		$covers=array_diff(scandir('../covers'), Array('..', '.'));
		
		echo '<br/><strong>Available covers currently online : </strong><br/>';
		foreach ($covers as $cover){
			echo htmlspecialchars($cover).'<br/>';
			
		}
	}
	else if ($_GET['covers']==='upload'){
		$dossier = '../covers/';
		$fichier = basename($_FILES['torrent']['name']);
		$filesize_max = 128000000;
		$filesize = filesize($_FILES['torrent']['tmp_name']);
		$extensions = array('.png','.gif','.jpeg', '.jpg');
		$extension = strrchr($_FILES['torrent']['name'], '.'); 
		$fichier=str_replace('.php', '', $fichier);
		if (file_exists($dossier.preg_replace('/[^-\w:";:+=\.\']/', '', str_replace(' ','',$fichier)))) {

		echo "<hr/>Please change the filename. This one is already in use<hr/></body></html>";
		die();

		}
		
		if(!in_array($extension, $extensions)) 
		{
			 $error_msg = 'We only accept .png, .jpeg, .jpg and .gif file extensions, in lowercase, as mentionned on the previous page. ';
		}
		if($filesize>$filesize_max)
		{
			 $error_msg = 'We do not accept file bigger than 128 megabytes !';
		}
		if(!isset($error_msg)) 
		{
			if(move_uploaded_file($_FILES['torrent']['tmp_name'], $dossier . preg_replace('/[^-\w:";:+=\.\']/', '', str_replace(' ','',$fichier)))) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
			 {
				  echo '<hr/><h2>Upload OK ! </h2><hr/>';
			 }
			 else 
			 {
				  echo 'Sorry, the system encountered an error. Note : We do not accept file bigger than 128 megabytes<br/>';
			  echo $_FILES['torrent']['tmp_name'].' '. $dossier . $fichier;
			 }
		}
		else
		{
			 echo '<hr/>'.$error_msg.'<hr/>';
		}
		}
		else
		{echo '<hr/>Please, no quote in filenames !<hr/>';}

		
		
		
		
		
		
	
	
	
	
}
else if (isset($_GET['cart'])){
 $filez=array_diff(scandir('./d/cartstats'), array('..', '.'));
 ksort($filez);
 $stats=array_reverse($filez);
 foreach ($stats as $stat)
	 {
		$mystat=unserialize(file_get_contents('./d/cartstats/'.$stat));
		echo '<hr/>'.htmlspecialchars(date(DATE_RSS, str_replace ('.dat', '', $stat))).'<br/>';
		echo 'Albums<br/>';
		$albs=$mystat['album'];
		foreach ($albs as $alb)
		 {
			echo $alb['title'].'<br/>';
		 
		 } 
		echo 'Tracks<br/>';
		$albs=$mystat['track'];
		foreach ($albs as $alb)
		 {
			echo $alb['title'].' <em>on</em> '.$alb['album'].'<br/>';
		 
		 } 
	 
	 } 
 
 } 

if (!isset($_GET['ajaxstatupdate'])){

?>
</body>
</html>
<?php } ?>
