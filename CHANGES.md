Changes
=======

1.11.0 to 1.12.0
----------------
* Automatic timeout of player. Configurable in [`js/streams.config.js`](js/streams.config.js).

1.10.0 to 1.11.0
----------------
* Personalized radio.
* Persistant Home button.
* Refactored `buildSearchIndex.php` into into `StreamsSearchIndexer` and updated `buildSearchIndex.php` accordingly. Also added unit tests.

1.9.0 to 1.10.0
---------------
* Handle logout after session timesout or is logged out from another tab and an action is performed.
* Fixed a bug when parsing HTML as JSON.
* Fixed bug in unit tests - missing some Config parameters.
* After searching, if you erase a search the main index returns. More work to be done.
* Login page style update.

1.8.0 to 1.9.0
--------------
* Support for ID3v1.
* Added script that assists with setting ID3 tags. [`scripts/update_id3.php`](scripts/update_id3.php)
* Search supports multiple quoted strings and they appear first in the list.

1.7.4 to 1.8.0
--------------
* Added [`js/example.streams.config.js`](js/example.streams.config.js) with variable for number of radio playlist items.
* Window scrolls to radio player when using a mobile device. Addresses a bug when at home you play radio and loose directory navigation.
* Added example configuration files so that on upgrades settings are blown away accidentally.
