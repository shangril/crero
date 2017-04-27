<?php
session_start();
error_reporting(0);
include ('./config.php');
if (!file_exists('./admin/d/cartstats')){
	mkdir('./admin/d/cartstats');
		 }
file_put_contents('./admin/d/cartstats/'.$_SESSION['cart_id'].'.dat', serialize($_SESSION['cart']));

$total=0;

$albcounter=0;
$trkcounter=0;
$albprice=$downloadCartAlbumPrice;
$trkprice=$downloadCartTrackPrice;
$orderid=microtime(true);

$supported_formats_remote=explode("\n", file_get_contents($clewnapiurl.'?listformats=true'));

?><!DOCTYPE html><html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="http://<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title><?php echo strip_tags($title); ?> - Your download page</title>

</head>
<body>
<a href="./">&lt; Go back to the site</a><br/>
<h1>You can now download</h1>
<?php echo count($_SESSION['cart']['album']);?>
 albums and 
<?php echo count($_SESSION['cart']['track']);?>
 individual tracks<br/>
 Download tip: use the contextual menu of your device then "save as"
 <h2>Albums : </h2>
 <?php
 foreach ($_SESSION['cart']['album'] as $alb){
    echo '<h4 style="display:inline;">'.$alb['title'].'</h4> <span style="">';
    echo count($alb['tracklist']).' tracks<br/></span>';
    foreach ($alb['tracklist'] as $trk){
		echo '<h5 style="display:inline;">'.$trk['title'].'</h5><span style="float:right;">Download: ';
		foreach ($supported_formats_remote as $mat){
		 ?>
		 <a href="<?php 
		 echo $clewnaudiourl.urlencode ($trk['file_basename']).'.'.$mat.'" download>'.htmlspecialchars($mat); 
		 ?></a> 
		 <?php
		 }

		echo '</span><br style="clear:both;float:none;"/>';
		}
    
    echo '<hr/>';
    $albcounter++;
    }
?>
<h2>Tracks : </h2>
 <?php
 foreach ($_SESSION['cart']['track'] as $trk){
    echo '<h4 style="display:inline;">'.$trk['title'].'</h4> by <strong>'.$trk['artist'].'</strong><br/>On <em>'.$trk['album'].'</em><span style="">';
    ?>
    <span style="float:right;">Download: <?php
		foreach ($supported_formats_remote as $mat){
		 ?>
		 <a href="<?php 
		 echo $clewnaudiourl.urlencode ($trk['file_basename']).'.'.$mat.'" download>'.htmlspecialchars($mat); 
		 ?></a> 
		 <?php
		 }

		echo '</span><br style="clear:both;float:none;"/>';
    
	echo '<hr/>';
    }



echo '<hr/> ';?>
<a href="./">&lt; Go back to the site</a><br/>


</body>
</html>
