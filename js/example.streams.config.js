function StreamsConfig() {
}

// The number of items in the radio playlist.
StreamsConfig.prototype.numberOfRadioItems = 10;

// The number of milliseconds to wait before performing a search after keyup events.
StreamsConfig.prototype.searchThreshold = 1000;

// Set to true if you want the player to timeout after X amount of seconds.
// Set StreamsConfig.playTimeout to X amount of seconds.
StreamsConfig.prototype.usePlayTimeout = true;

// The number of seconds to play before timing out. e.g. 3600 = 1 hour
StreamsConfig.prototype.playTimeout = 3600;
