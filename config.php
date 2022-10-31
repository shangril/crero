<?php
error_reporting(0);
$sessionstarted=session_start();
require_once('./crero-lib.php');

if (isset($_GET['purge'])){
		if (array_key_exists('loggedadmin', $_SESSION)){
		
		$myhtmlcache=new creroHtmlCache(0.2);
		$myhtmlcache->purgeCache();
		echo '<!DOCTYPE html><html><body>Cache purged. <a href="./">Proceed</a></body></html>';
		exit(0);

		}
		else
		{
		echo '<!DOCTYPE html><html><body>You must be logged as admin to purge cache</body></html>';
		exit(0);
	
		}
	}
//since this config.php is used on every main pages of the main section
//and also in the ./radio subsection
//and even in ./script.php <- hu, this .php script no longer exists now ! Don't worry for him. 
//and also in the "Fan network" ./network chat optionnal module
//it's a good entry point to start thinking about the .htaccess thing
//since each and every sensitive data is protected by such a file
//and if its not honored
//sensitive informations such as Paypal email adress to route donation (if enabled)
//shadowed (encrypted) Admin password
//and many things alike (IP address of current listeners of the radio)
//and (worst) if .htaccess is not active and Fan Network enable, fan users could
//possibly forge a RCE attack by including <?php //code in their chat message
//and access a private .php file if they got it's exact name
//to exectude the code they passed
// (this is on its way to be secured 20221007 but currently chat serialize data
// are stored in .php file (I think I'm dumb, as Nirvana sang) <- uh, this is no longer the case, 20221030, since many days, now
// but should be changed to .dat file somedays
// but in the meanwhile it .php which is silly, and the only protection against this "Fan network" RCE
// is the .htaccess directives)


//All of these are protected from outside access by .htaccess directive, which is the mandatory way to use CreRo
//so then we are now going to test that the server honors .htaccess
//and emit a general warning toward the server admin and exit if not
//please refer to README.md for more information about .htaccess

//also note that most if not any commercial-grade hostings will have enable .htaccess
//and that this checking requires that the server administrators
//*have set up a password to access their admin pannel (neccessary preamble to set up the $server variable, that is required for anything in CreRo to work)
//*the $server is correcty configured to the actual path of crero
// if one of the above is missing, a warning will be issued and the script will exit
//then the real checking will take place
//and if .htaccess is not honored, the whole (non ./admin) site will be disabled and awaiting for a FIX by admin team


//a first thing, we are going to disable the Site, not the API of it (we don't want to send HTML about a misconfigured file in reply to an API request
if (!strstr($_SERVER['PHP_SELF'], '/crero-yp-api.php')&&strpos($_SERVER['PHP_SELF'], '/crero-yp-api.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/crero-yp-api.php'))
{
	if (!array_key_exists('no-infinite-loop-please', $_GET)){//we don't want to test ANYTHING it is a hook call from this script to the homepage cuz it would cause infinite loop

			//here we go
			//firstly we check the existence of the shadowed password

			if (!file_exists('./admin/d/pwd.dat')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
			echo '<!DOCTYPE html><body>General error: Site administrators, please edit ./admin/config.php to configure username and password of your administrator account then browse &lt;yourserver.tld/(path/to/crero)/admin&gt; for changes to take effect. </body></html>';
			exit(0);

			}
			//then we check that server.txt is set and we also won't die() if the user is in admin (allong him/her to set server.txt)
			else if (!file_exists('./d/server.txt')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
			echo '<!DOCTYPE html><body style="font-size:320%;">General error: Site administrators, please log in the admin panel at &lt;yourserver.tld/(path/to/crero)/admin&gt; and set up the "server" option</body></html>';
			exit(0);
			}
			if (file_get_contents('./d/server.txt')===false){
				echo '<!DOCTYPE html><body style="font-size:320%;">General error: ./d/server.txt is not readable. Please check the permission of your www user on your www files. </body></html>';
					exit(0);
			}
			//useful data starting from now
			
			$server=trim(file_get_contents('./d/server.txt'));
			$proto='http';
			if (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!==''){
				$proto='https';
			}
			//then we check the consistency of server.tx and also won't die() if the user is admin at the admin panel 
			if (file_exists('./d/server.txt')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
				$page=file_get_contents($proto.'://'.$server.'/?no-infinite-loop-please=1');
				if ($page==false){
					echo '<!DOCTYPE html><body style="font-size:320%;">General error: get content replied false, the site is currently unavailable. Some cases are probable <ul><li>this is a temporary overload and you can wait a bit and reload the page</li>
					<li>The "server" parameter in this is configuration is inconsistent. If this error persists, site administrators should check the correctness of this parameter<ol><li>Especially this error will trigger if the VHOST of the webserver is not correctly set. Example with Apache with version>2 : make sur that CanonicalName is activated and that ServerName is set for your VHOST. Most if not any commercial-grade hosting will have made it already. But if you sysadmin your server on your own, it is your job.</li></ol></li>
					</ul></body></html>';
					exit(0);
				}
			}
			if (file_get_contents($proto.'://'.$server.'/?no-infinite-loop-please=1')!==false&&!strstr($http_response_header[0], ' 200 OK')){
					echo '<!DOCTYPE html><body style="font-size:320%;">General error: http response was not 200 OK, he site is currently unavailable. Some cases are probable <ul><li>this is a temporary overload and you can wait a bit and reload the page</li>
					<li>The "server" parameter in this is configuration is inconsistent. If this error persists, site administrators should check the correctness of this parameter<ol><li>Especially this error will trigger if the VHOST of the webserver is not correctly set. Example with Apache with version>2 : make sur that CanonicalName is activated and that ServerName is set for your VHOST. Most if not any commercial-grade hosting will have made it already. But if you sysadmin your server on your own, it is your job.</li></ol></li>
					</ul></body></html>';
					exit(0);
					}

					
				

			//Main thing now, check the .htaccess is honored

			if (strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')&&file_get_contents($proto.'://'.$server.'/admin/d/fields.txt')!==false&&strstr($http_response_header[0], ' 200 OK')&&file_get_contents($proto.'://'.$server.'/admin/d/fields.txt')===file_get_contents('./admin/d/fields.txt')){
			echo '<!DOCTYPE html><body style="font-size:320%;">General error: Site administrators, you MUST enable .htaccess directives respect in your webserver configuration. Please refer to README.me.</body></html>';
			exit(0);
			}
		}
}
else {
	//in case we are in crero-yp-api, we are doing the full range of testing, and if anything fails, output a "0" api response, which is
	//the special code for API calls, which are meant to start by a call to the "version" API action
	//and real version list of supported API version starts with version 1
	//while version -1 is here to indicate to the client that it is blacklisted
	//and version 0 is a reserved code to indicate an unavailability of the API
	header( 'Content-Type: text/plain; charset=utf-8');

	//here we go
	//firstly we check the existence of the shadowed password

	if (!file_exists('./admin/d/pwd.dat')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
		exit('0');

	}
	//then we check that server.txt is set and we also won't die() if the user is in admin (allong him/her to set server.txt)
	else if (!file_exists('./d/server.txt')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
		exit('0');
	}
	if (file_get_contents('./d/server.txt')===false){
		exit('0');
	}
	//useful data starting from now
	
	$server=trim(file_get_contents('./d/server.txt'));
	$proto='http';
	if (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!==''){
		$proto='https';
	}
	//then we check the consistency of server.tx and also won't die() if the user is admin at the admin panel 
	if (file_exists('./d/server.txt')&&strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')){
		$page=file_get_contents($proto.'://'.$server.'/?no-infinite-loop-please=1');
		if ($page==false){
	
			exit('0');
		}
	}
	if (file_get_contents($proto.'://'.$server.'/?no-infinite-loop-please=1')!==false&&!strstr($http_response_header[0], ' 200 OK')){
	
			exit('0');
			}

			
		

	//Main thing now, check the .htaccess is honored

	if (strpos($_SERVER['PHP_SELF'], '/admin/index.php')!==strlen($_SERVER['PHP_SELF'])-strlen('/admin/index.php')&&file_get_contents($proto.'://'.$server.'/admin/d/fields.txt')!==false&&strstr($http_response_header[0], ' 200 OK')&&file_get_contents($proto.'://'.$server.'/admin/d/fields.txt')===file_get_contents('./admin/d/fields.txt')){
	
		exit('0');
	}
	
	
}//end of basic configuration testing for crero-yp-api specifically




$proto='http';
if (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!==''){
	$proto='https';
}
/*** Known bugs
 * 
 * The support for non-ASCII artists, albums, tracks etc is still very partial
 * currently, non-ascii video-album and albums will work
 * non-ascii album blacklisting to prevent physical copies availability, too
 * non-ascii songbooks will work
 * all other non-ASCII datas of your media catalog may fail <- mostly cured now !
 * 
 **** When refering to 'the audio' thereafter, it will means
 * Three time the same audio track, with the same basename, in .flac, .ogg and.mp3 
 * With the following vorbiscomment/id3 tags set
 * Artist
 * Album
 * Title
 * Year
 * 
 ** Please note that the software relies, for sorting feature
 * First, on the Year tag
 * Then, on the modification time of each file on your server
 * 
 * 
 * Note that configuration (.txt) files expect UNIX-style
 * ("\n") end-of-lines. 
 * Windows/Mac OS user please set this in your text editor
 * or use admin panel to modify them
 * 
 * 
 *** Typical deployment is made of 
 * 
 ** -One Clewn API server to host and serve the "free download" audio with
 * -- 'the audio' uloaded in its ./audio subdir
 * -- the api/api.php file at the root of the install
 *			(not ./api.php ! ./api/api.php !)
 * ----please note that in case of DELETION of a free album or track you'll need to 
 * purge the cache by deleting the apicache.php file at the root of your Clewn
 * API server
 * 
 * (you'll have to edit the api.php file to 
 * modify './whatever/path/to/php-getid3'
 * to the path of your actual php-getid3 v 1.x installation(
 * 
 ** -One main backend CreRo with
 * --the d/artists.txt containing a list of the label' artists, one per line
 * -- the $labelhosting, $title, $description, $clewnapiurl 
 * and $clewnaudiurl options in ./config.php 
 * correctly set
 *
 * as well you'll have to edit ./api.php to reflect where php-getid3 is
 * installed
 * 
 * 
 * The "./z" subdir will be used to store the audio that is not available
 * for free download
 * Please note that it is incredibly easy to access it and STEAL your
 * music :)
 * 
 * The ./covers dir will contain cover artworks. 
 * They must be defined in a d/covers.txt file with the folling forma
 * Line 1 : album title
 * Line 2 : filename of the cover
 * Line 3 : album title
 * Line 4 : filename of the album defined at line 3
 * ...
 * 
 * Please note that covers requires to have GD installed. 
 * 
 * the "./songbook" subdir is to contain music sheets (for lyrics, 
 * chord, tablature)... If the basename of an audio file is foo, 
 * CreRo will look for a foo.txt file to be included on the site
 * 
 ***Advanced configuration
 * 
 ** What is the ./network subdir :
 * 
 * if you wish to use the chat feature, you'll have
 * to create two directories named "d" and "e" in the "network"
 * subdir of your install<- outdated. These dirs are here
 *  Don't forget to include a link somewhere on 
 * your page to "./network" for people to find it. <- outdated. Now included automagically
 * and to edit the "./network/site_variables.php" for site name 
 * description and so on for your chat subsite <- probably outdated but not sure
 *
 ** Video support
 * use video/api.php ; put it at the root of your video server
 * then there, put your videos in a <ROOT OF THE INSTALL>/audio subdir
 * same basename for each video file, in the folowing formats 
 * (none of them being mandatory) : avi, mpg, ogv, mp4, webm
 * basename.description.txt
 * basename.album.txt
 * basename.title.txt
 * basename.artist.txt
 * in the same dir, will be used to categorize your vids
 * you can set a downgraded version of any format, that will be
 * provided instead of the original for streaming purpose, by adding 
 * an extra <whatever-meaningful-digit>.basename.format file <- not working anymore currently
 * also note that non ASCII will fail.  
 *  
 ** How to sell physical items
 * 
 * OUDATED - LOOK at the online help in the admin panel ->Please look at the example "material_*.txt' files
 * in the d/ subdir. That's with theses files that you'll define
 * your physical copies product line
 * (remember Windows/Mac users, this is UNIX-style end of lines !)
 * 
 * please note that your paypal payment adress will be publicly available 
 * 
 * 
 * TODO : the script.js has several server url that are harcoded in it 
 * that you will have to manually replace to reflect your actual 
 * installation. It should move to a dynamic php file sooner or 
 * later. <- this is **** done for years
 * 
 * 
 * 
 */
if (!file_exists('./favicon.png')){
	file_put_contents('./favicon.png', file_get_contents('./media/favicon.png'));
}
$favicon = 'favicon.png' ; 

$description = trim(file_get_contents('./d/description.txt'));

$title=trim(file_get_contents('./d/title.txt'));

if (!isset($server)){
$server=trim(file_get_contents('./d/server.txt'));
//change this to your server's domain name
}


$sitename=trim(file_get_contents('./d/sitename.txt'));

$serverapi='http://'.$server.'/api.php';
//don't change this for typical install

if (file_exists('./d/clewnapiurl.txt')&&file_get_contents('./d/clewnapiurl.txt')!==false){
	$clewnapiurl=trim(file_get_contents('./d/clewnapiurl.txt'));
}
else {
	$clewnapiurl=false;//sorry for the news, newbies, but we have to plan for the future
}
//you may change this to http://<your server>/whatever/path/to/free/audio/api.php

$clewnaudiourl=trim(file_get_contents('./d/clewnaudiourl.txt'));
//and this to http://<your server>/whatever/path/to/free/audio/audio/ ; otherwise you can upload your free audio to clewn and use it as free audio media server tier

if (file_exists('./d/videoapiurl.txt')){
$videoapiurl=trim(file_get_contents('./d/videoapiurl.txt'));
}
else $videoapiurl=false;

//same applies for video, with the difference that Clewn Video doesn't currently support public upload
$videourl=trim(file_get_contents('./d/videourl.txt'));

//


//OUTDATED NEVER USE
//whatever message you want to display if the HTTP GET message is set to this value, e.g. for advertising campaign or whatever

$message['show_youtube']='


';
//will be displayed for http://yourserver.com/?message=show_youtube



$footerhtmlcode=trim(file_get_contents('./d/footerhtmlcode.txt'));

$activateaccountcreation=boolval(trim(file_get_contents('./d/activateaccountcreation.txt'))); 
//there's no true account management for now
//only a quick way to subscribe to label news
//without any kind of mailing list management
//just indicate your contact email adress in d/mailing-list-owner.txt (use an .htaccess if you don't want it to be web-accessible)
//and set this option to true
//then any email address entered in the "subscribe to label blah blah" field will cause a message to be sent to your address
//with the subscriber address in it
//and you can start spamming manually
//good luck

$activatechat=boolval(trim(file_get_contents('./d/activatechat.txt')));
//if set to any other value than false, including null, will enable the chat widget at the bottom of every page
//IMPORTANT : if you enable the chat feature, you'll have to create and *protect from outside access* 
//(with (as example with Apache) an .htaccess restriction file with directive set to deny access from all)
// two directories in <root of your install>/network/ : ./d/ and ./e/
//that's were sensitive data, sur as (if provided by the user) geolocation will be stored
//and you don't want the outside world to access them

$activatestats=boolval(trim(file_get_contents('./d/activatestats.txt')));
//if set to any other value than false, including null, will enable the chat widget at the bottom of every page
//IMPORTANT : if you enable the chat feature, you'll have to create and *protect from outside access* 
//(with (as example with Apache) an .htaccess restriction file with directive set to deny access from all)
// two directories in <root of your install>/admin/ : ./d/ and ./d/stats
//that's were sensitive data will be stored
//and you don't want the outside world to access them

$ismaterialnameyourprice=boolval(trim(file_get_contents('./d/isMaterialNameYourPrice.txt')));
//Caution : if set to 0 material shop will be a commercial one, with fixed price for items. If set to 1 people will name
//their price for whatever order they may make with no minimum required

$hasradio=boolval(trim(file_get_contents('./d/hasRadio.txt')));
//Do you want a radio station ?
$radiodescription=trim(file_get_contents('./d/radioDescription.txt'));
$radioname=trim(file_get_contents('./d/radioName.txt'));

$radiohasyp=boolval(trim(file_get_contents('./d/radioHasYp.txt')));

$labelgenres=explode (' ', trim(file_get_contents('./d/labelGenres.txt')));

//do we accept donations ? 

$acceptdonations=boolval(trim(file_get_contents('./d/allowDonations.txt')));
//set this to 1 to accept donations


$donationpaypaladdress=trim(file_get_contents('./d/donationPaypal.txt'));
//the email address of the paypal account where donations will go



$materialnameyourpricenotice=trim(file_get_contents('./d/materialNameYourPriceNotice.txt'));
//this can be used as a disclaimer for name your price physical release
//as "we reserve the right to refuse to complete any ordrer. No garantee"
//things like this


$materialmenu=trim(file_get_contents('./d/materialmenu.txt'));


$materialreleasessalesagreement=trim(file_get_contents('./d/materialreleasessalesagreement.txt'));
//what will be displayed at the bottom of the shop page if the material release eshop is enabled 


$socialmediaicons=Array();
//facebook twitter youtube links, things like that
if (file_exists('./d/social_media_icons.txt')){
	$socdata=trim(file_get_contents('./d/social_media_icons.txt'));
	$soctokens=explode("\n", $socdata);
	for ($p=0;$p<count($soctokens);$p++){
		$socialone=Array();
		$socialone['letter']=$soctokens[$p];
		$p++;
		$socialone['color']=$soctokens[$p];
		$p++;
		$socialone['background-color']=$soctokens[$p];
		$p++;
		$socialone['link']=$soctokens[$p];
		array_push($socialmediaicons, $socialone);
	}
}
$creroypservices=array();
//yellopages services
if (file_exists('./d/crero_yp_services.txt')){
	$ypdata=trim(file_get_contents('./d/crero_yp_services.txt'));
	$creroypservices=explode("\n", $ypdata);
}
//latest level html page caching
$activatehtmlcache=false;
if (file_exists('./d/activatehtmlcache.txt')){
	$activatehtmlcache=boolval(trim(file_get_contents('./d/activatehtmlcache.txt')));
}
$htmlcacheexpires=7;
if (file_exists('./d/htmlcacheexpires.txt')){
	$htmlcacheexpires=floatval(trim(file_get_contents('./d/htmlcacheexpires.txt')));
}
if (!file_exists('./htmlcache/cached')){
	mkdir('./htmlcache/cached');
	
}
$autobuildradiobase=false;
if (file_exists('./d/autoBuildRadioBase.txt')){
	$autobuildradiobase=boolval(trim(file_get_contents('./d/autoBuildRadioBase.txt')));
}
$autodeleteuntagguedtracks=false;
if (file_exists('./d/autoDeleteUntagguedTracks.txt')){
	$autodeleteuntagguedtracks=boolval(trim(file_get_contents('./d/autoDeleteUntagguedTracks.txt')));
}
$autodeleteprefixpath='';
if (file_exists('./d/autoDeletePrefixPath.txt')){
	$autodeleteprefixpath=boolval(trim(file_get_contents('./d/autoDeletePrefixPath.txt')));
}

//download cart options

$enableDownloadCart=false;
if (file_exists('./d/enable_download_cart.txt')){
	$enableDownloadCart=boolval(trim(file_get_contents('./d/enable_download_cart.txt')));
}
$isDownloadCartNameYourPrice=false;
if (file_exists('./d/is_download_cart_name_your_price.txt')){
	$isDownloadCartNameYourPrice=boolval(trim(file_get_contents('./d/is_download_cart_name_your_price.txt')));
}
$downloadCartCurrency='';
if (file_exists('./d/download_cart_currency.txt')){
	$downloadCartCurrency=trim(file_get_contents('./d/download_cart_currency.txt'));
}
$downloadCartTrackPrice=1;
if (file_exists('./d/download_cart_track_price.txt')){
	$downloadCartTrackPrice=floatval(trim(file_get_contents('./d/download_cart_track_price.txt')));
}
$downloadCartAlbumPrice=9;
if (file_exists('./d/download_cart_album_price.txt')){
	$downloadCartAlbumPrice=floatval(trim(file_get_contents('./d/download_cart_album_price.txt')));
}
$downloadCartPaypalAddress='';
if (file_exists('./d/download_cart_paypal_address.txt')){
	$downloadCartPaypalAddress=trim(file_get_contents('./d/download_cart_paypal_address.txt'));
}
$artisthighlighthomepage=false;
if (file_exists('./d/ArtistHighlightHomePage.txt')){
	$artisthighlighthomepage=boolval(trim(file_get_contents('./d/ArtistHighlightHomePage.txt')));
}
$streaming_albums_ratio=0.65;
if (file_exists('./d/streaming_albums_ratio.txt')){
	$streaming_albums_ratio=floatval(trim(file_get_contents('./d/streaming_albums_ratio.txt')));
}
$download_albums_magic_number=7550;
if (file_exists('./d/download_albums_magic_number.txt')){
	$download_albums_magic_number=floatval(trim(file_get_contents('./d/download_albums_magic_number.txt')));
}
$hlartists=Array();
//facebook twitter youtube links, things like that
if (file_exists('./d/highlight-artist-list.txt')){
	$socdata=trim(file_get_contents('./d/highlight-artist-list.txt'));
	$soctokens=explode("\n", $socdata);
	for ($p=0;$p<count($soctokens);$p++){
		
		while ($p<count($soctokens)){
			$socialone=Array();
			$socialone['name']=$soctokens[$p];
			$p++;
			$socialone['styles']=$soctokens[$p];
			$p++;
			$socialone['infos']=$soctokens[$p];
			$p++;
			$socialone['link']=$soctokens[$p];
			array_push($hlartists, $socialone);
			$p++;
		}
	}
}
$recentplay=false;
if (file_exists('./d/recentplay.txt')){
	$recentplay=boolval(trim(file_get_contents('./d/recentplay.txt')));
}

$pageHeaderContactInfo='false';
if (file_exists('./d/pageHeaderContactInfo.txt')){
	$pageHeaderContactInfo=trim(file_get_contents('./d/pageHeaderContactInfo.txt'));
}

$pageFooterSplash='';
if (file_exists('./d/pageFooterSplash.txt')){
	$pageFooterSplash=trim(file_get_contents('./d/pageFooterSplash.txt'));
}

$radioBanner='';
if (file_exists('./d/RadioBanner.txt')){
	$radioBanner=trim(file_get_contents('./d/RadioBanner.txt'));
}




//streamingAlbumsInfoNotice
$streamingAlbumsInfoNotice = Array();
if (file_exists('./d/streamingAlbumsInfoNotice.txt')){
	$saindata=trim(file_get_contents('./d/streamingAlbumsInfoNotice.txt'));
	$sain=explode("\n", $saindata);
	for ($p=0;$p<count($sain);$p++){
	
		$streamingAlbumsInfoNotice[htmlentities($sain[$p])]=$sain[$p+1];
		$p++;
	}

}

$streamingAlbumsInfoHeader = Array();
if (file_exists('./d/streamingAlbumsInfoHeader.txt')){
	$saindata=trim(file_get_contents('./d/streamingAlbumsInfoHeader.txt'));
	$sain=explode("\n", $saindata);
	for ($p=0;$p<count($sain);$p++){
	
		$streamingAlbumsInfoHeader[htmlentities($sain[$p])]=$sain[$p+1];
		$p++;
	}

}



$albumsForDownloadInfoNotice = Array();
if (file_exists('./d/albumsForDownloadInfoNotice.txt')){
	$saindata=trim(file_get_contents('./d/albumsForDownloadInfoNotice.txt'));
	$sain=explode("\n", $saindata);
	for ($p=0;$p<count($sain);$p++){
	
		$albumsForDownloadInfoNotice[htmlentities($sain[$p])]=$sain[$p+1];
		$p++;
	}

}

$IsRadioResyncing=false;
if (file_exists('./d/IsRadioResyncing.txt')){
	$IsRadioResyncing=boolval(trim(file_get_contents('./d/IsRadioResyncing.txt')));
}
$IsRadioResyncing=false;

$RadioResyncInterval=16800;
if (file_exists('./d/RadioResyncInterval.txt')){
	$RadioResyncInterval=intval(trim(file_get_contents('./d/RadioResyncInterval.txt')));
}


$RadioHasGentleResync=false;
if (file_exists('./d/RadioHasGentleResync.txt')){
	$RadioHasGentleResync=boolval(trim(file_get_contents('./d/RadioHasGentleResync.txt')));
}
$RadioHasGentleResync=false;

$AlbumsToBeHighlighted=0;
if (file_exists('./d/AlbumsToBeHighlighted.txt')){
	$AlbumsToBeHighlighted=intval(trim(file_get_contents('./d/AlbumsToBeHighlighted.txt')));
}
$IsRadioStreamHTTPS=false;
if (file_exists('./d/IsRadioStreamHTTPS.txt')){
	$IsRadioStreamHTTPS=boolval(trim(file_get_contents('./d/IsRadioStreamHTTPS.txt')));
}
$ArtistSites=Array();
	
	
	if (file_exists('./d/ArtistSites.txt')&&!is_dir('./d/ArtistSites.txt')){
		$covers=trim(file_get_contents('./d/ArtistSites.txt'));
		$coverlines=explode("\n", $covers);
		for ($i=0;$i<count($coverlines);$i++){
			$ArtistSites[$coverlines[$i]]=$coverlines[$i+1];
			$i++;
		}
	}

$recentplay=false;

if (file_exists('./d/RecentlyPlayed.txt')){
	$recentplay=boolval(trim(file_get_contents('./d/RecentlyPlayed.txt')));
}
$YPForceHTTPS=0;

if (file_exists('./d/YPForceHTTPS.txt')){
	$YPForceHTTPS=intval(trim(file_get_contents('./d/YPForceHTTPS.txt')));
	}
$YP_APIMisconfiguredDateOnHostingToleranceWindow=0;
if (file_exists('./d/YP-APIMisconfiguredDateOnHostingToleranceWindow.txt')) {
	$YP_APIMisconfiguredDateOnHostingToleranceWindow=floatval(file_get_contents('./d/YP-APIMisconfiguredDateOnHostingToleranceWindow.txt'));
}

$activate_musician_accounts=false;
//currently it as to be and stay false

$footerReadableName='+';

if (file_exists('./d/footerReadableName.txt')){
	$my_footerReadableName=file_get_contents('./d/footerReadableName.txt');
	if($my_footerReadableName!==false){
		$footerReadableName=$my_footerReadableName;
	}
}




?>
