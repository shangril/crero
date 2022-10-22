<?php
error_reporting(0);
session_start();
chdir ('..');
require_once ('./config.php');
chdir ('./network');
if (!$activatechat){
	exit(0);
}


include('site_variables.php');

if (!array_key_exists('meep_ppl', $_SESSION))
	$_SESSION['meep_ppl']=false;
if (!array_key_exists('meep_mus', $_SESSION))
	$_SESSION['meep_mus']=false;
		
if ($_GET['reg_meep']=='true'){
				if ($_GET['meep']==1){
					$_SESSION['meep_ppl']=true;
					$meepenabled=true;
				}
				else {
					$_SESSION['meep_ppl']=false;
					$meepenabled=false;
				}
				if ($_GET['mus_meep']==1){
					$_SESSION['meep_mus']=true;
					$mus_meepenabled=true;
				}
				else {
					$_SESSION['meep_mus']=false;
					$mus_meepenabled=false;
				}
				
		//echo 'please wait...';
		$_GET=array();
		}
	





//once for all
//big cleanup
$seedroot='';
$targ_dirs=array ('./'.$seedroot.'/d/','./'.$seedroot.'/e/','./'.$seedroot.'/f/');
foreach ($targ_dirs as $tar_dir){
		$rnfs=array_diff(scandir($tar_dir), array( '.htaccess', '..', '.'));
		foreach ($rnfs as $rnf){
			rename ($tar_dir.$rnf, str_replace('.php', '.dat', $tar_dir.$rnf));
		}
	
	
}
//usual cleanup
$files=scandir('./e');
		sort($files);
		foreach ($files as $fil)
		{	
			if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))<(microtime(true)-6))
			{
				unlink('./e/'.$fil);
				}
		}
		
		
		



if (isset($_GET['ajaxx'])||isset($_GET['meep'])){
	
		
		//count()
		$files=array_diff(scandir ('../network/e/'), Array ('..', '.', '.htaccess'));
		sort($files);
		
		$nicklist=Array();
		$musicianlist=Array();
		$onlinepeople=0;
		foreach ($files as $fil)
		{	
			
			$this_record=unserialize(file_get_contents('./e/'.$fil));
			
			if (isset($this_record['nick'])){
				$nicklist[$this_record['nick'].$this_record['color']]=1;
				if(array_key_exists('musician', $this_record)){
					$musicianlist[$this_record['nick'].$this_record['color']]=1;
				}
			}
			
			
		}
		$onlinepeople=intval(count($nicklist));
		$onlinemusicians=intval(count($musicianlist));
		
		if (!(isset($activate_musician_accounts)&&$activate_musician_accounts)){
			$onlinemusicians=intval('-1');
		}
		$meepenabled=boolval($_SESSION['meep_ppl']);
		$mus_meepenabled=boolval($_SESSION['meep_mus']);
		
		
		$havetomeep=false;
		$mus_havetomeep=false;
		
		if ($onlinepeople!==intval(file_get_contents('./onlinepeople.dat'))){
			file_put_contents('./onlinepeople.dat', $onlinepeople);
		
			if (file_get_contents('./oldonlinepeople.dat')!==file_get_contents('./onlinepeople.dat')){
				if (intval(file_get_contents('./oldonlinepeople.dat'))< $onlinepeople){
					$havetomeep=true;
					
				}
				file_put_contents('./oldonlinepeople.dat', $onlinepeople);
			
			}
		}
		
		if ($onlinemusicians!==intval(file_get_contents('./mus_onlinepeople.dat'))){
			file_put_contents('./mus_onlinepeople.dat', $onlinemusicians);
		
			if (file_get_contents('./mus_oldonlinepeople.dat')!==file_get_contents('./mus_onlinepeople.dat')){
				if (intval(file_get_contents('./mus_oldonlinepeople.dat'))< $onlinemusicians){
					$mus_havetomeep=true;
				
				}
				file_put_contents('./mus_oldonlinepeople.dat', $onlinemusician);
			
			}
		}
		$mus_txt=$onlinemusicians;
		
		if ($onlinemusicians==intval('-1')){
			$mus_txt='an unknown number of';
		}
		if ($havetomeep==true&&$mus_meepenabled==true){
			echo '<audio onload="this.play();" autoplay><source src="./usermeep.mp3" type="audio/mpeg"></audio>';
		}
		if ($mus_havetomeep==true&&$meepenabled==true){
			echo '<audio onload="this.play();" autoplay><source src="./musmeep.mp3" type="audio/mpeg"></audio>';
		}


		if (isset($_GET['ajaxx'])){
			echo '
		
			<h4 style="display:inline;">'.$sitename.' Fan Network &gt; </h4> ('.$onlinepeople.' people online including '.$mus_txt.' label musicians)<form method="post" style="display:inline;" action="./?login=login"><input type="hidden" name="reallogin" value="reallogin"/><input type="submit" value="Connect!"/></form>
			';
		} 
		
			
		
	//Ajax not meeponly
		if (isset($_GET['meep'])){	
		//finally
		echo '<br/><form method="GET" action="./index.php"><input type="hidden" name="ajaxx" value="true"/><input type="hidden" name="meep" value="true"/><input type="hidden" name="reg_meep" value="true"/>Play a sound whenever <input onclick="this.form.submit();" type="checkbox" ';
		if ($meepenabled){echo ' checked ';}
		echo ' name="meep" value="1" /> someone enters | <input onclick="this.form.submit();" type="checkbox" ';
		if ($mus_meepenabled){echo ' checked ';}
		echo ' name="mus_meep" value="1"/> a label musician enters</form>';
		
	}
		
	exit(0);	
	}// isset meep


if (isset($_GET['login'])){
	$_SESSION['logged']=true;
	
}

if (!$_SESSION['logged']&&!isset($GET['ajaxx'])){
	$seedroot='';
	$files=scandir('./'.$seedroot.'/e');
	sort($files);
	foreach ($files as $fil)
	{	
		if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))<(microtime(true)-6))
		{
			unlink('./'.$seedroot.'/e/'.$fil);
			}
	}
	
	
	
	echo '
	
	
	
	<!DOCTYPE html>
	<html>
		<head>
			<!--<meta http-equiv="refresh" content="20">-->
			
		<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
        /*    
        @licstart  The following is the entire license notice for the 
        JavaScript code in this page. While it is already specified
        * for external script.js ressourece
        * for each <script> tag in this file
        this is simply an indication for event handlers. 
        
        The JavaScript code in this page is free software: you can
        redistribute it and/or modify it under the terms of the GNU
        Affero General Public License (GNU AGPL) as published by the Free Software
        Foundation, either version 3 of the License, or (at your option)
        any later version.  The code is distributed WITHOUT ANY WARRANTY;
        without even the implied warranty of MERCHANTABILITY or FITNESS
        FOR A PARTICULAR PURPOSE.  See the GNU AGPL for more details.

        @licend  The above is the entire license notice
        for the JavaScript code in this page. It is already mentionned
        (same AGPL V3 or abose) in each <script>
        
        and then, this is clear for Event Handlers as well. 
        */
        
// @license-end
</script>

		<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
var syncLock=false;	
//var attempt=0;
	
function resync() {
			//attempt++;
			//document.getElementById("content").innerHTML+="<br/>Attempt: "+attempt;
			if (!syncLock){
			  syncLock=true;
			  
			  var xhttp = new XMLHttpRequest();
			  xhttp.onreadystatechange = function(){
				  if (xhttp.readyState==4) {
						syncLock=false;
						
						if (xhttp.status==200) {
							
							document.getElementById("content").innerHTML= xhttp.responseText;
							}
					}
				  
				  };
			  xhttp.open("GET", "?ajaxx=true&meep=true", true);
			  xhttp.send();
			}
		}


window.setInterval(resync, 3000);



// @license-end
</script>

		
		
		
		
		
		
		
		</head>
		
		<body>
		<a href="#" style="display:';
		if (in_array('fromajax', array_keys($_GET)))
			echo 'none;';
		else
			echo 'block;';
		
		echo '" onclick="this.style.display=\'none\'";>Audio of this tab may be muted. Click to unmute</a>
		<span id="content">Loading fan network...</span>
		
		
		
		</body>
		
		</html>';
	
	
	
		die();
	
	
}

else if (!isset($_GET['ajax'])) {
include('site_variables.php');

include ('header_functs.php');

if (isset($_POST['reallogin']))
		$_SESSION['reallogin']=true;

if (isset($_GET['norange'])){
	unset($mysession['lat']);
	unset($mysession['long']);

	$mysession['norange']=false;
	
}
if (isset($_GET['login'])){
	unset($mysession['logout']);
}
if (isset($_GET['logout'])){
	//session_unset();
	unset($mysession['nick']);
	unset($mysession['color']);
	unset($mysession['long']);
	unset($mysession['lat']);
	unset($mysession['range']);
	unset($mysession['norange']);
	unset($mysession['zero']);
	unset($mysession['seen_private']);
	unset($mysession['logout']);
	unset($_SESSION[$seedroot]);
	unset($_SESSION['logged']);
	
	echo '<!DOCTYPE html><html><head></head><body><script>/* @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL v3.0 */ setTimeout(function(){window.location.href=\'./?fromajax=1\';}, 1); /* @license-end */</script>Redirection<a href="./?fromajax=1">...</a></body></html>';
	die();

	
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
	file_put_contents('./'.$seedroot.'/'.$target.'/'.microtime(true).'.dat', $dat);
	
	



}

$_SESSION['seedroot']=$seedroot;

if (isset($_POST['nick'])&&trim($_POST['nick'])!==''){
		srand();
		$data['long']=$mysession['long'];
		$data['lat']=$mysession['lat'];
		$data['nick']=$mysession['nick'];
		$data['range']=$mysession['range'];
		$data['message']='changed nick to <'.$_POST['nick'].'> *';
		$_SESSION['saved_nick']=$_POST['nick'];
		$data['color']=$mysession['color'];
		$data['norange']=$mysession['norange'];
		$dat=serialize($data);
		file_put_contents('./'.$seedroot.'/d/'.microtime(true).'.dat', $dat);
		
		
		
		$mysession['nick']=$_POST['nick'];
		//$mysession['zero']=microtime(true);
		$mysession['color']='background-color:rgb('.rand(200,240).','.rand(200,240).','.rand(200,240).');';
		
}
echo generate_header($site_name.' - '.$site_slogan,$site_description);

if (isset($mysession['lat'])&&isset($mysession['long'])&&isset($mysession['nick']))
{
	if (!(in_array('fromajax', array_keys($_GET)))&&isset($_SESSION['reallogin'])&&!isset($_POST['reallogin'])){
		echo '<a href="#" style="display:block;" onclick="this.style.display=\'none\'";>Audio of this tab may be muted. Click to unmute</a>';
		
		
	}
	
	include ('chat.php');
	 }
	 
}///*big else
/* 
$getloc=false;
	
	?>
	


<?php if (!isset($_GET['lat'])&&!isset($mysession['logout'])&&isset($mysession['nick'])){
	$getloc=true;
	
	?>
<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
navigator.geolocation.getCurrentPosition(GetLocation);
function GetLocation(location) {
    window.location.assign('./?lat='+encodeURI(location.coords.latitude)+'&long='+encodeURI(location.coords.longitude));

}

// @license-end
</script>
<?php } ?>
<h1 class="main_title"><em><strong><?php echo htmlspecialchars($site_name);?></strong></em></h1>
<h2 class="main_subtitle"><em><strong><?php echo htmlspecialchars($site_slogan);?></strong></em></h2>
<div>
<?php 
if ($getloc){
	echo '<div>Please allow this website to access your location to continue !<hr/>Or enter GPS-style decimal coordinates if the browser location service is unavailable: <br/>
	<form action="./" method="GET">
	Lat: <input type="text" name="lat"/>
	Long: <input type="text" name="long"/>
	<input type="submit"/>
	
	</form>
	</div>or <a href="./?login=logout">cancel and logout</a>';
}

if (!isset($_GET['lat'])&&!$getloc){?>
<span style="float:right;">Now logued out ! Goodbye ! <a href="./?login=true">Login again</a></span>
<?php } 

else if (!$getloc) {
	$mysession['lat']=floatval($_GET['lat']);
	$mysession['long']=floatval($_GET['long']);
	$mysession['norange']=false;
	echo '<form style="display:inline;" method="POST" action="./">Enter a nickname <input type="text" name="nick" value="'.htmlspecialchars($mysession['nick']).'" /><input type="submit"/></form>';
	
	
}





?>
</div>

<?php
echo generate_footer($site_footer);
?>
*/
