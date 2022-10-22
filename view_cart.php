<?php
session_start();
error_reporting(0);
include ('./config.php');

$total=0;

$albcounter=0;
$trkcounter=0;
$albprice=$downloadCartAlbumPrice;
$trkprice=$downloadCartTrackPrice;
$orderid=microtime(true);
$_SESSION['cart_id']=$orderid;

if (isset($_GET['delete_album'])&&is_numeric($_GET['delete_album'])){
    unset($_SESSION['cart']['album'][$_GET['delete_album']]);
    $_SESSION['cart']['album']=array_values($_SESSION['cart']['album'] ?? Array());
    }

if (isset($_GET['delete_track'])&&is_numeric($_GET['delete_track'])){
    unset($_SESSION['cart']['track'][$_GET['delete_track']]);
    $_SESSION['cart']['track']=array_values($_SESSION['cart']['track'] ?? Array());
    }
?><!DOCTYPE html><html>
<head>
<link rel="shortcut icon" href="<?php echo $favicon;?>" />
<link rel="stylesheet" href="//<?php echo $server; ?>/style.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" value="utf-8" />
<title>In your cart</title>

<?php 
if ($isDownloadCartNameYourPrice){ ?>
<script>
	// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
	
	function initot(){
		return parseFloat(document.getElementById('init_total').value);
	}
	
	function maxmax(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		mytotal.value=myftotal+(parseFloat('0.2')*parseFloat(initot()));
		round_total();
	}
	function max_(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		mytotal.value=myftotal+(parseFloat('0.05')*parseFloat(initot()));
		round_total();
	}
	function minmin(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		mytotal.value=myftotal-(parseFloat('0.2')*parseFloat(initot()));
		round_total();
	}
	function min_(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		mytotal.value=myftotal-(parseFloat('0.05')*parseFloat(initot()));
		round_total();
	}
	function round_total(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		myftotal=parseFloat(myftotal*100);
		myftotal=parseFloat(Math.round(myftotal));
		myftotal=parseFloat(myftotal/100);
		if (myftotal<0){
			myftotal=0
		}
		mytotal.value=myftotal;
	}
	function floor_tot(){
		mytotal=document.getElementById('total');
		myftotal=parseFloat(mytotal.value);
		myftotal=Math.floor(myftotal);
		mytotal.value=myftotal;
	}
	function compute_total(){
    if (document.getElementById('total')!=null){
		if (!isNaN(parseFloat(document.getElementById('total').value))){
				document.getElementById('amount').value=document.getElementById('total').value.replace(',','.');
				if (parseFloat(document.getElementById('amount').value)>0){
					document.getElementById('paypal_form').submit();
					
					}
					else {
						document.getElementById('dl').click();
					}
			}
			else{
				alert('please enter a numeric value with a dot ( . ) for decimal separation');
			}
		}
    }
    // @license-end
</script>
<?php  } else { ?>
<script>
	// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

	function compute_total(){
					document.getElementById('paypal_form').submit();
    }
    // @license-end
</script>
	
	
	
<?php } ?>
</head>
<body>
<a href="./">&lt; Go back to the site</a><br/>
<h1>In your cart</h1>
<?php echo count($_SESSION['cart']['album'] ?? Array());?>
 albums and 
<?php echo count($_SESSION['cart']['track'] ?? Array());?>
 individual tracks
 <h2>Albums : </h2>
 <?php
 foreach ($_SESSION['cart']['album'] as $alb){
    echo '<h4 style="display:inline;">'.$alb['title'].'</h4><span style="float:right;">';
    echo count($alb['tracklist'] ?? Array()).' tracks';
    $total=$total+$albprice;
	echo ' <a href="?delete_album='.$albcounter.'">X Delete</a></span>';
	echo '<hr/>';
    $albcounter++;
    }
?>
<h2>Tracks : </h2>
 <?php
 foreach ($_SESSION['cart']['track'] as $trk){
    echo '<h4 style="display:inline;">'.$trk['title'].'</h4> by <strong>'.$trk['artist'].'</strong><br/>On <em>'.$trk['album'].'</em><span style="float:right;">';
    $total=$total+$trkprice;
	echo ' <a href="?delete_track='.$trkcounter.'">X Delete</a></span>';
	echo '<hr/>';
    $trkcounter++;
    }



echo '<hr/> '.count($_SESSION['cart']['album'] ?? Array()).' times '.htmlspecialchars($downloadCartCurrency.' '.$albprice).' (full albums) plus '.count($_SESSION['cart']['track'] ?? Array()).' times '.htmlspecialchars($downloadCartCurrency.' '.$trkprice).' (individual tracks)';
echo '<span style="float:right;"><strong>TOTAL: </strong>';
$payment=true;
if (count($_SESSION['cart']['album'] ?? Array())>0 || count($_SESSION['cart']['track'] ?? Array())>0) {//we got more than 0 item in the cart, let's go
	
	if ($isDownloadCartNameYourPrice){
			echo 'Choose what you want to pay<br/>';
			

			echo '<a href="javascript:void(0);" onclick="minmin()">[--]</a> <a href="javascript:void(0);" onclick="min_()">[-]</a>';
			echo htmlspecialchars($downloadCartCurrency). ' ';
			echo'  <input id="total" style="text-align:right;" size="7" type="text" readonly value="';
			echo htmlspecialchars($total);
			echo '">';
			
			echo '<input id="init_total" type="hidden" value="'.htmlspecialchars($total).'"/>';
			
			echo '<a href="javascript:void(0);" onclick="max_()">[+]</a> <a href="javascript:void(0);" onclick="maxmax()">[++]</a><br/><span style="float:right;font-size:84%;">Name your price, no minimum <a href="javascript:void(0);" onclick="floor_tot()">remove decimals</a></span>';
		}
		else{
			echo htmlspecialchars($downloadCartCurrency). ' ';

			echo htmlspecialchars($total);
			
			}
}
else {//we got 0 item in the cart, display a warning
	echo '<strong>You got 0 items in your download cart. Please <a href="./">go back and browse the site</a> to select songs or albums that you want to download, and try again then. </strong>';
	if ($isDownloadCartNameYourPrice){
		echo '<h3>The price is only a suggested donation. You pay what you want, including 0. It\'s &quot;<strong>Name your price, no minimum</strong>&quot;.</h3>';
	}
	
	
	
	$payment=false;

}
echo '</span>';
	if ($payment) {
 ?>
 <hr/>
<button style="float:right;" onClick="compute_total();" type="button">Proceed to payment</button>

<form id="paypal_form" name="_xclick" action="https://www.paypal.com/fr/cgi-bin/webscr" method="post" >
<input type="hidden" id="cmd" name="cmd" value="_xclick" />
<input type="hidden" name="custom" value="<?php echo microtime(true);?>"/>
<input type="hidden" name="business" value="<?php echo htmlspecialchars($downloadCartPaypalAddress);?>" />
<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($sitename); ?> - D/l Order <?php echo $orderid ?>" />
<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($downloadCartCurrency);?>" />
<input type="hidden" id="amount" name="amount" value="<?php echo htmlspecialchars($total);?>" /> 
<input type="hidden" name="return" value="http://<?php echo htmlspecialchars($server);?>/download-page.php"/>
<input type="hidden" name="rm" value="1"/>
<input type="hidden" name="cancel_return" value="http://<?php echo htmlspecialchars($server);?>"/> 
</form>




<?php 
}

if ($isDownloadCartNameYourPrice){ ?>

<a id="dl" href="download_page.php"></a>

<?php  } ?>


</body>
</html>
