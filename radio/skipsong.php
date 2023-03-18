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

if (!$activatechat===false||$allowradioskipsongwithoutchatnetwork){
		file_put_contents('./d/expire.txt', '0');


	$messg='';
	if (isset($_GET['auto'])){
		$messg="auto";
	}
	if(!$activatechat===false){
		$data['long']=$_SESSION['long'];
		$data['lat']=$_SESSION['lat'];
		$data['nick']=$_SESSION['nick'];
		$data['range']=$_SESSION['range'];
		$data['message']=' just '.$messg.'forked the radio, skipping "'.html_entity_decode(file_get_contents('./d/nowplayingtitle.txt')).'". Other users can stay on the dead-end branch until the end of the song, or refresh this page to join the new fork * * *';
		$data['color']=$_SESSION['color'];
		$dat=serialize($data);
		file_put_contents('../network/d/'.microtime(true).'.dat', $dat);
	}
}
?>
