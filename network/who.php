<?php if(!isset($_SESSION)){session_start();}
include('site_variables.php');
chdir ('..');
include ('./config.php');
chdir ('./network');
if (!$activatechat){
	exit(0);
}
include ('header_functs.php');

if (isset($mysession['nick'])&&trim($mysession['nick'])===''){
	
	session_unset();
}

if (!isset($_GET['ajaxx'])){
?>
<html>
<head>
<!--<meta http-equiv="refresh" content="3">-->
<style>
	a link {
		color:black;
		text-decoration:none;
		}
</style>


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

	if (isset($_GET['private_nick'])&&isset($_GET['private_sid'])) {
		echo 'Awaiting messages : ';
		$showonlymessages=true;
		$displaynone=true;
	}


	$nicklist=Array();


//20220710 UPDATE
	$files=array_diff(scandir('./'.$seedroot.'/e'), Array('.', '..', '.htaccess'));
	sort($files);
	$keys=array();
	foreach ($files as $fil)
	{
			$data=file_get_contents('./'.$seedroot.'/e/'.$fil);
			$dat=unserialize($data);
			$goonbaby=true;
			if (true){
				$messagesfiles=scandir('./'.$seedroot.'/f');
				sort($messagesfiles);
				$msgcount=0;
				foreach ($messagesfiles as $privatefile){
						$datap=file_get_contents('./'.$seedroot.'/f/'.$privatefile);
						$datp=unserialize($datap);
						if (
								(
									(
										($datp['to']['nick']===$mysession['nick']&&
										$datp['to']['color']===$mysession['color']&&
										$datp['from']['nick']===$dat['nick']&&
										$datp['from']['color']===$dat['color'])								
									)
								)
						
						
							
						
							){
								$lastseentimestamp=0;
								if (isset($mysession['seen_private'][$datp['from']['nick']][$datp['from']['color']])){
									$lastseentimestamp=$mysession['seen_private'][$datp['from']['nick']][$datp['from']['color']];
					
								}

									if(floatval(str_replace('.php', '', $privatefile))>$lastseentimestamp){

										$msgcount++;
									
								
								}
						
					}
					
				}
				if ($msgcount<=0&&$showonlymessages){
					$goonbaby=false;
				}
			}
			
			
			
			
			
			if($goonbaby&&$dat &&	$keys[$dat['color']][$dat['nick']]!==true &&
					(
							($mysession['range']==='Any distance'&&$dat['range']==='Any distance')
							||
							($dat['norange']!==true&&$mysession['norange']!==true&&($mysession['range']!=='Any distance'&&$dat['range']!=='Any distance')
							&&
									
					((floatval($mysession['range'])/111.12)> sqrt(pow(floatval($mysession['long'])-floatval($dat['long']),2)+pow(floatval($mysession['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($mysession['long']),2)+pow(floatval($dat['lat'])-floatval($mysession['lat']),2)))
							
							
							)
					)
					
					
					)


			{
				$output='';
				
				if ($msgcount>0){
					$output.='<span style="background-color:red;color:white;border-radius 4px;">'.htmlspecialchars($msgcount).'</span>';
				}
				$output.='<strong style="'.$dat['color'].'">
				
				<a style="color:black;text-decoration:none;" target="_parent" >'.htmlspecialchars($dat['nick']).'
				
				</a></strong> (
				
				';
				//href="./?private_nick='.urlencode($dat['nick']).'&private_sid='.urlencode($dat['color']).'"
				$distance=floor(sqrt(pow(floatval($mysession['long'])-floatval($dat['long']),2)+pow(floatval($mysession['lat'])-floatval($dat['lat']),2))/111.12);
				
				if ($mysession['norange']!==true&&$dat['norange']!==true) {
				
				$output.=htmlspecialchars($distance);
				}
			else {$output.= 'n/a ';}
				
				
				$output.= 'kms)<br/>';
				
				$keys[$dat['color']][$dat['nick']]=true;
				$nicklist[$msgcount][$dat['nick']]=$output;
				}
		
		
		
	}
	krsort($nicklist);

	foreach ($nicklist as $nickcount){
		$nicks=array_keys($nickcount);
		
		foreach ($nicks as $nick){
			echo $nickcount[$nick];
		}
	}

} // ajaxx 

if (!isset($_GET['ajaxx'])){

?>
</body>


</html>
<?php } //not ajaxx ?>
