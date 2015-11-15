<?php
/* When refering to 'the audio' thereafter, it will means
 * Three time the same audio track, with the same basename, in .flac, .ogg and.mp3 
 * With the following id3 tags set
 * Artist
 * Album
 * Title
 * Year
 * 
 * 
 * Typical deployment is made of 
 * 
 * -One Clewn API server to host and serve the "free download" audio with
 * -- 'the audio' uloaded in its ./audio subdir
 * -- the api.php file at the root of the install
 * 
 * (you'll have to edit the api.php file to 
 * modify './wahtever/path/to/php-getid3'
 * to the path of your actual php-getid3 v 1.x installation(
 * 
 * -One main backend CreRo with
 * --the d/artists.txt containing a list of the label' artists, one per line
 * -- the $title, $description, $clewnapiurl 
 * and $clewnaudiurl options in ./config.php 
 * correctly set
 * 
 * 
 * 
 * 
 * 
 */






$description = 'Independent music for the third millenium. Stream and download for free. Early access to works in progress for a small fee.';
$title='<a href="./">Crem Road</a> - The record label';
$server='cremroad.com';
$clewnapiurl='http://audio.clewn.org/api.php';
$clewnaudiourl='http://audio.clewn.org/audio/';
$footerhtmlcode='
<em>This is Crem Road. Formerly C0C. Formerly ZC Virtual. Formerly Slcnc Music.</em>
<em><br/>Copyright 1997-2015 N. Chartoire. No demo submissions for now. </em>
<br/>All the music here is free or open licensed. Please refer to the "comment" tag in each audio file for details. <br/><a style="font-size:small;" href="http://github.com/shangril/crero">CreRo</a>';

$activateaccountcreation='false';
//don't change this for now


?>
