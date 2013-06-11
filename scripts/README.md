The Scripts
===========
These are scripts used to assist with thumbnails.

Each script is a command line script.

* [`findMissingAlbumArt.php`](findMissingAlbumArt.php)
* [`makeSmallCover.php`](makeSmallCover.php)
* [`getAlbumArt.php`](getAlbumArt.php)
* [`makeMontages.php`](makeMontages.php)
* [`buildSearchIndex.php`](buildSearchIndex.php)
* [`move.php`](move.php)
* [`remove_trailing_spaces.php`](remove_trailing_spaces.php)
* [`update_id3.php`](update_id3.php)

*All of these scripts should be ran from the `scripts` directory.*


`findMissingAlbumArt.php`
-------------------------
Uses UNIX commands to find any missing album art. Each directory of music should
contain `cover.jpg` which is the album art cover.

Run the script on the command line with `php findMissingAlbumArt.php`

It will output a log file - `findMissingAlbumArt.log`. This file is configurable
inside the script.


`makeSmallCover.php`
--------------------
Each directory in addition to `cover.jpg` should contain `small_cover.jpg` which is a 175x175 pixel version.

This script will create `small_cover.jpg` from `cover.jpg`.

Run the script on the command line with `php getAlbumArt.php`


`getAlbumArt.php`
-----------------
This script actually uses a program called 'coverlovin.py' to find missing album art from the Internet.
See [https://launchpad.net/coverlovin](https://launchpad.net/coverlovin)

Edit this script to point to the location of coverlovin.py. Current at: `/root/src/coverlovin/coverlovin.py`

Run the script on the command line with `php getAlbumArt.php` (possibly *root* permission required)


`makeMontages.php`
-----------------
This script creates montage.jpg and `small_montage.jpg` images in directories that contain no mp3s or oggs.
It uses the Imagemagick command `montage`, as well as the UNIX `find` command.

Currently, even though cover art shown in the web app is pulled from the ID3v2 tags, montages can only be
generated if a `cover.jpg` exists in each album.

When you run it, it will find all directories, and then for each directory it will look for `small_cover.jpg`
images. It will create a thumbnail that is a montage of 1, 4 or 9 cover images.

Run the script on the command line with `php makeMontages.php`

It also takes an optional directory if you don't want to generate all montages.

e.g. `php makeMontages.php path/to/another/directory`

Remember, this script should be ran while inside the `scripts` directory. If you provide an optional directory,
it should be a relative directory to `$defaultMp3Dir` from `lib/Config.php`.


`buildSearchIndex.php`
----------------------
This script is responsible for building the search index. Currently the search index file and location is
not configurable. The search index is named `search.db` and should live in the root of the application
adjacent to `index.php`. i.e. `$streamsRootDir` from `lib/Config.php`

Note, you must use an absolute path to both `search.db` and `files.db`. The `$curdir` variable is there
to assist.

Open [`buildSearchIndex.php`](buildSearchIndex.php) and edit `$db = "{$curdir}/../streams/search.db";`

Also edit `$fdb = "{$curdir}/../streams/files.db";` This file contains a list of all music files in your
library. It is used for the radio mode.

Note, `$fdb`, `$db` and `$filter` must point to `search.db`, `files.db` and `filter.json` respectively 
and those two files must be placed in the root of your application adjacent to `index.php`.

Run the script with `php buildSearchIndex.php` on the command line to build the index.

### Radio filters

You can also provide a filter to include or exclude files from radio play. Just include a filter
string as below. Your filter regular expressions should go in the `include` and `exclude` arrays. This
JSON string should be in a file named `filter.json` that is in the same directory as `buildSearchIndex.php`.

Copy [`scripts/example.filter.json`](scripts/example.filter.json) to `scripts/filter.json` and add your filters.

If you need to include a backslash in a regular expression, you must use two. i.e. `\\`

<pre>
{
    "filter": {
        "include": [
            "/Phish/i",
            "/The Doors/i"
        ],
        "exclude": [
            "/\\.m4a$/",
            "/\\/Bogus Directory\\//i",
            "/Christmas/",
        ]
    }
}
</pre>

Include always comes before exclude. The include and exclude arrays should contain `preg_` style regular 
expressions and should begin and end with slashes. e.g. `/^some regex$/i` and can include modifiers such 
as `i` or `g`.


`move.php`
----------
A helper script I've used to rename files. I dropped this into an album that
contained `.mp3` files. It removed hypens at the beginning of files. It could be
extended to do other things.

_Examine the script and make backups of the files before running._


`remove_trailing_spaces.php`
----------------------------
A help script that removes trailing spaces from files. Just drop it into a folder
of files and run it.

_Examine the script and make backups of the files before running._


[`update_id3.php`](update_id3.php)
----------------------------------
This script can be used to update ID3 tags. Currently you must update `$base` so that it's an absolute
directory to the root of this application.

Then you put this script in a directory that contains mp3s.

As the script stands, if an ID3v2 tag exists, it will take the basic properties - artist, album, title, track and year and
set them and strip all other tags.

This was useful to me, but the script serves as a skeleton if you need to update ID3 tags in another fashion.
