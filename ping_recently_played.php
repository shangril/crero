<?php

if (session_start()){
	if (file_exists('./recently_ping.lock')&&floatval(filectime('./recently_ping.lock'))+2>microtime(true)){
		unlink('./recently_ping.lock');
	}
	

	while (file_exists('./recently_ping.lock')){
		if (file_exists('./recently_ping.lock')&&floatval(filectime('./recently_ping.lock'))+2>microtime(true)){
			unlink('./recently_ping.lock');
		}
		sleep(1);
	}
	touch ('./recently_ping.lock');
	$recents=Array();
	if (file_exists('./d/recent.dat')){
		$recents=unserialize(file_get_contents('./d/recent.dat'));
		
	}
	$recentsfinal=Array();
	foreach ($recents as $recent){
		if($recent['jailed']&&floatval($_SESSION['jailtime'])==floatval($recent['date'])){
			$recent['jailed']=false;
			
		}//if jailed && same user
		array_push($recentsfinal, $recent);
	}
	file_put_contents('./d/recent.dat', serialize($recentsfinal));
	unlink('./recently_ping.lock');
}

?>
