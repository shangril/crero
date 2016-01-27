<?php session_start();
include('site_variables.php');

include ('header_functs.php');

if (isset($_SESSION['nick'])&&trim($_SESSION['nick'])===''){
	
	session_unset();
}
?>
<html>
<head>
<meta http-equiv="refresh" content="3">

</head>
<body id="bod">
<?php





$files=scandir('./e');
sort($files);
$keys=array();
foreach ($files as $fil)
{
		$data=file_get_contents('./e/'.$fil);
		$dat=unserialize($data);
		if(	$dat &&	$keys[$dat['color']][$dat['nick']]!==true &&
				(
						($_SESSION['range']==='Any distance')
						||
						($dat['norange']!==true&&$_SESSION['norange']!==true&&$_SESSION['range']!=='Any distance'
						&&
				
				((floatval($_SESSION['range'])/111.12)> sqrt(pow(floatval($_SESSION['long'])-floatval($dat['long']),2)+pow(floatval($_SESSION['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($_SESSION['long']),2)+pow(floatval($dat['lat'])-floatval($_SESSION['lat']),2)))
						
						
						)
				)
				
				
				)


		{
		
			echo '<strong style="'.$dat['color'].'">'.htmlspecialchars($dat['nick']).'</strong> (';
			$distance=floor(sqrt(pow(floatval($_SESSION['long'])-floatval($dat['long']),2)+pow(floatval($_SESSION['lat'])-floatval($dat['lat']),2))/111.12);
			
			if ($_SESSION['norange']!==true&&$dat['norange']!==true) {
			
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
