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
else if (isset($_GET['artist'])||isset($_GET['listall'])){
	$_SESSION['random']=false;
	
	
}

error_reporting(E_ERROR | E_PARSE);

if (isset($_GET['artist'])) {
	$favicon='http://'.$server.'/favicon.png';
	$arturl='&artist='.urlencode($_GET['artist']);
	$title='<a href="./?artist='.urlencode($_GET['artist']).'">'.htmlspecialchars($_GET['artist']).'</a> - A Crem Road artist';
	$artists_file=file_get_contents('./d/artists.txt'); 

	$artists=explode("\n", $artists_file);
	if (!in_array($_GET['artist'], $artists)) {
		echo 'ooops... Invalid artist !';
		exit();
	}

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

$material_artists_file=htmlentities(trim(file_get_contents('./d/material_artists.txt')));
	
$material_artists=explode("\n", $material_artists_file);
	
$material_blacklist_file=htmlentities(trim(file_get_contents('./d/material_blacklist.txt')));
	
$material_blacklist=explode("\n", $material_blacklist_file);

if (isset($_GET['listall'])&&$_GET['listall']==='material') {
	$artists=$material_artists;
	$material_currency=trim(file_get_contents('./d/material_currency.txt'));
	$material_paypal_address=trim(file_get_contents('./d/material_paypal_address.txt'));
	$material_shipping_file=trim(file_get_contents('./d/material_shipping.txt'));
	$material_shippings=explode("\n", $material_shipping_file);
	
	$material_shipping=Array();
	$count=count($material_shippings);
	$i=0;
	while ($i < $count){
		$material_shipping[$material_shippings[$i]]=$material_shippings[$i+1];
		$i++;
		$i++;
	}
	$material_supports_file=trim(file_get_contents('./d/material_supports_and_prices.txt'));
	$material_supports=explode("\n", $material_supports_file);
	
	$material_support=Array();
	$i=0;
	$count=count($material_supports);
	while ($i<$count){

		$material_support[$material_supports[$i]]['description']=$material_supports[$i+1];
		$material_support[$material_supports[$i]]['price']=$material_supports[$i+2];
		$material_support[$material_supports[$i]]['options']=$material_supports[$i+3];
		$i++;
		$i++;
		$i++;
		$i++;
	}
	
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
		echo '<form id="orderform" style="display:inline;" method="POST" action="./">Your email address : <input type="text" name="validateemail"/><input type="submit"/></form>';
		
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
function generatevideo($track_name, $album, $track_artist, $videoapiurl, $videourl) {
	//let's see if there is a video available
					$videotarget=trim(file_get_contents($videoapiurl.'?artist='.urlencode($track_artist).'&album='.urlencode($album).'&title='.urlencode($track_name).'&gettarget=y'));
					
					
					$hasvideo=false;
					$haslyrics=false;
					if ($videotarget!==''){
						$hasvideo=true;
						
					}
					
					
					//if has video and GET vid not set, dislplay video display link
					//if has lyrics display lyrics link
					if (($hasvideo || $haslyrics)){
						echo '<div style="background-color:#FAFAFA;text-align:center;">';
						if ($hasvideo&&!isset($_GET['vid'])){
							
							echo '<a href="./?vid=play&track='.urlencode($track_name).'&album='.urlencode($album).'&artist='.urlencode($track_artist).'">Video available</a>';
						}
						
						echo '</div>';
						
					}
					if ($hasvideo&&isset($_GET['vid'])&&isset($_GET['artist'])&&isset($_GET['album'])&&isset($_GET['track'])){
						echo '<div style="background-color:#FAFAFA">';
						$videoformatsfile=trim(file_get_contents($videoapiurl.'?listformats='.urlencode($videotarget)));
						$videoformats=explode("\n",$videoformatsfile);
						$videotargetfiles=Array();
						foreach ($videoformats as $videoformat){
							$downgradefile=trim(file_get_contents($videoapiurl.'?hasdowngrade='.$videotarget.'&format='.$videoformat));
							$downgradelist=explode("\n", $downgradefile);
							sort($downgradelist);
							if (count($downgradelist)>0) {
								$videotargetfiles[$videoformat]=$downgradelist[0].'.'.$videotarget.'.'.$videoformat;
							}
							else {
								$videotargetfiles[$videoformat]=$videotarget.'.'.$videoformat;
							}
						
						}
						echo '<video controls="controls" autoplay="autoplay" style="width:100%;">';
						foreach($videoformats as $videoformat) {
							echo '<source src="'.htmlspecialchars($videourl.$videotargetfiles[$videoformat]).'"></source>';
							
							
						}
						echo 'Your browser is very old and does not support HTML5 video, sorry</video>';
						echo '<br/>';
						echo 'Download : ';
						foreach ($videoformats as $videoformat) {
							echo '<a href="'.$videourl.urlencode($videotarget).'.'.urlencode($videoformat).'">'.htmlspecialchars($videoformat).'</a> ';
							
						}
						echo '<br/>';
						echo htmlspecialchars(trim(file_get_contents($videourl.$videotarget.'.description.txt')));
						echo '</div>';
						
					}
					
}
function showsongsheet($track) {
	if (file_exists('./songbook/'.$track.'.txt')){
		echo '<div style="background-color:#A8A8A8;"><a href="#'.htmlspecialchars($track.'.txt').'" onclick="document.getElementById(\''.htmlspecialchars($track.'.txt').'\').style=\'display:block;\'">Lyrics/chords</a></div>';
		echo '<a name="'.htmlspecialchars($track.'.txt').'"><div style="font-family:monospace;display:none;background-color:#DFDFDF;" id="'.htmlspecialchars($track.'.txt').'">'.str_replace("\n", '<br/>', htmlspecialchars(file_get_contents('./songbook/'.$track.'.txt'))).'</div></a>';	
			
	}
	
}
function displaycover($album, $ratio){
	if (file_exists('./d/covers.txt')){
		$coversfile=trim(file_get_contents('./d/covers.txt'));
		$coverslines=explode("\n", $coversfile); 
		
		$i=0;
		$url=null;
		while ($i<count($coverslines)){
			if (htmlentities($coverslines[$i])===$album){
				$url=$coverslines[$i+1];
				
			}
			$i++;
			$i++;
		}
		if (isset($url)){
			$output='';
			$output.='<img id="cover_'.htmlspecialchars($album).'"/>';
		
			$output.='<script>;
			var size;
			if (document.documentElement.clientWidth>=document.documentElement.clientHeight){
				size=document.documentElement.clientHeight;
			}
			else
			{
				size=document.documentElement.clientWidth;
			}				 ';
			$output.='document.getElementById('."'".'cover_'.str_replace("'","\\'",$album)."'".').src='."'".'./thumbnailer.php?target='."'".'+encodeURI('."'".str_replace("'","\\'",'./covers/'.$url)."'".')+'."'".'&viewportwidth='."'".'+encodeURI(size)+'."'".'&ratio='."'".'+encodeURI('."'".str_replace("'","\\'",$ratio)."'".');';
							 
			$output.='</script>';
			
			return $output;
		}
		else {
			return '';
		}
	
	
	
	}
	else{
		return '';
	}	
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
<script src="http://<?php echo $server;?>/script.js">
</script>
</head>
<body>
<?php
if (isset ($_GET['message'])&&isset($message[$_GET['message']])){
	
	echo '<div style="color:red;background-color:black;width:100%;text-align:center;"><span><strong>'.$message[$_GET['message']].'</strong><a href="./" style="color:black;background-color:red;text-decoration:underline;float:right;text-align:right;">X</a></span></div>';
	
}


?>
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
<?php if (!isset($_GET['listall'])){
	echo '<a style=float:right;" href="./?listall=albums'.$arturl.'">List all albums</a><br/>';
	
}
?>
<span style="float:right;text-align:right;">
<?php
	loginpanel();
?>

</span>
<h2 style="clear:both;"><em><?php echo htmlspecialchars($description);?></em></h2>

<?php
//artist list
if (!isset($_GET['artist'])&&!isset($_GET['track'])&&!isset($_GET['album'])&&!(isset($_GET['listall'])&&$_GET['listall']==='material')){
	$artists_file=file_get_contents('./d/artists.txt'); 
	
	

	$artists=explode("\n", trim($artists_file));

	sort($artists);
	echo '<span style="margin-top:4px;marging-bottom:4px;"><a style="float:left;padding:2px;" href="./">Artists : </a> ';

	foreach ($artists as $artist) {
		echo '<a style="float:left;border:solid 1px;background-color:#A0A0A0;padding:2px;" href="http://'.$server.'/?artist='.urlencode($artist).'"> '.htmlspecialchars($artist).' </a> ';
		
		
	}
	echo '</span><br style="clear:both;"/>';
}


//material releases : listing products

$material=false;

if((isset($_GET['listall'])&&$_GET['listall']==='material')) {
	$material=true;
	echo '<h1>Material releases</h1><h2>What we offer : </h2>';
	echo 'All prices are indicated in '.htmlspecialchars($material_currency);
	echo '<div><em>This is the minimum price. You name you price, actually, and you can pay more than this if you wish to.</em></div>';
	$material_items=array_keys($material_support);
	echo '<table><tr>';
	echo '<td style="border: solid 1px;"><strong><em>Product</em></strong></td>';
	$material_item_lines=array_keys($material_support[$material_items[0]]);
	foreach ($material_item_lines as $material_item_line) {
				echo '<td style="border: solid 1px;"><strong><em>'.htmlspecialchars($material_item_line).'</em></strong></td>';
		
	}
		
	echo '</tr>';
	
	foreach ($material_items as $material_item)
	 {
		echo '<tr>';
		echo '<td style="border: solid 1px;"><strong>'.htmlspecialchars($material_item).'</strong></td>';
		$material_item_lines=array_keys($material_support[$material_item]);
		foreach ($material_item_lines as $material_item_line) {
			echo '<td style="border: solid 1px;">';
			if (isset($material_support[$material_item][$material_item_line])&&$material_support[$material_item][$material_item_line]!==''){
				echo htmlspecialchars($material_support[$material_item][$material_item_line]);
				}
			echo '</td>';
		}
		echo '</tr>';
			
	}
	echo '</table>';
	echo '<form method="POST" action="processorder.php">';
}
$weactuallydisplayedsomething=false;

//here we are, let's query  apis to fill the content arrays
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
//let's query the local storage
$querystring = '';
		foreach ($artists as $artist) 
		{
			$querystring.='&listallalbums[]='.urlencode($artist);
			
			
		}



		$albums_file=file_get_contents($serverapi.'?'.$querystring);
		$albums=unserialize($albums_file);
		ksort($albums);
		$albums=array_reverse($albums);
		
		$contentlocal=$albums;



if ($_SESSION['random']){
	$_GET['offset']=rand(0, count($content)-1);
	$_GET['autoplay']='autoplay';
}



?>



<?php
//here we go, let's output the content
if (isset($_GET['offset'])&&is_numeric($_GET['offset'])) {
	$offset=intval($_GET['offset']);
	
	if ($offset!==0&&!$_SESSION['random']&&!isset($_GET['listall'])){
	
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
else if (!isset($_GET['artist'])&&!$material)
{
	echo '<a style="float:right" href="./?random=true">random play</a>';
}

//local *****

foreach ($contentlocal as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0 || isset($_GET['listall'])) {
	$running=true;
	
	if ((isset($_GET['album'])&&$_GET['album']!==$item['album'])||(isset($_GET['listall'])&&$_GET['listall']==='material')) {
		$running=false;
		
	}
	
	
	
	
	if (isset ($item['album'])&&$running){
		$weactuallydisplayedsomething=true;
		$ran=true;

		if (!isset($_GET['listall'])){
			echo '<h1>Album : ';
		}
		else{
			echo '<h1>';
		}
		
		echo '<a href="./?album='.urlencode($item['album']).'">'.$item['album'].'</a></h1>';
		
		echo '<div style="margin-left:auto;margin-right:auto;">'.displaycover($item['album'], 0.62).'</div>';

		
		if (!isset($_GET['listall'])){
		
		
			//here we go, query local API for track list
			$tracks_file=file_get_contents($serverapi.'?gettracks='.urlencode($item['album']));

			$tracks=explode("\n", $tracks_file);
			$trackcounter=0;
			$hasntautoplayed=true;
			foreach ($tracks as $track) {
			if ($track!==''){
			
				//we want its name and the artist name as well
				$track_name=trim(file_get_contents($serverapi.'?gettitle='.urlencode($track)));
		
				$track_artist=trim(file_get_contents($serverapi.'?getartist='.urlencode($track)));
				
				if (!isset($_GET['track'])||$_GET['track']==$track_name)
				{
					if (in_array($track_artist, $artists)){
					?>
					<a href="#" onClick="play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, false);" id="<?php echo $trackcounter; ?>">▶</a>
					 <a href="./?artist=<?php echo urlencode ($track_artist); ?>">
					 <?php echo  $track_artist; ?></a> - 
					 <?php echo  '<a href="./?track='.urlencode($track_name).'&album='.urlencode($item['album']).'">'.$track_name.'</a>'; ?>
					 <div style="background-color:#F0F0F0;text-align:right;">Early access download not available for now</div>
					<?php
					generatevideo($track_name, $item['album'], $track_artist, $videoapiurl, $videourl);
					showsongsheet($track);
					?>
					
					<?php
					if (isset($_GET['autoplay'])&&$hasntautoplayed){
						?>
						<script>play('<?php echo htmlspecialchars($track); ?>', <?php echo $trackcounter; ?>, false);</script>
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
	
}
if ($ran) {
	$secondcounter++;

}	
$counter++;

}


//remote ****



//material header
if($material) {

	echo '<h2>Order form</h2><div>Please indicate your shipping option, then select your desired items in the list bellow. Once you are done, please click on "Process Checkout" at the bottom of the list</div>';
	
	echo 'Shipping option : <select name="shipping">';
	$shipping_options=array_keys($material_shipping);
	$i=1;
	foreach ($shipping_options as $shipping_option){
		echo '<option value="'.htmlspecialchars($shipping_option).'" ';
		
		if ($i==count($shipping_options)){
			echo 'selected="selected"';
		}
		
		echo '>'.htmlspecialchars($shipping_option).' ('.htmlspecialchars($material_currency.' '.$material_shipping[$shipping_option].'/item)').'</option>';
		$i++;
	}
	
	echo '</select><h2>Albums available as physical releases : </h2>';
			
	$itemid=0;
			
}

foreach ($content as $item){
	$ran=false;
	
	if ($counter>$offset && $secondcounter==0||isset($_GET['listall'])) {
	$running=true;
	
	if (isset($_GET['album'])&&$_GET['album']!==$item['album']) {
		$running=false;
		
	}
	if (!(in_array($item['artist'], $material_artists)&&!in_array($item['album'], $material_blacklist))&&(isset($_GET['listall'])&&$_GET['listall']==='material'))
	{
		$running=false;
	}
	
	
	
	if (isset ($item['album'])&&$running){
		$weactuallydisplayedsomething=true;
		$ran=true;

		if (!isset($_GET['listall'])){
			echo '<h1>Album : ';
		}
		else{
			echo '<h1>';
		}
		
		
		
		
		echo '<a href="./?album='.urlencode($item['album']).'">'.$item['album'].'</a></h1>';
		echo '<div style="margin-left:auto;margin-right:auto;">'.displaycover($item['album'], 0.62).'</div>';

		//material order form
		if ($material) {
			
			$material_items=array_keys($material_support);
			echo '<table><tr>';
		
			foreach ($material_items as $material_item)
			 {
				echo '<td style="border: solid 1px;"><strong>'.htmlspecialchars($material_item).'</strong><br/>quantity : ';
				
				echo '<input type="hidden" id="inputid'.$itemid.'"/>';
				?>
				<script>
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'item['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				</script>
				<?php
				echo '<select onchange="this.name=targetid'.$itemid.';">';
				echo '<option value="0">0</option>';
				$i=1;
				while ($i<=50) {
					echo '<option value="'.htmlspecialchars($i).'">'.htmlspecialchars($i).'</option>';
					$i++;
				}
				
				
				echo '</select>';
				$itemid++;
				if (isset($material_support[$material_item]['options'])&&$material_support[$material_item]['options']!=''){
				?>
				<script>
				var targetid<?php echo $itemid; ?>='<?php echo str_replace ("'", "\\'", 'option['.htmlspecialchars($item['album']).']['.htmlspecialchars($material_item).']'); ?>';
				
				</script>
				
				<?php
				
					$options=explode (' ', $material_support[$material_item]['options']);
					$optionheader=false;
					$optionselected=false;
					echo '<br/>';
					foreach ($options as $option) {
						if (!$optionheader) {
							echo htmlspecialchars($option);
							echo '<select onchange="this.name=targetid'.$itemid.';">'."\n";
							$optionheader=true;
							echo '<option>Select</option>';
							}
							
						else {
							echo '<option value="'.htmlspecialchars($option).'" ';
							
							echo '>'.htmlspecialchars($option).'</option>';
						}
						
						
						
						}
					echo '</select>';
					$itemid++;
					}	
				
				
				echo '</td>';
				
				
					
			}
			echo '</tr>';
		
			echo '</table>';
			
		echo '<input style="float:right;text-align:right;" type="submit" value="Process checkout now"/>';		
			
			
			
		}

		




			
		if (in_array($item['artist'], $material_artists)&&!in_array($item['album'], $material_blacklist)&&!$material){
			echo 'available on material support at our <a href="http://'.$server.'/?listall=material">physical releases shop</a><br/>';
			
		}
		
		if (!isset($_GET['listall'])){
			
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
						 <?php echo  '<a href="./?track='.urlencode($track_name).'&album='.urlencode($item['album']).'">'.$track_name.'</a>';?>
						 <div style="background-color:#F0F0F0;text-align:right;">Download <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.flac">flac</a> <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.ogg">ogg</a> <a href="<?php echo $clewnaudiourl.urlencode ($track); ?>.mp3">mp3</a></div>
						<?php
						generatevideo($track_name, $item['album'], $track_artist, $videoapiurl, $videourl);
						showsongsheet($track);
						?>
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
	
}

if ($ran) {
	$secondcounter++;

}	
$counter++;
}//foreach $content


if (!isset ($_GET['album'])&&!isset($_GET['track'])&&!$_SESSION['random']&&$weactuallydisplayedsomething&&!isset($_GET['listall'])){
	echo '<a id="digolder" style="float:right;" href="./?offset='.intval($offset+1).$arturl.'">Dig older...</a><br/>';

}
if (!$weactuallydisplayedsomething){
	
	echo 'Yeah ! You reached the bottom... There is nothing older.<br/>';
	
	
}
if($material) {
	
			
			
	echo '</form><br style="clear:both;"/>';
	echo $materialreleasessalesagreement.'<br style="clear:both;"/>';
}
echo $footerhtmlcode;

?>
</body>
</html>
