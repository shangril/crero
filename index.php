<?php
require_once('./config.php');


session_start();
srand();





if (isset($_GET['random'])&&$_GET['random']=='true'&&!isset($_GET['artist'])){
	$_SESSION['random']=true;
	
}
else if (isset($_GET['random'])&&$_GET['random']=='false'){
	$_SESSION['random']=false;
	
}
else if (isset($_GET['artist'])){
	$_SESSION['random']=false;
	
	
}

error_reporting(E_ERROR | E_PARSE);

if (isset($_GET['artist'])) {
	$favicon='http://'.$server.'/favicon.png';
	$arturl='&artist='.urlencode($_GET['artist']);
	$title='<a href="./?artist='.urlencode($_GET['artist']).'">'.htmlspecialchars($_GET['artist']).'</a> - A Crem Road artist';
}
else {
	$favicon='/favicon.png';
	$arturl='';
}

if (!isset($_GET['artist'])){

	$artists_file=file_get_contents('./d/artists.txt'); 

	$artists=explode("\n", $artists_file);
}
else {
	$artists=Array($_GET['artist']);
}

function loginpanel(){
	if (!$activateaccountcreation) {
		return;
	}
	
	
	if (isset($_SESSION['logged'])&&$_SESSION['logged']) {
		
		
	}	
	
	
	else if (!isset($_GET['login'])&&!isset($_GET['createaccount'])&&!isset($_POST['validateemail'])){
		echo '<a href="./?login=login">Login</a> or <a href="./?createaccount=createaccount">Create account</a>';
		
	}
	else if (isset($_GET['createaccount'])) {
		echo 'Please enter a <em>valid</em> email adress. You will receive a link to set your password and activate your account. <br/>';
		echo '<form style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
	}
	else if (isset ($_POST['validateemail'])) {
		
		$_POST['validateemail']=explode("\n",$_POST['validateemail'])[0];
		$_POST['validateemail']=trim($_POST['validateemail']);
		$message ='<html><body>Hello';
		
		$message.="\r\n".'Someone, probably you, requested the creation of a cremroad';
		$message.="\r\n".'account using the email address '.htmlentities($_POST['validateemail']);
		$message.="\r\n".'</body></html>';
		$message=chunk_split($message);
	
		if (
	
		mail($_POST['validateemail'], 'Cremroad.com account creation', $message, 'From: noreply@cremroad.com'."\r\n".'Content-Type: text/html;charset=UTF-8')
		
		){
		
			echo 'An email has been sent to the address '.htmlspecialchars($_POST['validateemail']).'. Please use the link provided in it to set your password.';
			
		}
		else {
			echo 'The system has not been able to send an email to the address '.htmlspecialchars($_POST['validateemail']).'. Please check it for spelling and <a href="">try again</a> in a few minutes';
			
			
		}
	}
	
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="http://cremroad.com/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo strip_tags($title); ?></title>
<meta name="description" value="<?php echo htmlspecialchars($description); ?>" />
<script src="http://cremroad.com/script.js">
</script>
</head>
<body>
<audio id="player" onEnded="playNext();">
	Your browser is very old ; sorry but streaming will not be enabled<br/>
</audio>
<noscript>Your browser does not support Javascript, which is required on this website if you want to stream. Don't panic, we dont include any kind of third party scripts<br/></noscript>
<?php
if (isset ($_GET['track'])) {
	echo '<a href="./">../</a><br/>';
}

else if (isset ($_GET['artist'])) {
	echo '<a href="http://cremroad.com/">../</a><br/>';
}

?>	
	
<span style=""><img style="float:left;width:3%;" src="http://cremroad.com/favicon.png"/></span>
	
<h1 style="display:inline;"><?php echo $title; ?></h1>
<span style="float:right;text-align:right;">
<?php
	loginpanel();
?>

</span>
<h2 style="clear:both;"><em><?php echo htmlspecialchars($description);?></em></h2>
<?php
//here we are, let's query clewn to fill the content array
$content=Array();

$querystring = '';
		foreach ($artists as $artist) 
		{
			$querystring.='&listallalbums[]='.urlencode($artist);
			
			
		}



		$albums_file=file_get_contents($clewnapiurl.'?'.$querystring);
		$albums=unserialize($albums_file);
		ksort($albums);
		$albums=array_reverse($albums);
		
		$content=$albums;


if ($_SESSION['random']){
	$_GET['offset']=rand(0, count($content)-1);
	$_GET['autoplay']='autoplay';
}



?>



<?php
//here we go, let's output the content
if (isset($_GET['offset'])&&is_numeric($_GET['offset'])) {
	$offset=intval($_GET['offset']);
	
	if ($offset!==0&&!$_SESSION['random']){
	
		echo '<a href="./?offset='.($offset-1).$arturl.'">Dig newer</a><br/>';
	
	}

}
else {
	$offset=0;
	
}
echo '<script>var offset='.$offset.'</script>';
if ($_SESSION['random']){
	echo '<a href="./?random=random">Skip this album</a>';
	
}
$counter=1;
$secondcounter=0;
if ($_SESSION['random']&&!isset($_GET['artist'])){
		echo '<a style="float:right" href="./?random=false">stop random</a>';

}	
else if (!isset($_GET['artist']))
{
	echo '<a style="float:right" href="./?random=true">random play</a>';
}

foreach ($content as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0) {
	$running=true;
	
	if (isset($_GET['album'])&&$_GET['album']!==$item['album']) {
		$running=false;
		
	}
	
	
	
	
	if (isset ($item['album'])&&$running){
		$ran=true;


		echo '<h1>Album : <a href="./?album='.urlencode($item['album']).'">'.$item['album'].'</a></h1>';
		
		
		
		
		//here we go, query Clewn API for track list
		$tracks_file=file_get_contents($clewnapiurl.'?gettracks='.urlencode($item['album']));

		$tracks=explode("\n", $tracks_file);
		$trackcounter=0;
		$hasntautoplayed=true;
		foreach ($tracks as $track) {
			if ($track!==''){
			
				//we want its name and the artist name as well
				$track_name=trim(file_get_contents($clewnapiurl.'?gettitle='.urlencode($track)));
		
				$track_artist=trim(file_get_contents($clewnapiurl.'?getartist='.urlencode($track)));
				
				if (!isset($_GET['track'])||$_GET['track']==$track_name)
				{
					if (in_array($track_artist, $artists)){
					?>
					<a href="#" onClick="play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, true);" id="<?php echo $trackcounter; ?>">▶</a>
					 <a href="./?artist=<?php echo urlencode ($track_artist); ?>">
					 <?php echo  $track_artist; ?></a> - 
					 <?php echo  '<a href="./?track='.urlencode($track_name).'&album='.urlencode($item['album']).'">'.$track_name.'</a>'; ?>
					 <div style="background-color:#F0F0F0;text-align:right;">Download <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.flac">flac</a> <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.ogg">ogg</a> <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.mp3">mp3</a></div>
					
					<?php
					if (isset($_GET['autoplay'])&&$hasntautoplayed){
						?>
						<script>play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, true);</script>
						<?php
						$hasntautoplayed=false;
					}
				}
			}
			$trackcounter++;
			}
		
		}









		
	}
	
}

if ($ran) {
	$secondcounter++;

}	
$counter++;
}//foreach $content


if (!isset ($_GET['album'])&&!isset($_GET['track'])&&!$_SESSION['random']){
	echo '<a style="float:right;" href="./?offset='.intval($offset+1).$arturl.'">Dig older...</a><br/>';

}


echo $footerhtmlcode;

?>
</body>
</html>
