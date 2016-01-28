<?php  if(!isset($_SESSION)){session_start();}
include('site_variables.php');

include ('header_functs.php');
?>
<html>
<head>
<meta http-equiv="refresh" content="3">

</head>
<body id="bod">
<?php
	$data['long']=$mysession['long'];
	$data['lat']=$mysession['lat'];
	$data['nick']=$mysession['nick'];
	$data['range']=$mysession['range'];
	$data['color']=$mysession['color'];
	$data['norange']=$mysession['norange'];
	$dat=serialize($data);
	file_put_contents('./'.$seedroot.'/e/'.microtime(true).'.php', $dat);




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




$files=scandir('./'.$seedroot.'/d');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))>floatval($mysession['zero']))
	{
		$data=file_get_contents('./'.$seedroot.'/d/'.$fil);
		$dat=unserialize($data);
		if (
				(
						($mysession['range']==='Any distance'&&$dat['range']==='Any distance')
						||
						(($dat['norange']!==true&&$mysession['norange']!==true&&($mysession['range']!=='Any distance'&&$dat['range']!=='Any distance'))
						&&
				
				((floatval($mysession['range'])/111.12)> sqrt(pow(floatval($mysession['long'])-floatval($dat['long']),2)+pow(floatval($mysession['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($mysession['long']),2)+pow(floatval($dat['lat'])-floatval($mysession['lat']),2)))
						
	
						
						)
				)
				
				
				){
			echo '<hr/>';
			echo '&lt;<strong style="'.$dat['color'].'">'.htmlspecialchars($dat['nick']).'</strong> &gt; '.htmlspecialchars($dat['message']); 
			echo '<hr/>';
		}
	}
	
	
	
}
?>
<script content="text/javascript">
x = 0;  
y = document.getElementById('bod').scrollHeight; 
window.scroll(x,y);
</script>
</body>


</html>
