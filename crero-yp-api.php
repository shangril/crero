<?php
error_reporting(E_ERROR | E_PARSE);
require_once('config.php');
header( 'Content-Type: text/plain; charset=urf-8');
if (!array_key_exists('a', $_GET)){
	echo '0';
	exit(0);
}
switch ($_GET['a']) {
	case 'version':
		//
		echo '1';//must be a string containing an integer >0
		break;
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
		echo file_get_contents('http://'.$server.'/api.php?listalbums='.urlencode(htmlentities($_GET['streaming_albums'])));
		break;
	case 'download_albums':
		//
		echo file_get_contents($clewnapiurl.'?listalbums='.urlencode(htmlentities($_GET['download_albums'])));
		break;
	case 'list_artists':
		//
		echo file_get_contents('./d/artists.txt');
			//return either a list of artists of the label, separated by "\n", or empty if likely to be a radio-only station autobuilding its radiobase with a huge catalog and not declaring specific artists
		break;
	case 'album_streaming':
		//
		echo file_get_contents('http://'.$server.'/api.php?gettracks='.urlencode(($_GET['album_streaming'])));
		break;
	case 'album_download':
		//
		echo file_get_contents($clewnapiurl.'?gettracks='.urlencode(($_GET['album_download'])));
		break;
	case 'title_streaming':
		//
		echo file_get_contents('http://'.$server.'/api.php?gettitle='.urlencode(($_GET['title_streaming'])));
		break;
	case 'title_download':
		//
		echo file_get_contents($clewnapiurl.'?gettitle='.urlencode(($_GET['title_download'])));
		break;
	case 'track_artist_streaming':
		//
		echo file_get_contents('http://'.$server.'/api.php?getartist='.urlencode(($_GET['track_artist_streaming'])));
		
		break;
	case 'track_artist_download':
		//
		echo file_get_contents($clewnapiurl.'?getartist='.urlencode(($_GET['track_artist_download'])));
		
		break;
	case 'artist_info':
		echo file_get_contents('./d/highlight-artist-list.txt');
			//returns additionnal info for some artists of the label
			//first line ("\n" for each newline) is the artist name
			//second line is styles separated by spaces
			//third line is info, typically years active
			//fouth line is blank and reserved for future usage
			//fifth line is 2nd artist name
			//sixth line is 2nd artist styles
			//...and so on
		break;
	case 'list_all_artists':
		echo html_entity_decode(trim(file_get_contents($clewnapiurl.'?listartists=1')));
		//todo : requires testing. 
		break;
	default: 
		echo 'Unsupported api action';
}
?>
