<?php 

if (isset($_GET['logout'])){
	session_unset();
	$_SESSION['logout']=true;
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
	$data['norange']=$_SESSION['norange'];
	$dat=serialize($data);
	file_put_contents('./d/'.microtime(true).'.php', $dat);
	
	
}



if (!isset($_SESSION['range'])){

	$_SESSION['range']='Any distance';

	
}
if (isset($_GET['range'])&&($_GET['range']==='Any distance'||is_numeric($_GET['range']))){

	$_SESSION['range']=$_GET['range'];
	
}




?>
<div style="height:10%;"><em>Crem Road fan network</em></div>
<?php
if ($_SESSION['norange']!==true){
			?>
			<form method="GET" action="./" id="formrange">You are seeing and being seen by people within<select onchange="document.getElementById('formrange').submit();" name="range">
		<?php
		foreach ($ranges as $range) {
			echo '<option value="'.$range.'" ';
			if ($range==$_SESSION['range']){
				echo 'selected';

				}
			echo ' >'.$range.'</option>';
		}


		?>


		</select>kms</form>

	
	<?php
}
?>
<iframe style="display:inline;float:left;width:60%;height:380px;border:0px;" src="./room.php"></iframe>
<iframe style="display:inline;float:left;width:40%;height:380px;border:0px;" src="./who.php"></iframe>
<span style="clear:both;"></span>
<form style="display:inline;" action="" method="post">Enter your chat message here : <input type="text" name="message" size="38"></input><input type="submit" value="Send"></input></form>
<?php
if ($_SESSION['norange']===true) {
	echo '<br/>Add your real world location to access geolocated chatrooms : <form style="display:inline;" method="GET" action=""><input type="hidden" name="norange" value="true"/><input type="submit" value="Add my location"/></form>';
}
echo '<br/><form action="" method="POST">Your nickname : <input type="text" name="nick" value="'.htmlspecialchars($_SESSION['nick']).'"/><input value="Change !" type="submit"/></form>';
echo '<a style="clear:both;float:right;" href="./?logout=true">Logout</a><br/><span style="float:right;">';
echo generate_footer($site_footer);
echo '</span><div style="float:left;">';
//include ('../bank/index.php');
echo '</div>';
?>
