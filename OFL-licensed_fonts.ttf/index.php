<?php
session_start();

if (!in_array('seen', array_keys($_SESSION))){
	die();
}
$_SESSION['font']=true;

$l=array_diff(scandir('./'), array('..', '.'));
$ok=false;
foreach ($l as $f){
	
	if (strpos($f, '.ttf')==strlen($f)-4){
		$ok=true;
	}
}

if ($ok){
	do{
		shuffle($l);
	}
	while (strpos($l[0], '.ttf')!=strlen($l[0])-4);

	header('Content-Type: font/ttf');

	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');


	readfile('./'.$l[0]);
	exit;

}

?>
