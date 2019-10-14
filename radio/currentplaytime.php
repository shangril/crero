<?php
session_start();
$starttime =float(trim(file_get_contents('./d/starttime.txt'))
$duration = float(trim(file_get_contents('./d/nowplayingduration.txt')));
echo  microtime(true)-$startime-($duration-(microtime(true)-$startime)+floatval($_GET['current']))+($starttime-$_SESSION ['streamhit']);
exit(0);

?>
