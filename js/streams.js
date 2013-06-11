/*
Copyright 2013 Weldon Sams

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

function toggleMusicOn(url) {
    if ($(".m3uplayer").length > 0 && url == $("#theurl").data("url")) {
        if ($(".jp-playlist ul li").length > 0) {
            if ($("#playbutton").html() == "Play") {
                $(".jp-play").click();
                $("#playbutton").html("Pause");
            } else {
                $(".jp-pause").click();
                $("#playbutton").html("Play");
            }
        }
    } else {
        createPlaylistJs(url);
    }
}

function createPlaylistJs(url) {
    isRadioMode = false;
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=createPlaylistJs&dir=" + encodeURIComponent(url),
        success: function(html){
            var width = $("#content").width();
            $("#content-player").html(html);

            // Currently the player only works in iPhone with the Chrome browser.
            // We remove this playlist because it is not functional while playing.
            if (isMobile && isMobile()) {
                $("#musicindex").remove();
                $("#playercontrols").remove();
                var newwidth = width - 16;
                //alert('newwidth = ' + newwidth);
                $("#mediaplayer_wrapper").css("width", newwidth + "px");
            }
            hideWorking();
        }
    });
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=openDir&url=" + encodeURIComponent(url) + "&dir=" + encodeURIComponent(url),
        success: function(html){
            $("#content").html(html);
            hideWorking();
        }
    });
}

function init() {
    var hash = window.location.hash;
    hash = hash.replace(/^#/, "");
    var hashVars = hash.split("/");
    switch(hashVars[1]) {
        case "open":
            var dir = decodeURIComponent(hashVars[2]);
            openDir(dir);
            break;
        default:
            var doNothing = true;
    }
}

function search(q) {
    if (q.length < 3) {
        return false;
    }
    if ($("#playbutton").length > 0) {
        var p = $("#playbutton").parent();
        p.remove();
    }
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=search&q=" + encodeURIComponent(q),
        success: function(html){
            $("#musicindex").html(html);
            hideWorking();
        }
    });
}

function displayWorking() {
    $("#loading").css("display", "block").css("visibility", "visible");
}

function hideWorking() {
    $("#loading").css("display", "none").css("visibility", "hidden");
}

function clearPlaylist(e, thiz) {
    $.getJSON("ajax.php?action=clearPlaylist", function(json) {
        myPlaylist.remove();
    });
}

function addToPlaylist(e, thiz) {
    var event = e || window.event;
    displayWorking();
    var type = $(thiz).data("type");
    var action = "";
    if (type == "dir") {
        action = "addToPlaylist";
    } else if (type == "file") {
        action = "addToPlaylistFile";
    }
    $.getJSON("ajax.php?action=" + action + "&dir=" + encodeURIComponent($(thiz).data("dir")) 
            + "&file=" + encodeURIComponent($(thiz).data("file")), function(json){
        $(json).each(function(i, audioFile) {
            myPlaylist.add(audioFile);
        });
        $(".album-title").text("Custom playlist");
        hideWorking();
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

function logout(e) {
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=logout",
        success: function(html){
            hideWorking();
            location.href="index.php";
        }
    });
}

function playRadio(e) {
    isRadioMode = true;
    displayWorking();
    var cfg = new StreamsConfig();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=playRadio&num=" + cfg.numberOfRadioItems,
        success: function(html){
            var width = $("#content").width();
            $("#content-player").html(html);
            $(".album-title").text("Radio");
            $("#playbutton").remove();

            // Currently the player only works in iPhone with the Chrome browser.
            // We remove this playlist because it is not functional while playing.
            if (isMobile && isMobile()) {
                var musicIndex = $("#musicindex");
                $("#playercontrols").remove();
                var newwidth = width - 16;
                $("#mediaplayer_wrapper").css("width", newwidth + "px");
                var playerTop = $("#mediaplayer").offset().top;
                window.scrollTo(0, playerTop);
            }

            // After each song plays, remove the first song.
            $("#mediaplayer").bind($.jPlayer.event.play, function(event) {
                // TODO: This is kind of a bug, but shouldn't happen with normal usage.
                //       If you click the last item in the list, after it's done, the player will stop.
                console.log("bind: Playlist size: " + myPlaylist.playlist.length + ", Current position: " + myPlaylist.current);
            }).bind($.jPlayer.event.ended, function(event) {
                var current = myPlaylist.current;
                myPlaylist.remove(current - 1);
                $.getJSON("ajax.php?action=getRandomPlaylist&num=1", function(json){
                    $(json).each(function(i, audioFile) {
                        myPlaylist.add(audioFile);
                    });
                });
            });

            hideWorking();
        }
    });
}

isRadioMode = false;
$(document).ready(function(){
    init();

    if ($("#content-player").length > 0 && $(".m3uplayer").length > 0) {
        $("#playbutton").html("Pause");
    }
    
    $(document).on("click", "#playbutton", function() {
        toggleMusicOn($(this).data('url'));
    });

    $(document).on("click", ".jp-play", function() {
        $("#playbutton").html("Pause");
    });

    $(document).on("click", ".jp-pause,.jp-stop", function() {
        $("#playbutton").html("Play");
    });

    $(document).on("click", ".droplink", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".dirlink", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".dirlinkcover", function() {
        openDir($(this).data('url'));
    });

    $(document).on("click", ".addtoplaylist", function(e) {
        addToPlaylist(e, this);
    });

    $(document).on("click", ".jp-clear-playlist", function(e) {
        clearPlaylist(e, this);
    });

    $(document).on("click", "#logout-link", function(e) {
        logout(this);
    });

    $(document).on("click", "#radio-button", function(e) {
        playRadio(e);
    });

    prevtime = parseInt(new Date().getTime());
    // Waits 500 milliseconds before performing search.
    curval = "";
    t = null;
    $(document).on("keyup", "#search", function() {
        var cfg = new StreamsConfig();
        curval = $(this).val();
        curtime = parseInt(new Date().getTime());
        next = prevtime + cfg.searchThreshold;
        prevtime = curtime;
        if (curtime < next) {
            clearTimeout(t);
            t = setTimeout("search('" + curval + "')", cfg.searchThreshold);
            return;
        }
    });
    
    // This allows the playlist to load with cover art in a non-blocking manner.
    $(".playlist-albumart").livequery(function() {
        var thiz = $(this);
        if (!thiz.data("done")) {
            $.getJSON("ajax.php?action=getAlbumArt&dir=" + encodeURIComponent(thiz.data("dir"))
                    + "&file=" + encodeURIComponent(thiz.data("file")), function(json) {
                thiz.attr("src", json['albumart']);
                thiz.data("done", true).css("width", "2em").css("height", "2em");
            });
        }
    });

});
