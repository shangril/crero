<?php
 
 
  if (!isset($_GET['target'])&&!isset($_GET['viewportwidth'])&&!isset($_GET['ratio'])){
	  die();
  }
  
  $file = str_replace('./','',$_GET['target']);
  $viewportwidth= $_GET['viewportwidth'];
  $ratio=$_GET['ratio'];
  
  if (file_exists('./'.$file) && strpos(mime_content_type('./'.$file),'image/')==0){   
	header('Content-type: application/x-png'); 
	list($width, $height) = getimagesize($file);
	$modwidth=floatval($ratio)*(floatval($viewportwidth)); 
	$modheight = $modwidth;
	$output= imagecreatetruecolor($modwidth, $modheight); 
	$type=mime_content_type('./'.$file);
		if (strstr($type,  'gif')){
			
			$source = imagecreatefromgif($file);
				}
		if (strstr($type,  'png')){
			$source = imagecreatefrompng($file);
			
		}
		if (strstr($type,  'jpeg')){
		
			$source = imagecreatefromjpeg($file);
			
		}
		imagecopyresized($output, $source, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);
		imagepng($output);
}
die();	   
?>
