<?php
session_start();

if (!isset($_SESSION['cart'])) {
    
		$_SESSION['cart']=array();
		
    
    }
if (!isset($_SESSION['cart']['album']))
	$_SESSION['cart']['album']=array();
if (!isset($_SESSION['cart']['track']))
	$_SESSION['cart']['track']=array();
        
echo count($_SESSION['cart']['album'])+count($_SESSION['cart']['track']);
die();
?>
