<?php if(!isset($_SESSION)){session_start();}
include('site_variables.php');

include ('header_functs.php');

if (isset($mysession['nick'])&&trim($mysession['nick'])===''){
	
	session_unset();
}
?>
<html>
<head>
<meta http-equiv="refresh" content="3">

</head>
<body id="bod">
<?php





$files=scandir('./'.$seedroot.'/e');
sort($files);
$keys=array();
foreach ($files as $fil)
{
		$data=file_get_contents('./'.$seedroot.'/e/'.$fil);
		$dat=unserialize($data);
		if(	$dat &&	$keys[$dat['color']][$dat['nick']]!==true &&
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
		
			echo '<strong style="'.$dat['color'].'">'.htmlspecialchars($dat['nick']).'</strong> (';
			$distance=floor(sqrt(pow(floatval($mysession['long'])-floatval($dat['long']),2)+pow(floatval($mysession['lat'])-floatval($dat['lat']),2))/111.12);
			
			if ($mysession['norange']!==true&&$dat['norange']!==true) {
			
			echo htmlspecialchars($distance);
			}
		else {echo 'n/a ';}
			
			
			echo 'kms)<br/>';
			
			$keys[$dat['color']][$dat['nick']]=true;
			
			}
	
	
	
}
?>
</body>


</html>
