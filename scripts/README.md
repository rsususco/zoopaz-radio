The Scripts
===========
These are scripts used to assist with thumbnails.

Each script is a command line script.

* `findMissingAlbumArt.php`
* `makeSmallCover.php`
* `getAlbumArt.php`
* `getMontage.php`
* `buildSearchIndex.php`


`findMissingAlbumArt.php`
-------------------------
Uses UNIX commands to find any missing album art. Each directory of music should
contain `cover.jpg` which is the album art cover.

Run the script on the command line with `php findMissingAlbumArt.php`

It will output a log file - `findMissingAlbumArt.log`


`makeSmallCover.php`
--------------------
Each directory in addition to `cover.jpg` should contain `small_cover.jpg` which is a 175x175 pixel version.

This script will create `small_cover.jpg` from `cover.jpg`.

Run the script on the command line with `php getAlbumArt.php`


`getAlbumArt.php`
-----------------
This script actually uses a program called 'coverlovin.py' to find missing album art from the Internet.
See https://launchpad.net/coverlovin

Edit this script to point to the location of coverlovin.py. Current at: `/root/src/coverlovin/coverlovin.py`

Run the script on the command line with `php getAlbumArt.php`


`makeMontage.php`
-----------------
This script creates montage.jpg and `small_montage.jpg` images in directories that contain no mp3s or oggs.
It uses the Imagemagick command `montage`, as well as the UNIX `find` command.

You copy this script into your top level images directory along with the white.jpg image.

When you run it, it will find all directories, and then for each directory it will look for `small_cover.jpg`
images. It will create a thumbnail that is a montage of 1, 4 or 9 cover images.

Run the script on the command line with `php makeMontage.php`

It also takes an optional directory if you don't want to generate all montages.

e.g. `php makeMontage.php path/to/another/directory`


`buildSearchIndex.php`
----------------------
This script is responsible for building the search index. Currently the search index file and location is
not configurable. The search index is named `search.db` and should live in the root of the application
adjacent to `index.php`.

Open `buildSearchIndex.php` and edit `$db = "{$curdir}/../streams/search.db";`

You place `buildSearchIndex.php` in the root of your music directory and point `$db` to the root of your
streams install.

Just run the script with `php buildSearchIndex.php` on the command line to build the index.
