<?php session_start();
include('site_variables.php');

include ('header_functs.php');
?>
<html>
<head>
<meta http-equiv="refresh" content="3">

</head>
<body id="bod">
<?php
	$data['long']=$_SESSION['long'];
	$data['lat']=$_SESSION['lat'];
	$data['nick']=$_SESSION['nick'];
	$data['range']=$_SESSION['range'];
	$data['color']=$_SESSION['color'];
	$data['norange']=$_SESSION['norange'];
	$dat=serialize($data);
	file_put_contents('./e/'.microtime(true).'.php', $dat);




$files=scandir('./e');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))<microtime(true)-6)
	{
		unlink('./e/'.$fil);
		}
	}
$files=scandir('./d');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))<microtime(true)-3000)
	{
		unlink('./d/'.$fil);
		}
	}




$files=scandir('./d');
sort($files);
foreach ($files as $fil)
{
	if (strstr($fil, '.php')&&floatval(str_replace('.php','',$fil))>floatval($_SESSION['zero']))
	{
		$data=file_get_contents('./d/'.$fil);
		$dat=unserialize($data);
		if (
				(
						($_SESSION['range']==='Any distance'&&$dat['range']==='Any distance')
						||
						($dat['norange']!==true&&$_SESSION['norange']!==true&&($_SESSION['range']!=='Any distance'||$dat['range']!=='Any distance')
						&&
				
				((floatval($_SESSION['range'])/111.12)> sqrt(pow(floatval($_SESSION['long'])-floatval($dat['long']),2)+pow(floatval($_SESSION['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($_SESSION['long']),2)+pow(floatval($dat['lat'])-floatval($_SESSION['lat']),2)))
						
	
						
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
