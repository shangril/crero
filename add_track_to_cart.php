<?php
session_start();

if (!isset($_SESSION['cart'])) {
    
		$_SESSION['cart']=array();
		$_SESSION['cart']['album']=array();
		
		$_SESSION['cart']['track']=array();
    
    
    }
    
$tracktoadd=array();

$track_album=$_GET['track_album'];
$track_title=$_GET['track_title'];
$track_basefile=$_GET['track_basefile'];
$track_artist=$_GET['track_artist'];


$tracktoadd['title']=$track_title;
$tracktoadd['artist']=$track_artist;
$tracktoadd['file_basename']=$track_basefile;
$tracktoadd['album']=$track_album;

array_push($_SESSION['cart']['track'], $tracktoadd);

?>
<div style="text-align:center;">

<h2>Album added to cart</h2>
<strong>The album was added to your download cart</strong>
<br/>
What would you want to do now ? 
<h2><a href="javascript:void(0);" onclick="document.getElementById('ajax_splash').style.display='none';">Continue picking songs</a></h2>
or
<h2><a href="view_cart.php" >Proceed to download</a></h2>

</div>

<?php

die();
?>
