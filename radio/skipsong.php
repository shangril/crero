<?php
session_start();
chdir('..');
require_once('./config.php');
chdir('./radio');

if (!$activatechat===false){
	file_put_contents('./d/expire.txt', '0');
		$data['long']=$_SESSION['long'];
		$data['lat']=$_SESSION['lat'];
		$data['nick']=$_SESSION['nick'];
		$data['range']=$_SESSION['range'];
		$data['message']=' just forked the radio, skipping "'.html_entity_decode(file_get_contents('./d/nowplayingtitle.txt')).'". Other users can stay on the dead-end branch until the end of the song, or refresh this page to join the new fork * * *';
		$data['color']=$_SESSION['color'];
		$dat=serialize($data);
		file_put_contents('../network/d/'.microtime(true).'.php', $dat);

}
?>
