<?php
/*** Known bugs
 * 
 * The support for non-ASCII artists, albums, tracks etc is still very partial
 * currently, non-ascii video-album and albums will work
 * non-ascii album blacklisting to prevent physical copies availability, too
 * non-ascii songbooks will work
 * all other non-ASCII datas of your media catalog may fail
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
 * Note that configuration (.txt) files expect expect UNIX-style
 * ("\n") end-of-lines. 
 * Windows/Mac OS user please set this in your text editor
 * 
 * 
 * 
 *** Typical deployment is made of 
 * 
 ** -One Clewn API server to host and serve the "free download" audio with
 * -- 'the audio' uloaded in its ./audio subdir
 * -- the api/api.php file at the root of the install
 *			(not ./api.php ! ./api/api.php !)
 * 
 * (you'll have to edit the api.php file to 
 * modify './whatever/path/to/php-getid3'
 * to the path of your actual php-getid3 v 1.x installation(
 * 
 ** -One main backend CreRo with
 * --the d/artists.txt containing a list of the label' artists, one per line
 * -- the $title, $description, $clewnapiurl 
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
 * subdir of your install. Don't forget to include a link somewhere on 
 * your page to "./network" for people to find it. 
 * and to edit the "./network/site_variables.php" for site name 
 * description and so on for your chat subsite 
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
 * an extra <whatever-meaningful-digit>.basename.format file 
 *  
 ** How to sell physical items
 * 
 * Please look at the example "material_*.txt' files
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
 * later. 
 * 
 * 
 * 
 */






$description = 'Independent music for the third millenium. Stream and download for free. Early access to works in progress for a small fee. Also CDs, SD Cards, Cassettes, posters and tees';
$title='<a href="./">Crem Road</a> - The record label';
$server='cremroad.com';
$serverapi='http://'.$server.'/api.php';
$clewnapiurl='http://audio.clewn.org/api.php';
$clewnaudiourl='http://audio.clewn.org/audio/';

$videoapiurl='http://video.clewn.org/api.php';
$videourl='http://video.clewn.org/audio/';


$footerhtmlcode='
<em>This is Crem Road. Formerly C0C. Formerly ZC Virtual. Formerly Slcnc Music.</em>
<em><br/>Copyright 1997-2015 N. Chartoire. No demo submissions for now. </em>
<br/>All the music here is free or open licensed. Please refer to the "comment" tag in each audio file for details. <br/>
<strong>Want to socialize ? </strong>Join our <a target="new" href="http://cremroad.com/network">fan network\'s chatroom</a> and get in touch with fans in your area !<br/>
Not finding what you are looking for ? Here are <a href="http://cremroad.com/?listall=material">our material releases shop</a> and <a href="http://cremroad.com/material_releases_order_history.php">the history of its orders</a><br/>';

$activateaccountcreation='false';
//don't change this for now

$materialreleasessalesagreement='Sales Agreement/CGV : No refund in any case. Items are sens at national/international letter rate, and are produced on demand, there is then no garantee for the delay. Please note that any tentative to bypass the payment system, for example by sending malformed requests to get a lower total, will lead in to the cancellation of your order with no possibility of refund. <br/>Sold by Crem Road Distribution, microcompany based in France. SIREN : Ongoing. CNIL: Ongoing';

?>
