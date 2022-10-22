<?php
session_start();

if (!isset($_SESSION['cart'])) {
    
		$_SESSION['cart']=array();
		$_SESSION['cart']['album']=array();
		
		$_SESSION['cart']['track']=array();
    
    
    }
    
$albumtoadd=array();

$album_title=$_GET['album'];

if (!isset($_SESSION['album_tracklisting'][$_GET['album']])){
    
    die ('FAILURE - Sorry, the system encoutered an error. Please try to refresh this page and try again. ');
    
    }
$tracklist=$_SESSION['album_tracklisting'][$_GET['album']];
$albumtoadd['title']=$album_title;
$albumtoadd['tracklist']=$tracklist;

array_push($_SESSION['cart']['album'], $albumtoadd);

?>
<!--<div style="text-align:center;">

<h2>Album added to cart</h2>-->
<strong>The album was added to your download cart</strong> <a style="text-decoration:undeline;" target="_blank" href="view_cart.php" onclick="get_player().pause();">[view cart and download]</a><!--
<br/>
What would you want to do now ? 
<h2><a href="javascript:void(0);" onclick="cr_c_document_getElementById('ajax_splash').style.display='none';">Continue picking songs</a></h2>
or
<h2><a target="_blank" href="view_cart.php" onclick="get_player().pause();">Proceed to download</a></h2>

</div>-->

<?php

die();
?>
