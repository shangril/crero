# CreRo
CreRo is a CMS for record labels, and was initially written to power Crem Road records. 
Paid download is not supported yet. 
Physical release shop feature. 
Streaming only or online music free download. 
Physical releases means free download, for now.

# New feature 16.11.30.0033 : create a subdir called "supporters" and put an index.php that you can code as you wish in it if you want to display a hall of fame of your donators. A "Our supporters" link will display then in the donation module.   

# Installation steps

## Crash course ; free download albums, media files served by audio.clewn.org

1. Clone the CreRo repository.

  `git clone https://github.com/shangril/crero.git`

2. edit ./admin/config.php and change username and password

3. Deploy to your web server

4. Check yourserver.tld/admin/d . If you don't get a HTTP 403 Forbidden, this means that .htaccess directives are not applied by your web server, and you'll need an alternate way to secure any sensitive data storage dir (especillay network/d/ e/ and f/ if you enable geolocated chats for your visitors)

5. Go to yourserver.tld/admin/ and log in

6. set the ''clewnapiurl'' and the ''clewnaudiourl'' to use audio.clewn.org as media tiers -you'll get the rights urls in the online help

7. set ''server'' config option to reflect the path of your install ; set site ''title'', site ''description'', maybe page footer... It should be enough for now

8. declare your artists list with the option called ''artists''

9. use Cover Art section to upload your cover arts. Indicate each file's corresponding album in configuration options -> covers

10. upload your audio files (same basename flac ogg mp3 with correctly set artist, album, title, comment tags) to audio.clewn.org


##  Hosting free download album on your own

Apply previous steps 1-5

1. download php-getid3/ from sourceforge.net (1.x version should always work) and put it at INSTALL ROOT/php-getid3

2. put your audio in INSTALL ROOT/api/audio (same basename flac ogg mp3 with correct artist, album, title and comment tags)

3. update ''clewnapiurl'' to http://your server/install path/api/api.php and ''clewnaudiourl'' to http://your server/install path/api/audio/ (mind the trailing slash)

4. Same as previous scenario steps 8 and 9

## Provide streaming only albums

1. same id3 download as previous scenario step 1

2. same audio upload as previous scenario step 2 but in INSTALL ROOT/z

3. create an empty index.html in this very directory if you want to prevent public listing

4. usual stuff for artist declaring and cover uploads

## Sell merch

1. providing your audio and covers are here, define your products in ''material_support_and_price''. Define ''material_shipping'', ''material_currency'', and don't forget to set **your own** paypal adress for payment routing

2. optionnal : if you wish "name your price, no minimum", set this option, otherwise it will be "name your price, with minimum"

3. declare whitlisted material artist in ''material_artist''. Maybe, blacklist some particular albums with ''material_blacklist''

## Fan network's chatrooms, label radio, and so on

1. refer to online help. Note that a bug in FF48 prevent your user to indicate their HTML5 geoloc currently (august 2016)

# Understanding the system architecure

When we coded a _real_ site for Crem Road records, we already had our material available from [Clewn.org](http://clewn.org/), a free download hosting we run. Then we created a Clewn API for CremRoad (and then CreRo) to query Clewn and pull audio media files from there. 

CreRo can be used as well with Clewn wich hosts its free albums as with your own free albums media tiers running on your own server. 

In both cases, the url of the api backend goes to clewnapiurl config option.

It is possible to restrict the acces to albums to streaming-only. 
Please note that your audios could be *easily* stolen. 
Be sure to add a blank i	ndex.php file in `<install root>/z/` to prevent public directory listing and put your streaming-only albums audio in this directory. 

Remember that piracy actually increases sales :)

The general requirement for audio is that same basename files have to be there in flac, ogg and mp3 format, and that metadata tags should be set, especially "artist", "album", "title", "year" and "coment". 

Concerning the free download audio, if you run a local media tier for free album storage, they have to be uploaded in `<install root>/api/audio` directory.

To host free audio by yourself or to host streaming only albums, you must download and install php-getID3, which is GPLed software primarily avaiable from SourceForge.net. Extract the php-getid3 archive directory at the root of your CreRo install.

# The configuration options most commonly update - refer to admin panel for a more up-to-date liste with online help

* The server name or path to your Crero install. Examples : "(myserver.com)", ("crero.myserver.com)" or "myserver.com/path/to/crero".

`server.txt`

* Your site name. Example "The really cool label".

  `sitename.txt`

* A short title for your label. Example "The really cool label - some cool music since 2010".

  `title.txt`

* A description of about 120/140 characters used by link sharer, search engines... To sumarize your site's content. Example : 
"The Raw Sound for the banks of the Titicaca Lake. Unlimited streaming and short run vynils and tapes".

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

* Can be 0 or 1. If set to 0, no online chat provided. If set to 1, a chatroom with geolocation features will be available for visitors. 
  * Make sure that the ./htaccess denial directive of the ./network/*/ subdirectories are working : Sensitive data such as geolocation may be exposed if the .htaccess directives are not working ! 
  * Make sure it works with your setup before actvating the chat. 

  `activatechat.txt`

* The http url of a the media server tier used to provide free download albums. If this tier is local to your install it would be probably (http://yourserver.tld/api/api.php). If your server is somewhere else on the internet, as example if you use 
Clewn.org for free audio hosting, it will be (http://audio.clewn.org/api.php).

  `clewnapiurl.txt`

* The basepath of the directory containing audio files on the free album media server tier. Like http://yourserver/api/audio if you run your own, or http://audio.clewn.org/audio for an install using Clewn.org for free hosting.

  `clewnaudiourl.txt`

* If you got a video tier installed on your server, indicate here its api url, like (http://myserver.tld/video/api.php).

  `videoapiurl.txt`

* If you got a video tier installed on your server, indicate here its media file directory basepath url, like 
(http://myserver.tld/video/audio).

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

* A free html block to insert whatever you want in a banner on top of the material relase list, like a set of external links to other online shops where your products can be found, or special custom subsection of the shop you may have created. 

  `materialmenu.txt`

* Not sure it is used. For information about vid integration refer to `<install root>/config.php`.
  `featured_vids.txt`
