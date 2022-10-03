<?php
require_once('./config.php');
error_reporting(E_ERROR | E_PARSE);
session_start();

if (!isset($_POST['item'])){
	
	die();
	
}

$material_artists_file=htmlentities(trim(file_get_contents('./d/material_artists.txt')));
	
$material_artists=explode("\n", $material_artists_file) ?? Array();

if ($material_artists_file==''||$material_artists[0]==''){
	die();
}
	
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
	<h1><?php echo $title; ?> - Almost complete</h1>
	<h2>
		
		
		Do you feel generous today ? 
		
		
		
		
		</h2>
<?php

echo '<table><tr><td>Album</td><td>Product</td><td>Quantity</td><td>Unit price</td><td>Total price</td></tr>';
$items=array_keys(json_decode($_POST['item'], true));
$_POST['shipping']=json_decode($_POST['shipping'], true);
$order=json_decode($_POST['item'], true);
$options=json_decode($_POST['option'], true);


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

if (!$ismaterialnameyourprice){

	echo '<div>You may want to support our work and our will to sell cheap by making an optional donation to support the label : </div>';

} else {
	echo '<div>It is time to choose how much you are willing to pay for this order. See below for information about our "name your price, no minimum" policy.</div>';
}

echo 'Order total : '.htmlspecialchars($material_currency).' ';
echo htmlspecialchars($totalshipping+$pricecount);
echo '<br/>';
if (!$ismaterialnameyourprice){
	echo 'Your tip : '.htmlspecialchars($material_currency).' ';
}
else {
			echo 'This is the <em>recommended, reasonable price</em>. You choose what you want to pay :  '.htmlspecialchars($material_currency).' ';

	
}

echo '<form action="payment.php" method="post" style="display:inline;">';
echo '<input type="text" size="5" value="';

if (!$ismaterialnameyourprice){
		echo '00.00';
	}
else {
		echo htmlspecialchars($totalshipping+$pricecount);
}

echo '" name="tip"></input><br/>';
echo '<span  style="float:right;"><a href="./">Cancel your order</a> or <input type="hidden" name="item" value="'.htmlspecialchars(json_encode($order)).'"></input>';
echo '<input type="hidden" name="option" value="'.htmlspecialchars(json_encode($options)).'"></input>';
echo '<input type="hidden" name="shipping" value="'.htmlspecialchars(json_encode($_POST['shipping'])).'"></input>';
echo '<input type="submit" value="Process to payment"></input></form>';

if ($ismaterialnameyourprice) {
	
	echo '<div>'.$materialnameyourpricenotice.'</div>';
}


echo '<br/>Please note that our orders, no matter if the payment has been processed or the order cancelled, are anonymously but publicly available, for people to know how the label is going. Personnal data will not be displayed, Only anonymous data about orders history are made available for the audience.';



?>
</body>
</html>
