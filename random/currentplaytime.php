<?php
session_start();

function session_put_contents($key, $value){
	$_SESSION[$key]=$value;
}

function session_get_contents($key){
	return $_SESSION[$key];
}
function session_exists($key){
	if (array_key_exists($key, $_SESSION)){
			return true;
	}
	else {
		return false;
	}
}



$offset=0;

$starttime = floatval(trim(session_get_contents('./d/starttime.txt')));
$duration = floatval(trim(session_get_contents('./d/nowplayingduration.txt')));
/*	echo  microtime(true)-$starttime
-($duration-(microtime(true)-$starttime)+floatval($_GET['current']))
+($starttime-floatval($_SESSION ['streamhit']))
+$offset;
*/
//echo ($duration-(microtime(true)-$starttime)-($starttime-floatval($_SESSION ['streamhit'])-floatval($_GET['current'])-$offset))-(microtime(true)-$starttime);

echo (microtime(true)-$starttime)-($starttime-floatval($_SESSION ['streamhit']))-floatval($_GET['current'])+$offset;
exit(0);

?>
