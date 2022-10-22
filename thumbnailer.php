<?php
include ('./config.php');
 $thumbz=array_diff(scandir('./thumbcache'), Array ('..', '.'));
 foreach ($thumbz as $thumb){
	 if (floatval(filectime('./thumbcache/'.$thumb)+intval(60*60*24*7))<=microtime(true)){
		 unlink ('./thumbcache/'.$thumb);
	 }
	 
 }
 
  if (!isset($_GET['target'])&&!isset($_GET['viewportwidth'])&&!isset($_GET['ratio'])){
	  die('no opt');
  }
  
  $file = str_replace('./','',$_GET['target']);
  $viewportwidth= intval($_GET['viewportwidth']);
  $ratio=floatval($_GET['ratio']);
  
  if ((file_exists('./'.$file) && strpos(mime_content_type('./'.$file),'image/')==0&&(dirname(realpath($file))===realpath('./covers')))||$file=='favicon.png'){ 
/*	  	  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
    strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime('./'.$file)
    &&$ratio<0.5)
		{
			header('HTTP/1.0 304 Not Modified');
			exit;
		}  
*/
	header('Content-type: application/x-png'); 
	list($width, $height) = getimagesize($file);
	$modwidth=intval(floatval($ratio)*(floatval($viewportwidth))); 
	$modheight = $modwidth;
	if (!isset($_GET['hook'])){
	if (file_exists('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png')){
		  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
			strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < filectime('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png')
			)
			{
				header('HTTP/1.0 304 Not Modified');
				
				exit();
			}  
			
			/*
			$handle=fopen('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png', 'rb');
			
			fpassthru($handle);
			fclose($handle);*/
			readfile('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png');
			exit();
			
		}
		else {
			file_put_contents('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png', file_get_contents(
			'http://'.$server.'/thumbnailer.php?hook=hook&ratio='.$ratio.'&target='.$file.'&viewportwidth='.$viewportwidth)
			);
			readfile('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png');
			die();

		}
		
	}


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


		if (isset($_GET['hook'])){
			header('Content-type: application/x-png'); 
	
			imagepng($output);
		} 
}
die();	   
?>
