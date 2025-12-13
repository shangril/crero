<?php 
if (!isset($lang)){
	$lang='en';
}
else {
	$lang='fr';
}
$trans['en']['monthly']='Donate this on every month';
$trans['fr']['monthly']='Donner cette somme tous les mois';
$trans['en']['help']='Want to support '.htmlentities($sitename).'? What about a donation?';
$trans['fr']['help']='Vous voulez soutenir '.htmlentities($sitename).' ? Et si vous faisiez un don ?';


$trans['en']['donate']='Donate';
$trans['fr']['donate']='Faire un don';

?>
<script>
	
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

var monthly=false;

function toggleMonthly(){
	if (!monthly){
		document.getElementById('cmd').value='_xclick-subscriptions';
		document.getElementById('amount').name='a3';
		document.getElementById('wrapper').innerHTML='<input type="hidden" name="p3" value="1"><input type="hidden" name="t3" value="M"><input type="hidden" name="src" value="1">';
		monthly=true;
	}
	else {
		document.getElementById('cmd').value='_xclick';
		document.getElementById('amount').name='amount';
		document.getElementById('wrapper').innerHTML='';
		
		
		monthly=false;
	}
}



// @license-end
</script>
<span style="font-size:76%;"><?php echo $trans[$lang]['help'];?>
<form target="_blank" name="_xclick" action="https://www.paypal.com/fr/cgi-bin/webscr" method="post" >
<input type="hidden" id="cmd" name="cmd" value="_xclick" />
<input type="hidden" name="custom" value="<?php echo microtime(true);?>"/>
<input type="hidden" name="business" value="<?php echo htmlspecialchars($donationpaypaladdress);?>" />
<input type="hidden" name="item_name" value="<?php echo htmlspecialchars($sitename); ?> - Donate" />
<input type="hidden" name="currency_code" value="EUR" />
<input type="text" size="1" id="amount" name="amount" value="2.50" /> &euro; (EUR) 
<span id="wrapper"></span> &nbsp; 
<input type="checkbox" onClick="toggleMonthly();" /><?php echo $trans[$lang]['monthly'] ;?>
<input type="submit" name="submit" value="<?php echo $trans[$lang]['donate']?>" />
<br/><span>Donations via Paypal. Note that it requires (sadly) to have it communicating to us your postal address<br/>We won't keep it any database of ours, and you may receive a "thank you" postcard<br/><?php
if (is_array($AdditionnalDonationLinks)&&count($AdditionnalDonationLinks)>0){
	echo ". Options:";
	$dk = array_keys($AdditionnalDonationLinks);
	foreach ($dk as $dkey){
		echo '[<a target="_BLANK" href="'.$AdditionnalDonationLinks[$dkey].'">'.htmlspecialchars($dkey).'</a>]';
	}
}
?></span>
</form>
<?php
if (file_exists('./supporters') && is_dir('./supporters')){
	
	echo '<a href="./supporters">Our supporters</a>';
}
?>
</span>
<?php ?>
