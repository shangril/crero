<?php  if(!isset($_SESSION)){session_start();}
include('site_variables.php');

include ('header_functs.php');
?>
<html>
<head>
<meta http-equiv="refresh" content="3">
<style>a link 
{
color:black;
text-decoration:none;
}</style>

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
	$mysession['seenprivate']['private_nick']['private_sid']=microtime(true);
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
			echo '<hr/>';
			echo '&lt;<strong style="'.$dat['color'].'"><a style="color:black;text-decoration:none;" target="_parent" href="./?private_nick='.urlencode($dat['nick']).'&private_sid='.urlencode($dat['color']).'">'.htmlspecialchars($dat['nick']).'</a></strong> &gt; '.htmlspecialchars($dat['message']); 
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
