<?php
session_start();
include('site_variables.php');

include ('header_functs.php');

if (isset($_POST['nick'])){
		srand();
		$_SESSION['nick']=$_POST['nick'];
		$_SESSION['zero']=microtime(true);
		$_SESSION['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
}
echo generate_header($site_name.' - '.$site_slogan,$site_description);

if (isset($_SESSION['lat'])&&isset($_SESSION['long'])&&isset($_SESSION['nick']))
{
	include ('chat.php');
	die(); } ?>
	


<?php if (!isset($_GET['lat'])){?>
<script>
navigator.geolocation.getCurrentPosition(GetLocation);
function GetLocation(location) {
    window.location.assign('./?lat='+encodeURI(location.coords.latitude)+'&long='+encodeURI(location.coords.longitude));

}
</script>
<?php } ?>
<h1 class="main_title"><em><strong><?php echo htmlspecialchars($site_name);?></strong></em></h1>
<h2 class="main_subtitle"><em><strong><?php echo htmlspecialchars($site_slogan);?></strong></em></h2>
<div>
<?php if (!isset($_GET['lat'])){?>
<span style="float:right;">To allow everyone to know who is living nearby,<br/>please allow your browser to send us your location.<br/>Only distances will be displayed,<br/>not your actual position. </span>
<?php } 

else {
	$_SESSION['lat']=$_GET['lat'];
	$_SESSION['long']=$_GET['long'];
	echo '<form style="display:inline;" method="POST" action="./">Enter a nickname <input type="text" name="nick" /><input type="submit"/></form>';
	
	
}





?>
</div>
<?php
echo generate_footer($site_footer);
?>
