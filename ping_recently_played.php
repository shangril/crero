<?php
if (session_start()){
	$recents=Array();
	if (file_exists('./d/recent.dat')){
		$recents=unserialize(file_get_contents('./d/recent.dat'));
		
	}
	$recentsfinal=Array();
	foreach ($recents as $recent){
		if($recent['jailed']&&floatval($_SESSION['jailtime'])===floatval($recent['date'])){
			$recent['jailed']=false;
			
		}//if jailed && same user
		array_push($recentsfinal, $recent);
	}
	file_put_contents('./d/recent.dat', serialize($recentsfinal));

}
?>
