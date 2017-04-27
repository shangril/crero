<?php
session_start();

if (!isset($_SESSION['cart'])) {
    
		$_SESSION['cart']=array();
		$_SESSION['cart']['album']=array();
		
		$_SESSION['cart']['track']=array();
    
    
    }
    
echo count($_SESSION['cart']['album'])+count($_SESSION['cart']['track']);
die();
?>
