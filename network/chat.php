<?php 
chdir ('..');
require_once ('./config.php');
chdir ('./network');
if (!$activatechat){
	exit(0);
}
if (isset($_GET['logout'])){
	//session_unset();
	unset($mysession['nick']);
	$mysession['logout']=true;
	echo '<!DOCTYPE html><html><head><script>setTimeout(function(window.location.href=\'./?fromajax=1\'){}, 0);</script></head><body>Redirection<a href="./?fromajax=1">...</a></body></html>';
	die();

	
}
if (isset($_POST['message'])){
	$target='d';
	
	$data['long']=$mysession['long'];
	$data['lat']=$mysession['lat'];
	$data['nick']=$mysession['nick'];
	$data['range']=$mysession['range'];
	$data['message']=$_POST['message'];
	$data['color']=$mysession['color'];
	$data['norange']=$mysession['norange'];
	
	if(isset($_GET['private_nick'])&&isset($_GET['private_sid'])){
			$target='f';
			$data['to']['nick']=$_GET['private_nick'];
			$data['to']['color']=$_GET['private_sid'];
			$data['from']['nick']=$mysession['nick'];
			$data['from']['color']=$mysession['color'];
			
	}
	
	
	$dat=serialize($data);
	file_put_contents('./'.$seedroot.'/'.$target.'/'.microtime(true).'.dat', $dat);
	
	
}



if (!isset($mysession['range'])){

	$mysession['range']='Any distance';

	
}
if (isset($_GET['range'])&&($_GET['range']==='Any distance'||is_numeric($_GET['range']))){

	$mysession['range']=$_GET['range'];
	$mysession['zero']=microtime(true);
	
}




?>
<span style="margin-top:0px;margin-bottom:0px;"><em><?php 
if (strstr($sitename, '.')&&!strstr($sitename, ' ')){
	echo '<a target="top" href="http://'.htmlspecialchars($server).'">';
}
echo '<h2 style="display:inline;margin-top:0px;margin-bottom:0px;">'.htmlspecialchars($sitename).'</h2>';

if (strstr($sitename, '.')&&!strstr($sitename, ' ')){
	echo '</a>';
	
}
?></em> &gt; </span><?php
if (isset($_GET['private_nick'])&&isset($_GET['private_sid'])) {
echo '<a href="./">General chat</a> &gt; Conversation with <span style="'.htmlspecialchars($_GET['private_sid']).'">'.htmlspecialchars($_GET['private_nick']).'</span><br/>';	

$mysession['seen_private'][$_GET['private_nick']][$_GET['private_sid']]=microtime(true);

}


if ($mysession['norange']!==true&&!isset($_GET['private_nick'])&&!isset($_GET['private_sid'])){
			?>
			<form method="GET" action="./" id="formrange" style="display:inline;"><!--You are seeing and being seen by people within <select onchange="document.getElementById('formrange').submit();" name="range">
		<?php
		foreach ($ranges as $range) {
			echo '<option value="'.$range.'" ';
			if ($range==$mysession['range']){
				echo 'selected';

				}
			echo ' >'.$range.'</option>';
		}


		?>


		</select> kms</form>-->

	
	<?php
}
?>
<br/>
<iframe style="display:inline;float:left;width:60%;height:358px;border:0px;" src="./room.php<?php 

	if (isset($_GET['private_nick'])&&isset($_GET['private_sid'])){
		echo '?private_nick='.urlencode($_GET['private_nick']).'&private_sid='.urlencode($_GET['private_sid']);
		
	}

?>"></iframe>
<iframe style="display:inline;float:left;width:40%;height:358px;border:0px;" src="./who.php<?php 

	if (isset($_GET['private_nick'])&&isset($_GET['private_sid'])){
		echo '?private_nick='.urlencode($_GET['private_nick']).'&private_sid='.urlencode($_GET['private_sid']);
		
	}

?>"></iframe>
<span style="clear:both;"></span>
<form style="display:block;margin-top:0px;margin-bottom:0px;padding:0px;" action="./<?php 

	if (isset($_GET['private_nick'])&&isset($_GET['private_sid'])){
		echo '?private_nick='.urlencode($_GET['private_nick']).'&private_sid='.urlencode($_GET['private_sid']);
		
	}

?>" method="post">Enter your chat message here : <input type="text" name="message" size="38"></input><input type="submit" value="Send"></input></form>
<?php
if ($mysession['norange']===true&&!isset($_GET['private_nick'])&&!isset($_GET['private_sid'])) {
	//echo ' Add your real world location to access geolocated chatrooms : <form style="display:inline;" method="GET" action=""><input type="hidden" name="norange" value="true"/><input type="submit" value="Add my location"/></form>';
}
echo ' <form style="display:inline;margin-bottom:0px;padding-bottom:0px;margin-top:0px;padding-top:0px;" action="" method="POST">Your nickname : <input type="text" name="nick" value="'.htmlspecialchars($mysession['nick']).'"/><input value="Change" type="submit"/></form>';
echo '<a style="float:right;margin-bottom:0px;padding-bottom:0px;margin-top:0px;padding-top:0px;" href="./?logout=true">Logout</a>';
?>
<div id="meepcontent"></div>
		<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 
 
var syncLock=false;	
var attempt=0;
var xshttp;	
function resync() {
			attempt++;
			if (attempt>10){
				
			  xshttp.abort;
			  syncLock=false;
			  attempt=0;	
			}
			//document.getElementById("content").innerHTML+="<br/>Attempt: "+attempt;
			if (!syncLock){
			  syncLock=true;
			  xshttp = new XMLHttpRequest();
			  xshttp.onreadystatechange = function(){
				  if (xshttp.readyState==4) {
						syncLock=false;
						
						if (xshttp.status==200) {
							
							document.getElementById("meepcontent").innerHTML= xshttp.responseText;
							}
					}
				  
				  };
			  xshttp.open("GET", "./index.php?meep=meeponly", true);
			  xshttp.send();
			}
		}


window.setInterval(resync, 3000);



// @license-end
</script>

</body></html>
