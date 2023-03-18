<?php
//include ('./config.php');
if ($_SERVER['HTTP_USER_AGENT']==''){
		http_response_code(403);
		exit(0);
	}
//We've nothing to say to most impolite bots
$options = array(
    'http'=>array(
        'method'=>"GET",
        'header'=> "User-Agent: CreRo Thumbnailer\r\n")
);

$context = stream_context_create($options);




 $thumbz=array_diff(scandir('./thumbcache'), Array ('..', '.'));
 foreach ($thumbz as $thumb){
	 if (floatval(filectime('./thumbcache/'.$thumb)+(4*2419200))<=microtime(true)){//2419200 = 4*60*60*24*7
		 unlink ('./thumbcache/'.$thumb);
	 }
	 
 }
 
  if (!isset($_GET['target'])&&!isset($_GET['viewportwidth'])&&!isset($_GET['ratio'])){
	  die('no opt');
  }
  
  $file = str_replace('./','',$_GET['target']);
  $viewportwidth= intval($_GET['viewportwidth']);
  $ratio=floatval($_GET['ratio']);
  if ($ratio>1){
	  $ratio=1;
  }
  
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
			strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) <= filemtime('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png')
			)
			{
				header(null);
				header('HTTP/1.0 304 Not Modified');
				
				exit();
			}  
			
			/*
			$handle=fopen('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png', 'rb');
			
			fpassthru($handle);
			fclose($handle);*/
			header('Last-Modified: '.date(DATE_RFC822, filemtime('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png')));

			readfile('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png');
			exit();
			
		}
		else {
			$content=false;
			$sleeper=0;
			while ($content===false){
				sleep($sleeper);
				$content=file_get_contents(
				'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?hook=hook&ratio='.$ratio.'&target='.urlencode($file).'&viewportwidth='.$viewportwidth
				
				, false, $context
				
				);
				$sleeper++;
			}
			file_put_contents('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png', 
				$content
			);
			header('Last-Modified: '.date(DATE_RFC822, filemtime('./thumbcache/'.$modwidth.'-'.$modheight.'-'.str_replace('/','',$file).'.png')));
			header('Cache-Control', 'public, max-age='.intval(3*864000)); //30 days
	
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
			header('Cache-Control', 'public, max-age='.intval(3*864000)); //30 days
	
			imagepng($output);
		} 
}
die();	   
?>
