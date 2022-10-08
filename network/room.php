<?php  if(!isset($_SESSION)){session_start();}
include('site_variables.php');
chdir ('..');
include ('./config.php');
chdir ('./network');
if (!$activatechat){
	exit(0);
}
include ('header_functs.php');
if (!isset($_GET['ajaxx'])){
?>
<html>
<head>
<!--<meta http-equiv="refresh" content="3">-->
<style>a link 
{
color:black;
text-decoration:none;
}</style>
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
        (same AGPL V3 or above) in each <script>
        
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
	file_put_contents('./'.$seedroot.'/e/'.microtime(true).'.php', $dat);

$files=scandir('./'.$seedroot.'/f');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))<microtime(true)-3000)
	{
		unlink('./'.$seedroot.'/f/'.$fil);
		}
}


$files=scandir('./'.$seedroot.'/e');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))<microtime(true)-6)
	{
		unlink('./'.$seedroot.'/e/'.$fil);
		}
}
$files=scandir('./'.$seedroot.'/d');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))<microtime(true)-3000)
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
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))>floatval($mysession['zero']))
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

			$ts=floatval(str_replace('.php','',$fil));
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
