<?php
chdir('..');
require_once('./config.php');
chdir('./radio');


if (!$hasradio){
	die();
}

if (isset($_GET['m3u'])){
	
	header('Content-type: application/x-mpegurl');
	echo 'http://'.$server.'/radio/stream.mp3';
	exit();
	
}


if (isset($_GET['ajax'])){
	if ($_GET['ajax']==='block'){
		
		$material_artists_file=htmlentities(trim(file_get_contents('../d/material_artists.txt')));
	
		$material_artists=explode("\n", $material_artists_file);
			
		$material_blacklist_file=htmlentities(trim(file_get_contents('../d/material_blacklist.txt')));
			
		$material_blacklist=explode("\n", $material_blacklist_file);

		$duration=intval(file_get_contents('./d/nowplayingduration.txt'));
		
		$nowplayingdurationminutes=floor($duration/60);
		
		$nowplayingdurationseconds=$duration-$nowplayingdurationminutes*60;
		
		$hasplayed=intval(microtime(true)-floatval(file_get_contents('./d/starttime.txt')));

		$hasplayedminutes=floor($hasplayed/60);
		
		$hasplayedseconds=$hasplayed-$hasplayedminutes*60;
		
		if ($hasplayedseconds<10){
			$hasplayedseconds='0'.$hasplayedseconds;
		}
		if ($nowplayingdurationseconds<10){
			$nowplayingdurationseconds='0'.$nowplayingdurationseconds;
		}
		
		echo '<strong style="font-size:125%;">';
		echo '<a target="new" href="../?artist='.urlencode(file_get_contents('./d/nowplayingartist.txt')).'">';
		echo file_get_contents('./d/nowplayingartist.txt');
		echo '</a>';
		echo '</strong>';
		echo ' - ';
		echo '<a target="new" href="../?album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'&track='.urlencode(file_get_contents('./d/nowplayingtitle.txt')).'">';
		echo '<em style="font-size:125%;">'.file_get_contents('./d/nowplayingtitle.txt').'</em>';
		echo '</a>';
		echo ' - ['.htmlspecialchars($hasplayedminutes).':'.htmlspecialchars($hasplayedseconds).'/'.htmlspecialchars($nowplayingdurationminutes).':'.htmlspecialchars($nowplayingdurationseconds).']';
		echo '<br/><span  style="font-size:125%;">(';
		echo '<a target="new" href="../?album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'">';
		echo file_get_contents('./d/nowplayingalbum.txt');
		echo '</a>';
		
		echo ')</span><br/>';
		if (boolval(trim(file_get_contents('./d/nowplayingisfeatured.txt')))){
			$target=file_get_contents('./d/nowplayingurl.txt');
			$targetflac=str_replace('.mp3', '.flac', $target);
			$targetogg=str_replace('.mp3', '.ogg', $target);
			
			echo 'Download <a target="new" href="'.$targetflac.'">flac</a> <a target="new" href="'.$targetogg.'">ogg</a> <a target="new" href="'.$target.'">mp3</a>';
			echo '<br/>'.file_get_contents('./d/license.txt');
		}
		else {
			echo 'Exclusive premiere track. Out for download soon';
		}
		if (!in_array(trim(html_entity_decode(file_get_contents('./d/nowplayingalbum.txt'))),$material_blacklist)
			&&
			in_array(trim(html_entity_decode(file_get_contents('./d/nowplayingartist.txt'))),$material_artists)
			&& boolval(trim(file_get_contents('./d/nowplayingisfeatured.txt')))
		
		
		) {
			echo '<br/>Available as <a target="new" href="../?listall=mixed&album='.urlencode(file_get_contents('./d/nowplayingalbum.txt')).'">material release</a> at our online shop';
			
		}
		if (!file_exists('./d/maxlisteners.txt')){
			file_put_contents('./d/maxlisteners.txt', '0');
		}
		
		$listeners=count(array_diff(scandir('./d/listeners'), Array ('.', '..')));
		
		if ($listeners>intval(file_get_contents('./d/maxlisteners.txt'))){
			file_put_contents('./d/maxlisteners.txt', $listeners);
		}
		if ((floatval(filectime('./d/maxlisteners24hours.txt'))+24*60*60)<=microtime(true)){
		
			unlink('./d/maxlisteners24hours.txt');
		
		}
		if ($listeners>intval(file_get_contents('./d/maxlisteners24hours.txt'))){
			file_put_contents('./d/maxlisteners24hours.txt', $listeners);
		}
		$peaktime=microtime(true)-filectime('./d/maxlisteners24hours.txt');
		$peakhours=ceil($peaktime/(60*60));

		echo '<br/>Listeners<br/>Current : '.$listeners.' / Peak : '.htmlspecialchars(file_get_contents('./d/maxlisteners.txt')).' (all-time) '.htmlspecialchars(file_get_contents('./d/maxlisteners24hours.txt')).' ('.htmlspecialchars($peakhours).'-hours)';
	}
	else if ($_GET['ajax']==='cover'){
		$covers=trim(file_get_contents('../d/covers.txt'));
		$coverlines=explode("\n", $covers);
		$artworks=Array();
		for ($i=0;$i<count($coverlines);$i++){
			$artworks[$coverlines[$i]]=$coverlines[$i+1];
			$i++;
		}
		if (isset( $artworks[html_entity_decode(trim(file_get_contents('./d/nowplayingalbum.txt')))])){
			echo $artworks[html_entity_decode(trim(file_get_contents('./d/nowplayingalbum.txt')))];
		
		}
		else {
		
			echo '../favicon.png';
		}
	}
	exit();
}



$sessionstarted=session_start();

srand();

if (!isset($_SESSION['origin'])){
	$_SESSION['origin']=$_SERVER['HTTP_REFERER'];
	
}



if ($activatestats&&isset($_GET['pingstat'])){
	//if audience figures are activated, let's store the hit details in the stats directory
	
	if ($sessionstarted){
		
		if (!isset($_SESSION['statid'])){
			$_SESSION['statid']=microtime(true);
			$_SESSION['css_color']='rgb('.rand(140, 255).','.rand(140, 255).','.rand(140, 255).')';
			
		}
		
		$page['data']['agent']=$_SERVER['HTTP_USER_AGENT'];
		$variable='agent';
		
		if (!(strstr($page['data'][$variable],'bot')||
		strstr($page['data'][$variable],'Yahoo! Slurp')||
		strstr($page['data'][$variable],'+http://')||
		strstr($page['data'][$variable],'+https://')||
		strstr($page['data'][$variable],'()'))) {
		//may be an human, we store it
			$figure['userid']=$_SESSION['statid'];
			$figure['css_color']=$_SESSION['css_color'];
			$figure['page']='/?radio=radio';
			$figure['referer']=$_SERVER['HTTP_REFERER'].'/?radio=radio';
			$figure['random']=$_SESSION['random'];
			$figure['origin']=$_SESSION['origin'];
			file_put_contents('../admin/d/stats/'.microtime(true).'.dat', serialize($figure));
		}
		
		
		
	}
		
		
	exit();
}



function loginpanel($activateaccountcreation){
	if (!$activateaccountcreation) {

		return;
	}
/*	
	
	if (isset($_SESSION['logged'])&&$_SESSION['logged']) {
		
		
	}	
	
	
	else if (!isset($_GET['login'])&&!isset($_GET['createaccount'])&&!isset($_POST['validateemail'])){
//		echo '<a href="./?login=login">Login</a> or <a href="./?createaccount=createaccount">Create account</a>';
*/ else if (!isset ($_POST['validateemail'])){


		echo '<form id="orderform" style="display:inline;" method="POST" action="./"><a href="#" onclick="document.getElementById(\'friends\').style.display=\'inline\';">Let\'s make friends ! </a><span id="friends" style="display:none;"><input type="text" name="validateemail" value="your email address" onfocus="if (this.value==\'your email address\'){this.value=\'\';}"/><input type="submit"/></span></form>';
		 	
	}
	else if (isset($_GET['createaccount'])) {
		echo 'Please enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
		echo '<form id="orderform" style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
	}
	else if (isset ($_POST['validateemail'])&&file_exists('../d/mailing-list-owner.txt')) {
		
		$_POST['validateemail']=explode("\n",$_POST['validateemail'])[0];
		$_POST['validateemail']=trim($_POST['validateemail']);
		$message ='<html><body>Hello<br/>';
		
		$message.="\r\n".'Someone requested mailing list ';
		$message.="\r\n".'subscription using the email address <br/>'.htmlentities($_POST['validateemail']);
		$message.="\r\n".'</body></html>';
		$message=chunk_split($message);
	
		if (
	
		mail(trim(file_get_contents('../d/mailing-list-owner.txt')), 'Mailing list subscription request', $message, 'Content-Type: text/html;charset=UTF-8')
		
		){
		
			echo 'A subscription request has been sent for the address '.htmlspecialchars($_POST['validateemail']).'. We will get in touch shortly to confirm. <a href="./">Close</a>';
			
		}
		else {
			echo 'The system has not been able to subscribe '.htmlspecialchars($_POST['validateemail']).'. Please <a href="">try again</a> later';
			
			
		}
	}
	
}


?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="/favicon.png" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($radioname); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($radiodescription); ?>" />
<style>
</style>
</head>
<body>
	<?php
	if ($acceptdonations){
		echo '<span style="float:right;text-align:right">';
		
		include ('../donate.php');
		
		echo '</span><br style="clear:both;float:none;"/>';
		
	}
	?>
<a name="menu"></a><div id="mainmenu" style="display:block;">	
	<span style=""><img style="float:left;width:3%;" src="/favicon.png"/></span>
		
	<h1 id="title" style="display:inline;"><?php echo $radioname; ?></h1>
	<h2 style="clear:both;"><em><?php echo htmlspecialchars($radiodescription);?></em> <br/><a href="../">Home</a></h2>
</div>
<!--<div><a href="#menu" onclick="mainmenu=document.getElementById('mainmenu');if(mainmenu.style.display=='none'){mainmenu.style.display='inline';this.innerHTML='&lt;';}else{mainmenu.style.display='none';this.innerHTML='☰<?php echo str_replace("'", "\\'", htmlspecialchars($title));?>';}">☰<?php echo strip_tags($radioname);?></a></div>-->

<span id="loginpanel" style="float:right;text-align:right;margin-bottom:2%;">
	<?php
		loginpanel($activateaccountcreation);
	?>

</span><br style="clear:both;"/>
<script>
	
var cover='';
var start;

var syncLock=false;
var allowGentleResync=true;

function resync() {
			if (!syncLock<?php
			
			if ($RadioHasGentleResync&&!$IsRadioResyncing){
			
				echo ('&&allowGentleResync');
			}
			?>){
			  syncLock=true;
			  
			  var d = new Date();
			  start = d.getTime()/1000;
				
			  var xhttp = new XMLHttpRequest();
			  xhttp.onreadystatechange = function(){
				  if (xhttp.readyState==4) {
						syncLock=false;
						
						if (xhttp.status==200) {
							var d = new Date ();
							var stop = d.getTime()/1000;
							
							if (true){//&&abs(document.getElementById('player').currentTime-(parseFloat(xhttp.responseText) + (stop-start)))<10) {
								//this if statement was to avoid the restart of a track from the beginning when sync replied just after a track change
							
										document.getElementById('player').currentTime = parseFloat (xhttp.responseText) + (stop-start) ;
										document.getElementById('resync').innerHTML="R: "+(document.getElementById('player').currentTime-(parseFloat (xhttp.responseText) + (stop-start))) ;
								}
						
							}
					
						<?php
							if ($RadioHasGentleResync&&!$IsRadioResyncing){
									echo 'else {syncLock=false;resync();}';
									
							}
							
							
							
							
							
							?>
					
					
					
					}
				  
				  };
			  xhttp.open("GET", "./currentplaytime.php?current="+encodeURI(document.getElementById('player').currentTime)+"&start="+encodeURI(start), true);
			  xhttp.send();
			}
		}

function refreshBlock() {

		  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function(){
			  if (xhttp.readyState==4 && xhttp.status==200) {

					document.getElementById('block').innerHTML = xhttp.responseText;
				}
			  
			  };
		  xhttp.open("GET", "./?ajax=block", true);
		  xhttp.send();
}
function refreshCover(){
			  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function(){
			  if (xhttp.readyState==4 && xhttp.status==200) {

					document.getElementById('cover').src = "../covers/"+xhttp.responseText;
				}
			  
			  };
		  xhttp.open("GET", "./?ajax=cover", true);
		  xhttp.send();

	
}
window.setInterval(refreshBlock, 2000);
window.setInterval(refreshCover, 30000);
setTimeout (refreshCover, 3000);

<?php if ($IsRadioResyncing) { ?>
window.setInterval(resync, <?php echo $RadioResyncInterval; ?>);
<?php   } ?>
</script>
<?php echo $radioBanner; ?>
Stream : <a href="?m3u=m3u">m3u</a> <a href="./stream.mp3">mp3</a><br/>

<img style="float:left;width:25%;" id="cover"/>
<span style="float:left;">
<div>Now Playing</div>
<div id="block" style="padding-left:4%;"></div>
<br style="clear:both;float:none;"/>
<script>
<?php
if (!$activatechat===false){
?>

function skipsong() {
	document.getElementById('skip').innerHTML= 'Skipping song, please be patient...';
				<?php
				if (!isset($_SESSION['nick'])){
					?>
				document.getElementById('social').data="../network/?login=login";

					<?php
					
				}
				?>
	
	var xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function(){
		  if (xhttp.readyState==4) {
				if (xhttp.status==200){
					document.getElementById('skip').innerHTML= 'Skip this song';
					location.reload();
				}
				else {
					skipsong();
				}
			}

		  
		  };
	  xhttp.open("GET", "skipsong.php", true);
	  xhttp.send();

}
<?php
}
?>

</script>
<div style="text-align:left;"><audio id="player" src="" preload="none" controls="controls" 
 onEnded="this.src='./stream.mp3?web=web&'+Math.random();this.load();this.play();allowGentleResync=true;" 
 onError="window.setTimeout(function(){document.getElementById('player').src='./stream.mp3?web=web&'+Math.random();document.getElementById('player').load();document.getElementById('player').play();}, 500);" 
 <?php
							if ($RadioHasGentleResync&&!$IsRadioResyncing){
									echo ' onPlay="resync();allowGentleResync=false;" ';
									
							}
 ?>
 ></audio><span id="resync" style="float:right;"></span></div>	
<?php
if (!$activatechat===false){
?>
		<a href="javascript:void(0);" id="skip" onclick="skipsong();">Fork the radio!</a> (other listeners may become angry)
<?php
}
?>
</span>

<hr style="float:none;clear:both;">
<script>
document.getElementById('player').src='./stream.mp3?web=web&'+Math.random();
document.getElementById('player').load();
document.getElementById('player').autoplay='autoplay';
document.getElementById('player').play();

</script>
<?php
if (!$activatechat===false){
?>
		<a name="social"/><object id="social" data="../network" style="	width:100%;height:495px;" width="100%" height="495"></object>
<?php
}


if ($activatestats){

?>
<script>

    var xhttp = new XMLHttpRequest();
	  xhttp.open("GET", "./?pingstat=true", true);
	  xhttp.send();

</script>

<?php
}
?>

<div style="float:rigth;font-size:76%;">Powered by <a href="http://crero.clewn.org" title="CreRo, the open-source CMS for record labels and webradios">CreRo, the CMS for record labels and webradios</a> - AGPL licensed - <a href="http://github.com/shangril/crero">code repo</a></div>
</body>
</html>
