<?php
function generate_header($title, $meta_description){

return '<!DOCTYPE html>
<html>
<head>
<!--link rel="shortcut icon" href="//'.$_SERVER['SERVER_NAME'].'/favicon.png" /-->
<link rel="stylesheet" href="/style.css" type="text/css" media="screen" />
<style>
body {
	padding-left:1%;
	padding-right:1%;
}
a link {
	color:black;text-decoration:none;
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="charset" content="utf-8"/>
<meta name="description" content="'.htmlspecialchars($meta_description).'" />
<title>'.htmlspecialchars($title).'
</title>
</head>
<body>';
}

function generate_footer($footer_text) {
	return '<div style="font-size:65%;">'.htmlspecialchars($footer_text).'</div></body></html>';

}

?>
