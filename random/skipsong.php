<?php
if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
//We've nothing to say to most impolite bots

session_start();
chdir('..');
require_once('./config.php');
chdir('./radio');
function session_put_contents($key, $value){
	$_SESSION[$key]=$value;
}

function session_get_contents($key){
	if (array_key_exists($key, $_SESSION)) {return $_SESSION[$key];}
		else
	{return false;}
}
function session_exists($key){
	if (array_key_exists($key, $_SESSION)){
			return true;
	}
	else {
		return false;
	}
}
function session_unlink($key){
	$_SESSION[$key]=null;
}


if (true||!$activatechat===false||$allowradioskipsongwithoutchatnetwork){
		session_put_contents('../d/expire.txt', '0');


}
?>
