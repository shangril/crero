<?php
function displaycover($album, $ratio, $param='cover', $AlbumsToBeHighlighted = -1, $highlightcounter = 0){
/******
This is a frozen -and slightly modified- version from displaycover 20220729 from index.html
* Please keep track of issue in the original function if any
* and correct this one

*/

	if (file_exists('./d/covers.txt')){
		
		if ($highlightcounter<$AlbumsToBeHighlighted){
			
			$ratio=$ratio*2;
			
			
			
		}
		
		
		
		
		$coversfile=trim(file_get_contents('./d/covers.txt'));
		$coverslines=explode("\n", $coversfile); 
		
		$i=0;
		$url=null;
		while ($i<count($coverslines)){
			if ($coverslines[$i]===html_entity_decode($album)){
				if (isset($coverslines[$i+1])){
					$url=$coverslines[$i+1];
				}
			}
			$i++;
			$i++;
		}
		if (isset($url)){
			$output='';
			$output.='<img style="width:100%;" onload="if(!get_page_init()){init_page();}" src="./covers/'.rawurlencode($url).'" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';
		
			
			return $output;
		}
		else {
			return '<img style="width:100%;" onload="if(!get_page_init()){init_page();}" src="./favicon.png" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';
		}
	
	
	
	}
	else{
		return '<img style="width:100%;" onload="if(!get_page_init()){init_page();}" src="./favicon.png" alt="'.$album.'" id="'.$param.'_'.htmlspecialchars($album).'"/>';

	}	
}










$recents=Array();
if (file_exists('./d/recent.dat')){
	$recents=unserialize(file_get_contents('./d/recent.dat'));
	
}
$allowedAlbums=Array();

if (file_exists('./d/recently_generated_albums.dat')){
	$allowedAlbums=unserialize(file_get_contents('./d/recently_generated_albums.dat'));
	
}
$counter=0;
$recentsr=array_reverse($recents);
echo 'Recently played: <a href="javascript:void(0);" onclick="displayRecentlyPlayed();">refresh</a> / <a href="javascript:void(0);" onclick="hideRecentlyPlayed();">hide</a><br/>';
foreach ($recentsr as $recent){
	if (!isset($recent['jailed'])){
		$recent['jailed']=false;
	}
	
	
	if(in_array($recent['album'], $allowedAlbums)&&!$recent['jailed']&&$counter<10){
		$counter++;
		echo '<span style="width:10%;float:left;margin-left:auto;margin-right:auto;"><a href="javascript:page_init=false;arr=[\''.urlencode(str_replace("'", "\\'", html_entity_decode($recent['album']))).'\'];update_ajax_body(\'./?album=\'+encodeURI(JSON.stringify(arr))+\'&autoplay=true\');">'.displaycover($recent['album'], 10, 'mini'.rand(0,1000)).'</a><br/>';
		echo htmlspecialchars(round((time()-intval($recent['date']))/60));
		echo ' mn<br/>';
		//echo ' <a href="#social" style="'.$recent['who']['color'].'">'.htmlspecialchars($recent['who']['nick']).'</a>';
		echo '</span>';
	}//if allowedAlbum
}
?>
