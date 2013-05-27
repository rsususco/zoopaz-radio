INSTALL
=======

Your mp3s or oggs should be stored in a web accessible directory - it does
not have to be inside this application. But this application and your music
archive must be on the same machine. The application should have read access
to your music archive.

See config.php, but your music will live in `$defaultMp3Dir`. This directory
should contain any level of sub-directories, but at least one more level
deep. e.g. If `$defaultMp3Dir='/var/www/mymusic'`, then put your music in
`/var/www/mymusic/myalbum`. e.g. `/var/www/mymusic/Rock/TheDoors/L.A.Woman/*.mp3`

Edit `config.php`.

See `scripts/README` for information on cover art. Each album can have a cover
art image. The directory of music should contain two images,
`cover.jpg` and `small_cover.jpg`

Edit `auth.php`, or disable.

This is a very simple authentication mechanism. It contains an array of username and password combinations.
Just comment out `require_once("auth.php")` in `streams.php` if you want to turn it off.
By default after 6 unsuccessful tries you get locked out.

See `scripts/README.md` for information on setting up the search index.

Open this application in a web browser and begin streaming your music.
