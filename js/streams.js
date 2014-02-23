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
                $("#mediaplayer_wrapper").css("width", newwidth + "px");
            }
            hideWorking();
        }
    });
}

function openMyRadio() {
    location.hash = "#/myradio";
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=openMyRadio",
        success: function(html){
            handleLogoutHtml(html);
            var controls = $("#playercontrols");
            if (controls.size() > 0) {
                controls.remove();
            }
            $("#musicwrapper").html(html);
            hideWorking();
        }
    });
}

function getHomeNavigation() {
    if ($("#navlist .previousDirectoryListItem .filesize_type .dirlink").size() < 1) {
        displayWorking();
        $.ajax({
            type: "GET",  
            url: "ajax.php",  
            data: "action=getHomeNavigation",
            success: function(html){
                handleLogoutHtml(html);
                $("#navlist").html(html);
                hideWorking();
            }
        });
    }
}

function openDir(url) {
    location.hash = "#/open/" + encodeURIComponent(url);
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=openDir&url=" + encodeURIComponent(url) + "&dir=" + encodeURIComponent(url),
        success: function(html){
            handleLogoutHtml(html);
            if (/^[\],:{}\s]*$/.test(html.replace(/\\["\\\/bfnrtu]/g, '@')
                    .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
                    .replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
                var json = JSON.parse(html);
                if (json['is_logged_in']) {
                    $("#content").html(html);
                }
            } else {
                $("#content").html(html);
            }
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
        case "myradio":
            getHomeNavigation();
            openMyRadio();
            break;
        case "viewstations":
            getHomeNavigation();
            viewMyRadio();
            break;
        case "loadstation":
            getHomeNavigation();
            var station = decodeURIComponent(hashVars[2]);
            loadRadio(station);
            break;
        default:
            var doNothing = true;
    }
}

function search(q) {
    if (q.length < 3) {
        return getHomeIndex();
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
            handleLogoutHtml(html);
            if ($("#musicindex").size() < 1) {
                $("#musicwrapper").prepend("<ul id=\"musicindex\"></ul>");
            }
            $("#musicindex").html(html);
            hideWorking();
        }
    });
}

function getHomeIndex() {
    displayWorking();
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=getHomeIndex",
        success: function(html){


//            TODO: Make this a JSON variable that contains navlist and musicindex.
//                  getHomeIndex() in ajax.php should return a JSON object.
//
//                  Look in,
//                  public function getFileIndex ($dir) {
//
//                  We also need to show Home when performin openDir("/"). This is
//                  when we're just logging in. Basically more than one case.


            handleLogoutHtml(html);
            $("#navlist").html(html);
            $("#musicindex").html(html);
            hideWorking();
        }
    });
}

function displayWorkingMsg(msg) {
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
        handleLogoutJson(json);

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

function isJson(input) {
    if (!input.match(/^\s*{/)) {
        return false;
    }
    if (!input.match(/^\s*\[/)) {
        return false;
    }
    if (input.match(/^\s*$/)) {
        return false;
    }
    return true;
}

function handleLogoutHtml(html) {
    if (!isJson(html)) {
        return false;
    }
    var json = JSON.parse(html);
    handleLogoutJson(json);
}

function handleLogoutJson(json) {
    if (undefined === json.is_logged_in) {
        return false;
    }
    if (!json.is_logged_in) {
        logout();
    }
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
            handleLogoutHtml(html);

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

currentMyRadioStation = null;
function playMyRadio(station) {
    currentMyRadioStation = station;
    isRadioMode = true;
    displayWorking();
    var cfg = new StreamsConfig();
    $.getJSON("ajax.php?action=startPersonalRadio&num=" 
            + cfg.numberOfRadioItems + "&station=" 
            + encodeURIComponent(station), function(json){
        handleLogoutJson(json);

        var width = $("#content").width();
        $("#content-player").html(json['contentPlayer']);
        $(".album-title").text("My Radio");
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
            console.log("Music has ended, changing tracks.");
            var current = myPlaylist.current;
            myPlaylist.remove(current - 1);
            // Playing a personal radio station. Must get the station from the URL.
            var stationQS = "";
            if (currentMyRadioStation != null) {
                stationQS = "&station=" + currentMyRadioStation;
            }
            $.getJSON("ajax.php?action=getRandomPlaylist&num=1&personal=yes" + stationQS, function(json){
                $(json).each(function(i, audioFile) {
                    myPlaylist.add(audioFile);
                });
            });
        });

        hideWorking();
    });
}

function createPersonalRadio(e, thiz) {
    var event = e || window.event;
    isRadioMode = true;
    displayWorking();
    var dir = $(thiz).data("dir");
    var cfg = new StreamsConfig();
    $.getJSON("ajax.php?action=createPersonalRadio&dir=" 
            + encodeURIComponent(dir) + "&num=" + cfg.numberOfRadioItems, function(json){
        handleLogoutJson(json);

        var width = $("#content").width();
        $("#content-player").html(json['contentPlayer']);
        $(".album-title").text("Radio " + dir);
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
            $.getJSON("ajax.php?action=getRandomPlaylist&num=1&personal=yes", function(json){
                $(json).each(function(i, audioFile) {
                    myPlaylist.add(audioFile);
                });
            });
        });

        hideWorking();
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

function removeFromPersonalRadio(e, thiz) {
    var event = e || window.event;
    displayWorking();
    var dir = $(thiz).data("dir");

    var hash = window.location.hash;
    hash = hash.replace(/^#/, "");
    var hashVars = hash.split("/");
    var station = "";
    switch(hashVars[1]) {
        case "loadstation":
            station = decodeURIComponent(hashVars[2]);
        default:
            var doNothing = true;
    }

    $.getJSON("ajax.php?action=removeFromPersonalRadio&dir=" 
            + encodeURIComponent(dir) + "&station=" 
            + encodeURIComponent(station), function(json){
        handleLogoutJson(json);

        var hash = window.location.hash;
        hash = hash.replace(/^#/, "");
        var hashVars = hash.split("/");
        switch(hashVars[1]) {
            case "myradio":
                $(thiz).parent().parent().parent().parent().remove();
            case "loadstation":
                $(thiz).parent().parent().parent().parent().remove();
            default:
                $(thiz).parent().replaceWith(json['button']);
        }

        hideWorking();
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

function addToPersonalRadio(e, thiz) {
    var event = e || window.event;
    displayWorking();
    var dir = $(thiz).data("dir");
    $.getJSON("ajax.php?action=addToPersonalRadio&dir=" 
            + encodeURIComponent(dir), function(json){
        $(thiz).parent().replaceWith(json['button']);
        handleLogoutJson(json);
        hideWorking();
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

pt = null;

function playTimeout() {
    clearTimeout(pt);
    $(".jp-pause").click();
    $("#playbutton").html("Play");
    var c = confirm("Are you still listening?");
    if (c) {
        $(".jp-play").click();
        $("#playbutton").html("Pause");
        setPlayTimeout();
    }
    return false;
}

function setPlayTimeout() {
    var cfg = new StreamsConfig();
    if (!cfg.usePlayTimeout) {
        return false;
    }
    pt = setTimeout("playTimeout()", parseInt(cfg.playTimeout) * 1000);
}

function clickSaveMyRadio() {
    if (saveDialog) {
        hideSaveRadioDialog();
    } else {
        showSaveRadioDialog();
    }
}

function showSaveRadioDialog() {
    var dialog = $("#save-radio-dialog");
    saveDialog = true;
    dialog.css("visibility", "visible").css("display", "block");
}

function hideSaveRadioDialog() {
    var dialog = $("#save-radio-dialog");
    saveDialog = false;
    dialog.css("visibility", "hidden").css("display", "none");
}

function saveMyRadio() {
    var name = $("#save-radio-name").val();
    displayWorking();
    $.getJSON("ajax.php?action=saveMyRadio&name=" 
            + encodeURIComponent(name), function(json){
        if (json['status'] === "ok") {
            hideSaveRadioDialog();
            //alert(json['message']);
        } else if (json['status'] === "error") {
            alert(json['message']);
        } else {
            alert("An unexpected error has occurred. Could not save radio.");
        }
        handleLogoutJson(json);
        hideWorking();
    });
}

function viewMyRadio() {
    location.hash = "#/viewstations";
    displayWorking();
    $.getJSON("ajax.php?action=viewMyRadio", function(json){
        if (json['status'] === "ok") {
            $("#musicwrapper").html(json['html']);
        } else if (json['status'] === "error") {
            alert(json['message']);
        } else {
            alert("An unexpected error has occurred. Could not save radio.");
        }
        handleLogoutJson(json);
        hideWorking();
    });
}

function loadRadio(station) {
    location.hash = "#/loadstation/" + station;
    displayWorking();
    $.getJSON("ajax.php?action=loadStation&station=" 
            + encodeURIComponent(station), function(json){
        if (json['status'] === "ok") {
            $("#musicwrapper").html(json['html']);
        } else if (json['status'] === "error") {
            alert(json['message']);
        } else {
            alert("An unexpected error has occurred. Could not save radio.");
        }
        handleLogoutJson(json);
        hideWorking();
    });
}

function loadMyRadio(e, thiz) {
    var station = $(thiz).data("station");
    location.hash = "#/loadstation/" + station;
    displayWorking();
    $.getJSON("ajax.php?action=loadStation&station=" 
            + encodeURIComponent(station), function(json){
        if (json['status'] === "ok") {
            $("#musicwrapper").html(json['html']);
        } else if (json['status'] === "error") {
            alert(json['message']);
        } else {
            alert("An unexpected error has occurred. Could not save radio.");
        }
        handleLogoutJson(json);
        hideWorking();
    });
}

function removeRadioStation(e, thiz) {
    var event = e || window.event;
    var station = $(thiz).data("station");
    displayWorking();
    $.getJSON("ajax.php?action=removeRadioStation&station=" 
            + encodeURIComponent(station), function(json){
        if (json['status'] === "ok") {
            $(thiz).parent().remove();
        } else if (json['status'] === "error") {
            alert(json['message']);
        } else {
            alert("An unexpected error has occurred. Could not save radio.");
        }
        handleLogoutJson(json);
        hideWorking();
    });
    event.stopPropagation();
    event.stopImmediatePropagation();
    event.cancelBubble = true;
    return false;
}

function getHashValue(action) {
    console.log("action=" + action);
    var hash = window.location.hash;
    hash = hash.replace(/^#/, "");
    var hashVars = hash.split("/");
    switch(hashVars[1]) {
        case action:
            return decodeURIComponent(hashVars[2]);
            break;
        default:
            return false;
    }
}

isRadioMode = false;
$(document).ready(function(){
    init();

    //$(".showtooltip").tooltip();

    $("#find-music-input").livequery(function() {
        $("#find-music-input").autocomplete({
            source: "ajax.php?action=suggest",
            minLength: 2,
            delay: 100,
            autoFocus: false,
            html: true,
            select: function(event, ui) {
                var dir = ui.item.dir;
                displayWorking();
                var station = getHashValue("loadstation");
                $.getJSON("ajax.php?action=addToRadioStation&dir=" 
                        + encodeURIComponent(dir) + "&station="
                        + encodeURIComponent(station), function(json){
                    if (json['status'] === "ok") {
                        $("#radio-station-wrapper").prepend(json['html']);
                    } else if (json['status'] === "error") {
                        alert(json['message']);
                    } else {
                        alert("An unexpected error has occurred. Could not save radio.");
                    }
                    handleLogoutJson(json);
                    hideWorking();
                });
            }
        });
    });

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

    $(document).on("click", "#my-radio-button", function(e) {
        getHomeNavigation();
        openMyRadio();
    });

    $(document).on("click", "#play-my-radio", function(e) {
        var hash = window.location.hash;
        hash = hash.replace(/^#/, "");
        var hashVars = hash.split("/");
        switch(hashVars[1]) {
            case "myradio":
                playMyRadio(null);
            case "loadstation":
                var station = decodeURIComponent(hashVars[2]);
                playMyRadio(station);
                break;
            default:
                var doNothing = true;
        }
    });

    $(document).on("click", "#radio-button", function(e) {
        playRadio(e);
    });

    $(document).on("click", ".createradiobutton", function(e) {
        createPersonalRadio(e, this);
    });

    $(document).on("click", ".addtoradiobutton", function(e) {
        addToPersonalRadio(e, this);
    });

    $(document).on("click", ".removefromradiobutton", function(e) {
        removeFromPersonalRadio(e, this);
    });

    saveDialog = false;
    $(document).on("click", "#save-my-radio", function(e) {
        clickSaveMyRadio();
    });

    $(document).on("click", "#save-radio-button", function(e) {
        saveMyRadio();
    });

    $(document).on("click", "#view-my-radio-stations-button", function(e) {
        viewMyRadio();
    });

    $(document).on("click", ".radio-station", function(e) {
        loadMyRadio(e, this);
    });

    $(document).on("click", ".radio-station-remove", function(e) {
        var c = confirm("Are you sure you want to remove this station?");
        if (!c) {
            return false;
        }
        removeRadioStation(e, this);
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
