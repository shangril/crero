<?php
error_reporting(0);

if ($_SERVER['HTTP_USER_AGENT']==''){
	http_response_code(403);
	die();
}

require_once('config.php');
//error_reporting(E_ALL);

//first off let's make a bit of cleaning on audio tiers every 4 hours
$last_cleanup=file_get_contents('last_cleanup.dat');
if ($last_cleanup===false){
	$last_cleanup=0;
}
$last_cleanup=floatval($last_cleanup);
if ($last_cleanup+14400<microtime(true)){
	file_get_contents ($clewnapiurl.'?cleanup=1');
	file_get_contents ($serverapi.'?cleanup=1');

	file_put_contents('last_cleanup.dat', microtime(true));
}

header( 'Content-Type: text/plain; charset=utf-8');
if (!array_key_exists('a', $_GET)){
	echo '0';
	exit(0);
}

$provided_api_versions = '1 2';
	//must be a (string) list of space separated integers >0 ; each of these integers indicates that the corresponding API version is supported by this instance
	//0 is a reserved value used to indicate that the requesting (CreRo or YP server) client cannot access this API, either that it is host-based blacklisted by the instance
	//or that the instance has an host-based whiteslist and that the requesting server is not listed in it
		


$version=0;

//manage all the black/whitelist stuff here
//TODO

	
//here we got all the caching stuff
$cache=true;
if (!file_exists('./crero-yp-api-cache')){
		mkdir('./crero-yp-api-cache');
		
	}
else if (!is_dir('./crero-yp-api-cache')){
	$cache=false;
}

if (!file_exists('./crero-yp-api-cache/.htaccess')||filectime('./crero-yp-api-cache/.htaccess')<filemtime('./d/.htaccess')){
	$hta=file_get_contents('./d/.htaccess');
	if ($hta!==false){
		file_put_contents('./crero-yp-api-cache/.htaccess', $hta);
	}
}


function readCache(string $version='0', string $action='void', string $actionvalue='', string $target='', bool $cache=false, float $windowT=0, string $additionnal_parameters='' ) {
	
	$cachepath='./crero-yp-api-cache/';
	$cachename=$version.'-'.$action.'.dat';
	if ($cache){
		$window=floatval(0);
		if ($windowT!=0){
			$window=floatval($windowT);
		}
		if (file_exists($cachepath.$cachename)){
				$cacheddata=unserialize($cachepath.$cachename);
				if ($cacheddata!==false){
					if (is_array($cacheddata)){
						if (array_key_exists($actionvalue, $cacheddata)&&array_key_exists('freshness', $cacheddata)&&array_key_exists($target, $cacheddata)){
								if (is_array($cacheddata[$actionvalue])){
								
								$fresh=file_get_contents($target.'?freshness=true');
								
								if ($fresh!==false){
									$fresh=floatval($fresh);
									if (floatval($fresh-$window)<floatval($cacheddata['freshness'])){
										if (array_key_exists($additionnal_parameters, $cacheddata[$actionvalue])){
												return $cacheddata[$actionvalue][$additonnal_parameters];
											
										}
										else
										{
											return false;
										}
										
									}
									else if (floatval($fresh+$window)<floatval($cacheddata['freshness'])) {
										if (array_key_exists($additionnal_parameters, $cacheddata[$actionvalue])){
												return $cacheddata[$actionvalue][$additonnal_parameters];
											
										}
										else
										{
											return false;
										}
										
									}
									else {
										return false;
									}
								}
								else {
									return false;
								}
							}
							else
							{
								return false;
							}
						}
						else
						{
							return false;
						}
						
						
					}
					else
					{
						return false;
					}
				}
				else {
					return false;
				}
			}
		else {
			return false;
		}
		
	}
	else {
		return false;
	}
}
function writeCache(string $version='0', string $action='void', string $actionvalue='', string $target='', bool $cache=false, string $content='', string $additionnal_parameters=''){
	
	$cachepath='./crero-yp-api-cache/';
	$cachename=$version.'-'.$action.'.dat';
	if ($cache){
		$data=array();
		$data[$actionvalue]=array();
		$data[$actionvalue]['freshness']=microtime(true);
		$data[$actionvalue][$target]=true;
		$data[$actionvalue][$additionnal_parameters]=$content;
		$sdata=serialize($data);
		if ($sdata!==false){
			file_put_contents($cachepath.$cachename, $sdata);
		}
	
	}
}




$version=1;//Just an indicative variable to say that we are above zero. Currently unused	
switch ($_GET['a']) {
	case 'version':
		//
		echo $provided_api_versions;
		break;
		
		
	//HERE STARTS what is defined as supported by the Version 1 of the crero-yp-api	
	case 'styles_defined':
		//
		if (count($labelgenres)>0)
				echo '1';
			else
				echo '0';
			break;
	case 'styles':
		//
		echo implode(" ", $labelgenres);
		//returns a list of styles for the label separated by spaces
		break;
	case 'streaming_albums':
		//
		
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true);
			break;
		}
		
		$ret=file_get_contents($serverapi.'?listalbums='.urlencode(($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'download_albums':
		//
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
			break;
		}
		
		$ret = file_get_contents($clewnapiurl.'?listalbums='.urlencode(($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'list_artists':
		//
		echo file_get_contents('./d/artists.txt');
			//return either a list of artists of the label, separated by "\n", or empty if likely to be a radio-only station autobuilding its radiobase with a huge catalog and not declaring specific artists
		break;
	case 'album_streaming':
		//
		
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true);
			break;
		}
		
		$ret=file_get_contents($serverapi.'?gettracks='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'album_download':
		//
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
			break;
		}
		
		$ret = file_get_contents($clewnapiurl.'?gettracks='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'title_streaming':
		//
		
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true);
			break;
		}
		
		$ret=file_get_contents($serverapi.'?gettitle='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'title_download':
		//
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
			break;
		}
		
		$ret = file_get_contents($clewnapiurl.'?gettitle='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'track_artist_streaming':
		//
				
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true);
			break;
		}
		
		$ret=file_get_contents($serverapi.'?getartist='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'track_artist_download':
		//
			if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
			break;
		}
		
		$ret = file_get_contents($clewnapiurl.'?getartist='.urlencode(strval($_GET[$_GET['a']])));
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
			echo $ret;
		}
		
		
		break;
	case 'artist_info':
		if (false!==file_get_contents('./d/highlight-artist-list.txt')){
			echo file_get_contents('./d/highlight-artist-list.txt');
				//returns additionnal info for some artists of the label
				//first line ("\n" for each newline) is the artist name
				//second line is styles separated by spaces
				//third line is info, typically years active
				//fouth line is blank and reserved for future usage
				//fifth line is 2nd artist name
				//sixth line is 2nd artist styles
				//...and so on
			}
		break;
	case 'list_all_artists':
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			echo readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
			break;
		}
		
		$ret = file_get_contents($clewnapiurl.'?listartists=1');
		
		if ($ret!==false){
			$ret=html_entity_decode($ret);
			writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
			echo $ret;
		}
		break;
	//Here should go the API V2 commands
	//(...)
	//Then the API V3 commands
	//(...)
	//And so on
	case 'list_albums_with_covers':
		$list_albums = '';
		//Step 1 get streaming albums
		if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			$list_albums =  readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true);
		}
		else {
			$ret=file_get_contents($serverapi.'?listalbums='.urlencode(($_GET[$_GET['a']])));
			
			if ($ret!==false){
				$ret=html_entity_decode($ret);
				writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$serverapi, true, $ret); 
				$list_albums = $ret;
			}		
		}
		$arr_alb_str = explode("\n", $list_albums);
		$arr_alb_str = array_diff($arr_alb_str, Array(''));
		
		$list_albums = '';
		//Step 2 get d/l albums
				if (false!==readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $YP_APIMisconfiguredDateOnHostingToleranceWindow)){
			$list_albums =  readCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true);
		}
		else {
			$ret=file_get_contents($clewnapiurl.'?listalbums='.urlencode(($_GET[$_GET['a']])));
			
			if ($ret!==false){
				$ret=html_entity_decode($ret);
				writeCache($version, $_GET['a'], strval($_GET[$_GET['a']]),$clewnapiurl, true, $ret); 
				$list_albums = $ret;
			}		
		}
		$arr_alb_dl = explode ("\n", $list_albums);
		$arr_alb_dl = array_diff($arr_alb_dl, Array(''));
		
		
		$arr_alb = array_merge($arr_alb_str, $arr_alb_dl);
		
		$coversdat = file_get_contents('./d/covers.txt');
		if ($coverdat === false){$coverdat=implode("\n\n", $arr_alb).'\n';}
		$arr_covers = explode ("\n", $coversdat);
		
		$albcov = Array();
				
		for ($i=0;$i<count($arr_covers);$i++) {
			$albcov[$arr_covers[$i]]=$arr_covers[$i+1];
			$i++;
		}
		
		foreach ($arr_alb as $thing){
			echo $thing."\n".$albcov[$thing]."\n";
		}
		
		
		break;
	
	default: 
		echo 'Unsupported api action';
		$version++;
		break;
}
?>
