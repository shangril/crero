<?php
function generate_header($title, $meta_description){

return '<!DOCTYPE html>
<html>
<head>
	<script>

// @license magnet:?xt=urn:btih:0b31508aeb0634b347b8270c7bee4d411b5d4109&dn=agpl-3.0.txt AGPL Version 3 or later
 
        /*    
        @licstart  The following is the entire license notice for the 
        JavaScript code in this page. While it is already specified
        * for external script.js ressourece
        * for each <script> tag in this file
        this is simply an indication for event handlers. 
        
        The JavaScript code in this page is free software: you can
        redistribute it and/or modify it under the terms of the GNU
        Affero General Public License (GNU AGPL) as published by the Free Software
        Foundation, either version 3 of the License, or (at your option)
        any later version.  The code is distributed WITHOUT ANY WARRANTY;
        without even the implied warranty of MERCHANTABILITY or FITNESS
        FOR A PARTICULAR PURPOSE.  See the GNU AGPL for more details.

        @licend  The above is the entire license notice
        for the JavaScript code in this page. It is already mentionned
        (same AGPL V3 or above) in each <script>
        
        and then, this is clear for Event Handlers as well. 
        */
        
// @license-end
</script>

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
