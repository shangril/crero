<?php
/* When refering to 'the audio' thereafter, it will means
 * Three time the same audio track, with the same basename, in .flac, .ogg and.mp3 
 * With the following vorbiscomment/id3 tags set
 * Artist
 * Album
 * Title
 * Year
 * 
 * Please note that the software relies, for sorting feature
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
 * Typical deployment is made of 
 * 
 * -One Clewn API server to host and serve the "free download" audio with
 * -- 'the audio' uloaded in its ./audio subdir
 * -- the api/api.php file at the root of the install
 *			(not ./api.php ! ./api/api.php !)
 * 
 * (you'll have to edit the api.php file to 
 * modify './whatever/path/to/php-getid3'
 * to the path of your actual php-getid3 v 1.x installation(
 * 
 * -One main backend CreRo with
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
 * What is the ./network subdir :
 * 
 * if you wish to use the chat feature, you'll have
 * to create two directories named "d" and "e" in the "network"
 * subdir of your install. Don't forget to include a link somewhere on 
 * your page to "./network" for people to find it. 
 * and to edit the "./network/site_variables.php" for site name 
 * description and so on for your chat subsite 
 *
 * Video support
 * use video/api.php ; put it at the root of your video server
 * then there, put your videos in a <ROOT OF THE INSTALL>/audio subdir
 * same basename for each video file, in the folowing formats 
 * (none of them being mandatory) : avi, mpg, ogv, mp4, webm
 * basename.description.txt
 * basename.album.txt
 * basename.title.txt
 * basename.artist.txt
 * in the same dir, will be used to categorize your vids
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






$description = 'Independent music for the third millenium. Stream and download for free. Early access to works in progress for a small fee.';
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
<strong>Want to socialize ? </strong>Join our <a target="new" href="http://cremroad.com/network">fan network\'s chatroom</a> and get in touch with fans in your area !';

$activateaccountcreation='false';
//don't change this for now


?>
