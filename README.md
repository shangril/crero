## Known bug workaround, temporarily

After install, create an empty (or filled by whatever text you want, you will be instructed on how to edit the option it drives, in your admin panel, during initial configuration) ./d/server.txt file ! Otherwise on a fresh install Admin Panel will never be reachable... Will be corrected for a release planned for Feb 2023.




## Security: 
Please note that API on any media tier (free download/Streaming only) will assume that the function returning the current date (time(); to name it) will return an up-to-date date, coherent with the "modification date" that is added to each file when uploaded on the tier. Make sure the system date of the server hasn't been moved to the past (like, 1 jan 1970, but it is just an example) or to the future. This is a concern both for uploading files and to build API cache freshness. 
# Help
Primary place for information is the #crero chatroom on the https://libera.chat IRC (Internet Relayed Chat) network. Volunteers to make IRC presence or even willing to pass around information and help people are welcome there. 
## Additional documentation
Once you have read some parts or more parts of this README file, a good additional reading, documenting interesting points for CreRo deployment for beginners, is to find in the README documentation of crero-yp (which is the YellowPage Service for CreRo instance CMS, allowing Your label to register its CreRo site to advertise in one or more YellowPage Services), especially in the following section: 
https://github.com/shangril/crero-yp/blob/main/README.md#requirement-for-crero-instance-to-be-listed-in-a-yp-server 
## Upgrading
Please note that if you got a custom ./style.css, full upgrades with any files will overwrite it. Either, make a backup before, and restore it, or, upgrade excluding the style.css file
## Multi-instance on the same server
If you plan to have several instances on the same server, you should set up a subdomain for each of them ; ie labelone.yourserver.tld ; labeltwo.yourserver.tld ; and so on. This is to prepare Syndication, then later, Federation, for which (black/white)listing of other servers will be made based on hostname, not full path. Then having different hostname will allow other instances to (black/white)list each of them at their convenience, and not all, at once, the instances you run on your server.  
# CreRo

Recommended (strongly) PHP version is PHP 8.1, but PHP 7.0 or above is mandatory and may work, but hasn't been tested for post-September 2022 releases, while PHP 8.1 was. Please refer to the "PHP 8.1 tested things" for details. Core features (a subset, but core) is TESTED and WORKING with PHP 8.2
If something fails (it can for quotes, double quotes, non-ASCII characters in either audio metadata (tags for artist, album, title, year and commment), filename of audio file stored by the server, please 1) upgrade your server to PHP 8.1 b) see "Emergency measures" section at the bottom of the document. 

CURL php extension required (likely to be installed already at least on commercial-grade hosting services)

GD php extension required if you need cover art (likely to be already installed alongsite with PHP)

GetID3 -most of what is used in it- is now included in Redist and no longer requires manual download. 

.htaccess support required in your webserver (in Apache >= 2.4 it is not enabled by default and you need to set "AllowOverride All" in your Apache host configuration in <Directory "/path/to/crero"></Directory> but most commercial hosting will have it done for you)
Please note that most default PHP installation "at home" will have their php.ini session.save-handler set to "file", which prevents the Radio (if enabled) to work properly. As a simple workaround, Session.save-handler should store the sessions in memory (see memcached and php8.1-memcache). In production environnement, your PHP will be likely already configured with memory, or databases.  

Your server(s) underlaying operating system must support gettimeofday(2) system calls, plus, system clock must be properly set to the exact time and never be changed over time like moving to an inexact date in the past or the future (such a change could lead to hasardous behaviours, like, Radio-feature DOSing bots succeeding their DOS, people reading "Fan network"-feature chat message posted prior to their chat connexion, loss of chat nicknames or messages, stall or complete fail of Radio-feature stream, API never updating their cache even after a file upload, HTMLCache (if enabled) either not expiring as set up, or, unable to recover when a page-caching while a media tier overload (empty tracklist and so on) is detected and the user is provided with a recovering mechanism, YellowPage API (used by YP services to query Crero, and for possible interinstance (syndication, federation) communication) either serving always outdated data or always requerying metadata and slowing down a lot everything, and, much, much more. So keep your time on time.   

# Quick jump

You may want to skip to the "crash courses" below in "installation steps" to get a quick overview on how to get your install up. 

# Notable milestones

* 20221010 release : Rogue media tiers no more able to RCE querying servers. No public media tier AFAIK has never been operated by anyone, exception the author of this line. If you run your own media tiers, front-end NOW requires updated api.php on each tier you operate. 

* 20221009 release : Security FIX. Affecting almost any version (20151123 or newer), please upgrade: "material things shop" SEVERE security issue. (2022/10/03 additionnal note: Please read about PHP 8.1 and tested things before enabling it)

* 20220623 release : Support for embed. Example : You got a label at cremroad.com . You got an artist, say Me In The Bath. You want to set up meinthebath.com ; somewhere in your html in meinthebath.com add an iframe with its src attribute set to the http url of your label domain, in our case cremroad.com followed by the following path : /?artist=Me+In+The+Bath&embed=Me+In+The+Bath and you are done. Make sure to escape whitespaces as + and any special caracter not allowed in a URL scheme by the %XX number needed (search for "escaping characters in HTTP GET parameters").
 Radio block redesign for something less cumbersome, also. Support for continuous (album after album) playback for embed artist sites. 

* 20220605-1 Security patch. Any version affected or almost any back to earlier ones. An unused feature in ./api.php and ./api/api.php could allow a remote attacker to access (read) any file located in the public www directory. Please update ./api.php in your front-end. If you use the "free download" feature, please replace api.php in your free media tier by ./api/api.php provided in this FIX. 


* 20220605 Security patch. All versions newer than 20190418 with htmlcache option enabled must upgrade for CRITICAL issue allowing Remote Code Execution (RCE). ./index.php modified. 

* 20210711 Security patch. All versions newer than 20200919 must upgrade to fix a security issue that affected .htaccess in /radio/e/, causing exposition of the IPs of the listeners of the radio. Upgrade and make sure you have .htaccess in /radio/e/ still present and working. 

* 20180817 release : stats rewritten. Please note that the ancient statistics you may have gathered won't now display correctly in the new stats system. If you need them for future reference, please make a backup before upgrade (ie: go to your admin pannel, select, copy, and paste elsewhere)

# As an introduction

CreRo is a CMS for record labels, and was initially written to power Crem Road records. 

Full multi tiers architecture with through a simple setup the possibility to use external services such as Clewn Audio to host your (free download) media files - or host your files on your own server, you choose. 

Paid download is very partially supported (no anti-stealing protection, see below). 

Physical release shop feature. 

Streaming only or online music free download. 

Radio stream for your catalog ; with Xiph yellowpages registration. If chat network is enabled, possibility for listeners to trigger the skipping of a particular song currently aired.  

Physical releases means free download, for now.

Chatroom allowing your fans to network with like-minded listeners. 

Sell your online digital music quick instructions: activate download_cart option. Maybe set is_download_cart_name_your_price for either, name your price no minimum, or name your price with minimum. Note that people downloading will get hotlinkable audio file links, with no auth system, and that there is no way to prevent them from passing the links around. 


New feature 16.11.30.0033 : create a subdir called "supporters" and put an index.php that you can code as you wish in it if you want to display a hall of fame of your donators. A "Our supporters" link will display then in the donation module.   

New Feature 17.03 there is now support for mp3 only catalogs (if you wish to host your audio on your own). Previously flac mp3 and ogg were all three mandatory. 

An old undocumented feature : you can code a splash.php free-form HTML/php file and it will be displayed at the top of every page of the main site (not the radio). 

# PHP 8.1 tested things

1. Streaming-only music
2. Free download music
3. Donation module
4. cover art, thumbnailer on the fly, caching of thumbnails
5. Fan Network (a.k.a online webchat for your visitors)
6. Radio module (inluding "skip song" feature if Fan Network is enabeld). Partial-only: the RadioHasYP Yellowpage registration in dir.xiph.org still needs to be tested on a live ("online and public") server as of 2022 Sept 15, and it will be done within a few day. If something goes wrong, expect a bugfix, don't expect much an update in the README 
7. last level cache (HTMLCache option in admin panel) including anti-overload & manual cache purging

Untestested things that should work but feedback is welcome, and, please, set up a server at home instead of testing these functionnalities in your production environnement
(Please also note that the CreRo yellopages directory currently shows, and since many year, only one server that has enabled public directory listing ; which is, namely, mine. So then these "production environnement" servers are quite accurately only a crowd of one. )
1. Sale of streaming only music for paid download NOW TESTED
2. Sale of physical releases and other merch bundles
3. sugar like social media icons, artist highlight block on index page (will be validated upon production deployment within a few days) NOW TESTED
4. ./splash.php to be coded as you wish and that will be include()'d on every non-radio page of the site (will be validated upon production deployment within a few days) NOW TESTED
5. Download cart. Used mandatory by point 1. Can also be used for "free download" music -fill up your cart, browse, fill fill fill, then validate card and download. More confusing to some users than straight download links on album page, which explains why it has retired from the CremRoad install finally. NOW TESTED
6. Mailing list subscritpion request. Was simply an omnipresent form inviting to enter an email address ; then a mail() was sent to MailingListOwner (an option to set in panel). My own hosting disabled mail() a while ago, but for ages, the feature had been disabled for me cause mostly used by spambot to send me random addresses (3 times in a few days, no reply from any of them), much more than by legitimate subscribers (1 people). Alt. tip : use splash.php to show a contact address and invite people to get in touch to register to your newsletter. 
7. Mixed pages ; undocumented, rarely used, useless, untested. 


# Installation steps

## Crash course ; free download albums, media files served by audio.clewn.org

1. Clone the CreRo repository.

  `git clone https://github.com/shangril/crero.git`

2. edit ./admin/config.php and change username and password

3. Deploy to your web server

4. Check yourserver.tld/admin/d . If you don't get a HTTP 403 Forbidden, this means that .htaccess directives are not applied by your web server, and you'll need an alternate way to secure any sensitive data storage dir (especiallay admin/d for the shadow password and visitors stats if stats enabled, if you use radio, radio/e to prevent exposition of the IPs of your listeners and radio/d if you use YP announcement and dont want someone to steal your YP sid, ./d to hide the email address that will receive mailing list subscription if the mailing list subscribtion is enabled, and, if you enable chatroom, network/d/ and network/e/ to protect your visitors chat privacy AND prevent remote code execution attack on your server. network/f/ used to be used for geolocated chatrooms, which are now no longer supported)

If you get and HTTP 500 Internal Server Error, your install is probably safe, but check nevertheless if yourserver.tld/admin/d/fields.txt just to make sure that you still get an HTTP 500 (because a good web server locks the whole dir and its subdirs when it cannot parse .htaccess)

5. Go to yourserver.tld/admin/ and log in

6. set the ''clewnapiurl'' and the ''clewnaudiourl'' to use audio.clewn.org as media tiers -you'll get the rights urls in the online help

7. set ''server'' config option to reflect the path of your install ; set site ''title'', site ''description'', maybe page footer... It should be enough for now

8. declare your artists list with the option called ''artists''

9. use Cover Art section to upload your cover arts. Indicate each file's corresponding album in configuration options -> covers

10. upload your audio files to audio.clewn.org (same basename flac ogg mp3 with correctly set artist, album, title, comment tags. Year is also useful, since used to sort albums on the site. Albums from a same year will be sorted by file freshness on the media tier) 


##  Hosting free download album on your own

Apply previous steps 1-5

1. A subset of GetID3 is now bundled with Crero in the Redist-LGPL directory. Separate download of GetID3 is no longer required. Please note that if you run your free download tier on a separate server, you'll need CreRo's ./Redist-LGPL and ./api at the top of your target install directory. 

2. put your audio in INSTALL ROOT/api/audio (same basename flac ogg mp3 with correct artist, album, title and comment tags)

3. update ''clewnapiurl'' to http://your server/install_path/api/api.php and ''clewnaudiourl'' to http://your server/install_path/api/audio/ (mind the trailing slash)

4. Same as previous scenario steps 8 and 9

## Provide streaming only albums

1. same GetID3 absence of download as previous scenario step 1

2. same audio upload as previous scenario step 2 but in INSTALL ROOT/z

3. create an empty index.html in this very directory if you want to prevent public listing

4. usual stuff for artist declaring and cover uploads

## Sell merch

1. providing your audio and covers are here, define your products in ''material_support_and_price''. Define ''material_shipping'', ''material_currency'', and don't forget to set **your own** paypal adress for payment routing

2. optionnal : if you wish "name your price, no minimum", set this option, otherwise it will be "name your price, with minimum"

3. declare whitlisted material artist in ''material_artist''. Maybe, blacklist some particular albums with ''material_blacklist''

## Fan network's chatrooms, label radio, and so on

1. refer to online help. 

# Understanding the system architecure

When we coded a _real_ site for Crem Road records, we already had our material available from [Clewn.org](http://clewn.org/), a free download hosting we run. Then we created a Clewn API for CremRoad (and then CreRo) to query Clewn and pull audio media files from there. 

CreRo can be used as well with Clewn which hosts its free albums as with your own free albums media tiers running on your own server. 

In both cases, the url of the api backend goes to clewnapiurl config option.

It is possible to restrict the access to albums to streaming-only. 
Please note that your audios could be *easily* stolen. 
Be sure to add a blank index.php file in `<install root>/z/` to prevent public directory listing and put your streaming-only albums audio in this directory. 

Remember that piracy actually increases sales :)

The general requirement for audio is that same basename files have to be there in flac, ogg and mp3 format, and that metadata tags should be set, especially "artist", "album", "title", "year" and "comment". 

Concerning the free download audio, if you run a local media tier for free album storage, they have to be uploaded in `<install root>/api/audio` directory.

To host free audio by yourself or to host streaming only albums, you must download and install php-getID3, which is GPLed software primarily avaiable from SourceForge.net. Extract the php-getid3 archive directory at the root of your CreRo install.

# Quite OLD ! The configuration options most commonly used - refer to admin panel for a more up-to-date list, with online help

* The server name or path to your Crero install. Examples : "(myserver.com)", ("crero.myserver.com)" or "myserver.com/path/to/crero".

`server.txt`

* Your site name. Example "The really cool label".

  `sitename.txt`

* A short title for your label. Example "The really cool label - some cool music since 2010".

  `title.txt`

* A description of about 120/140 characters used by link sharer, search engines... To sumarize your site's content. Example : 
"The Raw Sound for the banks of the Titicaca Lake. Unlimited streaming and short run vinyls and tapes".

  `description.txt`

* A list of any artist of your label, one per ligne. Can be "The Next Superband", "The Next Superband featuring Maria", etc. 
Metadata tags in the audio files must have their "artist" field matching one of this list's entries in order to be displayed by the site. 

  `artists.txt`
* This file is used to indicate which image file should be display as cover art for an album. First line must be an album name (as in metadata audio files 'album' tags), next line the filename of an image file in the ./covers/ subdirectory. Example : 
Line 1 "The Name Of The Great Album" line 2 "greatalbum.jpg" line 3 "And Another Album" line 4 "another.jpg" and so on

  `covers.txt`

* A free form html code or plain text that will be displayed at the bottom of each page, like for credits, copyright, legal, 
etc. 

  `footerhtmlcode.txt`

* Can be 0 or 1. If set to 0, no mailing list management. If set to 1, the people can submit thier email address with the "let's make friends" form. Their adresses will then be forwarded to the adress defined in mailing_list_owner.txt for manual list management purposes. 

  `activateaccountcreation.txt`

* Can be 0 or 1. If set to 0, no online chat provided. If set to 1, a chatroom will be available for visitors. 
  * Make sure that the ./htaccess denial directive of the ./network/*/ subdirectories are working : Sensitive data such as chat between your visitors may be exposed if the .htaccess directives are not working ! 
  * Make sure it works with your setup before actvating the chat. 
  * Note: all the geolocation stuff has been now removed, and geolocated chatroom ("chat with people nearby") are now no longer available, since all the instances of CreRo known, to date, never had enough visitor using the chat to have made geolocation useful, and when the Chat feature has been rewritten to use AJAX instead of HTTP-Refresh of iframes, it has not been considered useful to do the work required for geolocated rooms to work with the new Chat. 

  `activatechat.txt`

* The http url of a the media server tier used to provide free download albums. If this tier is local to your install it would be probably (http://yourserver.tld/api/api.php). If your server is somewhere else on the internet, you already know what to indicateas example if you use 
Clewn.org for free audio hosting, it will be the one indicated in the online help.

  `clewnapiurl.txt`

* The basepath of the directory containing audio files on the free album media server tier. Like http://yourserver/api/audio/ (mind the trailing slash) if you run your own, or the one indicated in the online help for an install using Clewn.org for free hosting.

  `clewnaudiourl.txt`

* If you got a video tier installed on your server, indicate here its api url, like (http://myserver.tld/video/api.php).

  `videoapiurl.txt`

* If you got a video tier installed on your server, indicate here its media file directory basepath url, like 
(http://myserver.tld/video/audio/).

  `videourl.txt`

* An email adress to which mailing list subscription requests will be forwared. Useful only if activateaccountcreation is set. 
  `mailing-list-owner.txt`

* Which of your artists should be whitelisted as having material releases for sale. One per line. 

  `material_artists.txt`

* Which particular albums should be blacklisted and not available as material releases. One per line. 

  `material_blacklist.txt`

* The base, three letter currency code used for money transfers. Example : EUR or USD or even JPY.

  `material_currency.txt`

* The email address of the paypal account to where payments will be sent.

  `material_paypal_address.txt`

* A file defining shipping zones and shipping price, per item ordered, in the globally configured currency. Example : first 
line "France", next line "1.80", third line "Europe", next line "2.60", fifth line "Rest of the world", next line "3.40".

  `material_shipping.txt`

* A file defining which products you do sell. Each produce is defined in a four lines block, each block goes after each other. Line 1 : the product name, ex "Standard CD", line 2, the product description, ex "An homeburnt CD-R with printed rear and 
front cover in a 120 micron flexible transparent jacket", line 3, the product price expressed in the globaly configured shop 
currency. Ex : "3.80". Line 4, the options of the products, separated by spaces. Ex : "S M L XL XXL". If the product has no 
option, leave a blank line. Line 5 : the name of the second product, line 5 : the second's product description, etc.

  `material_supports_and_prices.txt`

* Your legal sales terms and conditions.

  `materialreleasessalesagreement.txt`

* A free html block to insert whatever you want in a banner on top of the material release list, like a set of external links to other online shops where your products can be found, or special custom subsection of the shop you may have created. 

  `materialmenu.txt`

# Emergency measures for strange behavior (non playing track, undownloable track, missing artist, missing album, additionnal "info+" link not displaying anything) upon strange characters -"strange" includes single quotes- in audio metadata (artist name, album title, track title, description field, year) or in the audio file name itself. 

* Upgrade your server to a version of PHP supported and recommended by CreRo
* delete any .dat file excepting ./admin/d/pwd.dat in the following places :
.dat files at the root of your crero install ; .dat files in ./api/ directory ;.dat files in ./crero-yp-api-cache/ if this directory is present at the root of your install ; .dat file in the ./radio/d/ ./radio/e/ and ./radio/f ; ALSO FOR RADIO any .txt file in these same d, e, and f directories. This yould be enough. ./network/ (d/ e/ f/) may have strange behaviors like misformed quotes but for no longer than 100 minutes. 

Make sure that the underlying file system on your media hosting server(s) supports utf-8 character encoding. If not, avoid any non-latin character in filenames. 
