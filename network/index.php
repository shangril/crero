<?php
session_start();


if (isset($_GET['norange'])){
	unset($_SESSION['lat']);
	unset($_SESSION['long']);

	$_SESSION['norange']=false;
	
}
if (isset($_GET['login'])){
	unset($_SESSION['logout']);
}

if ((!isset($_SESSION['nick'])&&!isset($_SESSION['logout']))||isset($_GET['login'])&&!(strstr($_SERVER['HTTP_USER_AGENT'], 'http'))&&!(strstr($_SERVER['HTTP_USER_AGENT'], 'bot'))&&$_SERVER['HTTP_USER_AGENT']!==''){
	
	$_SESSION['long']=0;
	$_SESSION['lat']=0;
	$_SESSION['nick']="Anonymous ".microtime(true);
	$_SESSION['range']='Any distance';
	$_SESSION['norange']=true;
	$_SESSION['zero']=microtime(true);
	$_SESSION['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
}

include('site_variables.php');

include ('header_functs.php');

if (isset($_POST['nick'])&&trim($_POST['nick'])!==''){
		srand();
		$data['long']=$_SESSION['long'];
		$data['lat']=$_SESSION['lat'];
		$data['nick']=$_SESSION['nick'];
		$data['range']=$_SESSION['range'];
		$data['message']='changed nick to <'.$_POST['nick'].'> *';
		$data['color']=$_SESSION['color'];
		$data['norange']=$_SESSION['norange'];
		$dat=serialize($data);
		file_put_contents('./d/'.microtime(true).'.php', $dat);
		
		
		
		$_SESSION['nick']=$_POST['nick'];
		$_SESSION['zero']=microtime(true);
		$_SESSION['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
		
}
echo generate_header($site_name.' - '.$site_slogan,$site_description);

if (isset($_SESSION['lat'])&&isset($_SESSION['long'])&&isset($_SESSION['nick']))
{
	include ('chat.php');
	die(); } 
$getloc=false;
	
	?>
	


<?php if (!isset($_GET['lat'])&&!isset($_SESSION['logout'])&&isset($_SESSION['nick'])){
	$getloc=true;
	
	?>
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
<?php 
if ($getloc){
	echo '<div>Please allow this website to access your location to continue !</div>';
}

if (!isset($_GET['lat'])&&!$getloc){?>
<span style="float:right;">Now logued out ! Goodbye ! <a href="./?login=true">Login again</a></span>
<?php } 

else if (!$getloc) {
	$_SESSION['lat']=$_GET['lat'];
	$_SESSION['long']=$_GET['long'];
	echo '<form style="display:inline;" method="POST" action="./">Enter a nickname <input type="text" name="nick" value="'.htmlspecialchars($_SESSION['nick']).'" /><input type="submit"/></form>';
	
	
}





?>
</div>
<?php
echo generate_footer($site_footer);
?>
