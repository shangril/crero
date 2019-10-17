<?php
session_start();

$offset=microtime(true)-floatval($_GET['start']);

$starttime = floatval(trim(file_get_contents('./d/starttime.txt')));
$duration = floatval(trim(file_get_contents('./d/nowplayingduration.txt')));
echo  microtime(true)-$starttime-($duration-(microtime(true)-$starttime)+floatval($_GET['current']))+($starttime-$_SESSION ['streamhit'])+$offset;
exit(0);

?>
