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
td {border:solid 1px;}
</style>
</head>
<body>
	<h1><?php echo $title; ?> - Checkout</h1>
	<h2>Please review your order and, if everything is OK, please click on "Proceed to payment"</h2>
<?php

echo '<table><tr><td>Album</td><td>Product</td><td>Quantity</td><td>Unit price</td><td>Total price</td></tr>';
$items=array_keys($_POST['item']);
$order=$_POST['item'];
$options=$_POST['option'];


$productcount=0;
$unitcount=0;
$pricecount=0;

foreach ($items as $item){
	$products=array_keys($order[$item]);
	foreach ($products as $product){

		
		if (is_numeric($order[$item][$product])&&intval($order[$item][$product])>0){
			echo '<tr><td>'.htmlspecialchars($item).'</td><td>'.htmlspecialchars($product);
			if (in_array($product, array_keys($material_support))){
				$that=$material_support[$product]['options'];
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
echo '<tr><td colspan="4" style="background-color:yellow;">Raw total</td>';
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
echo '<td>'.htmlspecialchars($material_currency).htmlspecialchars($totalshipping+$pricecount).'</td>';
echo '</tr>';
echo '</table>';
echo '<span  style="float:right;"><a href="./">Cancel your order</a> or <form action="tip.php" method="post" style="display:inline;"><input type="hidden" name="item" value="'.htmlspecialchars(serialize($order)).'" />';
echo '<input type="hidden" name="option" value="'.htmlspecialchars(serialize($options)).'"></input>';
echo '<input type="hidden" name="shipping" value="'.htmlspecialchars(serialize($_POST['shipping'])).'"></input>';
echo '<input type="submit" value="Proceed to payment"></input></form>';




?>
</body>
</html>
