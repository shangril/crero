<?php 

if (isset($_GET['logout'])){
	session_destroy();
	echo '<html><head><meta http-equiv="refresh" content="0"></head></html>';
	die();

	
}
if (isset($_POST['message'])){
	$data['long']=$_SESSION['long'];
	$data['lat']=$_SESSION['lat'];
	$data['nick']=$_SESSION['nick'];
	$data['range']=$_SESSION['range'];
	$data['message']=$_POST['message'];
	$data['color']=$_SESSION['color'];
	$dat=serialize($data);
	file_put_contents('./d/'.microtime(true).'.php', $dat);
	
	
}



if (!isset($_SESSION['range'])){

	$_SESSION['range']='15000';

	
}
if (isset($_GET['range'])&&is_numeric($_GET['range'])){

	$_SESSION['range']=$_GET['range'];

	
}




?>
<div style="height:10%;"><em>Crem Road fan network</em></div>
<iframe style="display:inline;float:left;width:80%;height:380px;border:0px;" src="./room.php"></iframe>
<iframe style="display:inline;float:left;width:20%;height:380px;border:0px;" src="./who.php"></iframe>
<span style="clear:both;"></span>
<form style="display:inline;" action="" method="post"><input type="text" name="message" size="38"></input><input type="submit" value="Send"></input></form>
<?php
echo '<a style="clear:both;float:right;" href="./?logout=true">Logout</a><br/><span style="float:right;">';
echo generate_footer($site_footer);
echo '</span><div style="float:left;">';
//include ('../bank/index.php');
echo '</div>';
?>
