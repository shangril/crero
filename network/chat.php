<?php 

if (isset($_GET['logout'])){
	session_unset();
	$mysession['logout']=true;
	echo '<html><head><meta http-equiv="refresh" content="0"></head></html>';
	die();

	
}
if (isset($_POST['message'])){
	$data['long']=$mysession['long'];
	$data['lat']=$mysession['lat'];
	$data['nick']=$mysession['nick'];
	$data['range']=$mysession['range'];
	$data['message']=$_POST['message'];
	$data['color']=$mysession['color'];
	$data['norange']=$mysession['norange'];
	$dat=serialize($data);
	file_put_contents('./'.$seedroot.'/d/'.microtime(true).'.php', $dat);
	
	
}



if (!isset($mysession['range'])){

	$mysession['range']='Any distance';

	
}
if (isset($_GET['range'])&&($_GET['range']==='Any distance'||is_numeric($_GET['range']))){

	$mysession['range']=$_GET['range'];
	$mysession['zero']=microtime(true);
	
}




?>
<em><?php 
if (strstr($site_name, '.')&&!strstr($site_name, ' ')){
	echo '<a target="top" href="http://'.htmlspecialchars($site_name).'">';
}
echo '<h2 style="display:inline;">'.htmlspecialchars($site_name).'</h2>';

if (strstr($site_name, '.')&&!strstr($site_name, ' ')){
	echo '</a>';
	
}
?></em>
<?php
if ($mysession['norange']!==true){
			?>
			<form method="GET" action="./" id="formrange" style="display:inline;">You are seeing and being seen by people within <select onchange="document.getElementById('formrange').submit();" name="range">
		<?php
		foreach ($ranges as $range) {
			echo '<option value="'.$range.'" ';
			if ($range==$mysession['range']){
				echo 'selected';

				}
			echo ' >'.$range.'</option>';
		}


		?>


		</select> kms</form>

	
	<?php
}
?>
<br/>
<iframe style="display:inline;float:left;width:60%;height:380px;border:0px;" src="./room.php"></iframe>
<iframe style="display:inline;float:left;width:40%;height:380px;border:0px;" src="./who.php"></iframe>
<span style="clear:both;"></span>
<form style="display:inline;" action="" method="post">Enter your chat message here : <input type="text" name="message" size="38"></input><input type="submit" value="Send"></input></form>
<?php
if ($mysession['norange']===true) {
	echo ' Add your real world location to access geolocated chatrooms : <form style="display:inline;" method="GET" action=""><input type="hidden" name="norange" value="true"/><input type="submit" value="Add my location"/></form>';
}
echo ' <form action="" method="POST">Your nickname : <input type="text" name="nick" value="'.htmlspecialchars($mysession['nick']).'"/><input value="Change !" type="submit"/></form>';
echo '<a style="clear:both;float:right;" href="./?logout=true">Logout</a><br/><span style="float:right;">';
echo generate_footer($site_footer);
echo '</span><div style="float:left;">';
echo 'Powered by the <a href="http://social.clewn.org">Clewn Social widget</a> for websites';
echo '</div>';
?>
