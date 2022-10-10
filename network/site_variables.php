<?php
if(!isset($_SESSION)){session_start();}
error_reporting(0);

if(!isset($seedroot)){
	$seedroot='';
	$site_name=htmlspecialchars($sitename).' Fan Network';
	$site_slogan='Meet other fans in your area';
	$site_description='See what people listens to, chat about '.htmlspecialchars($sitename).' music! ';
	$site_footer='Copyright 2015-2022 '.$site_name;
	$ranges=array(1, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000, 3000, 5000, 8000, 12000, 15000, 'Any distance');
	$_SESSION['seedroot']='';

}
else{
	$site_name=htmlspecialchars($sitename).' Fan Network';
	$seedroot=$_SESSION['seedroot'];
	$site_slogan='Meet other fans in your area';
	$site_description='See what people listens to, chat about '.htmlspecialchars($sitename).' music! ';
	$site_footer='Copyright '.$site_name;
	$ranges=array(1, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000, 3000, 5000, 8000, 12000, 15000, 'Any distance');

}
		$mysession=&$_SESSION;
?>
