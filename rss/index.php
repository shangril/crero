<?php
header('Content-Type: application/rss+xml; charset=utf-8');
function xmlcdata($arg){
	return '<![CDATA['.$arg.']]>';
}
if(!file_exists('./metadatacache')){
	mkdir('./metadatacache');
}

if(!file_exists('./metadatacache/albums')){
	mkdir('./metadatacache/albums');
}

if(!file_exists('./metadatacache/tracklists')){
	mkdir('./metadatacache/tracklists');
}


if (!file_exists('../d/wizard_completed.txt')){
	die();
	//the initial CMS setup hasn't been completed
}
$art64='';

$artlist=file_get_contents('../d/artists.txt');
if ($artlist!==false){
	$artists=explode("\n", trim($artlist));
}
if (isset($_GET['artist'])){
	if (!in_array($_GET['artist'], $artists)){
		die();
	}
	$art64=str_replace('/', '_', base64_encode($_GET['artist']));
	$artists = Array($_GET['artist']);
}	
if (file_exists('./'.$art64.'rss.xml')&&(floatval(filemtime('./'.$art64.'rss.xml'))+floatval(24*3600))>microtime(true)){
	echo file_get_contents('./'.$art64.'rss.xml');
	die();
}

//chdir ('..');
//$_GET['no-infinite-loop-please']=1;
//require_once('./config.php');
//chdir ('./rss');

if ((
 ($clewnapiurl=file_get_contents('../d/clewnapiurl.txt')) === false ||
	($sitename=file_get_contents('../d/sitename.txt')) === false ||
	($description=file_get_contents('../d/description.txt')) === false ||
	($server=file_get_contents('../d/server.txt')) === false ||
	($clewnaudiourl=file_get_contents('../d/clewnaudiourl.txt')) === false
  )){
	  die();
  }

$clewnapiurl=trim($clewnapiurl);
$clewnaudiourl=trim($clewnaudiourl);
$sitename=trim($sitename);
$description=trim($description);
$server=trim($server);

$formats='';
if (!file_exists('./formats.txt')){
	if(($formats = file_get_contents($clewnapiurl.'?listformats=true'))!==false){
		file_put_contents('./formats.txt', $formats);
		}
	}

else {
	if(($formats=file_get_contents('./formats.txt'))===false){
		$formats='';
		}

	}

if ($formats!==false&&!strstr($formats, 'mp3')){
	die();
}	
$freshness = file_get_contents($clewnapiurl.'?freshness=1');

if ($freshness===false){
	$freshness=0;
}

if(file_exists('./'.$art64.'rss.xml')&&file_exists('./'.$art64.'freshness.txt')){

	if ($freshness!==false&&$freshness<=floatval(file_get_contents('./'.$art64.'freshness.txt'))){
		echo file_get_contents('./'.$art64.'rss.xml');
		die();
	}
}
$ret='';

$proto='http://';
if ((isset($_SERVER['HTTPS'])&&strtolower($_SERVER['HTTPS']) !== 'off' && !empty($_SERVER['HTTPS']))||isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'){
	$proto='https://';
}
$itemcount=0;
if ($artlist!==false){
	$ret.='<?xml version="1.0" encoding="UTF-8" ?>';
	$ret.='<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:podcast="https://podcastindex.org/namespace/1.0" version="2.0">';
	$ret.='<channel>';
	$ret.='<itunes:category text="Music"/>';
	if (count($artists)==1){
		$ret.='<title>'.xmlcdata($artists[0]).'</title>';
	}
	else {
		$ret.='<title>'.xmlcdata($sitename).'</title>';
	}

	if (file_exists('../d/allowDonations.txt')&&boolval(file_get_contents('../d/allowDonations.txt'))==true){
		//Donations are accepted 
		if ($proto=='https://'){
			//the podcasting 2.0 user-defined url like used for the podcast:funding tag won't allow http://
			
			$ret.='<podcast:funding url="https://'.$server.'/donate">Help funding us!</podcast:funding>';
			
		}
		
		
	}
	
	$LightningBTCDonationAddress = false;
	if (file_exists('../d/LightningBTCDonationAddress.txt')){
		$btcdata=trim(file_get_contents('../d/LightningBTCDonationAddress.txt'));
		$abtc=explode(" ", $btcdata);
		if (count($abtc)>2 && is_numeric($abtc[1])){
			$LightningBTCDonationAddress = $abtc;
		}
	}
	if ($LightningBTCDonationAddress!==false){

		$ret.='<podcast:value type="lightning" method="keysend" suggested="'.htmlspecialchars($LightningBTCDonationAddress[1]).'"><podcast:valueRecipient name="'.htmlspecialchars(implode(" ",array_splice($LightningBTCDonationAddress, 2))).'" type="node" address="'.htmlspecialchars($LightningBTCDonationAddress[0]).'" split="100"/></podcast:value>';
		
	}
	
	if ($proto=='https://'){
		if (isset($_GET['artist'])){
			if (file_exists('../d/ProjectPicture.txt')){
				$apdata = explode("\n", trim(file_get_contents('../d/ProjectPicture.txt')));
				$apa = Array();
				for ($i=0;$i<count($apdata);$i++){
					$apa[$apdata[$i]] = $apdata[$i+1];
					$i++;
				}
				
			}
			if (in_array($_GET['artist'], array_keys($apa))){
				$ret.='<podcast:image href="https://'.$server.'/projectpicture/'.rawurlencode($apa[$_GET['artist']]).'"/>';
				$ret.='<itunes:image href="https://'.$server.'/projectpicture/'.rawurlencode($apa[$_GET['artist']]).'"/>';
			}
		}
		else
			if (file_exists('../logo.png')){
			
				$ret.='<podcast:image href="https://'.$server.'/logo.png"/>';
				$ret.='<itunes:image href="https://'.$server.'/logo.png"/>';
			
			}
			else
			{
				$ret.='<podcast:image href="https://'.$server.'/favicon.png"/>';
				$ret.='<itunes:image href="https://'.$server.'/favicon.png"/>';
			}
	}
	
	$ret.='<description>';
	$descadd = '';
	if (isset($_GET['artist'])){
		$descadd = $sitename.': ';
	}
	$ret.=xmlcdata($descadd.$description).'</description>
	<itunes:author>'.xmlcdata($sitename).'</itunes:author>
	<link>'.$proto.htmlspecialchars($server);
	if (isset($_GET['artist'])){
		$ret.='/?artist='.urlencode($artists[0]);
		
	}
	$ret.='</link>';
	$tracklist=array();
	$albumlist=array();
	foreach ($artists as $artist){
		$albums=false;
		$artist64=str_replace('/', '_',base64_encode($artist));
		
		if (file_exists('./metadatacache/albums/'.$artist64.'.txt')&&
			filemtime('./metadatacache/albums/'.$artist64.'.txt')>$freshness){
				$albums=file_get_contents('./metadatacache/albums/'.$artist64.'.txt');
			}
		else {
			$albums=file_get_contents($clewnapiurl.'?listalbums='.urlencode($artist));
			file_put_contents('./metadatacache/albums/'.$artist64.'.txt', $albums);
		}
		if ($albums!==false){
			$albdat = explode("\n", trim($albums));
			foreach ($albdat as $album){
				$tracks=false;
				$album64=str_replace('/', '_',base64_encode($album)).$artist64;
				if (file_exists('./metadatacache/tracklists/'.$album64.'.txt')&&
					filemtime('./metadatacache/tracklists/'.$album64.'.txt')>$freshness){
						$tracks=file_get_contents('./metadatacache/tracklists/'.$album64.'.txt');
					}
				else {
					$tracks=file_get_contents($clewnapiurl.'?gettracks='.urlencode(htmlspecialchars_decode($album)));
					
					if ($tracks!==false){
						$tracks=trim($tracks);
					}
					file_put_contents('./metadatacache/tracklists/'.$album64.'.txt', $tracks);

				}
								
				if($tracks!==false){
					foreach (explode("\n", trim($tracks)) as $track){
						$tracklist[$track]=$track;
						$albumlist[$track]=$album;
					}
				}
			}
		}
	}
	foreach ($tracklist as $trackitem){
		$title=false;
		if (file_exists('./metadatacache/'.$trackitem.'.title.txt')&&
			filemtime('./metadatacache/'.$trackitem.'.title.txt')>$freshness){
				$title=file_get_contents('./metadatacache/'.$trackitem.'.title.txt');
			}
		else{
			$title=file_get_contents($clewnapiurl.'?gettitle='.urlencode($trackitem));
			
			if ($title!==false){
				$title=trim($title);
			}
			
			file_put_contents('./metadatacache/'.$trackitem.'.title.txt', $title);
		}
		
		$artist=false;
		if (file_exists('./metadatacache/'.$trackitem.'.artist.txt')&&
			filemtime('./metadatacache/'.$trackitem.'.artist.txt')>$freshness){
				$artist=file_get_contents('./metadatacache/'.$trackitem.'.artist.txt');
			}
		
		else{
			$artist=file_get_contents($clewnapiurl.'?getartist='.urlencode($trackitem));
			
			if ($artist!==false){
				$artist=trim($artist);
			}
			
			file_put_contents('./metadatacache/'.$trackitem.'.artist.txt', $artist);
		}
		
		
		if ($title!==false&&$artist!==false){
			$title=trim($title);
			$artist=trim($artist);
		
			$itemcount++;
			$ret.='<item>';
			$title_cdata = '';
			if (count($artists)>1){
				$title_cdata .= html_entity_decode($artist).' - ';
			}
			$title_cdata .= html_entity_decode($title).' ('.html_entity_decode($albumlist[$trackitem]).')';
			$ret.='<title>'.xmlcdata($title_cdata).'</title>';
			
			if (false!==($length=file_get_contents($clewnapiurl.'?length='
				.urlencode($trackitem).'.mp3'))&&is_numeric($length)){
				$ret.='<enclosure url="'.htmlspecialchars($clewnaudiourl).htmlspecialchars($trackitem).'.mp3" length="'.$length.'" type="audio/mpeg"/>';
			}
			$ret.='<guid>'.urlencode((htmlspecialchars_decode($artist))).'_'.urlencode(htmlspecialchars_decode($albumlist[$trackitem])).'_'.urlencode(htmlspecialchars_decode($title)).'</guid>';
			$ret.='<link><![CDATA['.$proto.$server.'?artist='.urlencode(htmlspecialchars_decode($artist)).'&album='.urlencode(htmlspecialchars_decode($albumlist[$trackitem])).'&track='.urlencode(htmlspecialchars_decode($title)).']]></link>';
			
			if (false!==($pubdate=file_get_contents($clewnapiurl.'?pubdate='.urlencode($trackitem).'.mp3'))&&is_numeric($pubdate)){
				$ret.='<pubDate>'.htmlspecialchars(date(DATE_RSS, intval($pubdate))).'</pubDate>';
			}
			if (false!==($duration=file_get_contents($clewnapiurl.'?duration='.urlencode($trackitem).'.mp3'))&&is_numeric($duration)){
				$ret.='<itunes:duration>'.htmlspecialchars(intval($duration)).'</itunes:duration>';
			}
			$coversdat=Array();
	
	
			if (file_exists('../d/covers.txt')&&!is_dir('../d/covers.txt')){
				$covers=trim(file_get_contents('../d/covers.txt'));
				$coverlines=explode("\n", $covers);
				for ($i=0;$i<count($coverlines);$i++){
					$coversdat[$coverlines[$i]]=$coverlines[$i+1];
					$i++;
				}
			}
			if (isset($coversdat[htmlspecialchars_decode($albumlist[$trackitem])])){
				$ret.='<itunes:image href="'.$proto.$server.'/covers/'.htmlspecialchars(rawurlencode($coversdat[htmlspecialchars_decode($albumlist[$trackitem])])).'"/>';
			}
			
			$ret.='</item>';
			
		}
	}
	
	
	
	
	$ret.='</channel>';
	$ret.='</rss>';
	
	
	

	if ($itemcount>0){
		echo $ret;	
		
		//Saving the cache
		file_put_contents('./'.$art64.'rss.xml', $ret);
		file_put_contents('./'.$art64.'freshness.txt', microtime(true));
	} 
	else if (file_exists('./'.$art64.'rss.xml')){
		echo file_get_contents('./'.$art64.'rss.xml');
		
	}
}
?>
