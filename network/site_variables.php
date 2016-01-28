<?php
if(!isset($_SESSION)){session_start();}
error_reporting(0);

if(!isset($seedroot)){
	$seedroot='';
	$site_name='Cremroad Fan Network';
	$site_slogan='Meet other fans in your area';
	$site_description='See what people listens to, chat about Crem Road music ! ';
	$site_footer='Copyright 2015-2016 '.$site_name;
	$ranges=array(1, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000, 3000, 5000, 8000, 12000, 15000, 'Any distance');
	$_SESSION['seedroot']='';

}
else if (!isset($_SESSION['seedroot'])||$_SESSION['seedroot']!==$seedroot){
	$site_name=$sitehandle;
	$_SESSION['seedroot']=$seedroot;
	
	$site_slogan='Meet and connect with the '.$seedroot.' community worldwide or in your area';
	$site_description='The online social presence for '.$seedroot;
	$site_footer='Copyright '.$site_name;
	$ranges=array(1, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000, 3000, 5000, 8000, 12000, 15000, 'Any distance');

}
else{
	$site_name=$sitehandle;
	$seedroot=$_SESSION['seedroot'];
	$site_slogan='Meet and connect with the '.$seedroot.' community worldwide or in your area';
	$site_description='The online social presence for '.$seedroot;
	$site_footer='Copyright '.$site_name;
	$ranges=array(1, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000, 3000, 5000, 8000, 12000, 15000, 'Any distance');

}
if ($_SESSION['seedroot']!==''){
	if (!isset($_SESSION[$_SESSION['seedroot']])){
		$_SESSION[$_SESSION['seedroot']]=Array();
	}
	
	$mysession=& $_SESSION[$_SESSION['seedroot']];
}
else
	{
		$mysession=& $_SESSION;
	}
?>
