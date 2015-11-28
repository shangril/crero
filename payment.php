<?php
require_once('./config.php');
error_reporting(E_ERROR | E_PARSE);
session_start();

if (!isset($_POST['item'])){
	
	die();
	
}

$material_artists_file=htmlentities(trim(file_get_contents('./d/material_artists.txt')));
	
$material_artists=explode("\n", $material_artists_file);
	
$material_blacklist_file=htmlentities(trim(file_get_contents('./d/material_blacklist.txt')));
	
$material_blacklist=explode("\n", $material_blacklist_file);

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
<script src="http://<?php echo $server; ?>/script.js">
</script>
<style>
table {display:none;}
td {border:solid 1px;}
</style>
</head>
<body>
	<h1><?php echo $title; ?> - Final step</h1>
	<h2>It's time for the money to travel for one hand to another</h2>
<?php

echo '<table><tr><td>Album</td><td>Product</td><td>Quantity</td><td>Unit price</td><td>Total price</td></tr>';
$items=array_keys(unserialize($_POST['item']));
$_POST['shipping']=unserialize($_POST['shipping']);
$order=unserialize($_POST['item']);
$options=unserialize($_POST['option']);
$tip=floatval($_POST['tip']);
$currency=$material_currency;


$bill=Array();
$bill['order']=$order;
$bill['options']=$options;
$bill['shipping']=$_POST['shipping'];
$bill['tip']=$tip;
$bill['currency']=$currency;

$productcount=0;
$unitcount=0;
$pricecount=0;

foreach ($items as $item){
	$products=array_keys($order[$item]);
	foreach ($products as $product){

		
		if (is_numeric($order[$item][$product])&&intval($order[$item][$product])>0){
			echo '<tr><td>'.htmlspecialchars($item).'</td><td>'.htmlspecialchars($product);
			if (in_array($product, array_keys($material_support))){
				$that=$material_support[$product]['option'];
				if (isset($that)&&$that!==''){
						echo '<br/>Option: ';
						if (isset($options[$item][$product])){
							echo htmlspecialchars($options[$item][$product]);
						}
						else {
							echo '<h3>Please select an option for this item, otherwise we will consider you do not mind and chose it randomly in our stock</h3>';
						}
							
				}
				
			
			}
			
			echo '</td>';
			
			
			echo '<td>'.htmlspecialchars($order[$item][$product]);
			

				
			
			echo '</td>';
			$howmany=intval($order[$item][$product]);
			$unitprice=$material_support[$product]['price'];
			$totalprice=$unitprice * $howmany;
			
			
			
			echo '<td>'.htmlspecialchars($unitprice).'</td>';
			echo '<td>'.htmlspecialchars($totalprice).'</td>';
			echo '</tr>';
			$unitcount+=$howmany;
			$pricecount+=$totalprice;
			$productcount+=$order[$item][$product];
			
		}
		
	}
	
}
echo '<tr><td colspan="2" style="background-color:yellow;">Raw total</td>';
echo '<td>'.htmlspecialchars($unitcount).'</td>';
echo '<td>N/A</td>';
echo '<td>'.htmlspecialchars($pricecount).'</td>';

echo '</tr>';

echo '<tr>';
echo '<td>Shipping</td>';
echo '<td>'.htmlspecialchars($_POST['shipping']).'</td>';
if (in_array($_POST['shipping'],array_keys($material_shipping))){
	$shipping_price=floatval($material_shipping[$_POST['shipping']]);
	
}
else
{ die(); }
echo '<td>'.htmlspecialchars($unitcount).'</td>';

echo '<td>'.htmlspecialchars($shipping_price).'</td>';


$totalshipping=$unitcount*$shipping_price;

echo '<td>'.htmlspecialchars($totalshipping).'</td>';
echo '</tr>';
echo '<tr><td colspan="4" style="background-color:yellow;">Net total</td>';
echo '<td>'.htmlspecialchars($totalshipping+$pricecount).'</td>';
echo '</tr>';
echo '</table>';

$bill['amount']=$totalshipping+$pricecount;

$total=$totalshipping+$pricecount+(floor($tip*100)/100);
$totalminusshipping=$pricecount+(floor($tip*100)/100);


$bill['total']=$total;

if (file_exists('./d/orders')&&!is_dir('./d/orders')) {
	echo 'The system is misconfigured. "orders" must be a directory. Please fix this, if you are the webmaster. If you are not, please tell him to do so.';
	
	
} else if (file_exists('./d/material_messages')&&!is_dir('./d/material_messages')) {
	echo 'The system is misconfigured. "material_messages" must be a directory. Please fix this, if you are the webmaster. If you are not, please tell him to do so.';
	
	
}



else {
	if (!file_exists('./d/orders')) {
		mkdir('./d/orders');
	}
	if (!file_exists('./d/material_messages')) {
		mkdir('./d/material_messages');
	}

	$past_bills=array_diff(scandir('./d/orders/'), array('..', '.'));
	$orderid=(count($past_bills)+1).'-'.microtime(true);
	$orderdata=serialize ($bill);
	mkdir ('./d/material_messages/'.$orderid);
	
	if (file_put_contents('./d/orders/'.$orderid.'.php', $orderdata)){
		echo 'Your order has been correctly recorded in our system under the reference '.htmlspecialchars($orderid).'. Please click on the above button to be redirected to a secure money transfer service and process the payment : ';
		?>
		<form style="text-align:right" name="_xclick" action="https://www.paypal.com/fr/cgi-bin/webscr" method="post" >
			<input type="hidden" name="cmd" value="_xclick" />
			<input type="hidden" name="business" value="<?php echo htmlspecialchars($material_paypal_address);?>" />
			<input type="hidden" name="item_name" value="<?php echo htmlspecialchars('Order '.$orderid.' at '.strip_tags($title)); ?>" />
			<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($material_currency); ?>" />
			<input type="hidden" name="amount" value="<?php echo htmlspecialchars($totalminusshipping); ?>" />
			<input type="hidden" name="shipping" value="<?php echo htmlspecialchars($totalshipping)?>" />
			<input type="hidden" name="cancel_return" value="http://<?php echo htmlspecialchars($server); ?>" />
			<input type="hidden" name="return" value="http://<?php echo htmlspecialchars($server); ?>/?thankyou=material" />
			<input type="submit" name="submit" value="Pay now" />
			</form>
		
		
		<?php
		
	}
	else {
		echo 'We were not able to save your order in the system. Such a thing should normaly not happen. You may want to try again, or not.';
		
	}

}


?>
</body>
</html>
