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





$files=scandir('./e');
sort($files);
$keys=array();
foreach ($files as $fil)
{
		$data=file_get_contents('./e/'.$fil);
		$dat=unserialize($data);

		if (((floatval($_SESSION['range'])/111.12)> sqrt(pow(floatval($_SESSION['long'])-floatval($dat['long']),2)+pow(floatval($_SESSION['lat'])-floatval($dat['lat']),2))) && ((floatval($dat['range'])/111.12)> sqrt(pow(floatval($dat['long'])-floatval($_SESSION['long']),2)+pow(floatval($dat['lat'])-floatval($_SESSION['lat']),2)))&&!isset($keys[$dat['color']][$dat['nick']])){
		
			echo '<strong style="'.$dat['color'].'">'.htmlspecialchars($dat['nick']).'</strong> ('.htmlspecialchars(floor(sqrt(pow(floatval($_SESSION['long'])-floatval($dat['long']),2)+pow(floatval($_SESSION['lat'])-floatval($dat['lat']),2))/111.12)).'kms)<br/>';
			
			$keys[$dat['color']][$dat['nick']]=true;
			
			}
	
	
	
}
?>
</body>


</html>
