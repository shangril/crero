<?php  session_start();
chdir ('..');
include ('./config.php');
chdir ('./network');
if (!$activatechat){
	exit(0);
}
include ('site_variables.php');
if (!isset($_GET['ajaxx'])){
?>
<!DOCTYPE html><html>
<head>
<!--<meta http-equiv="refresh" content="3">-->
<style>a link 
{
color:black;
text-decoration:none;
}</style>


<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
var syncLock=false;	
//var attempt=0;
	
function resync() {
			//attempt++;
			//document.getElementById('content').innerHTML+="<br/>Attempt: "+attempt;
			if (!syncLock){
			  syncLock=true;
			  
			  var xhttp = new XMLHttpRequest();
			  xhttp.onreadystatechange = function(){
				  if (xhttp.readyState==4) {
						syncLock=false;
						
						if (xhttp.status==200) {
							
							document.getElementById('content').innerHTML= xhttp.responseText;
							x = 0;  
							y = document.getElementById('bod').scrollHeight; 
							window.scroll(x,y);

							}
					}
				  
				  };
			  xhttp.open("GET", "?ajaxx=true", true);
			  xhttp.send();
			}
		}


window.setInterval(resync, 3000);



// @license-end
</script>

</head>
<body id="bod">
	<span id="content">Loading...</span>
<?php

}//not ajaxxx
if (isset($_GET['ajaxx'])){


	$data['long']=$mysession['long'];
	$data['lat']=$mysession['lat'];
	$data['nick']=$mysession['nick'];
	$data['range']=$mysession['range'];
	$data['color']=$mysession['color'];
	$data['norange']=$mysession['norange'];
	$dat=serialize($data);
	file_put_contents('./'.$seedroot.'/e/'.microtime(true).'.dat', $dat);

$files=scandir('./'.$seedroot.'/f');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))<microtime(true)-3000)
	{
		unlink('./'.$seedroot.'/f/'.$fil);
		}
}


$files=scandir('./'.$seedroot.'/e');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))<microtime(true)-6)
	{
		unlink('./'.$seedroot.'/e/'.$fil);
		}
}
$files=scandir('./'.$seedroot.'/d');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))<microtime(true)-3000)
	{
		unlink('./'.$seedroot.'/d/'.$fil);
		}
	}
$target='d';
$private=false;
if(isset($_GET['private_nick'])&&isset($_GET['private_sid'])){
	$target='f';
	$private=true;
	$mysession['seen_private']['private_nick']['private_sid']=microtime(true);
}


$files=scandir('./'.$seedroot.'/'.$target);
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.dat')&&floatval(str_replace('.dat','',$fil))>floatval($mysession['zero']))
	{
		$data=file_get_contents('./'.$seedroot.'/'.$target.'/'.$fil);
		$dat=unserialize($data);
		$goonbaby=true;
		
		if ($private){
				if (!
					(
						(
							($dat['to']['nick']===$mysession['nick']&&
							$dat['to']['color']===$mysession['color']&&
							$dat['from']['nick']===$_GET['private_nick']&&
							$dat['from']['color']===$_GET['private_sid'])
							||
							($dat['from']['nick']===$_GET['private_nick']&&
							$dat['from']['color']===$_GET['private_sid']&&
							$dat['to']['nick']===$mysession['nick']&&
							$dat['to']['color']===$mysession['color'])
						)
						||
						(
							($dat['from']['nick']===$mysession['nick']&&
							$dat['form']['color']===$mysession['color']&&
							$dat['to']['nick']===$_GET['private_nick']&&
							$dat['to']['color']===$_GET['private_sid'])
							||
							($dat['to']['nick']===$_GET['private_nick']&&
							$dat['to']['color']===$_GET['private_sid']&&
							$dat['from']['nick']===$mysession['nick']&&
							$dat['from']['color']===$mysession['color'])
						
						)
					)
				){
				$goonbaby=false;
				
			}
			
		}
		
		
		if ($goonbaby&&
				(
						($mysession['range']==='Any distance'&&$dat['range']==='Any distance')
						||
						(($dat['norange']!==true&&$mysession['norange']!==true&&($mysession['range']!=='Any distance'&&$dat['range']!=='Any distance'))
						&&
				
				((floatval($mysession['range'])/111.12)> sqrt(pow(floatval($mysession['long'])-floatval($dat['long']),2)+pow(floatval($mysession['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($mysession['long']),2)+pow(floatval($dat['lat'])-floatval($mysession['lat']),2)))
						
	
						
						)
				)
				
				
				){

			$ts=floatval(str_replace('.dat','',$fil));
			$ttl=3000-(microtime(true)-$ts);
			
			echo htmlspecialchars(round($ttl));

					
					
			echo ' &lt;<strong style="'.$dat['color'].'"><a style="color:black;text-decoration:none;" target="_parent" href="./?private_nick='.urlencode($dat['nick']).'&private_sid='.urlencode($dat['color']).'">'.htmlspecialchars($dat['nick']).'</a></strong> &gt; '.htmlspecialchars($dat['message']); 
			echo '<hr/>';
		}
	}
	
	
	
}
?>
<script content="text/javascript">
	
// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later

x = 0;  
y = document.getElementById('bod').scrollHeight; 
window.scroll(x,y);

// @license-end
</script>
<?php
} // ajaxx 

if (!isset($_GET['ajaxx'])){

?>
</body>


</html>
<?php } //not ajaxx ?>
