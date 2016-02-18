<?php
session_start();


include('site_variables.php');

include ('header_functs.php');


if (isset($_GET['norange'])){
	unset($mysession['lat']);
	unset($mysession['long']);

	$mysession['norange']=false;
	
}
if (isset($_GET['login'])){
	unset($mysession['logout']);
}

if (((!isset($mysession['nick'])&&!isset($mysession['logout']))&&!(strstr($_SERVER['HTTP_USER_AGENT'], 'http'))&&!(strstr($_SERVER['HTTP_USER_AGENT'], 'bot'))&&$_SERVER['HTTP_USER_AGENT']!=='')||isset($_GET['login'])){
	
	$mysession['long']=3000;
	$mysession['lat']=3000;
	$mysession['nick']="Anonymous ".microtime(true);
	$mysession['range']='Any distance';
	$mysession['norange']=true;
	$mysession['zero']=microtime(true);
	$mysession['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
	
	$target='d';
	
	$data['long']=$mysession['long'];
	$data['lat']=$mysession['lat'];
	$data['nick']=$mysession['nick'];
	$data['range']=$mysession['range'];
	$data['message']='*** '.$mysession['nick'].' joined the chatroom';
	$data['color']=$mysession['color'];
	$data['norange']=$mysession['norange'];
	
		
	$dat=serialize($data);
	file_put_contents('./'.$seedroot.'/'.$target.'/'.microtime(true).'.php', $dat);
	
	



}

$_SESSION['seedroot']=$seedroot;

if (isset($_POST['nick'])&&trim($_POST['nick'])!==''){
		srand();
		$data['long']=$mysession['long'];
		$data['lat']=$mysession['lat'];
		$data['nick']=$mysession['nick'];
		$data['range']=$mysession['range'];
		$data['message']='changed nick to <'.$_POST['nick'].'> *';
		$data['color']=$mysession['color'];
		$data['norange']=$mysession['norange'];
		$dat=serialize($data);
		file_put_contents('./'.$seedroot.'/d/'.microtime(true).'.php', $dat);
		
		
		
		$mysession['nick']=$_POST['nick'];
		//$mysession['zero']=microtime(true);
		$mysession['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
		
}
echo generate_header($site_name.' - '.$site_slogan,$site_description);

if (isset($mysession['lat'])&&isset($mysession['long'])&&isset($mysession['nick']))
{
	include ('chat.php');
	die(); } 
$getloc=false;
	
	?>
	


<?php if (!isset($_GET['lat'])&&!isset($mysession['logout'])&&isset($mysession['nick'])){
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
	$mysession['lat']=$_GET['lat'];
	$mysession['long']=$_GET['long'];
	$mysession['norange']=false;
	echo '<form style="display:inline;" method="POST" action="./">Enter a nickname <input type="text" name="nick" value="'.htmlspecialchars($mysession['nick']).'" /><input type="submit"/></form>';
	
	
}





?>
</div>
<?php
echo generate_footer($site_footer);
?>
