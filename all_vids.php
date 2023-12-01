<?php
require_once('./config.php');

$activatehtmlcache=true;

ob_start();
$myhtmlcache=null;

require_once('./crero-lib.php');

$willhavetocache=false;
if ($activatehtmlcache){
$myhtmlcache=new creroHtmlCache($htmlcacheexpires);

}
$cachingkey='key:';


$_GET['all_vids']='1';//necessary if cache enabled


$get_keys=array_keys($_GET);


$whitelist= array ('a', 'b', 'all_vids');

foreach ($get_keys as $get_key){
	//EXPECT crazy behaviors if target[] count more than one single elements. For now this is not a problem, cause the the sole target[] is array ('radio') and has only one element
	if (in_array($get_key, $whitelist)){
		$cachingkey.=$get_key."\n".$_GET[$get_key];
		}
}
if ($activatehtmlcache&&!isset($_POST['validateemail'])&&!isset($_GET['pingstat'])){
	if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
	
	if ($myhtmlcache->hasPageExpired($cachingkey)){
		$willhavetocache=true;
		
	}
	else {
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) <= date(DATE_RFC822, $myhtmlcache->getCachedPageDate($cachingkey))){
				header(null);
				
				header('HTTP/1.0 304 Not Modified');
				die();
			}
			
		}
		
		
		header('Last-Modified: '.date(DATE_RFC822, $myhtmlcache->getCachedPageDate($cachingkey)));
		echo $myhtmlcache->getCachedPage($cachingkey);
		die();
	}


}




$cache = array();
if (file_exists('./vidcache.dat')){
		$cache=unserialize(file_get_contents('./vidcache.dat'));
}

?><!DOCTYPE html>
<html>
<head>
<title>All videos from <?php echo htmlspecialchars($sitename);?></title>
<link rel="shortcut icon" href="./<?php echo $favicon;?>" />

<meta name="description" value="<?php echo htmlspecialchars($description);?>"/>
<meta charset="UTF-8"/>
<link href="./style.css" rel="stylesheet" type="text/css"/>
<meta name="viewport" content="width=device-width" />
</head>
<body>
	<a href="./"><?php echo htmlspecialchars($sitename);?> Home</a> &gt; All vids<hr/>
	<?php
	$arts = explode ("\n", file_get_contents('./d/artists.txt'));
	sort($arts);
	$selart='';
	if (isset($_GET['a'])){$selart=$_GET['a'];}
	foreach ($arts as $art){
		$col='';
		if ($art==$selart){$col='background-color:white;';}
		echo '<span style="'.$col.'">[<a href="?a='.urlencode($art).'">'.htmlspecialchars($art).'</a>]</span> ';
	}
	echo '<hr/>';
	if ($selart!=''){
		$querystring = 'listalbums='.urlencode($selart);
		$album_file=null;
		$remoteapicache=array();
		if (file_exists('./vidremoteapicache-v2.dat')){
			$remoteapicache=unserialize(file_get_contents('./vidremoteapicache-v2.dat'));
			if (isset($remoteapicache[$querystring])){
				$album_file=$remoteapicache[$querystring];
				
			}
		}
		
		if ($album_file==null) {		
			$query=file_get_contents($clewnapiurl.'?'.$querystring);
			if (file_exists('./vidremoteapicache-v2.dat')){
				$remoteapicache=unserialize(file_get_contents('./vidremoteapicache-v2.dat'));
			}
			$remoteapicache[$querystring]=$query;
			file_put_contents('./vidremoteapicache-v2.dat', serialize($remoteapicache));

	
			

		}
		else {
			$query=$album_file;
		}
		//$query=file_get_contents($clewnapiurl.'?'.$querystring);
			
		$query2=file_get_contents($serverapi.'?listalbums='.urlencode($selart));
		
		if ($query!==false&&$query2!==false){$query=trim($query).trim($query2);}
		
		$selalb='';
		if ($query!==false){
			$arts= explode("\n", trim($query));
			
			if (isset($_GET['b'])){$selalb=$_GET['b'];}
			$run=false;
			foreach ($arts as $art){
				$rerun=false;
				if ((in_array(html_entity_decode($art, ENT_COMPAT  | ENT_HTML401 ), $cache)))
				{$rerun=true;}
				else if (intval(trim(file_get_contents($videoapiurl.'?hasalbum='.urlencode(html_entity_decode($art)))))>0){
					$cached=array();
					if (file_exists('./vidcache.dat')){
								$cached=unserialize(file_get_contents('./vidcache.dat'));
							}
							array_push($cached, html_entity_decode($art));
							file_put_contents('./vidcache.dat', serialize($cached));
					
					
					$rerun=true;
				}
				if ($rerun){
					
					$run=true;
					
					$col='';
					if (html_entity_decode($art)==$selalb){$col='background-color:white;';}
					echo '<span style="'.$col.'">[<a href="?a='.urlencode($selart).'&b='.urlencode(html_entity_decode($art)).'">'.$art.'</a>]</span> ';

				}
				

			}
			if (!$run){			
				echo 'This artist/project has no video available for now… <a href="./?artist='.urlencode($selart).'">Listen to the music maybe?</a>';
			}
		}
		else {echo 'no album found. Maybe a temporary overload? Try again later maybe';}
		echo '<hr/>';
		if ($selalb!=''){
			$query=file_get_contents($videoapiurl.'?baselistformats=1');
			if ($query!==false){
				$formats = explode("\n", trim($query));
				
				$query=file_get_contents($videoapiurl.'?listalbvids='.urlencode($selalb));
				if ($query!==false){
					$array=explode("\n",trim($query));
					for ($i=0;$i<count($array);$i++){
						echo 'Video '.ceil(($i+1)/2).'/'.(count($array)/2).'<hr/>';
						echo '<video style="width:85%;" controls="controls" preload="none">';
							foreach ($formats as $format){
								echo '<source src="'.$videourl.rawurlencode($array[$i]).'.'.$format.'"/>';
							}
						
						echo '</video><br>'.htmlspecialchars($array[$i+1]).' - Download ';
						foreach ($formats as $format){
							echo '<a href="'.$videourl.rawurlencode($array[$i]).'.'.$format.'" download>'.htmlspecialchars($format).'</a>';
							}
						echo '<br/>';
						$i++;
					
					}
				}
				else {echo 'Video tier API did not reply. Maybe a temporary overload? You may <a href="./?album='.urlencode(html_entity_decode($selalb)).'">listen to the music maybe?</a>';}
				
			}
			else {echo 'Video tier API did not reply. Maybe a temporary overload? You may <a href="./?album='.urlencode(html_entity_decode($selalb)).'">listen to the music maybe?</a>';}
		}
	}
	
	?>
</body>
</html><?php
if ($activatehtmlcache){


	if ($willhavetocache){
			$htmlcode=ob_get_contents();
			if ($htmlcode!==false){
				$myhtmlcache->cachePage($cachingkey, $htmlcode);
		}
	}

}


?>
